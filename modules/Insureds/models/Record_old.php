<?php

 /* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): DOT Systems
 * *********************************************************************************** */

/**
 * Class Insureds_Record_Model.
 */
class Insureds_Record_Model extends Vtiger_Record_Model
{
  /**
   * Get County for Insured's Address with the use of Azure service
   * Save County in Insured.County
   * For each Claim related to Insured try to set Claim.County.
   *     If Claim.County is empty, set Claim.County = Insured.County
   *     If Claim.County <> Insured.County, send a Notification to Claim.Assigned to, Claim.Underwriter and Claim.Approver (without duplicates):
   *     "Important notice about County" - "A County for Claim claim id with link is inconsistent with Insured's County"
   *     If Claim.County = Insured.County, do nothing
   * For each Case related to Insured try to set Case.County.
   *     If Case.County is empty, set Case.County = Insured.County
   *     If Case.County <> Insured.County, send a Notification to Case.Assigned to, Case.Case Manager and Case.Attorney (without duplicates):
   *     "Important notice about County" - "A County for Case Case id with link is inconsistent with Insured's County"
   *     If Case.County = Insured.County, do nothing
   *
   * @return int|null County id
   */
  public function findCounty() {
    $id = $this->getId();
    
		\App\Log::warning("Insureds::findCounty:$id");

    $address = "{$this->get('street')} {$this->get('zip')} {$this->get('city')} {$this->get('state')}";

    $url = "https://atlas.microsoft.com/search/address/json?api-version=1.0&countrySet=US&limit=1&query=$address";
    $requestOptions = \App\RequestHttp::getOptions();
    $requestOptions['headers']['Authorization'] = \App\Config::api('azureAddressKey');

    \App\Log::warning("Insureds::findCounty:$url");

    $result = (new \GuzzleHttp\Client($requestOptions))->get($url, ['timeout' => 20, 'connect_timeout' => 10]);
    if (200 == $result->getStatusCode()) {
      $response = $result->getBody();
    } else {
      throw new \Exception("Azure Address request failed: {$result->getStatusCode()}/{$result->getReasonPhrase()}");
    }

    \App\Log::warning("Insureds::findCounty:$response");

    $json = \App\Json::decode($response);

    if ($json['summary']['numResults'] < 1) {
      // error, not found
      throw new \Exception("Could not geolocate address '$address'");
    } else {
      $countyName = $json['results'][0]['address']['countrySecondarySubdivision'];

      // find county in Counties
      $county = (new \App\QueryGenerator('Counties'))
        ->setField('id')
        ->addCondition('county', $countyName, 's')
        ->createQuery()
        ->scalar();

      if (empty($county)) {
        throw new \Exception("County $countyName not found in Counties dictionary");
      }

      // set whereever
      $this->set('county', $county);
      $this->save();

      $claims = VTWorkflowUtils::getAllRelatedRecords($this, 'Claims');
      $cases = VTWorkflowUtils::getAllRelatedRecords($this, 'Cases');
      $texasCases = VTWorkflowUtils::getAllRelatedRecords($this, 'TexasCases');

      foreach ($claims as $claimRow) {
        $claim = Vtiger_Record_Model::getInstanceById($claimRow['id']);

        if (empty($claim->get('county'))) {
          $claim->set('county', $county);
          $claim->save();
        } else if ($claim->get('county') != $county) {
          VTWorkflowUtils::createNotification(
            $claim, 
            'Claims', 
            array_unique([$claim->get('assigned_user_id'), $claim->get('claim_underwriter'), $claim->get('claim_acceptant')]),
            'Important notice about County',
            'County for claim <a href="$(record : CrmDetailViewURL)$">$(record : RecordLabel)$</a> is inconsistent with Insured\' County',
            'PLL_USERS'
          );
        }
      }

      foreach ($cases as $caseRow) {
        $case = Vtiger_Record_Model::getInstanceById($caseRow['id']);

        if (empty($case->get('county'))) {
          $case->set('county', $county);
          $case->save();
        } else if ($case->get('county') != $county) {
          VTWorkflowUtils::createNotification(
            $case, 
            'Cases', 
            array_unique([$case->get('assigned_user_id'), $case->get('attorney_user'), $case->get('case_manager')]),
            'Important notice about County',
            'County for case <a href="$(record : CrmDetailViewURL)$">$(record : RecordLabel)$</a> is inconsistent with Insured\' County',
            'PLL_USERS'
          );
        }
      }

      foreach ($texasCases as $caseRow) {
        $case = Vtiger_Record_Model::getInstanceById($caseRow['id']);

        if (empty($case->get('county'))) {
          $case->set('county', $county);
          $case->save();
        } else if ($case->get('county') != $county) {
          VTWorkflowUtils::createNotification(
            $case, 
            'TexasCases', 
            array_unique([$case->get('assigned_user_id'), $case->get('attorney_user'), $case->get('case_manager')]),
            'Important notice about County',
            'County for Texas case <a href="$(record : CrmDetailViewURL)$">$(record : RecordLabel)$</a> is inconsistent with Insured\' County',
            'PLL_USERS'
          );
        }
      }

      return $county;
    }
  }
}

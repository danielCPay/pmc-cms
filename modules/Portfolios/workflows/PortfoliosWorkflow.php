<?php

/**
 * PortfoliosWorkflow.
 *
 * @package   Workflow
 *
 * @copyright DOT Systems
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Kamiński <mkaminski@dotsystems.pl>
 */
class PortfoliosWorkflow
{
  /**
	 * Generate Portfolio ID as next numebr in sequence Provider.Provider Abbreviation - NNN.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 */
	public static function generatePortfolioId(Vtiger_Record_Model $recordModel)
	{
		$id = $recordModel->getId();
    $currentPortfolioId = $recordModel->get('portfolio_id');
    $providerId = $recordModel->get('provider');

		\App\Log::warning("Portfolios::Workflows::generatePortfolioId:$id/$currentPortfolioId/$providerId");

    if (empty($currentPortfolioId) || $currentPortfolioId === '---new---') {
      $provider = Vtiger_Record_Model::getInstanceById($providerId);
      $abbreviation = $provider->get('provider_abbreviation');

      // get max number from previous portfolios for provider
      $number = (new \App\QueryGenerator('Portfolios'))
          ->addCondition('provider', $providerId, 'eid')
          ->createQuery()
          ->andWhere(['rlike', 'portfolio_id', "^$abbreviation-\\d+$"])
          ->max("cast(regexp_replace(portfolio_id, '^$abbreviation-', '') as integer)")
        ?: 0;
      $number += 1;

      // set portfolio id
      $recordModel->set('portfolio_id', "$abbreviation-$number");
      $recordModel->save();
    }
	}

  /**
	 * Recalculate from claims
	 *
	 * @param \Portfolios_Record_Model $recordModel
	 */
	public static function recalculateFromClaims(Vtiger_Record_Model $recordModel)
	{
		$id = $recordModel->getId();

		\App\Log::warning("Portfolios::Workflows::recalculateFromClaims:" . $id);

    $recordModel->recalculateFromClaims();
	}

   /**
	 * Release reserves to provider (RELEASE_RESERVES_TO_PROVIDER)
   * 
   * Portfolio Level
   *   Create Payment to Provider:
   *     Value = Total Reserves to be Released
   *     Payment Name = "Reserves released by WF"
   *     Payment Date = aktualna data
   *     Payment Method = puste (mimo że pole obowiązkowe - zakładam, że da się z poziomu CF, a potem użytkownik będzie musiał to poprawić)
   *     Payment Direction = default (jeżeli się nie da, to wprost "Outgoing")
   *     Status = default
   *     Provider, Portfolio - z aktualnego Portfolio
   *     Portfolio Purchase, Claim - puste
   *     inne pola - puste
   * 
   *   Recalculate in Portfolio:
   *     increase Total Reserves Released += Total Reserves to be Released
   *     reset Total Reserves to be Released = 0
   *     set “Last reserves released date" to current date
   * 
   *   For each Claim:
   *     increase Total Reserves Released += Total Reserves to be Released
   *     reset Total Reserves to be Released = 0
   *     set “Last reserves released date" to current date
   * 
   * Take into account the Lock Automation on the Portfolio level (nothing starts if automation is locked).
   * Ignore Lock Automation on the Claims level to avoid data inconsistency.
   * Do not use the "Recalculate from Claim Collections" WF on the Claims level.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 */
	public static function releaseReservesToProvider(Vtiger_Record_Model $recordModel)
	{
		$id = $recordModel->getId();
    $lockAutomation = $recordModel->get('lock_automation');
    $totalReservesToBeReleased = $recordModel->get('total_reserves_to_be_released') ?: 0;

		\App\Log::warning("Portfolios::Workflows::releaseReservesToProvider:$id/$lockAutomation/$totalReservesToBeReleased");

    if(!$lockAutomation && $totalReservesToBeReleased > 0) {
      $currentDate = date('Y-m-d');
      
      $newPayment = Vtiger_Record_Model::getCleanInstance('Payments');
      $newPayment->set('payment_name', "Reserves released by WF");
      $newPayment->set('payment_date', $currentDate);
      $newPayment->set('payment_value', $totalReservesToBeReleased);
      $newPayment->set('provider', $recordModel->get('provider'));
      $newPayment->set('portfolio', $id);

      $recordModel->set('total_reserves_released', ($recordModel->get('total_reserves_released') ?: 0) + $totalReservesToBeReleased);
      $recordModel->set('total_reserves_to_be_released', 0);
      $recordModel->set('last_reserves_released_date', $currentDate);

      // process all claims related to this portfolio, storing them in array to later save them all at once
      $claims = [];
      $claimsData = \VTWorkflowUtils::getAllRelatedRecords($recordModel, 'Claims', ['>', 'u_yf_claims.total_reserves_to_be_released', 0]);
      foreach ($claimsData as $claimsRow) {
        $claims[] = $claim = \Vtiger_Record_Model::getInstanceById($claimsRow['id'], 'Claims');

        $claim->set('total_reserved_released', ($claim->get('total_reserved_released') ?: 0) + ($claim->get('total_reserves_to_be_released') ?: 0));
        $claim->set('total_reserves_to_be_released', 0);
        $claim->set('last_reserves_released_date', $currentDate);
      }

      $newPayment->save();
      foreach ($claims as $claim) {
        $claim->save();
      }
      $recordModel->save();
    }
	}

  /**
   * Stop WF if more claims to underwrite
	 *
	 * @param \Portfolios_Record_Model $recordModel
   */
  public static function stopIfMoreClaimsToUnderwrite(Vtiger_Record_Model $recordModel) {
    $id = $recordModel->getId();
    
		\App\Log::warning("Portfolios::stopIfMoreClaimsToUnderwrite:$id");

    $claims = Vtiger_RelationListView_Model::getInstance($recordModel, "Claims");
    $claimsRows = $claims->getRelationQuery()->all();
    $claimsRecords = $claims->getRecordsFromArray($claimsRows);
    $err = "Claims with Onboarding Status 'Pending Underwriting' or 'In Underwriting' exists";
    
    foreach ($claimsRecords as $id => $claim) {
      $claim = Vtiger_Record_Model::getInstanceById($claim->getId());
      
      if ($claim->get('onboarding_status') === "Pending Underwriting" || $claim->get('onboarding_status') === "In Underwriting") {
        \App\Log::warning("Portfolios::stopIfMoreClaimsToUnderwrite:$id - stop processing");
		    throw new \App\Exceptions\BatchErrorHandledNoRethrowWorkflowException($err);
      }
    }
  }

  /**
   * Set HO Attorney confirmation fields and generate document packages for HO Attorneys
	 *
	 * @param \Portfolios_Record_Model $recordModel
   */
  public static function createHOAttorneyConfirmationRequests(Vtiger_Record_Model $recordModel) {
    $id = $recordModel->getId();
    $currentDate = date('Y-m-d');
    \App\Log::warning("Portfolios::Workflows::createHOAttorneyConfirmationRequests:$id/$currentDate");

    // find Document Package Template with name "LOP Pre-Purchase Confirmation"
    $docPackageName = 'LOP Pre-Purchase Confirmation';
    $docPackageId = \VTWorkflowUtils::getDocumentPackageByName($docPackageName);
    if (empty($docPackageId) || !\App\Record::isExists($docPackageId, 'DocumentPackages')) {
      throw new \Exception("Document Package with name '$docPackageName' not found");
    }
    /** @var DocumentPackages_Record_Model $docPackage */
    $docPackage = Vtiger_Record_Model::getInstanceById($docPackageId, 'DocumentPackages');

    // get all Claims related to this Portfolio, that have Onboarding Status equal to "Pending Underwriting" and HO Attorney Confirmation Status empty
    $relatedClaims = \VTWorkflowUtils::getAllRelatedRecords($recordModel, 'Claims', ['and', ['onboarding_status' => 'Pending Underwriting'], ['ho_attorney_confirmation_statu' => '']], ['u_yf_claims.ho_attorney']);

    $claimsWithoutHOAttorney = array_filter($relatedClaims, function($claim) {
      return empty($claim['ho_attorney']) || !\App\Record::isExists($claim['ho_attorney'], 'Attorneys');
    });
    if (!empty($claimsWithoutHOAttorney)) {
      $claimIds = array_column($claimsWithoutHOAttorney, 'claim_id');
      throw new \Exception("Claims with IDs " . implode(',', $claimIds) . " have no HO Attorney assigned");
    }

    $relatedClaimsIds = array_column($relatedClaims, 'id');
    \App\Log::warning("Portfolios::Workflows::createHOAttorneyConfirmationRequests:num relatedClaims = " . count($relatedClaimsIds));

    // set HO Attorney Conf. Request Sent to current date for all found Claims
    // get distinct HO Attorney IDs from all found Claims
    $hoAttorneyIds = [];
    foreach ($relatedClaimsIds as $claimId) {
      $claim = Vtiger_Record_Model::getInstanceById($claimId);
      $claim->set('ho_attorney_conf_request_date', $currentDate);
      $claim->save();

      $hoAttorneyIds[] = $claim->get('ho_attorney');
    }
    $hoAttorneyIds = array_unique($hoAttorneyIds);
    \App\Log::warning("Portfolios::Workflows::createHOAttorneyConfirmationRequests:HO Attorneys = " . implode(',', $hoAttorneyIds));

    // for each HO Attorney ID generate Document Package named "LOP Pre-Purchase Confirmation"
    foreach($hoAttorneyIds as $hoAttorneyId) {
      $hoAttorney = Vtiger_Record_Model::getInstanceById($hoAttorneyId);

      $documentId = $docPackage->generate($hoAttorney);
      \App\Log::warning("Portfolios::Workflows::createHOAttorneyConfirmationRequests:generated document $documentId");
      $docPackage->send($hoAttorney, $documentId);
      \App\Log::warning("Portfolios::Workflows::createHOAttorneyConfirmationRequests:sent document $documentId by e-mail (if configured in package)");
      $docPackage->dropbox($hoAttorney, $documentId);
      \App\Log::warning("Portfolios::Workflows::createHOAttorneyConfirmationRequests:sent document $documentId to Dropbox (if configured in package)");
    }

    // for each Claim matching conditions: HO Attorney Conf. Request Sent equalt to current date and is related to current Portfolio and Onboarding Status equals "Pending Underwriting" and HO Attorney Confirmation Status is empty, set HO Attorney Confirmation Status to "Pending"
    $claimsToChangeStatus = \VTWorkflowUtils::getAllRelatedRecords($recordModel, 'Claims', ['and', ['ho_attorney_conf_request_date' => $currentDate], ['onboarding_status' => 'Pending Underwriting'], ['ho_attorney_confirmation_statu' => '']]);
    $claimsToChangeStatusIds = array_unique(array_merge($relatedClaimsIds, array_column($claimsToChangeStatus, 'id')));
    foreach ($claimsToChangeStatusIds as $claimId) {
      $claim = Vtiger_Record_Model::getInstanceById($claimId);
      $claim->set('ho_attorney_confirmation_statu', 'Pending');
      $claim->save();
    }
  }

  /**
   * Call resetToNew
	 *
	 * @param \Portfolios_Record_Model $recordModel
   */
  public static function resetToNew(Vtiger_Record_Model $recordModel) {
    \App\Log::warning("Portfolios::Workflows::resetToNew");

    Portfolios_Module_Model::resetToNew();
  }
}

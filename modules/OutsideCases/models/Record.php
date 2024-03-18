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
 * Class OutsideCases_Record_Model.
 */
class OutsideCases_Record_Model extends Vtiger_Record_Model
{
  /**
   * Contains list of funtions to call in recalculateAll(). This field is used instead 
   * of `get_class_methods` with filter to allow mainatining order of calls.
   */
  protected $recalculateFunctions = ["recalculateFromClaims", "recalculateFromCollections", "recalculateFromCase"];

  /**
   * Calculate the maximum amount of money that the provider is allowed to collect for this case.
   * @param int|null $providerId
   * @return int
   */
  public function calculatePMCCollectionsLimit(?int $providerId = null) {
    // Get the ID of this case
    $id = $this->getId();
    // Log the ID of this case
    \App\Log::warning("Cases::calculatePMCCollectionsLimit:$id/$providerId"); 

    // Get the total of all the claims for this case that are not in the Open, Paid, or Closed status
    $pmcCollectionsLimitQG = (new \App\QueryGenerator('Claims'))
      ->setField('adjusted_face_value')
      ->addCondition('outside_case', $id, 'eid')
      ->addCondition('claim_status', 'Open', 'e', false)
      ->addCondition('claim_status', 'Paid', 'e', false)
      ->addCondition('claim_status', 'Closed', 'e', false);

    // If a provider ID was passed in, add a condition to the query to only include claims for that provider
    if ($providerId) {
      $pmcCollectionsLimitQG->addCondition('provider', $providerId, 'eid');
    }

    // Return the total of all the claims for this case that are not in the Open, Paid, or Closed status
    return 
      $pmcCollectionsLimitQG
        ->createQuery()
        ->sum('adjusted_face_value') ?: 0;
  }

  public function recalculateAll() {
    $id = $this->getId();
    $lockAutomation = $this->get('lock_automation');
    \App\Log::warning("OutsideCases::recalculateAll:$id/$lockAutomation");

    if (!$lockAutomation) {
      \App\Log::warning("OutsideCases::recalculateAll:recalculateMethods = [" . implode(', ', $this->recalculateFunctions) . "]");
      foreach ($this->recalculateFunctions as $method) {
        $this->$method();
      }
    }
  }

  public function recalculateFromClaims() {
    $id = $this->getId();
    $lockAutomation = $this->get('lock_automation');
    
		\App\Log::warning("OutsideCases::recalculateFromClaims:$id/$lockAutomation");

    /*
    
    If Lock automation = Yes, do nothing
    Calculate:

    Total Bill Amount= sum of related Claim’s Total Bill Amount (no matter what is their status, purchased or not)
    PMC Collections Limit = sum of related Claim’s Adjusted Face Value, only if Claim.Status=Open, Paid or Closed (use 0 otherwise) (i.e. purchased, not buyback)
    First Notice of Loss = min(related Claim.Date of First Notification)
    Total Hurdle (total_hurdle) - sum of related Claims' Hurdle

    */
    
    if (!$lockAutomation) {
      $oldTotalBillAmount = $this->get('total_bill_amount');
      $oldPMCCollectionsLimit = $this->get('pmc_collections_limit');
      $oldDateOfFirstNotification = $this->get('first_notice_of_loss');
      $oldTotalHurdle = $this->get('total_hurdle');

      // Calculate Total Bill Amount= sum of related Claim’s Total Bill Amount (no matter what is their status, purchased or not)
      $totalBillAmount = (new \App\QueryGenerator('Claims'))
        ->setField('total_bill_amount')
        ->addCondition('outside_case', $id, 'eid')
        ->createQuery()
        ->sum('total_bill_amount') ?: 0;
      
      // Calculate PMC Collections Limit = sum of related Claim’s Adjusted Face Value, only if Claim.Status=Open, Paid or Closed (use 0 otherwise) (i.e. purchased, not buyback)
      $pmcCollectionsLimit = $this->calculatePMCCollectionsLimit();

      // calculate min Claim.Date of First Notification
      $dateOfFirstNotification = (new \App\QueryGenerator('Claims'))
        ->setField('date_of_first_notification')
        ->addCondition('outside_case', $id, 'eid')
        ->createQuery()
        ->min('date_of_first_notification');

      // Total Hurdle (total_hurdle) - sum of related Claims' Hurdle
      $totalHurdle = (new \App\QueryGenerator('Claims'))
        ->setField('hurdle')
        ->addCondition('outside_case', $id, 'eid')
        ->createQuery()
        ->sum('hurdle') ?: 0;

      $shouldSave = false;
      if ($totalBillAmount != $oldTotalBillAmount) {
        $this->set('total_bill_amount', $totalBillAmount);
        $shouldSave = true;
      }
      if ($pmcCollectionsLimit != $oldPMCCollectionsLimit) {
        $this->set('pmc_collections_limit', $pmcCollectionsLimit);
        $shouldSave = true;
      }
      if ($dateOfFirstNotification != $oldDateOfFirstNotification) {
        $this->set('first_notice_of_loss', $dateOfFirstNotification);
        $shouldSave = true;
      }
      if ($totalHurdle != $oldTotalHurdle) {
        $this->set('total_hurdle', $totalHurdle);
        $shouldSave = true;
      }

      if ($shouldSave) {
        $this->save();
      }
    }
  }

  /** RECALCULATE_FROM_COLLECTIONS 
   * https://github.com/dotsystemsspzoo/hestia-pdss/issues/415
   * 
   * Total Collections = sum of all Collections related to the Outside Case
   **/
  public function recalculateFromCollections() {
    $id = $this->getId();

		\App\Log::warning("OutsideCases::recalculateFromCollections:$id");

    $oldTotalCollections = $this->get('total_collections');
    
    // Total Collections = sum of all Collections related to the Outside Case
    $totalCollections = (new \App\QueryGenerator('Collections'))
      ->setField('value')
      ->addCondition('outside_case', $id, 'eid')
      ->createQuery()
      ->sum('value') ?: 0;

    
    $shouldSave = false;
    if ($totalCollections != $oldTotalCollections) {
      $this->set('total_collections', round($totalCollections, 2));
      $shouldSave = true;
    }

    if ($shouldSave) {
      $this->save();
    }
  }

  /**
   * Calculations for all fields that are based on this Case.
   */
  public function recalculateFromCase() {
    $id = $this->getId();
    $lockAutomation = $this->get('lock_automation');
    \App\Log::warning("OutsideCases::recalculateFromCase:$id/$lockAutomation");

    if (!$lockAutomation) {
      /*
        Total Balance = Total Bill Amount - Total Collections
      */
      $this->set('total_balance', ($this->get('total_bill_amount') ?: 0) - ($this->get('total_collections') ?: 0));

      $this->save();
    }
  }
}

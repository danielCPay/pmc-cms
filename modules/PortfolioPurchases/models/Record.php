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
 * Class PortfolioPurchases_Record_Model.
 */
class PortfolioPurchases_Record_Model extends Vtiger_Record_Model
{
  public function recalculateFromClaims() {
    $id = $this->getId();
    $lockAutomation = $this->get('lock_automation');
		\App\Log::warning("PortfolioPurchases::recalculateFromClaims:$id/$lockAutomation");

    // If Lock automation = Yes, do nothing
    if(!$lockAutomation) {

      // Total Number of Claims - Number of all claims in this Purchase
      $num = 0;

      // Total Number of AOB Claims - Number of all Claims in this Purchase where Type of Claim = AOB
      $numAOB = 0;

      // Total Claim Value - Sum of values (Total Bill Amount) from all claims in this Purchase
      $val = 0;

      // Adjusted Claim Value - Sum of values (Adjusted Face Value) from all claims in this Purchase
      $valAdj = 0;

      // Purchase Value - Sum of values (Purchase Price) from all claims in this Purchase
      $purchaseVal = 0;

      // Factor Fee - Sum of values (Factor Fee) from all claims in this Purchase
      $factorFee = 0;

      // Projected Refundable Reserve = Adjusted Claim Value - Sum of values (Hurdle) from all claims in this Purchase
      $projectedRes = 0;

      // Cash to Seller = ( sum of all related Claims' Cash to Seller )  - Wire Fees - Buyback Clearance
      $cashSeller = 0;

      $purchaseP = 0;

      $claims = Vtiger_RelationListView_Model::getInstance($this, "Claims");
      $claimsRows = $claims->getRelationQuery()->all();
      $claimsRecords = $claims->getRecordsFromArray($claimsRows);
      
      foreach ($claimsRecords as $id => $claim) {
      	$claim = Vtiger_Record_Model::getInstanceById($claim->getId());
        
        $num = $num + 1;

        if ($claim->get('type_of_claim') === "AOB") {
          $numAOB = $numAOB + 1;
        }

        $totalBill = $claim->get('total_bill_amount');
        $adjFaceVal = $claim->get('adjusted_face_value');
        $purchaseP = $claim->get('purchase_price');
        $hurdle = $claim->get('hurdle');
        $fFee = $claim->get('factor_fee');
        $claimCashToSeller = $claim->get('cash_to_seller');

        if (!empty($totalBill)) {
          $val = $val + $totalBill;
        }

        if (!empty($adjFaceVal)) {
          $valAdj = $valAdj + $adjFaceVal;
        }

        if (!empty($purchaseP)) {
          $purchaseVal = $purchaseVal + $purchaseP;
        }

        if (!empty($hurdle)) {
          $projectedRes = $projectedRes + $hurdle;
        }

        if (!empty($fFee)) {
          $factorFee = $factorFee + $fFee;
        }

        if (!empty($claimCashToSeller)) {
          $cashSeller += $claimCashToSeller;
        }
      }

      $projectedRes = $valAdj - $projectedRes;

      $cashSeller = $cashSeller - (!empty($this->get('wire_fees')) ? $this->get('wire_fees') : 0) - (!empty($this->get('buyback_clearance')) ? $this->get('buyback_clearance') : 0);

      \App\Log::trace("PortfolioPurchases::recalculateFromClaims:total_number_of_claims = $num");
      $this->set('total_number_of_claims', $num);

      \App\Log::trace("PortfolioPurchases::recalculateFromClaims:total_number_of_aob_claims = $numAOB");
      $this->set('total_number_of_aob_claims', $numAOB);

      \App\Log::trace("PortfolioPurchases::recalculateFromClaims:total_claim_value = $val");
      $this->set('total_claim_value', round($val, 2));

      \App\Log::trace("PortfolioPurchases::recalculateFromClaims:adjusted_claim_value = $valAdj");
      $this->set('adjusted_claim_value', round($valAdj, 2));

      \App\Log::trace("PortfolioPurchases::recalculateFromClaims:purchase_value = $purchaseVal");
      $this->set('purchase_value', round($purchaseVal, 2));

      \App\Log::trace("PortfolioPurchases::recalculateFromClaims:projected_refundable_reserve = $projectedRes");
      $this->set('projected_refundable_reserve', round($projectedRes, 2));

      \App\Log::trace("PortfolioPurchases::recalculateFromClaims:cash_to_seller = $cashSeller");
      $this->set('cash_to_seller', round($cashSeller, 2));

      \App\Log::trace("PortfolioPurchases::recalculateFromClaims:factor_fee = $factorFee");
      $this->set('factor_fee', round($factorFee, 2));

      $this->save();
    }
  }

  public function recalculateFromBuybackClaims() {
    $id = $this->getId();
    $lockAutomation = $this->get('lock_automation');
		
    \App\Log::warning("PortfolioPurchases::recalculateFromBuybackClaims:$id/$lockAutomation");

    // If Lock automation = Yes, do nothing
    if(!$lockAutomation) {
      $buybackClearance = (new \App\QueryGenerator('Claims'))->setField('buyback_amount')->addCondition('buyback_portfolio_purchase', $id, 'eid')->createQuery()->sum('Coalesce(buyback_amount, 0)') ?: 0;

      \App\Log::trace("PortfolioPurchases::recalculateFromBuybackClaims:buyback_clearance = $buybackClearance");
      $this->set('buyback_clearance', round($buybackClearance, 2));

      $this->save();

      $this->recalculateFromClaims();
    }
  }
}

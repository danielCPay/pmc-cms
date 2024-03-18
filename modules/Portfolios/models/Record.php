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
 * Class Portfolios_Record_Model.
 */
class Portfolios_Record_Model extends Vtiger_Record_Model
{
  public function recalculateFromClaims() {
    $id = $this->getId();
    
		\App\Log::warning("Portfolios::recalculateFromClaims:$id");

    // If Lock automation = Yes, do nothing
    if(!$this->get('lock_automation')) {

      // Total Number of Claims - Number of all claims filed by Provider in this Portfolio
      $num = 0;

      // Total Number of AOB Claims - Number of all Claims filed by Provider in this Portfolio where Type of Claim = AOB
      $numAOB = 0;

      // Total Claim Value - Sum of values (Total Bill Amount) from all claims (accepted+rejected)
      $val = 0;

      // Adjusted Claim Value - Sum of values (Adjusted Face Value) from all claims
      $valAdj = 0;

      // Total Number of Rejected Claims - Number of Claims where Onboarding Status = Rejected
      $numRejected = 0;

      // Total Value of Rejected Claims - Sum of Values (Total Bill Amount) of Claims where Onboarding Status = Rejected
      $valRejected = 0;

      // Total Number of Accepted Claims - Number of Claims where Onboarding Status = Purchased
      $numAccepted = 0;

      // Total Value of Accepted Claims - Sum of Values (Total Bill Amount) of Claims where Onboarding Status = Purchased
      $valAccepted = 0;

      // Total Adjusted Face Value - Sum of Values (Adjusted Face Value) of Claims where Onboarding Status = Purchased
      $valAdjAccepted = 0;

      // Total Purchase Price - Sum of values (Purchase Price) of Claims where Onboarding Status = Purchased
      $purchasePrice = 0;

      // Total Factor Fee - Sum of values (Factor Fee) of Claims where Onboarding Status = Purchased
      $factorFee = 0;

      // Hurdle - Sum of values (Hurdle) of Claims where Onboarding Status = Purchased 
      $hurdle = 0; 

      // Hurdle % - Hurdle / Total Adjusted Face Value (wpisz pustą wartość, jeżeli dzielenie przez 0)
      $hurdlePerc = NULL;

      // Total Number of Paid Claims - Number of claims that have: Claim Status = Paid (in other words: Remaining to hurdle = 0)
      $numPaid = 0;

      // Total Number of Buybacks - Number of claims that have: Claim Status = Buyback
      $numBuybacks = 0;

      // Total Buybacks Value - Sum of Buyback Amount of claims that have: Claim Status = Buyback
      $valBuybacks = 0;

      // Calculate Total Voluntary Collections, Total Pre-suit Collections, Total Litigated Collections, Total Collections as sums of respective fields from Claims. 
      // NOTE: these values do not cover Limit reserve (there is a separate field for it)
      $volCol = 0;
      $preCol = 0;
      $litCol = 0;
      $col = 0;

      // “Total balance owed” = Adjusted Face Value – Total Collections
      $balance = 0;

      // “Total profit” = Total collections – Purchase price, not more than Factor Fee, not less than 0 (i.e Max(Min(Total collections, Hurdle) – Purchase Price, 0))
      $profit = 0;

      // Refundable Reserve -
      // if Portfolio.Program.Type of Program = By Claim then sum of Claim.Refundable Reserve
      // if Portfolio.Program.Type of Program = Pool, then calculate “Refundable reserve” = Total Collections – Hurdle, not less than 0; assert that it should not be more than (Adjusted Face Value – Hurdle)
      $refundable = 0;

      // “Total Limit reserve” = sum of Claim.Limit reserve
      $limitReserve = 0;

      // “Total Reserves” = Refundable Reserve + Total Limit Reserve
      $totalReserves = 0;

      // Total Reserves to be Released = Total Reserves – Total Reserves Released
      $releasedReserves = 0;

      // Internal Cash Transfer = sum of [Internal Cash Transfer] for all Claims
      $internalCashTransfer = 0;

      // Total Penalty = sum of [Total Penalty] for all Claims
      $totalPenalty = 0;

      // Remaining to Hurdle = Portfolio Hurdle - Total Collections - Internal Cash Transfer - Total Penalty (0 if calculated value less than 0)
      $remainingToHurdle = 0;

      $program = NULL;

      if(!empty(($this->get('program')))) {
        $program = Vtiger_Record_Model::getInstanceById($this->get('program'));
      }

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
        $pprice = $claim->get('purchase_price');
        $ffee = $claim->get('factor_fee');
        $hrdl = $claim->get('hurdle');
        $internalCashTransferClaim = $claim->get('internal_cash_transfer');
        $totalPenaltyClaim = $claim->get('total_penalty');

        if (!empty($totalBill)) {
          $val = $val + $totalBill;
        }

        if (!empty($adjFaceVal)) {
          $valAdj = $valAdj + $adjFaceVal;
        }

        if ($claim->get('onboarding_status') === "Rejected") {
          $numRejected = $numRejected + 1;

          if (!empty($totalBill)) {
            $valRejected = $valRejected + $totalBill;
          }
        }
        else if ($claim->get('onboarding_status') === "Purchased") {
          $numAccepted = $numAccepted + 1;

          if (!empty($totalBill)) {
            $valAccepted = $valAccepted + $totalBill;
          }

          if (!empty($adjFaceVal)) {
            $valAdjAccepted = $valAdjAccepted + $adjFaceVal;
          }

          if (!empty($pprice)) {
            $purchasePrice = $purchasePrice + $pprice;
          }

          if (!empty($ffee)) {
            $factorFee = $factorFee + $ffee;
          }

          if (!empty($hrdl)) {
            $hurdle = $hurdle + $hrdl;
          }
        }

        if ($claim->get('claim_status') === "Paid") {
          $numPaid = $numPaid + 1;
        }
        else if ($claim->get('claim_status') === "Buyback") {
          $numBuybacks = $numBuybacks + 1;

          $buyback = $claim->get('buyback_amount');

          if (!empty($buyback)) {
            $valBuybacks = $valBuybacks + $buyback;
          }
        }

        $voluntary = $claim->get('total_voluntary_ollections');
        $pre = $claim->get('total_pre_suit_collections');
        $lit = $claim->get('total_litigated_collections');
        $c = $claim->get('total_collections');

        if (!empty($voluntary)) {
          $volCol = $volCol + $voluntary;
        }

        if (!empty($pre)) {
          $preCol = $preCol + $pre;
        }

        if (!empty($lit)) {
          $litCol = $litCol + $lit;
        }

        if (!empty($c)) {
          $col = $col + $c;
        }

        if($program !== NULL && $program->get('program_type') === "By Claim") {
          $reserve = $claim->get('refundable_reserve');

          if (!empty($reserve)) {
            $refundable = $refundable + $reserve;
          }
        }

        $limR = $claim->get('limit_reserve');

        if (!empty($limR)) {
          $limitReserve = $limitReserve + $limR;
        }

        if (!empty($internalCashTransferClaim)) {
          $internalCashTransfer = $internalCashTransfer + $internalCashTransferClaim;
        }

        if (!empty($totalPenaltyClaim)) {
          $totalPenalty = $totalPenalty + $totalPenaltyClaim;
        }
      }

      $balance = $valAdj - $col;

      if(!empty($valAdjAccepted) && $valAdjAccepted != 0) {
        $hurdlePerc = $hurdle / $valAdjAccepted * 100;
      }

      $remainingToHurdle = $hurdle - $col - $internalCashTransfer - $totalPenalty;

      if($remainingToHurdle < 0) {
        $remainingToHurdle = 0;
      }
      
      $profit = $col - $purchasePrice;
      
      if($profit > $factorFee) {
        $profit = $factorFee;
      }

      if($profit < 0) {
        $profit = 0;
      }

      if($program !== NULL && $program->get('program_type') === "Pool") {
        $refundable = $col - $hurdle;
        $refundableMax = $valAdj - $hurdle;

        if($refundable < 0) {
          $refundable = 0;
        }
        if($refundable > $refundableMax) {
          $refundable = $refundableMax;
        }
      }

      $totalReserves = $refundable + $limitReserve;
      $totalReservesRel = !empty($this->get('total_reserves_released')) ? $this->get('total_reserves_released') : 0;

      $releasedReserves = $totalReserves - $totalReservesRel;

      \App\Log::trace("Portfolios::recalculateFromClaims:total_number_of_claims = $num");
      $this->set('total_number_of_claims', $num);

      \App\Log::trace("Portfolios::recalculateFromClaims:total_number_of_aob_claims = $numAOB");
      $this->set('total_number_of_aob_claims', $numAOB);

      \App\Log::trace("Portfolios::recalculateFromClaims:total_claim_value = $val");
      $this->set('total_claim_value', round($val, 2));

      \App\Log::trace("Portfolios::recalculateFromClaims:adjusted_claim_value = $valAdj");
      $this->set('adjusted_claim_value', round($valAdj, 2));

      \App\Log::trace("Portfolios::recalculateFromClaims:total_num_of_rejected_claims = $numRejected");
      $this->set('total_num_of_rejected_claims', $numRejected);
      
      \App\Log::trace("Portfolios::recalculateFromClaims:total_value_of_rejected_claims = $valRejected");
      $this->set('total_value_of_rejected_claims', round($valRejected, 2));

      \App\Log::trace("Portfolios::recalculateFromClaims:total_number_accepted_claims = $numAccepted");
      $this->set('total_number_accepted_claims', $numAccepted);
      
      \App\Log::trace("Portfolios::recalculateFromClaims:total_value_accepted_claims = $valAccepted");
      $this->set('total_value_accepted_claims', round($valAccepted, 2));

      \App\Log::trace("Portfolios::recalculateFromClaims:total_adjusted_face_value = $valAdjAccepted");
      $this->set('total_adjusted_face_value', round($valAdjAccepted, 2));

      \App\Log::trace("Portfolios::recalculateFromClaims:total_purchase_price = $purchasePrice");
      $this->set('total_purchase_price', round($purchasePrice, 2));

      \App\Log::trace("Portfolios::recalculateFromClaims:total_factor_fee = $factorFee");
      $this->set('total_factor_fee', round($factorFee, 2));
      
      \App\Log::trace("Portfolios::recalculateFromClaims:hurdle = $hurdle");
      $this->set('hurdle', round($hurdle, 2));

      \App\Log::trace("Portfolios::recalculateFromClaims:hurdle_percent = $hurdlePerc");
      $this->set('hurdle_percent', round($hurdlePerc, 2));

      \App\Log::trace("Portfolios::recalculateFromClaims:total_number_of_paid_claims = $numPaid");
      $this->set('total_number_of_paid_claims', $numPaid);

      \App\Log::trace("Portfolios::recalculateFromClaims:total_number_of_buybacks = $numBuybacks");
      $this->set('total_number_of_buybacks', $numBuybacks);

      \App\Log::trace("Portfolios::recalculateFromClaims:total_buybacks = $valBuybacks");
      $this->set('total_buybacks', round($valBuybacks, 2));
      
      \App\Log::trace("Portfolios::recalculateFromClaims:total_voluntary_collections = $volCol");
      $this->set('total_voluntary_collections', round($volCol, 2));

      \App\Log::trace("Portfolios::recalculateFromClaims:total_presuit_collections = $preCol");
      $this->set('total_presuit_collections', round($preCol, 2));

      \App\Log::trace("Portfolios::recalculateFromClaims:total_litigated_collections = $litCol");
      $this->set('total_litigated_collections', round($litCol, 2));

      \App\Log::trace("Portfolios::recalculateFromClaims:total_collections = $col");
      $this->set('total_collections', round($col, 2));

      \App\Log::trace("Portfolios::recalculateFromClaims:total_balance_owed = $balance");
      $this->set('total_balance_owed', round($balance, 2));

      \App\Log::trace("Portfolios::recalculateFromClaims:remaining_to_hurdle = $remainingToHurdle");
      $this->set('remaining_to_hurdle', round($remainingToHurdle, 2));

      \App\Log::trace("Portfolios::recalculateFromClaims:total_profit = $profit");
      $this->set('total_profit', round($profit, 2));

      \App\Log::trace("Portfolios::recalculateFromClaims:refundable_reserve = $refundable");
      $this->set('refundable_reserve', round($refundable, 2));

      \App\Log::trace("Portfolios::recalculateFromClaims:total_limit_reserve = $limitReserve");
      $this->set('total_limit_reserve', round($limitReserve, 2));

      \App\Log::trace("Portfolios::recalculateFromClaims:total_reserves = $totalReserves");
      $this->set('total_reserves', round($totalReserves, 2));

      \App\Log::trace("Portfolios::recalculateFromClaims:total_reserves_to_be_released = $releasedReserves");
      $this->set('total_reserves_to_be_released', round($releasedReserves, 2));

      \App\Log::trace("Portfolios::recalculateFromClaims:total_penalty = $totalPenalty");
      $this->set('total_penalty', round($totalPenalty, 2));

      \App\Log::trace("Portfolios::recalculateFromClaims:internal_cash_transfer = $internalCashTransfer");
      $this->set('internal_cash_transfer', round($internalCashTransfer, 2));
      
      $this->save();
    }
  }
}

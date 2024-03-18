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

class ClaimCollectionData {
  public Claims_Record_Model $claim;
  public Vtiger_Record_Model $claimCollection;
  public Portfolios_Record_Model $portfolio;
  public Vtiger_Record_Model $program;
  public bool $shouldSaveCollection = false;
  public bool $shouldSaveClaim = false;
  public bool $shouldSavePortfolio = false;

  public function __construct(Vtiger_Record_Model $caseClaim, Vtiger_Record_Model $collection, $number) {
    $claimCollection = Vtiger_Record_Model::getCleanInstance('ClaimCollections');
    $claimCollection->set('claim_collection_name', "{$collection->get('collection_name')} - $number");
    $claimCollection->set('collection', $collection->getId());
    $claimCollection->set('portfolio', $caseClaim->get('portfolio'));
    $claimCollection->set('claim', $caseClaim->getId());

    $this->claim = $caseClaim;
    $this->claimCollection = $claimCollection;
    try {
      $this->portfolio = Vtiger_Record_Model::getInstanceById($caseClaim->get('portfolio'));
    } catch (\Throwable $t) {
      throw new \Exception("Claim " . $caseClaim->getDisplayName() . " doesn't have portfolio set", 0, $t);
    }
    try {
      $portfolioPurchase = Vtiger_Record_Model::getInstanceById($caseClaim->get('portfolio_purchase'));
    } catch (\Throwable $t) {
      throw new \Exception("Claim " . $caseClaim->getDisplayName() . " doesn't have portfolio purchase set", 0, $t);
    } 
    try {
      $this->program = Vtiger_Record_Model::getInstanceById($portfolioPurchase->get('program'));
    } catch (\Throwable $t) {
      throw new \Exception("Portfolio purchase " . $portfolioPurchase->getDisplayName() . " doesn't have program set", 0, $t);
    }
  }
}

/**
 * Class Collections_Record_Model.
 */
class Collections_Record_Model extends Vtiger_Record_Model
{
  public function applyCollectionToClaims() {
    $id = $this->getId();
    $calculationDate = $this->get('date_of_calculations');
    
		\App\Log::warning("Collections::applyCollectionToClaims:$id/$calculationDate");

    /*
    The algorithm (APPLY_COLLECTIONS_TO_CLAIMS):

    1) For each previous Claim Collections related to this Collection
    i. Delete this Claim Collection
    ii. Execute Claim Collection.Claim.RECALCULATE_FROM_CLAIM_COLLECTIONS
    2) Calculate general Case level values (or Outside Case - the one that is relevant):
    i. Execute Case.RECALCULATE_FROM_CLAIMS
    ii. Execute Case.RECALCULATE_FROM_COLLECTIONS

    If "Don't apply to Claims" = Yes, STOP.
    Otherwise take "Provider" into account in further calculations:
    If Provider is empty - use all Claims related to this Case, ignore Claim's Provider (as in the original algorithm)
    If Provider is not empty - use only Claims related to this Case AND to this Provider. 
      The tweaked algorithm will use "PMC Collections Limit" calculated dynamically on the level of both Case and Provider, 
      not as the original algorithm on the level of a Case only. I.e. the "PMC Collections Limit" field in the Cases module 
      will be less informative than it is now.
    
		3) Check and calculate Limit reserve (on Case level)
    i. Value to apply to Claims = Value, not more than (Case.PMC Collections Limit – Case.Total Collections + Value), 
      not less than 0 (i.e. Value to apply to Claims = max(min(Value, (Case.PMC Collections Limit – Case.Total Collections + Value)), 0); 
      note that Value is already included in Case.Total Collections, that is why it has to be added; in case of future changes in 
      RECALCULATE_FROM_COLLECTIONS it has to be rewritten, too!)
    ii. Limit reserve to apply to Claims = Value - Value to apply to Claims
    iii. Set variable “value left” = Value to apply to Claims
    
		4) Temporarily create Claim Collections for each Claim (where Claim Status is Open, Paid or Closed) related to the Case, in the sequence of Claim.Created date
    i. Set references to Collection, Portfolio and Claim
    
		5) Try to assign Collection to fill hurdle on Claim level: for each Claim Collection (in the sequence of their creation), 
      if Claim.Claim Status is (Open, Paid or Closed) and Claim.Remaining to Hurdle > 0 and “value left” > 0, then:
    i. Variable “value to apply” = Min( value left, Claim.Remaining to Hurdle, Portfolio. Remaining to Hurdle (apply this only if Portfolio Purchase.Program.Type of Program = “Pool”); 
      If “value to apply” > 0 then:
    ii. Increase “Assigned value” by “value to apply”
    iii. Increase “Assigned below hurdle” by “value to apply”
    iv. Decrease “Claim.Remaining to Hurdle” by “value to apply”
    v. Decrease “Portfolio.Remaining to Hurdle” by “value to apply”
    vi. Decrease “value left” by “value to apply”
    
		6) Try to assign Collection to fill hurdle on “pool” Portfolio level (even if Claim hurdle is already filled): 
        for each Claim Collection (in the sequence of their creation), if Claim.Claim Status is (Open, Paid or Closed) and Portfolio Purchase.Program.Type of Program = “Pool” and Portfolio.Remaining to Hurdle > 0 and “value left” > 0, then:
    i. Variable “value to apply” = Min( value left, Portfolio. Remaining to Hurdle, Max(Claim. Adjusted Face Value – Claim.Total Collections, 0)); If “value to apply” > 0 then:
    ii. Increase “Assigned value” by “value to apply”
    iii. Increase “Assigned below hurdle” by “value to apply”
    iv. Decrease “Portfolio.Remaining to Hurdle” by “value to apply”
    v. Decrease “value left” by “value to apply”
    
		7) Assign the rest to fill Refundable Reserve: for each Claim Collection (in the sequence of their creation), if Claim.Claim Status is (Open, Paid or Closed) and “value left” > 0, then:
    i. Variable “value to apply” = Min( value left, max(Claim.Adjusted Face Value – Claim.Total Collections)); If “value to apply” > 0 then:
    ii. Increase “Assigned value” by “value to apply”
    iii. Increase “Assigned refundable reserve” by “value to apply”
    iv. Decrease “value left” by “value to apply”
    7b. After "for each": Assert, that “Value left” = 0
    
		8) Assign “Collection.Limit reserve to apply to Claims” proportionally to Claim’s Total Bill Amount
    i. Set "Value left" = "Collection.Limit reserve to apply to Claims”
    ii. For each Claim Collection set Assigned limit reserve = share of “Collection.Limit reserve to apply to Claims” weighted by Claim.Total Bill Amount, round down to cents
    iii. decrease "Value left" by each Assigned limit reserve
    iv. take care for the last rounding cent from “Value left” (assign to some Claim collection)
    
		9) Save Claim Collections created temporariy in p.4., if they are not empty.
    
		10) Set Date of calculations = current date and time
    
		11) Recalculate values in related Claims and Portfolios; for each Claim Collection:
    i. Execute Claim.RECALCULATE_FROM_CLAIM_COLLECTIONS
    ii. Execute Portfolio.RECALCULATE_FROM_CLAIMS
    
		12) Technical notes:
    i. Between 7. and 8. - assert, that “Value left” = 0
    ii. Assert, that for each Claim: Total PMC Collections <= Adjusted Claim Value
    iii. Minimize number of history updates in all related modules
    */

    // 20220603 Disable condition (https://github.com/dotsystemsspzoo/hestia-pdss/issues/57#issuecomment-1145437575)
    // if (empty($calculationDate)) {
      $shouldSaveCase = false;
      $isOutside = !empty($this->get('outside_case'));
      if ($isOutside) {
        $caseId = $this->get('outside_case');
        $caseModule = 'OutsideCases';
      } else {
        $caseId = $this->get('case');
        $caseModule = 'Cases';
      }

      // 1) For each previous Claim Collections related to this Collection
      $oldClaimCollections = VTWorkflowUtils::getAllRelatedRecords($this, 'ClaimCollections');
      foreach ($oldClaimCollections as $oldClaimCollectionRow) {
        $oldClaimCollection = Vtiger_Record_Model::getInstanceById($oldClaimCollectionRow['id']);

        // i. Delete this Claim Collection
        $oldClaimCollection->changeState('Trash');

        // ii. Execute Claim Collection.Claim.RECALCULATE_FROM_CLAIM_COLLECTIONS
        /** @var Claims_Record_Model $oldClaimCollectionClaim */
        $oldClaimCollectionClaim = Vtiger_Record_Model::getInstanceById($oldClaimCollection->get('claim'));
        $oldClaimCollectionClaim->recalculateFromClaimCollections();
      }

      if (!\App\Record::isExists($caseId, $caseModule)) {
        \App\Log::warning("Collections::applyCollectionToClaims:Case $caseModule.$caseId not found");
        return;
      }
      
      $case = Vtiger_Record_Model::getInstanceById($caseId);
      

      // 2. Calculate general Case level values
      $case->recalculateAll();

      if ($this->get('dont_apply_to_claims') == 1) {
        \App\Log::warning("Collections::applyCollectionToClaims:dont_apply_to_claims == 1");
        return;
      }

      // If Collection provider is set, check if there are any related claims with that provider. If there are none, throw exception.
      $collectionProvider = $this->get('provider');
      \App\Log::warning("Collections::applyCollectionToClaims:collectionProvider = $collectionProvider");
      if (!empty($collectionProvider)) {
        $claimsWithProvider = \VTWorkflowUtils::getAllRelatedRecords($case, 'Claims', ['claim_status' => ['Open', 'Paid', 'Closed'], 'u_yf_claims.provider' => $collectionProvider], false, false, ['createdtime' => SORT_ASC]);
        \App\Log::warning(var_export($claimsWithProvider, true));
        if (empty($claimsWithProvider)) {
          throw new \Exception('Case has provider set, but there are no claims with that provider.');
        }

        // get dynamic collections limit
        $pmcCollectionsLimit = $case->calculatePMCCollectionsLimit($collectionProvider);
      } else {
        $pmcCollectionsLimit = $case->get('pmc_collections_limit') ?: 0;
      }

      // 3. Check and calculate Limit reserve (on Case level)
      $totalCollections = $case->get('total_collections');
      $value = $this->get('value') ?: 0;
      $valueToApplyToClaims = round(max(min($value, $pmcCollectionsLimit - $totalCollections + $value), 0), 2);
      $limitReserveToApplyToClaims = $value - $valueToApplyToClaims;
      $valueLeft = $valueToApplyToClaims;
      $this->set('value_to_apply_to_claims', $valueToApplyToClaims);
      $this->set('limit_reserve_to_apply_to_clai', $limitReserveToApplyToClaims);

      \App\Log::warning("Collections::applyCollectionToClaims:pmcCollectionsLimit = $pmcCollectionsLimit, value = $value, valueToApplyToClaims = $valueToApplyToClaims, limitReserve = $limitReserveToApplyToClaims, valueLeft = $valueLeft");

      // 4. Create Claim Collections for each Claim (where Claim Status is Open, Paid or Closed) related to the Case, in order of Claim.Created date
      $caseClaims = !empty($claimsWithProvider) ? $claimsWithProvider : VTWorkflowUtils::getAllRelatedRecords($case, 'Claims', ['claim_status' => ['Open', 'Paid', 'Closed']], false, false, ['createdtime' => SORT_ASC]);
      $number = 1;
      /** @var ClaimCollectionData[] $claimCollections */
      $claimCollections = [];
      $sumTotalClaimValue = 0;
      foreach ($caseClaims as $caseClaimRow) {
        $caseClaim = Vtiger_Record_Model::getInstanceById($caseClaimRow['id']);

        $claimCollection = new ClaimCollectionData($caseClaim, $this, $number++);
        $sumTotalClaimValue += ($claimCollection->claim->get('total_bill_amount') ?: 0);
        $claimCollections[] = $claimCollection;
      }

      \App\Log::warning("Collections::applyCollectionToClaims:sumTotalClaimValue = $sumTotalClaimValue");

      // 5. Try to assign Collection to fill hurdle on Claim level: for each Claim Collection (in the sequence of their creation), if Claim.Claim Status is (Open, Paid or Closed) and Claim.Remaining to Hurdle > 0 and “value left” > 0, then:
      foreach ($claimCollections as $claimCollection) {
        if ($valueLeft <= 0) {
          break;
        }

        $remainingToHurdle = $claimCollection->claim->get('remaining_to_hurdle') ?: 0;
        $portfolioRemainingToHurdle = $claimCollection->program->get('program_type') === 'Pool' ? ($claimCollection->portfolio->get('remaining_to_hurdle') ?: 0) : PHP_INT_MAX;
        if (in_array($claimCollection->claim->get('claim_status'), ['Open', 'Paid', 'Closed'])
          && $remainingToHurdle > 0) {
          $valueToApply = min($valueLeft, $remainingToHurdle, $portfolioRemainingToHurdle);
          \App\Log::warning("Collections::applyCollectionToClaims:claim {$claimCollection->claim->getId()} remainingToHurdle = $remainingToHurdle, valueToApply = $valueToApply");
          if ($valueToApply > 0) {
            $claimCollection->claimCollection->set('assigned_value', ($claimCollection->claimCollection->get('assigned_value') ?: 0) + $valueToApply);
            $claimCollection->claimCollection->set('assigned_below_hurdle', ($claimCollection->claimCollection->get('assigned_below_hurdle') ?: 0) + $valueToApply);
            $claimCollection->claim->set('remaining_to_hurdle', $remainingToHurdle - $valueToApply);
            $claimCollection->claim->set('total_collections', ($claimCollection->claim->get('total_collections') ?: 0) + $valueToApply);
            $claimCollection->portfolio->set('remaining_to_hurdle', ($claimCollection->portfolio->get('remaining_to_hurdle') ?: 0) - $valueToApply);

            $claimCollection->shouldSaveCollection = true;
            $claimCollection->shouldSaveClaim = true;
            $claimCollection->shouldSavePortfolio = true;

            $valueLeft = round($valueLeft - $valueToApply, 2);
          }
        }
      }

      \App\Log::warning("Collections::applyCollectionToClaims:valueLeft after 5 = $valueLeft");

      // 6. Try to assign Collection to fill hurdle on “pool” Portfolio level (even if Claim hurdle is already filled): for each Claim Collection (in the sequence of their creation), if Claim.Claim Status is (Open, Paid or Closed) and Portfolio.Program.Type of Program = “Pool” and Portfolio.Remaining to Hurdle > 0 and “value left” > 0, then:
      foreach ($claimCollections as $claimCollection) {
        if ($valueLeft <= 0) {
          break;
        }

        $remainingToHurdle = $claimCollection->portfolio->get('remaining_to_hurdle') ?: 0;
        $adjustedFaceValue = $claimCollection->claim->get('adjusted_face_value') ?: 0;
        $claimTotalCollections = $claimCollection->claim->get('total_collections') ?: 0;
        if (in_array($claimCollection->claim->get('claim_status'), ['Open', 'Paid', 'Closed'])
          && $claimCollection->program->get('program_type') === 'Pool'
          && $remainingToHurdle > 0) {
          $valueToApply = min($valueLeft, $remainingToHurdle, max(($adjustedFaceValue) - ($claimTotalCollections), 0));
          \App\Log::warning("Collections::applyCollectionToClaims:claim {$claimCollection->claim->getId()} remainingToHurdle = $remainingToHurdle, adjustedFaceValue = $adjustedFaceValue, claimTotalCollections = $claimTotalCollections, valueToApply = $valueToApply");
          if ($valueToApply > 0) {
            $claimCollection->claimCollection->set('assigned_value', ($claimCollection->claimCollection->get('assigned_value') ?: 0) + $valueToApply);
            $claimCollection->claimCollection->set('assigned_below_hurdle', ($claimCollection->claimCollection->get('assigned_below_hurdle') ?: 0) + $valueToApply);
            $claimCollection->claim->set('total_collections', ($claimCollection->claim->get('total_collections') ?: 0) + $valueToApply);
            $claimCollection->portfolio->set('remaining_to_hurdle', $remainingToHurdle - $valueToApply);
            
            $claimCollection->shouldSaveCollection = true;
            $claimCollection->shouldSavePortfolio = true;

            $valueLeft = round($valueLeft - $valueToApply, 2);
          }
        }
      }

      \App\Log::warning("Collections::applyCollectionToClaims:valueLeft after 6 = $valueLeft");

      // 7. Assign the rest to fill Refundable Reserve: for each Claim Collection (in the sequence of their creation), if Claim.Claim Status is (Open, Paid or Closed) and “value left” > 0, then:
      foreach ($claimCollections as $claimCollection) {
        if ($valueLeft <= 0) {
          break;
        }

        if (in_array($claimCollection->claim->get('claim_status'), ['Open', 'Paid', 'Closed'])) {
          $valueToApply = min($valueLeft, max(($claimCollection->claim->get('adjusted_face_value') ?: 0) - ($claimCollection->claim->get('total_collections') ?: 0), 0));
          \App\Log::warning("Collections::applyCollectionToClaims:claim {$claimCollection->claim->getId()} valueToApply = $valueToApply");
          if ($valueToApply > 0) {
            $claimCollection->claimCollection->set('assigned_value', ($claimCollection->claimCollection->get('assigned_value') ?: 0) + $valueToApply);
            $claimCollection->claimCollection->set('assigned_refundable_reserve', ($claimCollection->claimCollection->get('assigned_refundable_reserve') ?: 0) + $valueToApply);

            $claimCollection->shouldSaveCollection = true;

            $valueLeft -= $valueToApply;
          }
        }
      }

      \App\Log::warning("Collections::applyCollectionToClaims:valueLeft after 7 = $valueLeft");

      // 12. Assertions
      if ($valueLeft != 0) {
        throw new \Exception("Value left is not 0 ($valueLeft)");
      }

      // 8. Assign “Collection.Limit reserve to apply to Claims” proportionally to Claim’s Total Claim Value
      $valueLeft = $limitReserveToApplyToClaims;
      \App\Log::warning("Collections::applyCollectionToClaims:limitReserveToApplyToClaims = $limitReserveToApplyToClaims, sumTotalClaimValue = $sumTotalClaimValue");
      foreach ($claimCollections as $claimCollection) {
        $valueToApply = round(($claimCollection->claim->get('total_bill_amount') ?: 0) / $sumTotalClaimValue * $limitReserveToApplyToClaims, 2);
        \App\Log::warning("Collections::applyCollectionToClaims:claim {$claimCollection->claim->getId()} total_bill_amount = " . ($claimCollection->claim->get('total_bill_amount') ?: 0) . ", valueToApply = $valueToApply, valueLeft = $valueLeft, adjust = " . (round($valueLeft, 2) - $valueToApply));
        if ($valueToApply > 0) {
          if (round(abs(round($valueLeft, 2) - $valueToApply), 2) == 0.01) {
            $valueToApply += round(round($valueLeft, 2) - $valueToApply, 2);
            \App\Log::warning("Collections::applyCollectionToClaims:claim {$claimCollection->claim->getId()} adjusted valueToApply = $valueToApply");
          }
          $claimCollection->claimCollection->set('assigned_limit_reserve', $valueToApply);

          $claimCollection->shouldSaveCollection = true;

          $valueLeft -= $valueToApply;
        }
      }
      $valueLeft = round($valueLeft, 2);

      \App\Log::warning("Collections::applyCollectionToClaims:valueLeft after 8 = $valueLeft");

      // take care of last rounding cent
      $roundingLimit = round((count($claimCollections) + 1) * 0.01, 2);
      if ($valueLeft > 0 && $valueLeft <= $roundingLimit) {
        \App\Log::warning("Collections::applyCollectionToClaims:valueLeft is less then $roundingLimit, applying $valueLeft to first claim");
        $claimCollections[0]->claimCollection->set('assigned_limit_reserve',
          round(($claimCollections[0]->claimCollection->get('assigned_limit_reserve') ?: 0) + $valueLeft, 2));

        $valueLeft = 0;
      }

      // 12. Assertions
      if ($valueLeft != 0) {
        throw new \Exception("Value left is not 0 ($valueLeft)");
      }

      // 9. Save Claim Collections created temporariy in p.4., if they are not empty.
      foreach ($claimCollections as $claimCollection) {
        if ($claimCollection->shouldSaveCollection) {
          $claimCollection->claimCollection->save();
        }
        if ($claimCollection->shouldSaveClaim) {
          $claimCollection->claim->setHandlerExceptions(['disableHandlerClasses' => ['ModTracker_ModTrackerHandler_Handler']]);
          $claimCollection->claim->save();
          $claimCollection->claim->setHandlerExceptions(['disableHandlerClasses' => []]);
        }
        if ($claimCollection->shouldSavePortfolio) {
          $claimCollection->portfolio->save();
        }
      }

      if ($shouldSaveCase) {
        $case->save();
      }

      // 10. Set Date of calculations = current date and time
      $this->set('date_of_calculations', date('Y-m-d H:i:s'));

      $this->save();

      // 11. Recalculate values in related Claims and Portfolios; for each Claim Collection
      foreach ($claimCollections as $claimCollection) {
        $claimCollection->claim = \Vtiger_Record_Model::getInstanceById($claimCollection->claim->getId());
        $claimCollection->portfolio = \Vtiger_Record_Model::getInstanceById($claimCollection->portfolio->getId());
        $claimCollection->claim->recalculateFromClaimCollections();
        $claimCollection->portfolio->recalculateFromClaims();
      }

      // 12. Assertions
      foreach ($claimCollections as $claimCollection) {
        $totalPMCCollections = $claimCollection->claim->get('total_pmc_collections') ?: 0;
        $adjustedFaceValue = $claimCollection->claim->get('adjusted_face_value') ?: 0;

        if ($totalPMCCollections > $adjustedFaceValue) {
          throw new \Exception("PMC Collections ($totalPMCCollections) > Adjusted Face Value ($adjustedFaceValue) for Claim {$claimCollection->claim->getId()}");
        }
      }
    // }
  }
}

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
 * Class Claims_Record_Model.
 */
class Claims_Record_Model extends Vtiger_Record_Model
{
  public function recalculateFromClaimCollections() {
    $id = $this->getId();
    
		\App\Log::warning("Claims::recalculateFromClaimCollections:$id");

    // If Lock automation = Yes, do nothing
    if(!$this->get('lock_automation')) {
      // Calculate Total Voluntary Collections = sum of “Claim Collection. Assigned value” if “Claim Collection. Collection. Collection Type” = Voluntary
      $totalVoluntary = 0;

      // Calculate Total Pre-suit Collections = sum of “Claim Collection. Assigned value” if “Claim Collection. Collection. Collection Type” = Pre-suit
      $totalPreSuit = 0;

      // Calculate Total Litigated Collections = sum of “Claim Collection. Assigned value” if “Claim Collection. Collection. Collection Type” = Litigation
      $totalLit = 0;

      // Calculate Total Collections = sum of “Claim Collection. Assigned value”; NOTE: it does not cover Limit reserve (there is a separate field for it)
      $total = 0;

      // Calculate “Total balance owed” = Adjusted Face Value – Total Collections
      $balanceOwed = 0;

      // Calculate “Remaining to hurdle” = Min(Hurdle – Total Collections, 0 )
      $remainingHurdle = 0;

      // Calculate “Total profit” = Total collections – Purchase price, not more than Factor Fee, not less than 0 (i.e Max(Min(Total collections, Hurdle) – Purchase Price, 0))
      $profit = 0;

      // if Portfolio Purchase. Program. Type of Program = By Claim then calculate “Refundable reserve” = Total Collections – Hurdle, not less than 0, not more than (Adjusted Face Value – Hurdle); otherwise (if Program = Pool) set “empty value”
      $refundableReserve = null;

      // Calculate “Limit reserve” = sum of Claim Collection. Assigned limit reserve
      $limitReserve = 0;

      // Calculate Total Reserves to be Relased = (Refundable Reserve + Limit Reserve) – Total Reserves Released
      $totalReservesToBeReleased = 0;

      // Total PMC Collections = Sum of Claim Collections.(Assigned below hurdle + Assigned refundable reserve)
      $pmc = 0;

      $claimCollections = Vtiger_RelationListView_Model::getInstance($this, "ClaimCollections");
      $claimCollectionsRows = $claimCollections->getRelationQuery()->all();
      $claimCollectionsRecords = $claimCollections->getRecordsFromArray($claimCollectionsRows);
      $portfolioPurchase = \App\Record::isExists($this->get('portfolio_purchase')) ? Vtiger_Record_Model::getInstanceById($this->get('portfolio_purchase')) : null;

      $program = NULL;

      if(!empty($portfolioPurchase) && !empty(($portfolioPurchase->get('program')))) {
        $program = Vtiger_Record_Model::getInstanceById($portfolioPurchase->get('program'));
      }

      foreach ($claimCollectionsRecords as $id => $claimCol) {
        $claimCol = Vtiger_Record_Model::getInstanceById($claimCol->getId());
        if (!\App\Record::isExists($claimCol->get('collection'), 'Collections')) {
          continue;
        }
        
        $assignedVal = $claimCol->get('assigned_value');
        // $attorneyFees = $claimCol->get('assigned_value');
        $assignedLimReserve = $claimCol->get('assigned_limit_reserve');

        $assignedBelowHurdle = $claimCol->get('assigned_below_hurdle');
        $assignedRefundReserve = $claimCol->get('assigned_refundable_reserve');


        if (!empty($assignedVal)) {
          $total = $total + $assignedVal;
        }
        
        $collection = Vtiger_Record_Model::getInstanceById($claimCol->get('collection'));
        
        if($collection->get('collection_type') === "Pre-Suit Voluntary") {
          if (!empty($assignedVal)) {
            $totalVoluntary = $totalVoluntary + $assignedVal;
          }
        }

        if($collection->get('collection_type') === "Pre-Suit Settlement") {
          if (!empty($assignedVal)) {
            $totalPreSuit = $totalPreSuit + $assignedVal;
          }
        }

        if($collection->get('collection_type') === "Damages") {
          if (!empty($assignedVal)) {
            $totalLit = $totalLit + $assignedVal;
          }
        }

        if (!empty($assignedLimReserve)) {
          $limitReserve = $limitReserve + $assignedLimReserve;
        }

        if (!empty($assignedBelowHurdle)) {
          $pmc = $pmc + $assignedBelowHurdle;
        }
        if (!empty($assignedRefundReserve)) {
          $pmc = $pmc + $assignedRefundReserve;
        }
      }

      $adjFaceVal = $this->get('adjusted_face_value') ?: 0;
      $balanceOwed = $adjFaceVal - ($total ?: 0);

      $hurdle = $this->get('hurdle') ?: 0;
      $remainingHurdle = $hurdle - ($total ?: 0);
      if($remainingHurdle < 0) {
        $remainingHurdle = 0;
      }

      $purchasePrice = $this->get('purchase_price') ?: 0;
      $factorFee = $this->get('factor_fee') ?: 0;

      $profit = ($total ?: 0) - $purchasePrice;
      if($profit < 0) {
        $profit = 0;
      }
      if($profit > $factorFee) {
        $profit = $factorFee;
      }

      if($program !== NULL && $program->get('program_type') === "By Claim") {
        $refundableReserve = ($total ?: 0) - $hurdle;

        if($refundableReserve < 0) {
          $refundableReserve = 0;
        }
        if($refundableReserve > ($adjFaceVal - $hurdle)) {
          $refundableReserve = $adjFaceVal - $hurdle;
        }
      }

      $totalReservesToBeReleased = ($refundableReserve ?: 0) + ($limitReserve ?: 0) - ($this->get('total_reserved_released') ?: 0);

      \App\Log::trace("Claims::recalculateFromClaimCollections:total_voluntary_ollections = $totalVoluntary");
      $this->set('total_voluntary_ollections', round($totalVoluntary, 2));

      \App\Log::trace("Claims::recalculateFromClaimCollections:total_pre_suit_collections = $totalPreSuit");
      $this->set('total_pre_suit_collections', round($totalPreSuit, 2));

      \App\Log::trace("Claims::recalculateFromClaimCollections:total_litigated_collections = $totalLit");
      $this->set('total_litigated_collections', round($totalLit, 2));

      \App\Log::trace("Claims::recalculateFromClaimCollections:total_collections = $total");
      $this->set('total_collections', round($total, 2));

      \App\Log::trace("Claims::recalculateFromClaimCollections:total_balance_owed = $balanceOwed");
      $this->set('total_balance_owed', round($balanceOwed, 2));

      \App\Log::trace("Claims::recalculateFromClaimCollections:remaining_to_hurdle = $remainingHurdle");
      $this->set('remaining_to_hurdle', round($remainingHurdle, 2));

      \App\Log::trace("Claims::recalculateFromClaimCollections:total_profit = $profit");
      $this->set('total_profit', round($profit, 2));

      \App\Log::trace("Claims::recalculateFromClaimCollections:refundable_reserve = $refundableReserve");
      $this->set('refundable_reserve', round($refundableReserve, 2));

      \App\Log::trace("Claims::recalculateFromClaimCollections:limit_reserve = $limitReserve");
      $this->set('limit_reserve', round($limitReserve, 2));

      \App\Log::trace("Claims::recalculateFromClaimCollections:total_reserves_to_be_released = $totalReservesToBeReleased");
      $this->set('total_reserves_to_be_released', round($totalReservesToBeReleased, 2));

      \App\Log::trace("Claims::recalculateFromClaimCollections:total_pmc_collections = $pmc");
      $this->set('total_pmc_collections', round($pmc, 2));

      $this->save();
    }
  }

  /** FIND_RELATED_CASE */
  public function findRelatedCase() {
    $id = $this->getId();
    $identifier = $this->getIdentifier();
    $caseId = $this->get('case') ?: $this->get('outside_case');

    $claimNumber = preg_replace('/[^[:alnum:]]+/ui', '', $this->get('claim_number'));
    $provider = $this->get('provider');
    $claimType = $this->get('type_of_claim');
    $insured = $this->get('insured');
    if (\App\Record::isExists($insured, 'Insureds'))
    {
      $state = Vtiger_Record_Model::getInstanceById($insured, 'Insureds')->get('state');
    }
    $supportedClaimTypes = ['AOB', 'LOP/DTP'];

		\App\Log::warning("Claims::findRelatedCase:$id/$caseId ($identifier/$claimNumber/$provider/$state)");

    if (\in_array($claimType, $supportedClaimTypes) && (empty($caseId) || !\App\Record::isExists($caseId)) && $claimNumber) {
      try {
        $isOutside = $this->checkIsOutside();

        $queryGenerator = (new \App\QueryGenerator($isOutside ? 'OutsideCases' : ( $state === 'TX Texas' ? 'TexasCases' : 'Cases')))
          ->setField('id')
          ->addCondition('provider', $provider, 'eid')
          ->addCondition('type_of_claim', $supportedClaimTypes, 'e')
          ->addCondition($isOutside ? 'otc_final_status' : 'final_status', 'CLOSED', 'n');
        $query = $queryGenerator
          ->createQuery();
        if ($claimNumber) {
          $query->andWhere(["regexp_replace(claim_number, '[^[:alnum:]]+', '')" => $claimNumber]);
        }
        $caseId = $query->scalar();

        if (!empty($caseId)) {
          $this->set($isOutside ? 'outside_case' : 'case', $caseId);
          $this->set($isOutside ? 'case' : 'outside_case', null);
          $this->save();
        } else {
          \App\Log::warning("Case not found using query: " . $queryGenerator->createQuery()->createCommand()->getRawSql());
        }
      } catch (\Exception $e) {
        \App\Log::error("Claims::findRelatedCase:ERROR " . var_export($e, true));
      }
    }
  }

  private function getIdentifier() {
    $conductedBy = $this->get('conducted_by');
    $typeOfClaim = $this->get('type_of_claim');
    $attorney = 'empty';
    if (\App\Record::isExists($this->get('aob_dtp_attorney'))) {
      $attorneyRecord = Vtiger_Record_Model::getInstanceById($this->get('aob_dtp_attorney'));
      if (\App\Record::isExists($attorneyRecord->get('law_firm'))) {
        $lawFirm = \App\Record::getLabel($attorneyRecord->get('law_firm'));
        $attorney = preg_match('/fl.*ins.*law/i', $lawFirm, $matches) === 1 ? 'FLINS' : 'non-FLINS';
      }
    }
    $identifier = "$conductedBy/$typeOfClaim/$attorney";

    return $identifier;
  }

  /** 
   * Checks if is outside case. Throws exception for unsupported parameter combinations
   */
  public function checkIsOutside() {
    $identifier = $this->getIdentifier();

    switch ($identifier) {
      case 'FLINSLAW/AOB/FLINS':
      case 'FLINSLAW/AOB/non-FLINS':
      case 'FLINSLAW/AOB/empty':
        // PDC
        return false;
        break;
      case 'FLINSLAW/LOP/DTP/FLINS':
      case 'FLINSLAW/LOP/DTP/non-FLINS':
      case 'FLINSLAW/LOP/DTP/empty':
      case 'FLINSLAW/PA/FLINS':
      case 'FLINSLAW/PA/non-FLINS':
      case 'FLINSLAW/PA/empty':
        // LOP
        return false;
        break;
      case 'Outside/AOB/FLINS':
      case 'Outside/AOB/non-FLINS':
      case 'Outside/AOB/empty':
      case 'Outside/LOP/DTP/non-FLINS':
      case 'Outside/LOP/DTP/empty':
      case 'Outside/HO/FLINS':
      case 'Outside/HO/non-FLINS':
      case 'Outside/HO/empty':
      case 'Outside/Estimates/FLINS':
      case 'Outside/Estimates/non-FLINS':
      case 'Outside/Estimates/empty':
      case 'Outside/PA/FLINS':
      case 'Outside/PA/non-FLINS':
      case 'Outside/PA/empty':
        // OTC
        return true;
        break;
      case 'FLINSLAW/HO/FLINS':
      case 'FLINSLAW/HO/non-FLINS':
      case 'FLINSLAW/HO/empty':
      case 'FLINSLAW/Estimates/FLINS':
      case 'FLINSLAW/Estimates/non-FLINS':
      case 'FLINSLAW/Estimates/empty':
        // HOS
        return false;
        break;
      case 'FLINSLAW/FIGA/FLINS':
      case 'FLINSLAW/FIGA/non-FLINS':
      case 'FLINSLAW/FIGA/empty':
      case 'Outside/FIGA/FLINS':
      case 'Outside/FIGA/non-FLINS':
      case 'Outside/FIGA/empty':
        // FIGA
        return false;
        break;
      // case '/FLOOD/':
      // case 'Outside/LOP/DTP/FLINS':
      default:
        // ERROR!
        throw new \Exception("Invalid claim parameters combination to create case - $identifier");
    }
  }

  /** ON_PURCHASED_COND_CREATE_CASE */
  public function onPurchasedCondCreateCase() {
    $id = $this->getId();
    $identifier = $this->getIdentifier();
    $caseId = $this->get('case') ?: $this->get('outside_case');
    $insured = $this->get('insured');
    if (\App\Record::isExists($insured, 'Insureds'))
    {
      $state = Vtiger_Record_Model::getInstanceById($insured, 'Insureds')->get('state');
    }

		\App\Log::warning("Claims::onPurchasedCondCreateCase:$id/$caseId ($identifier, $state)");

    if (empty($caseId) || !\App\Record::isExists($caseId)) {
      $isOutside = $this->checkIsOutside();

      $case = Vtiger_Record_Model::getCleanInstance($isOutside ? 'OutsideCases' : ($state === 'TX Texas' ? 'TexasCases' : 'Cases'));
      $case->setRecordFieldValues($this);
      $case->set('first_notice_of_loss', $this->get('date_of_first_notification'));
      $case->set('pre_litigation_status', null);
      $case->save();

      $this->set($isOutside ? 'outside_case' : 'case', $case->getId());
      $this->set($isOutside ? 'case' : 'outside_case', null);
      $this->save();
    }
  }

  /** FIND_SIMILAR_CLAIMS */
  public function findSimilarClaims() {
    $id = $this->getId();
    
		\App\Log::warning("Claims::findSimilarClaims:$id");

    /*
    
    Temporarily create a new Similar Claims entry
    Find all Claims with the same Claim Number, temporarily assign them to the Similar Claims
    Find all Claims with the same Policy Number, temporarily assign them to the Similar Claims
    Find all Claims with the same Insured, temporarily assign them to the Similar Claims
    Find all Claims with the same Insured.Address (street+zip+state+city, w/o white characters, case insensitive), temporarily assign them to the Similar Claims
    If there is more than one Claim attached to the Similar Claims entry
        set Similar Claims name = the first Claim ID (alphabetically) from all Claims attached to this Similar Claims entry
        set Similar Claims.# similar claims = number of similar claims
        if Similar Claims entry (by name) does not exist in CMS, save the Similar Claims entry in CMS database
        for each Claim found in the first steps of the algorithm:
            unattach this claim from its Similar Claims and if only one Claim is left in this Similar Claims, delete this Similar Claims
            set Claim.Similar Claims = this Similar Claims entry
    If there is more only one Claim attached to the Similar Claims entry
        do not save this temporary entry
        Set Claim.Similar Claims = empty

    In other words: the algorithm updates Claim.Similar Claims for this Claim and all Claims that are similar. Resulting Similar Claims objects should have at least one related Claim – if not, such Similar Claims entry should be deleted.

    The algorithm should show changes in history, but without showing any temporary steps.
    */

    // get claims
    $queryGenerator = (new \App\QueryGenerator('Claims'));

    $insuredNameQueryField = $queryGenerator->getQueryRelatedField('insured_name:Insureds:insured');
    $insuredNameRelatedField = $insuredNameQueryField->getRelated();
    $insuredStreetQueryField = $queryGenerator->getQueryRelatedField('street:Insureds:insured');
    $insuredStreetRelatedField = $insuredStreetQueryField->getRelated();
    $insuredZipQueryField = $queryGenerator->getQueryRelatedField('zip:Insureds:insured');
    $insuredZipRelatedField = $insuredZipQueryField->getRelated();
    $insuredStateQueryField = $queryGenerator->getQueryRelatedField('state:Insureds:insured');
    $insuredStateRelatedField = $insuredStateQueryField->getRelated();
    $insuredCityQueryField = $queryGenerator->getQueryRelatedField('city:Insureds:insured');
    $insuredCityRelatedField = $insuredCityQueryField->getRelated();

    $queryGenerator
      ->setFields(['id', 'claim_number', 'policy_number'])
      ->addRelatedField($insuredNameRelatedField)
      ->addRelatedField($insuredStreetRelatedField)
      ->addRelatedField($insuredZipRelatedField)
      ->addRelatedField($insuredStateRelatedField)
      ->addRelatedField($insuredCityRelatedField)
      ->setOrder('claim_id');

    $query = $queryGenerator->createQuery();

    $claimNumber = preg_replace('/[^[:alnum:]]+/ui', '', $this->get('claim_number'));
    $policyNumber = preg_replace('/[^[:alnum:]]+/ui', '', $this->get('policy_number'));
    if (\App\Record::isExists($this->get('insured'))) {
      $insured = Vtiger_Record_Model::getInstanceById($this->get('insured'));
      $insuredName = $insured->get('insured_name');
      $insuredAddress = $insured->get('street') . $insured->get('zip') . $insured->get('state') . $insured->get('city');
    }
    $conditions = ['or'];
    if ($claimNumber) {
      $conditions[] = ["regexp_replace(claim_number, '[^[:alnum:]]+', '')" => $claimNumber];
    }
    if ($policyNumber) {
      $conditions[] = ["regexp_replace(policy_number, '[^[:alnum:]]+', '')" => $policyNumber];
    }
    if ($insured) {
      $conditions[] = ["lower(replace(replace({$insuredNameQueryField->getColumnName()}, ',', ''), ' ', ''))" => strtolower(str_replace(' ', '', str_replace(',', '', $insuredName)))];
      if ($insuredAddress) {
        $conditions[] = ["lower(replace(replace(concat({$insuredCityQueryField->getColumnName()}, {$insuredZipQueryField->getColumnName()}, {$insuredStateQueryField->getColumnName()}, {$insuredCityQueryField->getColumnName()}), ',', ''), ' ', ''))" => strtolower(str_replace(' ', '', str_replace(',', '', $insuredAddress)))];
      }
    }

    $query->andWhere($conditions);

    $allClaims = array_map(
      function ($row) { return Vtiger_Record_Model::getInstanceById($row); }, 
      $query->column()
    );

    if (count($allClaims) > 1) {
      $claimsToSave = [];
      
      // get first (alphabetically) Claim ID of $allClaims
      $name = $this->get('claim_id');

      // calculate amounts
      $numSameClaim = 0;
      $numSamePolicy = 0;
      $numSameName = 0;
      $numSameAddress = 0;
      foreach ($allClaims as $claim) {
        if ($claimNumber === preg_replace('/[^[:alnum:]]+/ui', '', $claim->get('claim_number'))) {
          $numSameClaim++;
        }
        if ($policyNumber === preg_replace('/[^[:alnum:]]+/ui', '', $claim->get('policy_number'))) {
          $numSamePolicy++;
        }
        if ($insured && \App\Record::isExists($claim->get('insured'))) {
          $claimInsured = Vtiger_Record_Model::getInstanceById($claim->get('insured'));
          $claimInsuredName = $claimInsured->get('insured_name');
          $claimInsuredAddress = $claimInsured->get('street') . $claimInsured->get('zip') . $claimInsured->get('state') . $claimInsured->get('city');
          if (\App\Utils::str_equal($insuredName, $claimInsuredName)) {
            $numSameName++;
          }
          if (\App\Utils::str_equal($insuredAddress, $claimInsuredAddress)) {
            $numSameAddress++;
          }
        }
      }

      // retrieve Similar Claims by name; if not exists, create
      $similarClaimsId = (new \App\QueryGenerator('SimilarClaims'))
        ->setField('id')
        ->addCondition('similar_claims', $name, 'e')
        ->createQuery()
        ->scalar();

      if (empty($similarClaimsId)) {
        $similarClaims = Vtiger_Record_Model::getCleanInstance('SimilarClaims');
        $similarClaims->set('similar_claims', $name);
        $similarClaims->set('no_similar_claims', count($allClaims));
        $similarClaims->set('no_same_claim_number', $numSameClaim);
        $similarClaims->set('no_same_policy_number', $numSamePolicy);
        $similarClaims->set('no_same_insured_name', $numSameName);
        $similarClaims->set('no_same_insured_address', $numSameAddress);
        $similarClaims->save();

        $similarClaimsId = $similarClaims->getId();
      } else {
        $similarClaims = Vtiger_Record_Model::getInstanceById($similarClaimsId);
        $similarClaims->set('no_similar_claims', count($allClaims));
        $similarClaims->set('no_same_claim_number', $numSameClaim);
        $similarClaims->set('no_same_policy_number', $numSamePolicy);
        $similarClaims->set('no_same_insured_name', $numSameName);
        $similarClaims->set('no_same_insured_address', $numSameAddress);
        $similarClaims->save();

        // remove from all old claims, will be readded for matching ones below
        $allClaimIds = array_map(function ($el) { return $el->getId(); }, $allClaims);
        foreach (VTWorkflowUtils::getAllRelatedRecords($similarClaims, 'Claims') as $claimRow) {
          if (!\in_array($claimRow['id'], $allClaimIds)) {
            $claim = Vtiger_Record_Model::getInstanceById($claimRow['id']);
            $claim->set('similar_claims', 0);
    
            $claimsToSave[] = $claim;
          }
        }
      }

      // foreach $allClaims - store it's current similar claim, assign current
      $similarClaimsToCheck = [];
      foreach ($allClaims as $claim) {
        $oldSimilarClaimsId = $claim->get('similar_claims');
        if (!empty($oldSimilarClaimsId)) {
          $similarClaimsToCheck[] = $oldSimilarClaimsId;
        }
        $claim->set('similar_claims', $similarClaimsId);

        $claimsToSave[] = $claim;
      }

      // save
      foreach ($claimsToSave as $claimToSave) {
        $claimToSave->save();
      }

      foreach (array_unique($similarClaimsToCheck) as $similarClaimToCheckId) {
        if (!\App\Record::isExists($similarClaimToCheckId)) {
          continue;
        }

        $similarClaimToCheck = Vtiger_Record_Model::getInstanceById($similarClaimToCheckId);

        // check if has 0 or 1 claims, if so then delete
        $claims = VTWorkflowUtils::getAllRelatedRecords($similarClaimToCheck, 'Claims');
        if (count($claims) <= 1) {
          $similarClaimToCheck->changeState('Trash');
        }
      }
    } else if ($this->get('similar_claims')) {
      $similarClaimToCheckId = $this->get('similar_claims');

      $this->set('similar_claims', 0);
      $this->save();

      if (\App\Record::isExists($similarClaimToCheckId)) {
        $similarClaimToCheck = Vtiger_Record_Model::getInstanceById($similarClaimToCheckId);
        // check if has 0 or 1 claims, if so then delete
        $claims = VTWorkflowUtils::getAllRelatedRecords($similarClaimToCheck, 'Claims');
        if (count($claims) <= 1) {
          if (count($claims) === 1) {
            $claim = Vtiger_Record_Model::getInstanceById($claims['id']);
            $claim->set('similar_claims', 0);
            $claim->save();
          }

          $similarClaimToCheck->changeState('Trash');
        }
      }
    }
  }

  /**
   * Calculates Total PMC Collections for specified, closed threshold. If threshold is open, returns false.
   * 
   * @param int $months Threshold months
   * @return float|false Threshold collections or false if threshold is open
   */
  function calculateTotalPMCCollectionsForMonths(int $months) {
    $claimId = $this->getId();
    \App\Log::warning("Claims::calculateTotalPMCCollectionsForMonths:$claimId/$months");

    // sum `Claim Collections`.`assigned_below_hurdle + assigned_refundable_reserve` related to this Claim 
    // for which `Claim Collection`.`Collection`.`payment_date` is 
    // between `Claim`.`Portfolio Purchase`.`purchase_date` 
    // and `Claim`.`Portfolio Purchase`.`purchase_date` + threshold months

    // find purchase date
    $portfolioPurchaseId = $this->get('portfolio_purchase');
    if (empty($portfolioPurchaseId) || !\App\Record::isExists($portfolioPurchaseId, 'PortfolioPurchases')) {
      throw new \Exception("Claim {$this->getDisplayName()} ($claimId) does not have a Portfolio Purchase set");
    }
    $purchaseDate = Vtiger_Record_Model::getInstanceById($portfolioPurchaseId, 'PortfolioPurchases')->get('purchase_date');
    $purchaseDatePlusThreshold = date('Y-m-d', strtotime("+$months months", strtotime($purchaseDate)));
    if ($purchaseDatePlusThreshold > date('Y-m-d')) {
      return false;
    }

    $collectionPaymentDateRelatedField = [
      'sourceField' => 'collection',
      'relatedModule' => 'Collections',
      'relatedField' => 'payment_date'
    ];
    $qg = (new \App\QueryGenerator('ClaimCollections'))
      ->addCondition('claim', $claimId, 'eid')
      ->addRelatedCondition(
        array_merge(
          $collectionPaymentDateRelatedField, 
          ['value' => "$purchaseDate,$purchaseDatePlusThreshold", 
          'operator' => 'bw'])
        )
      ->setFields(['assigned_below_hurdle', 'assigned_refundable_reserve']);

    $sum = ($qg->createQuery()->sum('Coalesce(`assigned_below_hurdle`, 0) + Coalesce(`assigned_refundable_reserve`, 0)') ?? 0);
    \App\Log::warning("Claims::calculateTotalPMCCollectionsForMonths:$claimId/$months:sum = $sum");
    return $sum;
  }
}

<?php

/**
 * ClaimsWorkflow.
 *
 * @package   Workflow
 *
 * @copyright DOT Systems
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Kamiński <mkaminski@dotsystems.pl>
 * @author    Michał Jastrzębski <mjastrzebski@dotsystems.pl>
 */
class ClaimsWorkflow
{
  /**
	 * Recalculate from claim collections
	 *
	 * @param \Claims_Record_Model $recordModel
	 */
	public static function recalculateFromClaimCollections(Vtiger_Record_Model $recordModel)
	{
		$id = $recordModel->getId();

		\App\Log::warning("Claims::Workflows::recalculateFromClaimCollections:" . $id);
    
    $recordModel->recalculateFromClaimCollections();
	}

  /**
	 * Recalculate financial summary
	 *
	 * @param \Claims_Record_Model $recordModel
	 */
	public static function recalculateFinancialSummary(Vtiger_Record_Model $recordModel)
	{
		$id = $recordModel->getId();

		\App\Log::warning("Claims::Workflows::recalculateFinancialSummary:" . $id);
    
    // Factor Fee = Adjusted Face Value * Program.Factor Fee %
    $factorFee = 0;

    // Hurdle = Purchase price + Factor Fee
    $hurdle = 0;

    // Hurdle % = Hurdle / Adjusted Face Value
    $hurdlePercent = 0;

    // Purchase Price = sum of (ClaimedInvoice.Purchase Price) of all Claimed Invoices attached to this Claim
    $pprice = 0;

    // Total Bill Amount = sum of (ClaimedInvoice.Invoice Value) of all Claimed Invoices attached to this Claim
    $bill = 0;

    // Prior Collections = sum of (ClaimedInvoice.Prior Collections) of all Claimed Invoices attached to this Claim
    $prior = 0;

    // Overhead and Profit = sum of (ClaimedInvoice.Overhead and Profit) of all Claimed Invoices attached to this Claim
    $overhead = 0;

    // Adjustments = sum of (ClaimedInvoice.Adjustment) of all Claimed Invoices attached to this Claim
    $adjustments = 0;

    // Adjusted Face Value = Total Bill Amount - Prior Collections - Overhead and Profit - Adjustments
    $adjFaceVal = 0;

    // Types of Services = sum of sets (ClaimedInvoice.Types of Services) of all Claimed Invoices attached to this Claim
    $toServices = [];

    // Cash to Seller = perc_advance / 100 * purchase_price
    $cashToSeller = 0;

    // When Buyback Status = BB Applied Then
    // Internal Cash Transfer = Purchase Price - Total Collections
    // Total Penalty = Adjusted FV * Buyback Penalty %

    $portfolioPurchase = NULL;

    if(!empty($recordModel->get('portfolio_purchase'))) {
      $portfolioPurchase = Vtiger_Record_Model::getInstanceById($recordModel->get('portfolio_purchase'));
    }

    $claimedInvoices = Vtiger_RelationListView_Model::getInstance($recordModel, "ClaimedInvoices");
		$claimedInvoicesRows = $claimedInvoices->getRelationQuery()->all();
		$claimedInvoicesRecords = $claimedInvoices->getRecordsFromArray($claimedInvoicesRows);

		foreach ($claimedInvoicesRecords as $id => $ci) {
      $ci = Vtiger_Record_Model::getInstanceById($ci->getId());

      $val = $ci->get('invoice_value');
      $pc = $ci->get('prior_collections');
      $op = $ci->get('overhead_and_profit');
      $ad = $ci->get('adjustment');
      $pp = $ci->get('purchase_price');
      $tos = $ci->get('types_of_services');

      if (!empty($val)) {
        $bill = $bill + $val;
      }

      if (!empty($pc)) {
        $prior = $prior + $pc;
      }

      if (!empty($op)) {
        $overhead = $overhead + $op;
      }

      if (!empty($ad)) {
        $adjustments = $adjustments + $ad;
      }

      if (!empty($pp)) {
        $pprice = $pprice + $pp;
      }

      if (!empty($tos)) {
        $toServices = array_unique(array_merge($toServices, explode(" |##| ", $tos)));
      }
		}

    if (!empty($toServices)) {
      $toServices = implode(" |##| ", $toServices);
    }
    else {
      $toServices = NULL;
    }

    $program = NULL;
    $factorPerc = 0;

    if($portfolioPurchase !== NULL && !empty(($portfolioPurchase->get('program')))) {
      /** @var Programs_Record_Model $program */
      $program = Vtiger_Record_Model::getInstanceById($portfolioPurchase->get('program'));
      $factorPerc = $program->get('factor_fee_perc') ?: 0;

      $algorithmId = $program->get('program_algorithm');
      if (!empty($algorithmId)) {
        $algorithm = Vtiger_Record_Model::getInstanceById($algorithmId);
        $algorithmNumber = $algorithm->get('number');
      }
    }
    
    $adjFaceVal = ($bill ?: 0) - ($prior ?: 0) - ($overhead ?: 0) - ($adjustments ?: 0);

    switch ($algorithmNumber) {
      case 'FF_STEP_UPS_AFTER_MONTHS':
        \App\Log::warning("Claims::Workflows::recalculateFinancialSummary:FF_STEP_UPS_AFTER_MONTHS data = ". $recordModel->get('internal_temporal_data') . ', params = ' . $program->get('algorithm_parameters'));

        $factorFee = $adjFaceVal * $factorPerc / 100;

        // REPLACED by online calculation according to https://github.com/dotsystemsspzoo/hestia-pdss/issues/527
        // get temporal data for claim
        // $temporalData = \App\Json::decode($recordModel->get('internal_temporal_data')) ?: [];
        // $temporalData = $temporalData['Total PMC Collections of threshold dates'];
        $temporalData = [];

        // get algorithm parameters
        $thresholds = $program->parseParametersForMonthStepUps();

        // use thresholds and percentage differences to add to factor fee
        $lastPercentage = $factorPerc;
        // REPLACED by online calculation according to https://github.com/dotsystemsspzoo/hestia-pdss/issues/527
        // for ($i = 0; $i < count($temporalData) && $i < count($thresholds); $i++) {
          for ($i = 0; $i < count($thresholds); $i++) {
          ['percent' => $percentage, 'months' => $months] = $thresholds[$i];
          // REPLACED by online calculation according to https://github.com/dotsystemsspzoo/hestia-pdss/issues/527
          // $totalCollections = $temporalData[$i];
          // if (!\is_numeric($totalCollections)) {
          //   throw new \Exception("Total PMC Collections for " . ($i + 1) . " threshold is not a number");
          // }
          // calculate temporal data for threshold
          $totalCollections = $recordModel->calculateTotalPMCCollectionsForMonths($months);
          if ($totalCollections === false) {
            \App\Log::warning("Claims::Workflows::recalculateFinancialSummary:FF_STEP_UPS_AFTER_MONTHS threshold is open");
            break;
          }

          $temporalData[] = $totalCollections;

          // \App\Log::warning(var_export(['last' => $lastPercentage, 'perc' => $percentage, 'total' => $totalCollections, 'adj' => $adjFaceVal, 'delta' => ($adjFaceVal - $totalCollections) * ($percentage - $lastPercentage) / 100], true));

          $factorFee += ($adjFaceVal - $totalCollections) * ($percentage - $lastPercentage) / 100;
          $lastPercentage = $percentage;
        }

        $temporalData = [ 'Total PMC Collections of threshold dates' => $temporalData ];
        $recordModel->set('internal_temporal_data', \App\Json::encode($temporalData));
        break;
      case 'FF_FROM_PP':
        /*
        Factor Fee is calculated based on Purchase Price; 
        Claim.Factor Fee = Claim.Purchase Price / Portfolio Purchase.Program.Purchase Price % * Portfolio Purchase.Program.Factor Fee %
        */
        $programPPPerc = $program->get('purchase_price_perc');
        \App\Log::warning("Claims::Workflows::recalculateFinancialSummary:FF_FROM_PP pp perc = $programPPPerc");
        $factorFee = $pprice / ($programPPPerc/100) * $factorPerc / 100;
        break;
      case 'FF_SPECIAL_PCG_1500_1100':
        /* 
        Factor Fee is based on constant amount based on related Claimed Invoice Purchase Price, 
        600 for 1500 and 450 for 1100 (other values should not happen, but if they do than use 
        difference between Claimed Invoice Adjusted Invoice Value and Claimed Invoice Purchase Price)
        */
        \App\Log::warning("Claims::Workflows::recalculateFinancialSummary:FF_SPECIAL_PCG_1500_1100");
        $factorFee = 0;
        foreach ($claimedInvoicesRecords as $id => $ci) {
          $ci = Vtiger_Record_Model::getInstanceById($ci->getId());
          $ciPurchasePrice = $ci->get('purchase_price') ?: 0;
          $ciAdjustedInvoiceValue = $ci->get('adjusted_invoice_value') ?: 0;
          switch ($ciPurchasePrice) {
            case 1500:
              $factorFee += 600;
              break;
            case 1100:
              $factorFee += 450;
              break;
            default:
              $factorFee += $ciAdjustedInvoiceValue - $ciPurchasePrice;
              break;
          }
        }
        break;
      default:
        $factorFee = $adjFaceVal * $factorPerc / 100;
        break;
    }
    

    $hurdle = $pprice + $factorFee;

    if ($adjFaceVal) {
      $hurdlePercent = $hurdle / $adjFaceVal * 100;
    }

    $cashToSeller = $pprice * $recordModel->get('perc_advance') / 100;

    $recordModel->set('total_bill_amount', round($bill, 2));
    $recordModel->set('prior_collections', round($prior, 2));
    $recordModel->set('overhead_and_profit', round($overhead, 2));
    $recordModel->set('adjustments', round($adjustments, 2));
    $recordModel->set('adjusted_face_value', round($adjFaceVal, 2));
    $recordModel->set('purchase_price', round($pprice, 2));
    $recordModel->set('factor_fee', round($factorFee, 2));
    $recordModel->set('hurdle', round($hurdle, 2));
    $recordModel->set('hurdle_percent', round($hurdlePercent, 2));
    $recordModel->set('types_of_services', $toServices);
    $recordModel->set('cash_to_seller', round($cashToSeller, 2));

    if ($recordModel->get('buyback_status') === 'BB applied') {
      $internalCashTransfer = $pprice - ($recordModel->get('total_collections') ?: 0);
      $totalPenalty = $adjFaceVal * ($recordModel->get('buyback_penalty_percent') ?: 0) / 100;

      $recordModel->set('internal_cash_transfer', round($internalCashTransfer, 2));
      $recordModel->set('total_penalty', round($totalPenalty, 2));
    }

    $recordModel->save();
  }

  /**
	 * Find related case
	 *
	 * @param \Claims_Record_Model $recordModel
	 */
  public static function findRelatedCase(Vtiger_Record_Model $recordModel)
	{
    $id = $recordModel->getId();

		\App\Log::warning("Claims::Workflows::findRelatedCase:$id");

    $recordModel->findRelatedCase();
	}

  /**
	 * Create case
	 *
	 * @param \Claims_Record_Model $recordModel
	 */
  public static function onPurchasedCondCreateCase(Vtiger_Record_Model $recordModel)
	{
    $id = $recordModel->getId();

		\App\Log::warning("Claims::Workflows::onPurchasedCondCreateCase:$id");

    $recordModel->onPurchasedCondCreateCase();
	}

  /**
	 * Create similar claims
	 *
	 * @param \Claims_Record_Model $recordModel
	 */
  public static function findSimilarClaims(Vtiger_Record_Model $recordModel)
	{
    $id = $recordModel->getId();

		\App\Log::warning("Claims::Workflows::findSimilarClaims:$id");

    $recordModel->findSimilarClaims();
	}

  /**
	 * Verify ONB data
   * 
   * VERIFY_ONB_DATA
   * https://github.com/dotsystemsspzoo/hestia-pdss/issues/117
	 *
	 * @param \Claims_Record_Model $recordModel
	 */
	public static function verifyOnbData(Vtiger_Record_Model $recordModel)
	{
		$id = $recordModel->getId();

		\App\Log::warning("Claims::Workflows::verifyOnbData:" . $id);

    $onb = "";
    $onbAcceptant = "";
    $onbOld = $recordModel->get('onb_warnings') ?: "";
    $onbAcceptantOld = $recordModel->get('onb_warning_to_acceptant') ?: "";
    $days = NULL;
    $nl = "\n";
    $insuredChanged = false;

    if(!empty(($recordModel->get('date_of_first_notification'))) && !empty(($recordModel->get('aob_date')))) {
			$start = date('Y-m-d', strtotime($recordModel->get('aob_date')));
			$end = date('Y-m-d', strtotime($recordModel->get('date_of_first_notification')));

			$days = date_diff(date_create_from_format('Y-m-d', $start), date_create_from_format('Y-m-d', $end))->days;
		}

    \App\Log::trace("Claims::verifyOnbData:days_apart_dofn_aob = $days");
    $recordModel->set('days_apart_dofn_aob', $days);
    $recordModel->save();

    $insuranceCompanyId = $recordModel->get('insurance_company');
    if (\App\Record::isExists($insuranceCompanyId)) {
      $company = Vtiger_Record_Model::getInstanceById($insuranceCompanyId);


      $blockIC = $company->get('block_ic') == 1;
      $forcePlaceCarrier = $company->get('force_place_carrier') == 'Yes';
      $inGoodStanding = $company->get('in_good_standing') == 'No';

      if ($blockIC) {
        $onb .= ($onb !== "" ? $nl : "") . "Insurance Company is blocked, claim has to be rejected";
        $onbAcceptant .= ($onbAcceptant !== "" ? $nl : "") . "Insured Company is blocked, claim has to be rejected";
      }

      if ($forcePlaceCarrier) {
        $onb .= ($onb !== "" ? $nl : "") . "Insurance Company in Forced Place Carrier";
        $onbAcceptant .= ($onbAcceptant !== "" ? $nl : "") . "Insurance Company in Forced Place Carrier";
      }

      if ($inGoodStanding) {
        $onb .= ($onb !== "" ? $nl : "") . "Insurance  Company in Receivership";
        $onbAcceptant .= ($onbAcceptant !== "" ? $nl : "") . "Insurance Company in Receivership";
      }
    }


    if(empty($recordModel->get('insured'))) {
      $onb = $onb . ($onb !== "" ? $nl : "") . "Insured is empty";
    } else {
      $insured = Vtiger_Record_Model::getInstanceById($recordModel->get('insured'));

      if(!empty($recordModel->get('onb_street')) && empty($insured->get('street'))) {
        \App\Log::trace("Claims::verifyOnbData:insured street empty = set onb street");
        $insured->set('street', $recordModel->get('onb_street'));
        $insuredChanged = true;
      }
      
      if(!empty($recordModel->get('onb_city')) && empty($insured->get('city'))) {
        \App\Log::trace("Claims::verifyOnbData:insured city empty = set onb city");
        $insured->set('city', $recordModel->get('onb_city'));
        $insuredChanged = true;
      }

      if(!empty($recordModel->get('onb_zip')) && empty($insured->get('zip'))) {
        \App\Log::trace("Claims::verifyOnbData:insured zip empty = set onb zi[");
        $insured->set('zip', $recordModel->get('onb_zip'));
        $insuredChanged = true;
      }

      if(!empty($recordModel->get('state')) && empty($insured->get('state'))) {
        \App\Log::trace("Claims::verifyOnbData:insured state empty = set onb state");
        $insured->set('state', $recordModel->get('state'));
        $insuredChanged = true;
      }

      if($insuredChanged) {
        $insured->save();
      }

      if(!\App\Utils::str_equal($recordModel->get('onb_street'), $insured->get('street'))) {
        $onbAcceptant = $onbAcceptant . ($onbAcceptant !== "" ? $nl : "") . "Street: " . $recordModel->get('onb_street') . " <> " . $insured->get('street');
        $onb = $onb . ($onb !== "" ? $nl : "") . "Insured.Street is different than ONB Street";
      }

      if(!\App\Utils::str_equal($recordModel->get('onb_city'), $insured->get('city'))) {
        $onbAcceptant = $onbAcceptant . ($onbAcceptant !== "" ? $nl : "") . "City: " . $recordModel->get('onb_city') . " <> " . $insured->get('city');
        $onb = $onb . ($onb !== "" ? $nl : "") . "Insured.City is different than ONB City";
      }

      if(!\App\Utils::str_equal($recordModel->get('onb_zip'), $insured->get('zip'))) {
        $onbAcceptant = $onbAcceptant . ($onbAcceptant !== "" ? $nl : "") . "ZIP: " . $recordModel->get('onb_zip') . " <> " . $insured->get('zip');
        $onb = $onb . ($onb !== "" ? $nl : "") . "Insured.ZIP is different than ONB ZIP";
      }

      if(!\App\Utils::str_equal($recordModel->get('state'), $insured->get('state'))) {
        $onbAcceptant = $onbAcceptant . ($onbAcceptant !== "" ? $nl : "") . "State: " . $recordModel->get('state') . " <> " . $insured->get('state');
        $onb = $onb . ($onb !== "" ? $nl : "") . "Insured.State is different than ONB State";
      }

      if(!\App\Utils::str_equal($recordModel->get('onb_email'), $insured->get('e_mail'))) {
        $onbAcceptant = $onbAcceptant . ($onbAcceptant !== "" ? $nl : "") . "Email: " . $recordModel->get('onb_email') . " <> " . $insured->get('e_mail');
        $onb = $onb . ($onb !== "" ? $nl : "") . "Insured.Email is different than ONB Email";
      }

      if(empty($insured->get('street'))) {
        $onb = $onb . ($onb !== "" ? $nl : "") . "Insured.Street is empty";
      }

      if(empty($insured->get('city'))) {
        $onb = $onb . ($onb !== "" ? $nl : "") . "Insured.City is empty";
      }

      if(empty($insured->get('zip'))) {
        $onb = $onb . ($onb !== "" ? $nl : "") . "Insured.ZIP is empty";
      }

      if(empty($insured->get('state'))) {
        $onb = $onb . ($onb !== "" ? $nl : "") . "Insured.State is empty";
      }

      if(empty($insured->get('e_mail'))) {
        $onb = $onb . ($onb !== "" ? $nl : "") . "Insured.Email is empty";
      }
    }

    if(!\App\Utils::str_equal($recordModel->get('onb_claim_number'), $recordModel->get('claim_number'))) {
      $onb = $onb . ($onb !== "" ? $nl : "") . "Claim Number: " . $recordModel->get('onb_claim_number') . " <> " . $recordModel->get('claim_number');
    }

    if(!\App\Utils::str_equal($recordModel->get('onb_policy_number'), $recordModel->get('policy_number'))) {
      $onb = $onb . ($onb !== "" ? $nl : "") . "Policy Number: " . $recordModel->get('onb_policy_number') . " <> " . $recordModel->get('policy_number');
    }

    if(empty($recordModel->get('date_of_loss'))) {
      $onb = $onb . ($onb !== "" ? $nl : "") . "Date of Loss is empty";
    }

    if(empty($recordModel->get('aob_date'))) {
      $onb = $onb . ($onb !== "" ? $nl : "") . "AOB Date is empty";
    }

    if(empty($recordModel->get('date_of_first_notification'))) {
      $onb = $onb . ($onb !== "" ? $nl : "") . "Date of First Notification is empty";
    }

    if(empty($recordModel->get('client_signature'))) {
      $onb = $onb . ($onb !== "" ? $nl : "") . "Client Signature is empty";
    }
    else if(!empty($recordModel->get('client_signature')) && $recordModel->get('client_signature') === "No") {
      $onb = $onb . ($onb !== "" ? $nl : "") . "No Client Signature";
    }

    if(empty($recordModel->get('home_owner_signature'))) {
      $onb = $onb . ($onb !== "" ? $nl : "") . "Home Owner Signature is empty";
    }
    else if(!empty($recordModel->get('home_owner_signature')) && $recordModel->get('home_owner_signature') === "No") {
      $onb = $onb . ($onb !== "" ? $nl : "") . "No Home Owner Signature";
    }

    if(empty($recordModel->get('dates_verified'))) {
      $onb = $onb . ($onb !== "" ? $nl : "") . "Dates Verified is empty";
    }
    else if(!empty($recordModel->get('dates_verified')) && $recordModel->get('dates_verified') === "No") {
      $onb = $onb . ($onb !== "" ? $nl : "") . "No Dates Verified";
    }

    if(!empty($days) && $days < 0) {
      $onb = $onb . ($onb !== "" ? $nl : "") . "Days apart DOFN - AOB: " . $days;
    }

    $claimedInvoices = Vtiger_RelationListView_Model::getInstance($recordModel, "ClaimedInvoices");
		$claimedInvoicesRows = $claimedInvoices->getRelationQuery()->all();
		$claimedInvoicesRecords = $claimedInvoices->getRecordsFromArray($claimedInvoicesRows);
    $dateEmpty = false;
    $valueEmpty = false;

		foreach ($claimedInvoicesRecords as $id => $ci) {
      $ci = Vtiger_Record_Model::getInstanceById($ci->getId());

			if (!$dateEmpty && empty($ci->get('invoice_date'))) {
        $dateEmpty = true;
        $onb = $onb . ($onb !== "" ? $nl : "") . "Invoice Date in some Claimed Invoice is empty";
			}

      if (!$valueEmpty && empty($ci->get('invoice_value'))) {
        $valueEmpty = true;
        $onb = $onb . ($onb !== "" ? $nl : "") . "Invoice Value in some Claimed Invoice is empty";
			}

      if($dateEmpty && $valueEmpty) {
        break;
      }
		}

    $documents = Vtiger_RelationListView_Model::getInstance($recordModel, "Documents");
		$documentsRows = $documents->getRelationQuery()->all();
		$documentsRecords = $documents->getRecordsFromArray($documentsRows);
    $policy = false;

		foreach ($documentsRecords as $id => $d) {
      $doc = Vtiger_Record_Model::getInstanceById($d->getId());

      if(!empty($doc->get('document_type'))) {
        $docType = Vtiger_Record_Model::getInstanceById($doc->get('document_type'));

        if (!empty($docType->get('document_type')) && $docType->get('document_type') == "POLICY AND DEC PAGE") {
          $policy = true;
          break;
        }
      }
		}

    if (!$policy) {
      $onb = $onb . ($onb !== "" ? $nl : "") . "POLICY AND DEC PAGE is not uploaded";
    }

    if(!empty($recordModel->get('insurance_shared')) && $recordModel->get('insurance_shared') && empty($recordModel->get('insurance_shared_notes'))) {
      $onb = $onb . ($onb !== "" ? $nl : "") . "Insurance Shared Notes is empty";
    }

    if(!empty($recordModel->get('date_of_loss')) && !empty($recordModel->get('policy_valid_from')) && !empty($recordModel->get('policy_valid_to'))) {
      $loss = date('Y-m-d', strtotime($recordModel->get('date_of_loss')));
      $from = date('Y-m-d', strtotime($recordModel->get('policy_valid_from')));
      $to = date('Y-m-d', strtotime($recordModel->get('policy_valid_to')));

      if($loss < $from || $loss > $to) {
        $onb = $onb . ($onb !== "" ? $nl : "") . "Date of Loss is outside of policy time frame";
      }
    }

    /* new estimate and limits checks
    sprawdzanie limitów ubezpieczenia - cały kontekst w PMC: nowe pola z limitami ubezpieczenia i z szacunkami #200:

    if any specific Estimate in a Claimed Invoice is greater than the matching Policy Limit in a related Claim (i.e. Dwelling Estimate vs Dwelling Policy Limit, etc.)
    if any Estimate in a Claimed Invoice is greater than the "General Policy Limit" in a related Claim (i.e. Estimate Amount vs General Policy Limit, Dwelling Estimate vs General Policy Limit, etc.)
    If in a comparison any compared field is empty, the verification is skipped, ONB Warning is not created.
    w treści ONB Warning wpisać odpowiednio nierówność z nazwami pól i z wartościami, np. "Dwelling Estimate > General Policy Limit: $123.456,78 > $122.456,78"
    */
    $generalPolicyLimit = $recordModel->get('general_policy_limit');
    $dwellingPolicyLimit = $recordModel->get('dwelling_policy_limit');
    $otherStructuresPolicyLimit = $recordModel->get('other_structures_policy_limit');
    $personalPropertyPolicyLimit = $recordModel->get('personal_property_policy_limit');
    $lossOfUsePolicyLimit = $recordModel->get('loss_of_use_policy_limit');
    $generalPolicyLimitDV = $recordModel->getDisplayValue('general_policy_limit');
    $dwellingPolicyLimitDV = $recordModel->getDisplayValue('dwelling_policy_limit');
    $otherStructuresPolicyLimitDV = $recordModel->getDisplayValue('other_structures_policy_limit');
    $personalPropertyPolicyLimitDV = $recordModel->getDisplayValue('personal_property_policy_limit');
    $lossOfUsePolicyLimitDV = $recordModel->getDisplayValue('loss_of_use_policy_limit');
    foreach ($claimedInvoicesRecords as $id => $ci) {
      $ci = Vtiger_Record_Model::getInstanceById($ci->getId());

      $ciName = 'Claimed Invoice ' . $ci->get('claimed_invoice_name');
      $estimateAmount = $ci->get('estimate_amount');
      $dwellingEstimate = $ci->get('dwelling_estimate');
      $otherStructuresEstimate = $ci->get('other_structures_estimate');
      $personalPropertyEstimate = $ci->get('personal_property_estimate');
      $lossOfUseEstimate = $ci->get('loss_of_use_estimate');
      $estimateAmountDV = $ci->getDisplayValue('estimate_amount');
      $dwellingEstimateDV = $ci->getDisplayValue('dwelling_estimate');
      $otherStructuresEstimateDV = $ci->getDisplayValue('other_structures_estimate');
      $personalPropertyEstimateDV = $ci->getDisplayValue('personal_property_estimate');
      $lossOfUseEstimateDV = $ci->getDisplayValue('loss_of_use_estimate');

      if (!empty($generalPolicyLimit)) {
        if (!empty($estimateAmount) && $estimateAmount > $generalPolicyLimit) 
        {
          $onb = $onb . ($onb !== "" ? $nl : "") . "Estimate Amount > General Policy Limit for $ciName: $estimateAmountDV > $generalPolicyLimitDV";
        }

        if (!empty($dwellingEstimate) && $dwellingEstimate > $generalPolicyLimit) 
        {
          $onb = $onb . ($onb !== "" ? $nl : "") . "Dwelling Estimate > General Policy Limit for $ciName: $dwellingEstimateDV > $generalPolicyLimitDV";
        }

        if (!empty($otherStructuresEstimate) && $otherStructuresEstimate > $generalPolicyLimit) 
        {
          $onb = $onb . ($onb !== "" ? $nl : "") . "Other Structure Estimate > General Policy Limit for $ciName: $otherStructuresEstimateDV > $generalPolicyLimitDV";
        }

        if (!empty($personalPropertyEstimate) && $personalPropertyEstimate > $generalPolicyLimit) 
        {
          $onb = $onb . ($onb !== "" ? $nl : "") . "Personal Property Estimate > General Policy Limit for $ciName: $personalPropertyEstimateDV > $generalPolicyLimitDV";
        }

        if (!empty($lossOfUseEstimate) && $lossOfUseEstimate > $generalPolicyLimit) 
        {
          $onb = $onb . ($onb !== "" ? $nl : "") . "Loss of Use Estimate > General Policy Limit for $ciName: $lossOfUseEstimateDV > $generalPolicyLimitDV";
        }
      }

      if (!empty($dwellingPolicyLimit) && !empty($dwellingEstimate) 
        && $dwellingEstimate > $dwellingPolicyLimit) 
      {
        $onb = $onb . ($onb !== "" ? $nl : "") . "Dwelling Estimate > Dwelling Policy Limit for $ciName: $dwellingEstimateDV > $dwellingPolicyLimitDV";
      }

      if (!empty($otherStructuresPolicyLimit) && !empty($otherStructuresEstimate) 
        && $otherStructuresEstimate > $otherStructuresPolicyLimit) 
      {
        $onb = $onb . ($onb !== "" ? $nl : "") . "Other Structures Estimate > Other Structures Policy Limit for $ciName: $otherStructuresEstimateDV > $otherStructuresPolicyLimitDV";
      }

      if (!empty($personalPropertyPolicyLimit) && !empty($personalPropertyEstimate) 
        && $personalPropertyEstimate > $personalPropertyPolicyLimit) 
      {
        $onb = $onb . ($onb !== "" ? $nl : "") . "Personal Property Estimate > Personal Property Policy Limit for $ciName: $personalPropertyEstimateDV > $personalPropertyPolicyLimitDV";
      }

      if (!empty($lossOfUsePolicyLimit) && !empty($lossOfUseEstimate) 
        && $lossOfUseEstimate > $lossOfUsePolicyLimit) 
      {
        $onb = $onb . ($onb !== "" ? $nl : "") . "Loss of Use Estimate > Loss of Use Policy Limit for $ciName: $lossOfUseEstimateDV > $lossOfUsePolicyLimitDV";
      }
		}

    $recordModel->set('insurance_policy_uploaded', $policy);

    $recordModel->save();

    // ONB didnt change - disable history
    if(\App\Utils::str_equal($onb, $onbOld)) {
    	$recordModel->setHandlerExceptions(['disableHandlerClasses' => ['ModTracker_ModTrackerHandler_Handler']]);
    }

    \App\Log::trace("Claims::verifyOnbData:onb_warnings = $onb");
    $recordModel->set('onb_warnings', $onb);

    $recordModel->save();

    // ONB Acceptant didnt change - disable history
    if(\App\Utils::str_equal($onbAcceptant, $onbAcceptantOld)) {
    	$recordModel->setHandlerExceptions(['disableHandlerClasses' => ['ModTracker_ModTrackerHandler_Handler']]);
    }
    else {
      $recordModel->setHandlerExceptions([]);
    }

    \App\Log::trace("Claims::verifyOnbData:onb_warning_to_acceptant = $onbAcceptant");
    $recordModel->set('onb_warning_to_acceptant', $onbAcceptant);
    
    $recordModel->save();
  }

  /**
	 * Update ONB Comments for insurance companies
	 *
	 * @param \Claims_Record_Model $recordModel
	 */
	public static function updateInsuranceCompanyComments(Vtiger_Record_Model $recordModel)
	{
		$id = $recordModel->getId();

		\App\Log::warning("Claims::Workflows::updateInsuranceCompanyComments:" . $id);

    $forcePlaceCarrierString = '<font color="red">Insurance Company in Forced Place Carrier </font><br>';
    $inGoodStandingString = '<font color="red">Insurance Company in Receivership </font><br>';
    $forcePlaceCarrierStringAlt = '<font color="#FF0000">Insurance Company in Forced Place Carrier </font><br>';
    $inGoodStandingStringAlt = '<font color="#FF0000">Insurance Company in Receivership </font><br>';
    $forcePlaceCarrierStringRe = '/<font\s+color=".*">.*Insurance.*Company.*in.*Forced.*Place.*Carrier.*<\/font>(\s*<br\s*\/?>\s*)?/';
    $inGoodStandingStringRe = '/<font\s+color=".*">.*Insurance.*Company.*in.*Receivership.*<\/font>(\s*<br\s*\/?>\s*)?/';
    
    $insuranceCompanyId = $recordModel->get('insurance_company');
    $onbComments = trim($recordModel->get('onb_comments'));
    // $onbComments = str_replace([$forcePlaceCarrierString, $inGoodStandingString, $forcePlaceCarrierStringAlt, $inGoodStandingStringAlt], '', $onbComments);
    $onbComments = preg_replace([$forcePlaceCarrierStringRe, $inGoodStandingStringRe], '', $onbComments);

    if (!empty($insuranceCompanyId) && \App\Record::isExists($insuranceCompanyId, 'InsuranceCompanies')) {
      $company = Vtiger_Record_Model::getInstanceById($insuranceCompanyId);

      $forcePlaceCarrier = $company->get('force_place_carrier') == 'Yes';
      $inGoodStanding = $company->get('in_good_standing') == 'No';

      if (!empty($onbComments) && !str_ends_with($onbComments, '<br>') && !str_ends_with($onbComments, '<br/>')) {
        $onbComments .= '<br>';
      }

      if ($forcePlaceCarrier) {
        $onbComments .= $forcePlaceCarrierString;
      }

      if ($inGoodStanding) {
        $onbComments .= $inGoodStandingString;
      }
    }

    $recordModel->set('onb_comments', $onbComments);
    $recordModel->save();
  }
}

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
 * Class TexasCases_Record_Model.
 */
class TexasCases_Record_Model extends Vtiger_Record_Model
{
  /**
   * Contains list of funtions to call in recalculateAll(). This field is used instead 
   * of `get_class_methods` with filter to allow mainatining order of calls.
   */
  protected $recalculateFunctions = ["recalculateFromClaims", "recalculateFromCollections", "recalculateFromOthers", "recalculateFromCase"];
  
  /**
   * Wrapper function that calls all recalculateX functions.
   */
  public function recalculateAll() {
    $id = $this->getId();
    $lockAutomation = $this->get('lock_automation');
    \App\Log::warning("TexasCases::recalculateAll:$id/$lockAutomation");

    if (!$lockAutomation) {
      \App\Log::warning("TexasCases::recalculateAll:recalculateMethods = [" . implode(', ', $this->recalculateFunctions) . "]");
      foreach ($this->recalculateFunctions as $method) {
        $this->$method();
      }
    }
  }

  /**
   * Calculations for all fields that are based on the related Claims, Claimed Invoices or HO Claimed Invoices.
   */
  public function recalculateFromClaims() {
    $id = $this->getId();
    $lockAutomation = $this->get('lock_automation');
    \App\Log::warning("TexasCases::recalculateFromClaims:$id/$lockAutomation");

    if (!$lockAutomation) {
      /*
        Provider Invoices = sum of Claims' Total Bill Amount
        Provider Prior Collections = sum of related Claims' "Prior Collections"
        Adjusted Face Value = sum of related Claims' Adjusted Face Value (NOTE: Prior Collections are already taken into account on the Claims level)
        Hurdle = sum of related Claims' Hurdle
        Overhead and Profit = sum of related Claims' Overhead and Profit
        Purchase price = sum of related Claims' Purchase Price
        Calculate PMC Collections Limit = sum of related Claim’s Adjusted Face Value, only if Claim.Status=Open, Paid or Closed (use 0 otherwise) (i.e. purchased, not buyback)
        
        First Notice of Loss = nvl( min(related Claim.Date of First Notification), Case.First Notice of Loss )

        Investors = comma separated from Claims->Portfolio Purchase->Investor
        Portfolios = comma separated from Claims->Portfolio
        Providers = comma separated from Claims->Provider->Provider Abbreviation
        
        HO Damages = sum of HO Claimed Invoices' Invoice Value
        HO Prior Collections = sum of related HO Claimed Invoices' "Prior Collections"
       */

      $providerInvoices = 0;
      $providerPriorCollections = 0;
      $adjustedFaceValue = 0;
      $hurdle = 0;
      $overheadAndProfit = 0;
      $purchasePrice = 0;
      $pmcCollectionsLimit = 0;
      $firstNoticeOfLoss = false;
      $investors = [];
      $portfolios = [];
      $providers = [];
      $claimStatuses = [];
      $claimOnboardingStatuses = [];

      $currentUserId = \App\User::getCurrentUserId();
      $currentBaseUserId = \App\Session::has('baseUserId') && \App\Session::get('baseUserId') ? \App\Session::get('baseUserId') : null;
      $targetUserId = \App\User::getActiveAdminId();
      \App\Log::warning("TexasCases::recalculateFromClaims:user is $currentUserId, should be $targetUserId");
      if ($targetUserId != $currentUserId) {
        \App\Log::warning("TexasCases::recalculateFromClaims:setting user to $targetUserId");
        \App\User::setCurrentUserId($targetUserId);
        if (\App\Session::has('baseUserId') && \App\Session::get('baseUserId')) {
          \App\Session::delete('baseUserId');
        }
      }

      $queryGenerator = new \App\QueryGenerator('Claims');
      $investorField = $queryGenerator->getQueryRelatedField('investor:PortfolioPurchases:portfolio_purchase');
      $providerField = $queryGenerator->getQueryRelatedField('provider_abbreviation:Providers:provider');
      $claims = $queryGenerator
        ->addRelatedField($investorField->getRelated())
        ->addRelatedField($providerField->getRelated())
        ->addCondition('case', $id, 'eid')
        ->setFields(['total_bill_amount', 'prior_collections', 'adjusted_face_value', 'hurdle', 
          'overhead_and_profit', 'purchase_price', 'date_of_first_notification',
          'portfolio', 'claim_status', 'onboarding_status'])
        ->createQuery()
        ->all();

      if ($targetUserId != $currentUserId) {
        \App\Log::warning("TexasCases::recalculateFromClaims:resetting user to $currentUserId");
        \App\User::setCurrentUserId($currentUserId);
        if ($currentBaseUserId) {
          \App\Log::warning("TexasCases::recalculateFromClaims:resetting base user to $currentBaseUserId");
          \App\Session::set('baseUserId', $currentBaseUserId);
        }
      }

      foreach ($claims as $claimData) {
        $providerInvoices += $claimData['total_bill_amount'] ?: 0;
        $providerPriorCollections += $claimData['prior_collections'] ?: 0;
        $adjustedFaceValue += $claimData['adjusted_face_value'] ?: 0;
        $hurdle += $claimData['hurdle'] ?: 0;
        $overheadAndProfit += $claimData['overhead_and_profit'] ?: 0;
        $purchasePrice += $claimData['purchase_price'] ?: 0;

        if (!$firstNoticeOfLoss || $claimData['date_of_first_notification'] < $firstNoticeOfLoss) {
          $firstNoticeOfLoss = $claimData['date_of_first_notification'];
        }

        $investor = \App\Record::getLabel($claimData['portfolio_purchasePortfolioPurchasesinvestor']);
        if (!in_array($investor, $investors)) {
          $investors[] = $investor;
        }
        $portfolio = \App\Record::getLabel($claimData['portfolio']);
        if (!in_array($portfolio, $portfolios)) {
          $portfolios[] = $portfolio;
        }
        if (!in_array($claimData['providerProvidersprovider_abbreviation'], $providers)) {
          $providers[] = $claimData['providerProvidersprovider_abbreviation'];
        }

        $claimStatus = $claimData['claim_status'];
        if (!in_array($claimStatus, $claimStatuses)) {
          $claimStatuses[] = $claimStatus;
        }
        $claimOnboardingStatus = $claimData['onboarding_status'];
        if (!in_array($claimOnboardingStatus, $claimOnboardingStatuses)) {
          $claimOnboardingStatuses[] = $claimOnboardingStatus;
        }
      }
      $firstNoticeOfLoss = $firstNoticeOfLoss ?: $this->get('first_notice_of_loss');
      
      sort($investors);
      $investors = \App\TextParser::textTruncate(implode(', ', $investors), 255);
      sort($portfolios);
      $portfolios = \App\TextParser::textTruncate(implode(', ', $portfolios), 255);
      sort($providers);
      $providers = \App\TextParser::textTruncate(implode(', ', $providers), 255);
      sort($claimStatuses);
      $claimStatuses = \App\TextParser::textTruncate(implode(', ', $claimStatuses), 255);
      sort($claimOnboardingStatuses);
      $claimOnboardingStatuses = \App\TextParser::textTruncate(implode(', ', $claimOnboardingStatuses), 255);
      
      $pmcCollectionsLimit = $this->calculatePMCCollectionsLimit();

      $this->set('provider_invoices', $providerInvoices);
      $this->set('provider_prior_collections', $providerPriorCollections);
      $this->set('adjusted_face_value', $adjustedFaceValue);
      $this->set('hurdle', $hurdle);
      $this->set('overhead_and_profit', $overheadAndProfit);
      $this->set('purchase_price', $purchasePrice);
      $this->set('pmc_collections_limit', $pmcCollectionsLimit);
      $this->set('first_notice_of_loss', $firstNoticeOfLoss);
      $this->set('investors', $investors);
      $this->set('portfolios', $portfolios);
      $this->set('providers', $providers);
      $this->set('claims_statuses', $claimStatuses);
      $this->set('claims_onboarding_statuses', $claimOnboardingStatuses);

      $hoDamages = 0;
      $hoPriorCollections = 0;
      $queryGenerator = new \App\QueryGenerator('HOClaimedInvoices');
      $hoClaimedInvoices = $queryGenerator
        ->addCondition('case', $id, 'eid')
        ->setFields(['invoice_value', 'prior_collections'])
        ->createQuery()
        ->all();
      foreach ($hoClaimedInvoices as $invoiceData) {
        $hoDamages += $invoiceData['invoice_value'] ?: 0;
        $hoPriorCollections += $invoiceData['prior_collections'] ?: 0;
      }

      $this->set('ho_damages', $hoDamages);
      $this->set('ho_prior_collections', $hoPriorCollections);

      $this->save();
    }
  }

  /**
   * Calculations for all fields that are based on the related Collections.
   */
  public function recalculateFromCollections() {
    $id = $this->getId();
    $lockAutomation = $this->get('lock_automation');
    \App\Log::warning("TexasCases::recalculateFromCollections:$id/$lockAutomation");

    if (!$lockAutomation) {
      /*
        Provider Collections = sum of related Collections' Value that have “Don’t apply to Claims” = No and not ("Sorting Status" ="Unsorted")
        HO Collections = sum of related Collections' Value that have “Don’t apply to Claims” = Yes and not ("Sorting Status" ="Unsorted")
        Note: Sorting Status can be empty, it should be treated as "Sorted".
      */
      $providerCollections = 0;
      $hoCollections = 0;
      $collections = (new \App\QueryGenerator('Collections'))
        ->addCondition('case', $id, 'eid')
        ->setFields(['value', 'dont_apply_to_claims', 'sorting_status'])
        ->createQuery()
        ->all();
      foreach($collections as $collectionData) {
        if ($collectionData['sorting_status'] === 'Unsorted') {
          continue;
        }
        if (empty($collectionData['dont_apply_to_claims'])) {
          $providerCollections += $collectionData['value'] ?: 0;
        } else {
          $hoCollections += $collectionData['value'] ?: 0;
        }
      }

      $this->set('provider_collections', $providerCollections);
      $this->set('ho_collections', $hoCollections);

      $this->save();
    }
  }

  /**
   * Calculations for all fields that are based on other related objects.
   */
  public function recalculateFromOthers() {
    $id = $this->getId();
    $lockAutomation = $this->get('lock_automation');
    \App\Log::warning("TexasCases::recalculateFromOthers:$id/$lockAutomation");

    if (!$lockAutomation) {
      /*
        Total Partial Settlements = sum of related Partial Settlements' Settlement Amount
        Partial Sett. Fees and Costs = sum of related Partial Settlements' attorneys_fees_and_costs
        Total Litigation Costs = sum of related Litigation Costs' Litigation Cost Amount
      */
      $totalPartialSettlements = 0;
      $partialSettlementsFeesAndCosts = 0;
      $settlements = (new \App\QueryGenerator('PartialSettlements'))
        ->addCondition('case', $id, 'eid')
        ->setFields(['settlement_amount', 'attorneys_fees_and_costs'])
        ->createQuery()
        ->all();
      foreach($settlements as $settlementData) {
        $totalPartialSettlements += $settlementData['settlement_amount'] ?: 0;
        $partialSettlementsFeesAndCosts += $settlementData['attorneys_fees_and_costs'] ?: 0;
      }
      $this->set('total_partial_settlements', $totalPartialSettlements);
      $this->set('partial_sett_fees_and_costs', $partialSettlementsFeesAndCosts);
      
      $totalLitigationCosts = 0;
      $costs = (new \App\QueryGenerator('LitigationCosts'))
        ->addCondition('case', $id, 'eid')
        ->setFields(['litigation_cost_amount'])
        ->createQuery()
        ->all();
      foreach($costs as $costData) {
        $totalLitigationCosts += $costData['litigation_cost_amount'] ?: 0;
      }
      $this->set('total_litigation_costs', $totalLitigationCosts);

      $this->save();
    }
  }

  /**
   * Calculations for all fields that are based on the related Settlement Negotiations.
   * 
   * **IMPORTANT** Special handling of empty values - if any input field is empty in these calculations in the Settlement Negotiations section, the result should be empty (NOTE: contrary to calculations described for other sections).
   */
  public function recalculateSettlementNegotiations() {
    $id = $this->getId();
    $lockAutomation = $this->get('lock_automation');
    \App\Log::warning("TexasCases::recalculateSettlementNegotiations:$id/$lockAutomation");

    $demandBasedOff = $this->get('demand_based_off');
    if (!$lockAutomation && !empty($demandBasedOff)) {
      /*
        If any input field is empty in these calculations in the Settlement Negotiations section, the result should be empty (NOTE: contrary to calculations described for other sections),
        if "Demand based off" = "Total Bill Amount less Collections"
            Demand = "Demand %" * "Total Bill Amount" - "HO Prior Collections" - "Provider Prior Collections" - "Total Collections"
        if "Demand based off" = "Adjusted Face Value less Collections"
            Demand = "Demand %" * "Adjusted Face Value" - "Total Collections"
        if "Demand based off" = "HO Damages less Collections"
            Demand = "Demand %" * "HO Damages" - "HO Collections"
        if "Demand based off" = "Provider Invoices less Collections"
            Demand = "Demand %" * "Provider Invoices " - "Provider Collections"
        for "Demand based off" is any other value, not ending with "... less Collections"
            Demand = "Demand %" * [value of a field pointed by "Demand based off"]
      */
      $isEmpty = function($val) { return $val === null || $val === '';};
      $demandPercent = !$isEmpty($this->get('demand_perc')) ? ($this->get('demand_perc') / 100) : null;
      $demand = null;
      switch ($demandBasedOff) {
        case 'Total Bill Amount less Collections':
          if (!$isEmpty($demandPercent) && !$isEmpty($this->get('total_bill_amount')) && !$isEmpty($this->get('ho_prior_collections')) && !$isEmpty($this->get('provider_prior_collections')) && !$isEmpty($this->get('total_collections'))) {
            $demand = $demandPercent * $this->get('total_bill_amount') - $this->get('ho_prior_collections') - $this->get('provider_prior_collections') - $this->get('total_collections');
          }
          break;
        case 'Adjusted Face Value less Collections':
          if (!$isEmpty($demandPercent) && !$isEmpty($this->get('adjusted_face_value')) && !$isEmpty($this->get('total_collections'))) {
            $demand = $demandPercent * $this->get('adjusted_face_value') - $this->get('total_collections');
          }
          break;
        case 'HO Damages less Collections':
          if (!$isEmpty($demandPercent) && !$isEmpty($this->get('ho_damages')) && !$isEmpty($this->get('ho_collections'))) {
            $demand = $demandPercent * $this->get('ho_damages') - $this->get('ho_collections');
          }
          break;
        case 'Provider Invoices less Collections':
          if (!$isEmpty($demandPercent) && !$isEmpty($this->get('provider_invoices')) && !$isEmpty($this->get('provider_collections'))) {
            $demand = $demandPercent * $this->get('provider_invoices') - $this->get('provider_collections');
          }
          break;
        default:
          // check if ends with "... less Collections"
          if (!empty($demandBasedOff) && !$isEmpty($demandPercent) && !str_ends_with($demandBasedOff, 'less Collections')) {
            $matchingFields = $this->getModule()->getFieldsByLabel()[$demandBasedOff];

            if (empty($matchingFields)) {
              throw new \Exception("No matching field for label '$demandBasedOff'");
            }
            $demand = $demandPercent * $this->get($matchingFields->getName());
          }
          break;
      }

      if (!empty($demand)) {
        $demand = round($demand, 2);
      }

      $this->set('settl_negot_demand', $demand);
      $this->save();
    }
  }

  /**
   * Calculations for all fields that are based on this Case.
   */
  public function recalculateFromCase() {
    $id = $this->getId();
    $lockAutomation = $this->get('lock_automation');
    \App\Log::warning("TexasCases::recalculateFromCase:$id/$lockAutomation");

    if (!$lockAutomation) {
      /*
        Total Bill Amount = Provider Invoices + HO Damages
        Total Collections = Provider Collections + HO Collections
        Provider Invoices Balance = Provider Invoices - Provider Prior Collections - Provider Collections
        HO Damages Balance = HO Damages - HO Prior Collections - HO Collections.
        Total Balance = Provider Invoices Balance + HO Damages Balance
        Adjusted Claim Balance = Adjusted Face Value - Provider Collections (NOTE: Prior Collections are already taken into account on the Claims level)
        "Global Demand" = "Attorney Fees (Demand Letter)" + "Total Balance"
        partial_settlements_balance = total_bill_amount - total_partial_settlements
      */
      $this->set('total_bill_amount', ($this->get('provider_invoices') ?: 0) + ($this->get('ho_damages') ?: 0));
      $this->set('total_collections', ($this->get('provider_collections') ?: 0) + ($this->get('ho_collections') ?: 0));
      $this->set('provider_invoices_balance', ($this->get('provider_invoices') ?: 0) - ($this->get('provider_prior_collections') ?: 0) - ($this->get('provider_collections') ?: 0));
      $this->set('ho_damages_balance', ($this->get('ho_damages') ?: 0) - ($this->get('ho_prior_collections') ?: 0) - ($this->get('ho_collections') ?: 0));
      $this->set('total_balance', ($this->get('provider_invoices_balance') ?: 0) + ($this->get('ho_damages_balance') ?: 0));
      $this->set('adjusted_claim_balance', ($this->get('adjusted_face_value') ?: 0) - ($this->get('provider_collections') ?: 0));
      $this->set('global_demand', ($this->get('attorney_fees_demand_letter') ?: 0) + ($this->get('total_balance') ?: 0));
      $this->set('partial_settlements_balance', ($this->get('total_bill_amount') ?: 0) - ($this->get('total_partial_settlements') ?: 0));

      $this->save();
    }
  }

  /**
   * Calculate the maximum amount of money that the provider is allowed to collect for this case.
   * @param int|null $providerId
   * @return int
   */
  public function calculatePMCCollectionsLimit(?int $providerId = null) {
    // Get the ID of this case
    $id = $this->getId();
    // Log the ID of this case
    \App\Log::warning("TexasCases::calculatePMCCollectionsLimit:$id/$providerId"); 

    // Get the total of all the claims for this case that are not in the Open, Paid, or Closed status
    $pmcCollectionsLimitQG = (new \App\QueryGenerator('Claims'))
      ->setField('adjusted_face_value')
      ->addCondition('case', $id, 'eid')
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

  /** FIND_SIMILAR_TEXASCASES */
  public function findSimilarTexasCases() {
    $id = $this->getId();
    
		\App\Log::warning("TexasCases::findSimilarTexasCases:$id");

    // get texas cases
    $queryGenerator = (new \App\QueryGenerator('TexasCases'));

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
      ->setOrder('case_id');

    $query = $queryGenerator->createQuery();

    $anyCondition = false;

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
      $anyCondition = true;
    }
    if ($policyNumber) {
      $conditions[] = ["regexp_replace(policy_number, '[^[:alnum:]]+', '')" => $policyNumber];
      $anyCondition = true;
    }
    if ($insured) {
      if (!empty(strtolower(str_replace(' ', '', str_replace(',', '', $insuredName))))) {
        $conditions[] = ["lower(replace(replace({$insuredNameQueryField->getColumnName()}, ',', ''), ' ', ''))" => strtolower(str_replace(' ', '', str_replace(',', '', $insuredName)))];
        $anyCondition = true;
      }
      if (!empty($insuredAddress)) {
        $conditions[] = ["lower(replace(replace(concat({$insuredCityQueryField->getColumnName()}, {$insuredZipQueryField->getColumnName()}, {$insuredStateQueryField->getColumnName()}, {$insuredCityQueryField->getColumnName()}), ',', ''), ' ', ''))" => strtolower(str_replace(' ', '', str_replace(',', '', $insuredAddress)))];
        $anyCondition = true;
      }
    }

    if (!$anyCondition) {
      $allCases = [];
      \App\Log::warning("TexasCases::findSimilarTexasCases:no conditions");
    } else {
      $query->andWhere($conditions);

      $allCases = array_map(
        function ($row) { return Vtiger_Record_Model::getInstanceById($row); }, 
        $query->column()
      );

      \App\Log::warning("TexasCases::findSimilarTexasCases:all cases = " . count($allCases) . ", query = " . $query->createCommand()->getRawSql());
    }

    if (count($allCases) > 1) {
      $casesToSave = [];

      // get first (alphabetically) Case ID of $allCases
      $name = $allCases[0]->get('case_id');

      // calculate amounts
      $numSameClaim = 0;
      $numSamePolicy = 0;
      $numSameName = 0;
      $numSameAddress = 0;
      foreach ($allCases as $case) {
        if ($claimNumber === preg_replace('/[^[:alnum:]]+/ui', '', $case->get('claim_number'))) {
          $numSameClaim++;
        }
        if ($policyNumber === preg_replace('/[^[:alnum:]]+/ui', '', $case->get('policy_number'))) {
          $numSamePolicy++;
        }
        if ($insured && \App\Record::isExists($case->get('insured'))) {
          $caseInsured = Vtiger_Record_Model::getInstanceById($case->get('insured'));
          $caseInsuredName = $caseInsured->get('insured_name');
          $caseInsuredAddress = $caseInsured->get('street') . $caseInsured->get('zip') . $caseInsured->get('state') . $caseInsured->get('city');
          if (\App\Utils::str_equal($insuredName, $caseInsuredName)) {
            $numSameName++;
          }
          if (\App\Utils::str_equal($insuredAddress, $caseInsuredAddress)) {
            $numSameAddress++;
          }
        }
      }

      // retrieve Similar TexasCases by name; if not exists, create
      $similarCasesId = (new \App\QueryGenerator('SimilarTexasCases'))
        ->setField('id')
        ->addCondition('similar_texascases', $name, 'e')
        ->createQuery()
        ->scalar();

      if (empty($similarCasesId)) {
        $similarCases = Vtiger_Record_Model::getCleanInstance('SimilarTexasCases');
        $similarCases->set('similar_texascases', $name);
        $similarCases->set('no_similar_cases', count($allCases));
        $similarCases->set('no_same_claim_number', $numSameClaim);
        $similarCases->set('no_same_policy_number', $numSamePolicy);
        $similarCases->set('no_same_insured_name', $numSameName);
        $similarCases->set('no_same_insured_address', $numSameAddress);
        $similarCases->save();

        $similarCasesId = $similarCases->getId();
      } else {
        $similarCases = Vtiger_Record_Model::getInstanceById($similarCasesId);
        $similarCases->set('no_similar_cases', count($allCases));
        $similarCases->set('no_same_claim_number', $numSameClaim);
        $similarCases->set('no_same_policy_number', $numSamePolicy);
        $similarCases->set('no_same_insured_name', $numSameName);
        $similarCases->set('no_same_insured_address', $numSameAddress);
        $similarCases->save();

        // remove from all old claims, will be readded for matching ones below
        $allCaseIds = array_map(function ($el) { return $el->getId(); }, $allCases);
        foreach (VTWorkflowUtils::getAllRelatedRecords($similarCases, 'TexasCases') as $caseRow) {
          if (!\in_array($caseRow['id'], $allCaseIds)) {
            $case = Vtiger_Record_Model::getInstanceById($caseRow['id']);
            $case->set('similar_texascases', 0);
    
            $casesToSave[] = $case;
          }
        }
      }

      // foreach $allCases - store it's current similar case, assign current
      $similarCasesToCheck = [];
      foreach ($allCases as $case) {
        $oldSimilarCasesId = $case->get('similar_texascases');
        if (!empty($oldSimilarCasesId)) {
          $similarCasesToCheck[] = $oldSimilarCasesId;
        }
        $case->set('similar_texascases', $similarCasesId);

        $casesToSave[] = $case;
      }

      // save
      foreach ($casesToSave as $caseToSave) {
        $caseToSave->save();
      }

      foreach (array_unique($similarCasesToCheck) as $similarCaseToCheckId) {
        if (!\App\Record::isExists($similarCaseToCheckId)) {
          continue;
        }

        $similarCaseToCheck = Vtiger_Record_Model::getInstanceById($similarCaseToCheckId);

        // check if has 0 or 1 cases, if so then delete
        $cases = VTWorkflowUtils::getAllRelatedRecords($similarCaseToCheck, 'TexasCases');
        if (count($cases) <= 1) {
          $similarCaseToCheck->changeState('Trash');
        }
      }
    } else if ($this->get('similar_texascases')) {
      $similarCaseToCheckId = $this->get('similar_texascases');

      $this->set('similar_texascases', 0);
      $this->save();

      if (\App\Record::isExists($similarCaseToCheckId)) {
        $similarCaseToCheck = Vtiger_Record_Model::getInstanceById($similarCaseToCheckId);
        // check if has 0 or 1 cases, if so then delete
        $cases = VTWorkflowUtils::getAllRelatedRecords($similarCaseToCheck, 'TexasCases');
        if (count($cases) <= 1) {
          if (count($cases) === 1) {
            $case = Vtiger_Record_Model::getInstanceById($cases['id']);
            $case->set('similar_texascases', 0);
            $case->save();
          }

          $similarCaseToCheck->changeState('Trash');
        }
      }
    }
  }
}

<?php

/**
 * ProvidersWorkflow.
 *
 * @package   Workflow
 *
 * @copyright DOT Systems
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Kamiński <mkaminski@dotsystems.pl>
 */
class ProvidersWorkflow
{
  /**
	 * Reset Eligibility Tests answers
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 */
	public static function resetProvidersEligibility(Vtiger_Record_Model $recordModel)
	{
		$id = $recordModel->get('id');
		\App\Log::warning("Providers::Workflows::resetProvidersEligibility:" . $id);
		$relationModel = Vtiger_RelationListView_Model::getInstance($recordModel, "ProviderEligibility");
    $rows = $relationModel->getRelationQuery()->all();
    $relatedRecords = $relationModel->getRecordsFromArray($rows);
    foreach ($relatedRecords as $id => $relatedRecord) {
      $relatedRecord->delete();
    }

    $rows = Vtiger_ListView_Model::getInstance("ProviderEligibilityConf")->getQueryGenerator()->createQuery()
      ->orderBy(['criteria_name' => \SORT_ASC])
      ->all();
    
    $eligibilityIds = [];
    foreach($rows as $row) {
      $eligibility = Vtiger_Record_Model::getCleanInstance("ProviderEligibility");
      $eligibility->set('provider', $id);
      $eligibility->set('criteria_name', $row['criteria_name']);
      $eligibility->set('criteria', $row['criteria_full']);

      $eligibility->save();
      $eligibilityIds[] = $eligibility->get('id');
    }
    \App\Log::trace("Providers::resetProvidersEligibility:" . print_r($eligibilityIds, true));

    $relation = $relationModel->getRelationModel();
    $relation->addRelation($id, $eligibilityIds);

    return $relation;
	}

  /**
	 * Check eligibility
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 */
	public static function calculateAllEligibilityCriteriaMet(Vtiger_Record_Model $recordModel)
	{
		$id = $recordModel->get('id');
		\App\Log::warning("Providers::Workflows::calculateAllEligibilityCriteriaMet:" . $id);

		$numMet = 0;
    $numConditional = 0;
    $numUnmet = 0;
    $commentsConditional = [];

    $relationModel = Vtiger_RelationListView_Model::getInstance($recordModel, "ProviderEligibility");
    $rows = $relationModel->getRelationQuery()->all();
    $relatedRecords = $relationModel->getRecordsFromArray($rows);
    $numAll = count($rows);
    foreach ($relatedRecords as $id => $eligibility) {
      $isCriteriaMet = $eligibility->get('is_criteria_met');
      switch($isCriteriaMet) {
        case 'Yes':
        case 'N/A':
          $numMet++;
          break;
        case 'Conditionally':
          $numConditional++;
          $commentsConditional[] = $eligibility->get('comments');
          break;
        case 'No':
          $numUnmet++;
        default:
          break;
      }
    }

    $commentsFail = [];
    // check has at least one provider contact => no, comment
    $contacts = VTWorkflowUtils::getAllRelatedRecords($recordModel, 'ProviderContacts', false, ['dob', 'social_security_number']);
    if (count($contacts) === 0) {
      $numUnmet++;
      $commentsFail[] = 'No provider contacts';
    }
    // check has at least three provider references => no, comment
    $references = VTWorkflowUtils::getAllRelatedRecords($recordModel, 'ProviderReferences');
    if (count($references) < 3) {
      $numUnmet++;
      $commentsFail[] = 'Not enough provider references, must have at least 3';
    }
    // check provider contact of type Owner has SSN and DOB => no, comment
    $hasFullOwner = false;
    foreach ($contacts as $contact) {
      if ($contact['provider_contact_type'] !== 'Owner/Principal' || empty($contact['dob']) || empty($contact['social_security_number'])) {
        continue;
      }
      $hasFullOwner = true;
      break;
    }
    if (!$hasFullOwner) {
      $numUnmet++;
      $commentsFail[] = 'Provider must have Owner contact with DOB and SSN';
    }

    \App\Log::trace("Providers::calculateAllEligibilityCriteriaMet:all = $numAll, met = $numMet, unmet = $numUnmet, cond = $numConditional");

    $status = '';
    $conditions = '';
    if ($numUnmet === 0 && $numAll === $numMet) {
      $status = 'Yes';
    } else if ($numUnmet > 0) {
      $status = 'No';
      $conditions = implode("\n", $commentsFail);
    } else if ($numConditional > 0 && $numUnmet === 0 && $numConditional + $numMet === $numAll) {
      $status = 'Conditionally';
      $conditions = implode("\n", $commentsConditional);
    }
    
    $recordModel->set('all_eligibility_criteria_met', $status);
    $recordModel->set('conditions_to_meet_eligibility', $conditions);
    $recordModel->save();
	}

  /**
	 * Refresh number of contacts with same email
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 */
	public static function refreshNumberOfContactsWithSameEmail(Vtiger_Record_Model $recordModel)
	{
		$id = $recordModel->get('id');
		\App\Log::warning("Providers::Workflows::refreshNumberOfContactsWithSameEmail:" . $id);
		
    Providers_Module_Model::refreshNumberOfContactsWithSameEmail();
	}

  /**
	 * Refresh buyback wallet value
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 */
	public static function refreshBuybackWalletValue(Vtiger_Record_Model $recordModel)
	{
		$id = $recordModel->getId();

		\App\Log::warning("Providers::Workflows::refreshBuybackWalletValue:" . $id);

    $value = 0;
    $claimsBuyback = 0;
    $paymentsBuyback = 0;

    $relationModel = Vtiger_RelationListView_Model::getInstance($recordModel, "Claims");
    $rows = $relationModel->getRelationQuery()->all();
    $relatedRecords = $relationModel->getRecordsFromArray($rows);
    
    foreach ($relatedRecords as $id => $claim) {
      $claim = Vtiger_Record_Model::getInstanceById($claim->getId());
      
      $bbAmount = $claim->get('buyback_amount');

      if ($claim->get('claim_status') === "Buyback" && \in_array($claim->get('buyback_status'), [ 'Cash BB pending', 'BB pending' ]) && !empty($bbAmount)) {
        $claimsBuyback = $claimsBuyback + $bbAmount;
      }
    }

    \App\Log::trace("Providers::refreshBuybackWalletValue:claimsBuyback = $claimsBuyback");

    $value = $claimsBuyback;

    \App\Log::trace("Providers::refreshBuybackWalletValue:value = $value");

    if ($value < 0) {
      $value = 0;
    }

    \App\Log::trace("Providers::refreshBuybackWalletValue:value = $value");

    $recordModel->set('buyback_wallet_value', round($value, 2));
    $recordModel->save();
	}

  /**
	 * Calculates multiple KPIS values from Portfolios
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 */
	public static function calculateKPISFromPortfolios(Vtiger_Record_Model $recordModel)
	{
		$id = $recordModel->getId();

		\App\Log::warning("Providers::Workflows::calculateKPISFromPortfolios:" . $id);

    $portfolios = Vtiger_RelationListView_Model::getInstance($recordModel, "Portfolios");
    $portfoliosRows = $portfolios->getRelationQuery()->all();
    $portfoliosRecords = $portfolios->getRecordsFromArray($portfoliosRows);

    $portfoliosPurchases = Vtiger_RelationListView_Model::getInstance($recordModel, "PortfolioPurchases");
    $portfoliosPurchasesRows = $portfoliosPurchases->getRelationQuery()->all();
    $portfoliosPurchasesRecords = $portfoliosPurchases->getRecordsFromArray($portfoliosPurchasesRows);

    $filedClaimsTotal = 0; //  Total Number of Filed Claims = Sum of all Total Number of Claims for all portfolios related to Provider
    $claimsFaceValAvg = 0; // Average Face Value of claims = Total Adjusted Face Value / Total number of Claims over all portfolios
    $filedAOBClaimsTotal = 0; // Total Number of Filed AOB Claims = Sum of all Total Number of AOB Claims for all portfolios related to Provider

    $claimsFaceVal = 0;

    $AOBClaimsPercent = 0; // Percentage of AOB Claims = Total Number of Filed AOB Claims / Total Number of Filed Claims

    $claimsPerMonthAvg = NULL; // Average no. of Claims handled per month = Total number of Accepted Claims / Months since first funded
    $firstFunded = NULL;

    $durationTillClosedAvg = NULL; // Average duration till portfolio closed (months) = Months from portfolio status=Open to portfolio status=Closed

    $monthsValue = 0;
    $monthsCount = 0;

    $voluntaryToTotalPercent = 0; // % of voluntary collection to total collection = Total voluntary collection / Total Collections over all portfolios
    $voluntaryToFaceValPercent = 0; // % of voluntary collection to face value = Total voluntary collection / Total Adjusted Face Value over all portfolios
    $litigatedToTotalPercent = 0; // % of litigated collection to total collection = Total litigated collection / Total Collections over all portfolios
    $litigatedToFaceValPercent = 0; // % of litigated collection to face value = Total litigated collection / Total Adjusted Face Value over all portfolios

    $voluntary = 0;
    $total = 0;
    $adjusted = 0;
    $litigated = 0;

    $buybackSwapsPercent = 0; // % of buyback/swaps = Total Number of Buybacks / Total Number of Accepted Claims
    $buyback = 0;

    foreach ($portfoliosRecords as $id => $portfolio) {
      $portfolio = Vtiger_Record_Model::getInstanceById($portfolio->getId());
      
      $acceptedClaimsNum = $portfolio->get('total_number_of_claims');
      $acceptedAOBClaimsNum = $portfolio->get('total_number_of_aob_claims');
      $adjFaceVal = $portfolio->get('total_adjusted_face_value');

      if (!empty($acceptedClaimsNum)) {
        $filedClaimsTotal = $filedClaimsTotal + $acceptedClaimsNum;
      }

      if (!empty($acceptedAOBClaimsNum)) {
        $filedAOBClaimsTotal = $filedAOBClaimsTotal + $acceptedAOBClaimsNum;
      }

      if (!empty($adjFaceVal)) {
        $claimsFaceVal = $claimsFaceVal + $adjFaceVal;
      }

      if (!empty($portfolio->get('opened_date')) && !empty($portfolio->get('closed_date'))) {
        $months_start = date('Y-m-d', strtotime($portfolio->get('opened_date')));
        $months_end = date('Y-m-d', strtotime($portfolio->get('closed_date')));
        $days_diff = 1;

        if (date('d', strtotime($months_end)) > date('d', strtotime($months_start))) {
          $days_diff = 0;
        }
        
        $months = ((date('Y', strtotime($months_end)) - date('Y', strtotime($months_start))) * 12) +
		    	(date('m', strtotime($months_end)) - date('m', strtotime($months_start))) - $days_diff;

        $monthsValue = $monthsValue + $months;
        $monthsCount = $monthsCount + 1;
      }

      $vol = $portfolio->get('total_voluntary_collections');
      $tot = $portfolio->get('total_collections');
      $adj = $portfolio->get('total_adjusted_face_value');
      $lit = $portfolio->get('total_litigated_collections');

      if (!empty($vol)) {
        $voluntary = $voluntary + $vol;
      }

      if (!empty($tot)) {
        $total = $total + $tot;
      }

      if (!empty($adj)) {
        $adjusted = $adjusted + $adj;
      }

      if (!empty($lit)) {
        $litigated = $litigated + $lit;
      }

      $totalBB = $portfolio->get('total_number_of_buybacks');

      if (!empty($totalBB)) {
        $buyback = $buyback + $totalBB;
      }
    }

    if(!empty($filedClaimsTotal) && $filedClaimsTotal !== 0) {
      $claimsFaceValAvg = round($claimsFaceVal / $filedClaimsTotal, 2);
      $AOBClaimsPercent = round($filedAOBClaimsTotal / $filedClaimsTotal * 100, 2);
      $buybackSwapsPercent = round($buyback / $filedClaimsTotal * 100, 2);
    }

    if(!empty($monthsCount) && $monthsCount !== 0) {
      $durationTillClosedAvg = round($monthsValue / $monthsCount, 2);
    }

    if(!empty($total) && $total !== 0) {
      $voluntaryToTotalPercent = round($voluntary / $total * 100, 2);
    }

    if(!empty($adjusted) && $adjusted !== 0) {
      $voluntaryToFaceValPercent = round($voluntary / $adjusted * 100, 2);
    }

    if(!empty($total) && $total !== 0) {
      $litigatedToTotalPercent = round($litigated / $total * 100, 2);
    }

    if(!empty($adjusted) && $adjusted !== 0) {
      $litigatedToFaceValPercent = round($litigated / $adjusted * 100, 2);
    }

    \App\Log::trace("Providers::calculateKPISFromPortfolios:filedClaimsTotal = $filedClaimsTotal");
    $recordModel->set('total_filed_claims', $filedClaimsTotal);

    \App\Log::trace("Providers::calculateKPISFromPortfolios:claimsFaceValAvg = $claimsFaceValAvg");
    $recordModel->set('avg_face_value_of_claims', $claimsFaceValAvg);

    \App\Log::trace("Providers::calculateKPISFromPortfolios:filedAOBClaimsTotal = $filedAOBClaimsTotal");
    $recordModel->set('total_filed_aob_claims', $filedAOBClaimsTotal);

    \App\Log::trace("Providers::calculateKPISFromPortfolios:AOBClaimsPercent = $AOBClaimsPercent");
    $recordModel->set('percentage_aob_claims', $AOBClaimsPercent);

    \App\Log::trace("Providers::calculateKPISFromPortfolios:durationTillClosedAvg = $durationTillClosedAvg");
    $recordModel->set('avg_months_till_portfol_closed', $durationTillClosedAvg);

    \App\Log::trace("Providers::calculateKPISFromPortfolios:voluntaryToTotalPercent = $voluntaryToTotalPercent");
    $recordModel->set('pct_voluntary_to_total_collect', $voluntaryToTotalPercent);

    \App\Log::trace("Providers::calculateKPISFromPortfolios:voluntaryToFaceValPercent = $voluntaryToFaceValPercent");
    $recordModel->set('pct_voluntary_coll_to_face_val', $voluntaryToFaceValPercent);

    \App\Log::trace("Providers::calculateKPISFromPortfolios:litigatedToTotalPercent = $litigatedToTotalPercent");
    $recordModel->set('pct_litigated_to_total_collect', $litigatedToTotalPercent);

    \App\Log::trace("Providers::calculateKPISFromPortfolios:litigatedToFaceValPercent = $litigatedToFaceValPercent");
    $recordModel->set('pct_litigated_coll_to_face_val', $litigatedToFaceValPercent);

    \App\Log::trace("Providers::calculateKPISFromPortfolios:buybackSwapsPercent = $buybackSwapsPercent");
    $recordModel->set('pct_buyback_swaps', $buybackSwapsPercent);

    foreach ($portfoliosPurchasesRecords as $id => $portfolioPurchase) {
      $portfolioPurchase = Vtiger_Record_Model::getInstanceById($portfolioPurchase->getId());
      
      $purchase = $portfolioPurchase->get('purchase_date');

      if(empty($firstFunded)) {
        $firstFunded = $purchase;
      }
      else {
        if(!empty($purchase) && strtotime($purchase) < strtotime($firstFunded)) {
          $firstFunded = $purchase;
        }
      }
    }

    if(!empty($firstFunded)) {
      $months_start = date('Y-m-d', strtotime($firstFunded));
      $months_end = date('Y-m-d');
      $days_diff = 1;

      if (date('d', strtotime($months_end)) > date('d', strtotime($months_start))) {
        $days_diff = 0;
      }
      
      $months = ((date('Y', strtotime($months_end)) - date('Y', strtotime($months_start))) * 12) +
        (date('m', strtotime($months_end)) - date('m', strtotime($months_start))) - $days_diff;

      if(!empty($months) && $months !== 0) {
        $claimsPerMonthAvg = round($filedClaimsTotal / $months, 2);
      }

      \App\Log::trace("Providers::calculateKPISFromPortfolios:claimsPerMonthAvg = $claimsPerMonthAvg");
      $recordModel->set('avg_claims_handled_per_month', $claimsPerMonthAvg);
    }

    $recordModel->setHandlerExceptions(['disableHandlerClasses' => ['ModTracker_ModTrackerHandler_Handler']]);

    $recordModel->save();
	}

  /**
	 * Calculates multiple KPIS values from Claims
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 */
	public static function calculateKPISFromClaims(Vtiger_Record_Model $recordModel)
	{
		$id = $recordModel->getId();

		\App\Log::warning("Providers::Workflows::calculateKPISFromClaims:" . $id);

    $claims = Vtiger_RelationListView_Model::getInstance($recordModel, "Claims");
    $claimsRows = $claims->getRelationQuery()->all();
    $claimsRecords = $claims->getRecordsFromArray($claimsRows);

    $durationTillSettledAvg = NULL; // Average duration till case settled (months) = Months from claim funded to paid claim, counted for paid claims only
    
    $monthsValue = 0;
    $monthsCount = 0;
    
    $voluntaryCollectionPercent = 0; // % of cases having voluntary collection = Total Number of claims having voluntary collection / Total Number of Accepted Claims over all portfolios
    $voluntaryCollection = 0;

    $litigationPercent = 0; // % of cases going to litigation = Total Number of claims going to litigation / Total Number of Accepted Claims over all portfolios
    $litigation = 0;

    $writtenOffPercent = 0; // % of written off cases = Total Claims with Write-Off/Total Number of Accepted Claims
    $writtenOff = 0;

    foreach ($claimsRecords as $id => $claim) {
      $claim = Vtiger_Record_Model::getInstanceById($claim->getId());
      if ($claim->get('claim_status') === "Paid" && !empty($claim->get('purchased_time')) && !empty($claim->get('claim_paid_date'))) {
        $months_start = date('Y-m-d', strtotime($claim->get('purchased_time')));
        $months_end = date('Y-m-d', strtotime($claim->get('claim_paid_date')));
        $days_diff = 1;

        if (date('d', strtotime($months_end)) > date('d', strtotime($months_start))) {
          $days_diff = 0;
        }
        
        $months = ((date('Y', strtotime($months_end)) - date('Y', strtotime($months_start))) * 12) +
		    	(date('m', strtotime($months_end)) - date('m', strtotime($months_start))) - $days_diff;

        $monthsValue = $monthsValue + $months;
        $monthsCount = $monthsCount + 1;
      }

      if ($claim->get('total_voluntary_ollections') > 0) {
        $voluntaryCollection = $voluntaryCollection + 1;
      }

      if (!empty($claim->get('litigation_started_date'))) {
        $litigation = $litigation + 1;
      }

      if (!empty($claim->get('write_off'))) {
        $writtenOff = $writtenOff + 1;
      }
    }

    if(!empty($monthsCount) && $monthsCount !== 0) {
      $durationTillSettledAvg = round($monthsValue / $monthsCount, 2);
    }

    if(!empty($filedClaimsTotal) && $filedClaimsTotal !== 0) {
      $voluntaryCollectionPercent = round($voluntaryCollection / $filedClaimsTotal * 100, 2);
    }

    if(!empty($filedClaimsTotal) && $filedClaimsTotal !== 0) {
      $litigationPercent = round($litigation / $filedClaimsTotal * 100, 2);
    }

    if(!empty($filedClaimsTotal) && $filedClaimsTotal !== 0) {
      $writtenOffPercent = round($writtenOff / $filedClaimsTotal * 100, 2);
    }

    \App\Log::trace("Providers::calculateKPISFromClaims:durationTillSettledAvg = $durationTillSettledAvg");
    $recordModel->set('avg_months_till_case_settled', $durationTillSettledAvg);

    \App\Log::trace("Providers::calculateKPISFromClaims:voluntaryCollectionPercent = $voluntaryCollectionPercent");
    $recordModel->set('pct_cases_voluntary_collection', $voluntaryCollectionPercent);

    \App\Log::trace("Providers::calculateKPISFromClaims:litigationPercent = $litigationPercent");
    $recordModel->set('pct_cases_litigation', $litigationPercent);

    \App\Log::trace("Providers::calculateKPISFromClaims:writtenOffPercent = $writtenOffPercent");
    $recordModel->set('pct_written_off_cases', $writtenOffPercent);

    $recordModel->setHandlerExceptions(['disableHandlerClasses' => ['ModTracker_ModTrackerHandler_Handler']]);

    $recordModel->save();
	}

  /**
	 * Calculates multiple values: Total Number of Filed Claims, Total Number of Filed AOB Claims, Percentage of AOB Claims etc...
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 */
	public static function calculateKPIS(Vtiger_Record_Model $recordModel)
	{
		$id = $recordModel->getId();

		\App\Log::warning("Providers::Workflows::calculateKPIS:" . $id);

    $portfoliosPurchases = Vtiger_RelationListView_Model::getInstance($recordModel, "PortfolioPurchases");
    $portfoliosPurchasesRows = $portfoliosPurchases->getRelationQuery()->all();
    $portfoliosPurchasesRecords = $portfoliosPurchases->getRecordsFromArray($portfoliosPurchasesRows);

    $portfolios = Vtiger_RelationListView_Model::getInstance($recordModel, "Portfolios");
    $portfoliosRows = $portfolios->getRelationQuery()->all();
    $portfoliosRecords = $portfolios->getRecordsFromArray($portfoliosRows);

    foreach ($portfoliosPurchasesRecords as $id => $portfolioPurchase) {
      $portfolioPurchase = Vtiger_Record_Model::getInstanceById($portfolioPurchase->getId());
      $portfolioPurchase->recalculateFromClaims();
    }

    foreach ($portfoliosRecords as $id => $portfolio) {
      $portfolio = Vtiger_Record_Model::getInstanceById($portfolio->getId());
      $portfolio->recalculateFromClaims();
    }

    self::calculateKPISFromClaims($recordModel);

    self::calculateKPISFromPortfolios($recordModel);

    self::calculateYearsInBusiness($recordModel);
	}

  /**
	 * Calculate years in business
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 */
	public static function calculateYearsInBusiness(Vtiger_Record_Model $recordModel)
	{
		$id = $recordModel->getId();

		\App\Log::warning("Providers::Workflows::calculateYearsInBusiness:" . $id);
    
    $years = 0;
    $granted = $recordModel->get('date_of_license_to_do_business');

    if(!empty($granted)) {
      $years = date('Y', strtotime(date('Y-m-d'))) - date('Y', strtotime($granted));
        
      \App\Log::trace("Providers::calculateYearsInBusiness:years_in_business = $years");
      $recordModel->set('years_in_business', $years);

      $recordModel->save();
    }
	}

  /**
	 * Request e-mail confirmation.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 */
	public static function requestEmailConfirmation(Vtiger_Record_Model $recordModel)
	{
		$id = $recordModel->get('id');
    $emailToBeConfirmed = $recordModel->get('email_to_be_confirmed');
		\App\Log::warning("Providers::Workflows::requestEmailConfirmation:$id/$emailToBeConfirmed");
		
    if ($emailToBeConfirmed) {
      $db = \App\Db::getInstance();

      // delete previous codes for module/record
      $db->createCommand()->delete('s_yf_challenges', ['module_name' => 'Providers', 'record_id' => $id])->execute();
      // generate code
      $code = substr(md5(uniqid(mt_rand(), true)) , 0, 32);
      // save challenge
      $db->createCommand()->insert('s_yf_challenges', [
        'module_name' => 'Providers',
        'record_id' => $id,
        'challenge_code' => $code,
        'challenge_date' => date('Y-m-d H:i:s'),
      ])->execute();
      // get and parse email template
      $templateId = VTWorkflowUtils::getEmailTemplateByNumber('N44');
      $template = \App\Mail::getTemplate($templateId);
      $textParser = \App\TextParser::getInstanceByModel($recordModel);
      $subject = $template['subject'];
      $subject = \App\Utils\Completions::processIfs($subject, $textParser);
      $subject = $textParser->setContent($subject)->parse()->getContent();
      
      $content = $template['content'];
      $content = \App\Utils\Completions::processIfs($content, $textParser);
      $content = $textParser->setContent($content)->parse()->getContent();
      // replace $(AUTHORIZATION_LINK)$ with link to confirm.php callback
      $content = str_replace('$(AUTHORIZATION_LINK)$', \App\Config::main('site_URL') . 'confirm.php?code=' . urlencode($code), $content);
      $content = \App\Utils\Completions::decode(\App\Purifier::purifyHtml($content));

      // send email
      $mailerContent = [ 
        'to' => $emailToBeConfirmed, 
        'subject' => $subject,
        'content' => $content,
        'template' => $templateId, 
        'recordId' => $recordModel->getId(),
      ];

      \App\Mailer::sendDirect($mailerContent);
    }
	}

  /**
	 * Call Portal API to create activtation link
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 */
	public static function createActivationLink(Vtiger_Record_Model $recordModel) {
		$id = $recordModel->getId();
		\App\Log::warning("Providers::Workflows::createActivationLink:" . $id);

		$url = \App\Config::api('PROVIDER_PORTAL_API_URL') . 'createActivationLink';
		$body = ['json' => ['id' => $id]];

		// TODO remove verify => false
		$options = array_merge(\App\RequestHttp::getOptions(), ['verify' => false]);
		$client = (new \GuzzleHttp\Client($options));
		$request = $client->request('POST', $url, $body);
		if (200 !== $request->getStatusCode()) {
			throw new \App\Exceptions\AppException('Error with connection |' . $request->getReasonPhrase());
		}
	}

  /**
	 * Call Portal API to create reset password link
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 */
	public static function createResetPasswordLink(Vtiger_Record_Model $recordModel) {
		$id = $recordModel->getId();
		\App\Log::warning("Providers::Workflows::createResetPasswordLink:" . $id);

		$url = \App\Config::api('PROVIDER_PORTAL_API_URL') . 'resetPasswordLink';
		$body = ['json' => ['id' => $id]];

    // TODO remove verify => false
		$options = array_merge(\App\RequestHttp::getOptions(), ['verify' => false]);
    $client = (new \GuzzleHttp\Client($options));
		$request = $client->request('POST', $url, $body);
		if (200 !== $request->getStatusCode()) {
			throw new \App\Exceptions\AppException('Error with connection |' . $request->getReasonPhrase());
		}
	}

  public static function createPortfolio(Vtiger_Record_Model $recordModel) {
    $id = $recordModel->getId();
    \App\Log::warning("Providers::Workflows::createPortfolio:" . $id);

    $portfolios = VTWorkflowUtils::getAllRelatedRecords($recordModel, 'Portfolios', ['in', 'portfolio_status', ['New', 'In Underwriting']]);

    if (count($portfolios)) {
      \App\Log::warning("Providers::Workflows::createPortfolio:already has portfolios in New/In Underwriting status");
      return;
    }

    $portfolio = Vtiger_Record_Model::getCleanInstance('Portfolios');
    $portfolio->set('provider', $id);
    $portfolio->set('assigned_user_id', \App\User::getUserIdByName('Portal'));
    $portfolio->set('portfolio_id', '---new---');
    $portfolio->save();
  }

  /**
	 * Create onetime password
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 */
	public static function createOnetimePassword(Vtiger_Record_Model $recordModel) {
		$id = $recordModel->getId();
		\App\Log::warning("LawFirms::Workflows::createOnetimePassword:" . $id);

		// generate random onetime password
		$onetimePassword = \App\Encryption::generatePassword(8);

		// save onetime password in database
		$recordModel->set('onetime_password', $onetimePassword);
		$recordModel->save();
	}
}

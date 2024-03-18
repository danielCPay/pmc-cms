<?php

use App\Exceptions\BatchErrorHandledWorkflowException;

/**
 * PortfolioPurchasesWorkflow.
 *
 * @package   Workflow
 *
 * @copyright DOT Systems
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Kamiński <mkaminski@dotsystems.pl>
 */
class PortfolioPurchasesWorkflow
{
  /**
	 * Generate Portfolio Purchase Name as next letter in sequence Portfolio.Portfolio ID AAA.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 */
	public static function generatePortfolioPurchaseName(Vtiger_Record_Model $recordModel)
	{
		$id = $recordModel->getId();
    $currentPortfolioPurchaseName = $recordModel->get('portfolio_purchase_name');
    $portfolioId = $recordModel->get('portfolio');

		\App\Log::warning("PortfolioPurchases::Workflows::generatePortfolioPurchaseName:$id/$currentPortfolioPurchaseName/$portfolioId");

    if (empty($currentPortfolioPurchaseName) || $currentPortfolioPurchaseName === '---new---') {
      $portfolio = Vtiger_Record_Model::getInstanceById($portfolioId);
      $portfolioID = $portfolio->get('portfolio_id');

      // get max number from previous portfolios for provider
      $number = (new \App\QueryGenerator('PortfolioPurchases'))
          ->addCondition('portfolio', $portfolioId, 'eid')
          ->createQuery()
          ->andWhere(['rlike', 'portfolio_purchase_name', "^" . $portfolioID . "[A-Za-z]+$"])
          ->max("regexp_replace(portfolio_purchase_name, '^$portfolioID', '')");

      if ($number) {
        $next = '';
        $carry = true;
        foreach (str_split(strrev($number)) as $char) {
          $ord = ord($char);
          if ($carry) {
            if ($ord === 90) {
              $nextChar = 'A';
              $carry = true;
            } else {
              $nextChar = chr($ord + 1);
              $carry = false;
            }
          } else {
            $nextChar = $char;
          }

          $next .= $nextChar;
        }
        if ($carry) {
          $next = "{$next}A";
        }
        $number = strrev($next);
      } else {
        $number = 'A';
      }
      
      
      // set portfolio id
      $recordModel->set('portfolio_purchase_name', "$portfolioID$number");
      $recordModel->save();
    }
	}

  /**
	 * Recalculate from claims
	 *
	 * @param \PortfolioPurchases_Record_Model $recordModel
	 */
	public static function recalculateFromClaims(Vtiger_Record_Model $recordModel)
	{
		$id = $recordModel->getId();

		\App\Log::warning("PortfolioPurchases::Workflows::recalculateFromClaims:" . $id);

    $recordModel->recalculateFromClaims();
	}

  /**
	 * Checks if all Claims are approved
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 */
	public static function checkClaimsApproved(Vtiger_Record_Model $recordModel)
	{
		$id = $recordModel->getId();

		\App\Log::warning("PortfolioPurchases::Workflows::checkClaimsApproved:" . $id);
			
		$claims = Vtiger_RelationListView_Model::getInstance($recordModel, "Claims");
		$claimsRows = $claims->getRelationQuery()->all();
		$claimsRecords = $claims->getRecordsFromArray($claimsRows);

		foreach ($claimsRecords as $claim) {
      /** @var Claims_Record_Model $claim */
      $claim = Vtiger_Record_Model::getInstanceById($claim->getId());
      
      $err = "All claims in Portfolio Purchase have to be approved";
			if ($claim->get('onboarding_status') !== "Approved") {
				$entry = VTWorkflowUtils::createBatchErrorEntryRaw($err, $id, $recordModel->getModuleName(), $err, $id, $err);
				
		    throw new \App\Exceptions\BatchErrorHandledWorkflowException($err, 0, null, $entry);
			}

      $err = "All claims in Portfolio Purchase have to have Conducted by set to some value";
			if (empty($claim->get('conducted_by'))) {
				$entry = VTWorkflowUtils::createBatchErrorEntryRaw($err, $id, $recordModel->getModuleName(), $err, $id, $err);
				
		    throw new \App\Exceptions\BatchErrorHandledWorkflowException($err, 0, null, $entry);
			}

      $err = "All claims in Portfolio Purchase have to have Type of claim set to some value";
			if (empty($claim->get('type_of_claim'))) {
				$entry = VTWorkflowUtils::createBatchErrorEntryRaw($err, $id, $recordModel->getModuleName(), $err, $id, $err);
				
		    throw new \App\Exceptions\BatchErrorHandledWorkflowException($err, 0, null, $entry);
			}

      $err = "All claims in Portfolio Purchase have to have HO Law Firm or HO Attorney set";
			if (empty($claim->get('ho_law_firm')) && empty($claim->get('ho_attorney'))) {
				$entry = VTWorkflowUtils::createBatchErrorEntryRaw($err, $id, $recordModel->getModuleName(), $err, $id, $err);
				
		    throw new \App\Exceptions\BatchErrorHandledWorkflowException($err, 0, null, $entry);
			}

      $err = "All claims must have supported combination of Type of claim, Conducted by and AOB/DTP Attorney";
      try {
        $isOutside = $claim->checkIsOutside();
      } catch (Exception $e) {
        $entry = VTWorkflowUtils::createBatchErrorEntryRaw($err, $id, $recordModel->getModuleName(), $err, $id, $e->getMessage());

		    throw new \App\Exceptions\BatchErrorHandledWorkflowException($err, 0, null, $entry);
      }

      $err = "All claims must have matching type of case, if case is set";
      if ($isOutside && !empty($claim->get('case'))) {
        $entry = VTWorkflowUtils::createBatchErrorEntryRaw($err, $id, $recordModel->getModuleName(), $err, $id, $err);

		    throw new \App\Exceptions\BatchErrorHandledWorkflowException($err, 0, null, $entry);
      } else if (!$isOutside && !empty($claim->get('outside_case'))) {
        $entry = VTWorkflowUtils::createBatchErrorEntryRaw($err, $id, $recordModel->getModuleName(), $err, $id, $err);

		    throw new \App\Exceptions\BatchErrorHandledWorkflowException($err, 0, null, $entry);
      }

      $err = "No claim in Portfolio Purchase can have Insurance Company with Block IC set to Yes";
      $insuranceCompanyId = $claim->get('insurance_company');
      if (\App\Record::isExists($insuranceCompanyId)) {
        $company = Vtiger_Record_Model::getInstanceById($insuranceCompanyId);
        if ($company->get('block_ic') == 1) {
          $entry = VTWorkflowUtils::createBatchErrorEntryRaw($err, $id, $recordModel->getModuleName(), $err, $id, $err);

          throw new \App\Exceptions\BatchErrorHandledWorkflowException($err, 0, null, $entry);
        }
      }
		}
	}

  /**
   * Create journal entry in QuickBooks. Ensures required accounts and customers exist.
	 *
	 * @param \PortfolioPurchases_Record_Model $recordModel
   */
  public static function exportPurchaseToQuickBooks(Vtiger_Record_Model $recordModel) {
    $id = $recordModel->getId();

		\App\Log::warning("PortfolioPurchases::Workflows::exportPurchaseToQuickBooks:" . $id);

    try {
      Investors_Module_Model::ensureAccounts($recordModel);

      // for each claim in portfolio purchase create journal entries
      // 1.	Debit the “[Provider]:[Portfolio Purchase]:Purchase Price” account with value = “[ClaimPurchase Price]”, name = “ClaimClaim ID”, description = “[ClaimClaim Number] / [ClaimInsured]”
      // 2.	Credit the “Cash” account with value = “[ClaimPurchase Price]”, name = “ClaimClaim ID”, description = “[ClaimClaim Number] / [ClaimInsured]”
      // 3.	Debit the “[Provider]:[Portfolio Purchase]:Factor Fee Receivable” account with value = “[ClaimFactor Fee]”, name = “ClaimClaim ID”, description = “[ClaimClaim Number] / [ClaimInsured]”
      // 4.	Credit the “[Portfolio Purchase] Deferred Factor Fee” account with value = “[ClaimFactor Fee]”, name = “ClaimClaim ID”, description = “[ClaimClaim Number] / [ClaimInsured]”
      // create Batch Error if any error occurs

      $data = Investors_Module_Model::prepareData($recordModel);

      $claims = VTWorkflowUtils::getAllRelatedRecords($recordModel, 'Claims');

      $customerCache = [];
      
      foreach ($claims as $claimRow) {
        $creditLines = [];
        $debitLines = [];

        /** @var Claims_Record_Model $claim */
        $claim = Vtiger_Record_Model::getInstanceById($claimRow['id']);
        
        $claimId = $claim->get('claim_id');
        $claimNumber = $claim->get('claim_number');
        $insuredName = \App\Record::getLabel($claim->get('insured'));
        
        $purchasePrice = $claim->get('purchase_price');
        $factorFee = $claim->get('factor_fee');

        if (!array_key_exists($claimId, $customerCache)) {
          $customer = \App\QuickBooks\Api::createCustomer($claimId, $insuredName, "Claim Number: $claimNumber");
          $customerCache[$claimId] = $customer->DisplayName;
        }
        
        $customer = $customerCache[$claimId];
        
        if ($purchasePrice > 0) {
          $debitLines[] = [
            'accountName' => Investors_Module_Model::processAccountName('[Provider]:[Portfolio Purchase]:Purchase Price', $data),
            'amount' => $purchasePrice,
            'description' => "$claimNumber / $insuredName",
            'customer' => $customer
          ];
          $creditLines[] = [
            'accountName' => 'Cash',
            'amount' => $purchasePrice,
            'description' => "$claimNumber / $insuredName",
            'customer' => $customer
          ];
        }

        if ($factorFee > 0) {
          $debitLines[] = [
            'accountName' => Investors_Module_Model::processAccountName('[Provider]:[Portfolio Purchase]:Factor Fee Receivable', $data),
            'amount' => $factorFee,
            'description' => "$claimNumber / $insuredName",
            'customer' => $customer
          ];
          $creditLines[] = [
            'accountName' => Investors_Module_Model::processAccountName('[Portfolio Purchase] Deferred Factor Fee', $data),
            'amount' => $factorFee,
            'description' => "$claimNumber / $insuredName",
            'customer' => $customer
          ];
        }

        if (count($debitLines) > 0 && count($creditLines) > 0) {
          \App\QuickBooks\Api::createJournalEntry($creditLines, $debitLines);
        }
      }
    } catch (\Exception $e) {
      \App\Log::warning("PortfolioPurchases::Workflows::exportPurchaseToQuickBooks:$id - " . $e->getMessage());
			// try to add BatchError, do not rethrow exception to allow WF to continue
			\VTWorkflowUtils::createBatchErrorEntryRaw('Export Purchase to QuickBooks', '', 'PortfolioPurchases', 'Failed to export to QuickBooks', $id, $e->getMessage());
		}
  }

  /**
	 * Recalculate from buyback claims
	 *
	 * @param \PortfolioPurchases_Record_Model $recordModel
	 */
	public static function recalculateFromBuybackClaims(Vtiger_Record_Model $recordModel)
	{
		$id = $recordModel->getId();

		\App\Log::warning("PortfolioPurchases::Workflows::recalculateFromBuybackClaims:" . $id);

    $recordModel->recalculateFromBuybackClaims();
	}

  /**
	 * Checks if there are no portfolio purchase documents.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 */
	public static function checkNoPurchaseDocument(Vtiger_Record_Model $recordModel)
	{
		$id = $recordModel->getId();

		\App\Log::warning("PortfolioPurchases::Workflows::checkNoPurchaseDocument:" . $id);
			
		['typeId' => $documentTypeId] = \App\DocuSign\Api::getDocumentTypeAndAreaIdByName('Portfolio Purchase Documents');
    $documents = \VTWorkflowUtils::getAllRelatedRecords($recordModel, 'Documents', ['document_type' => $documentTypeId]);

    if (count($documents) > 0) {
      $error = 'Addendum document already exists in this Portfolio Purchase';
      $description = "There should be no documents of type \"Portfolio Purchase Documents\" attached. " . count($documents) . " documents were found:\n";
      foreach($documents as $document) {
        $description .= "- {$document['notes_title']}\n";
      }
      \VTWorkflowUtils::createBatchErrorEntryRaw('Send exhibits to be signed by Seller', '', 'PortfolioPurchases', $error, $id, $description);
      throw new \App\Exceptions\BatchErrorHandledWorkflowException($error);
    }
	}


  /**
	 * Send 'Portfolio Purchase Documents' for signing using DocuSign.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 */
	public static function sendForSigning(Vtiger_Record_Model $recordModel)
	{
		$id = $recordModel->getId();
    $providerId = $recordModel->get('provider');
    $userId = $recordModel->get('assigned_user_id');

		\App\Log::warning("PortfolioPurchases::Workflows::sendForSigning:$id/$providerId");

    if (!\App\Config::docusign('enabled')) {
      \App\Log::warning("PortfolioPurchases::Workflows::sendForSigning:interface disabled");
      return;
    }

    // check if single Portfolio Purchase Documents
    ['typeId' => $documentTypeId] = \App\DocuSign\Api::getDocumentTypeAndAreaIdByName('Portfolio Purchase Documents');
    $documents = \VTWorkflowUtils::getAllRelatedRecords($recordModel, 'Documents', ['document_type' => $documentTypeId]);
    if (count($documents) > 1) {
      $error = 'There is more then one Addendum document in this Portfolio Purchase';
      $description = "Exactly one document of type \"Portfolio Purchase Documents\" is required. " . count($documents) . " documents were found:\n";
      foreach($documents as $document) {
        $description .= "- {$document['notes_title']}\n";
      }
      \VTWorkflowUtils::createBatchErrorEntryRaw('Send exhibits to be signed by Seller', '', 'PortfolioPurchases', $error, $id, $description, $userId);
      throw new \App\Exceptions\BatchErrorHandledWorkflowException($error);
    } else if (count($documents) === 0) {
      $error = 'There is no Addendum document in this Portfolio Purchase';
      $description = 'Exactly one document of type "Portfolio Purchase Documents" is required. None were found.';
      \VTWorkflowUtils::createBatchErrorEntryRaw('Send exhibits to be signed by Seller', '', 'PortfolioPurchases', $error, $id, $description, $userId);
      throw new \App\Exceptions\BatchErrorHandledWorkflowException($error);
    } else {
      /** @var Documents_Record_Model $document */
      $document = \Vtiger_Record_Model::getInstanceById($documents[0]['id']);
      $fileDetails = $document->getFileDetails();
    }

    // get name and e-mail of Provider
    $provider = \Vtiger_Record_Model::getInstanceById($providerId);
    $providerName = $provider->get('contact_person');
    $providerEmail = $provider->get('email');

    try {
      // send for signing, save envelope id
      $envelopeId = \App\DocuSign\Api::sendForSigning(
        'Please sign attached document',
        [
          [
            'path' => $fileDetails['path'] . $fileDetails['attachmentsid'],
            'name' => $document->get('notes_title'),
            'id' => 1
          ],
        ],
        [
          [
            'email' => $providerEmail,
            'name' => $providerName,
            'id' => 1,
            'order' => 1,
            'tabs' => [ 
              'sign_here_tabs' => [
                ['placeholder' => '/sn1/', 'id' => 1],
              ],
              'text_tabs' => [
                ['placeholder' => '/ps/', 'id' => 2, 'x_offset' => -3, 'y_offset' => -6, 'tab_label' => 'Enter your position', 'font' => 'TimesNewRoman', 'font_size' => 'Size11'],
                ['placeholder' => '/fn/', 'id' => 3, 'x_offset' => -3, 'y_offset' => -6, 'tab_label' => 'Enter your full name', 'font' => 'TimesNewRoman', 'font_size' => 'Size11'],
              ],
              'date_signed_tabs' => [
                ['placeholder' => '/ds/', 'id' => 4, 'x_offset' => -1, 'y_offset' => -3, 'font' => 'TimesNewRoman', 'font_size' => 'Size11'],
              ],
            ],
          ],
        ],
        \App\Config::docusign('carbonCopies', [])
        );

      $recordModel->set('docusign_envelope_id', $envelopeId);
      $recordModel->save();
    } catch (\Throwable $t) {
      \App\Log::error("PortfolioPurchases::Workflows::sendForSigning:error " . var_export($t, true));

      $error = 'Error while sending document for signing';
      $description = $t->getMessage();
      if (stripos($description, 'failed, but for an unknown reason') !== false) {
        $error .= '. The system will retry.';
      }
      \VTWorkflowUtils::createBatchErrorEntryRaw('Send exhibits to be signed by Seller', '', 'PortfolioPurchases', $error, $id, $description, $userId);
      throw new \App\Exceptions\BatchErrorHandledWorkflowException($error);
    }
  }
}

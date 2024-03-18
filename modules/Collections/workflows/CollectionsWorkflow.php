<?php

/**
 * CollectionsWorkflow.
 *
 * @package   Workflow
 *
 * @copyright DOT Systems
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Kamiński <mkaminski@dotsystems.pl>
 */
class CollectionsWorkflow
{
  /**
	 * Apply collection to claims - APPLY_COLLECTION_TO_CLAIMS algorigthm
	 *
	 * @param \Collections_Record_Model $recordModel
	 */
	public static function applyCollectionToClaims(Vtiger_Record_Model $recordModel)
	{
    $id = $recordModel->getId();

		\App\Log::warning("Collections::Workflows::applyCollectionToClaims:$id");

    $recordModel->applyCollectionToClaims();
	}

	/**
	 * DISBURSE_COLLECTION
	 *
	 * @param \Collections_Record_Model $recordModel
	 */
	public static function disburseCollection(Vtiger_Record_Model $recordModel)
	{
    $id = $recordModel->getId();

		\App\Log::warning("Collections::Workflows::disburseCollection:$id");

    /*
		For each Claim Collection

    Set Disbursed Date = Collection.Disbursed date
		*/
		$disbursedDate = $recordModel->get('disbursed_date');

		$claimCollections = VTWorkflowUtils::getAllRelatedRecords($recordModel, 'ClaimCollections');
		foreach ($claimCollections as $claimCollectionRow) {
			try {
				/** @var ClaimCollections_Record_Model $claimCollection */
				$claimCollection = Vtiger_Record_Model::getInstanceById($claimCollectionRow['id']);
				$claimCollection->set('disbursed_date', $disbursedDate);

				$claimCollection->save();
			} catch (\Exception $e) {
				\App\Log::warning("Collections::Workflows::disburseCollection:$id/{$claimCollectionRow['id']} - " . $e->getMessage());
				// try to add BatchError, do not rethrow exception to allow WF to continue
				\VTWorkflowUtils::createBatchErrorEntryRaw('Disburse collection', '', 'ClaimCollections', 'Failed to export to QuickBooks', $claimCollectionRow['id'], $e->getMessage());
			}
		}
	}

	/**
   * Create journal entry in QuickBooks. Ensures required accounts and customers exist.
	 *
	 * @param \Collections_Record_Model $recordModel
   */
  public static function exportCollectionToQuickBooks(Vtiger_Record_Model $recordModel) {
    $id = $recordModel->getId();

		\App\Log::warning("Collections::Workflows::exportCollectionToQuickBooks:" . $id);

    try {
			// foreach Claim Collection related to this Collection
			// 1.	OB installation is selected on the grounds of this PMCClaim CollectionClaimPortfolio PurchaseInvestor. 
			// 	a.	If the Investor is not “PMC Funding 2021, LLC”, then this Claim Collections is skipped.
			// 2.	Read current balance of “[Provider]:[Portfolio Purchase]:Purchase Price” account in QB for Portfolio Purchase read from PMCClaim CollectionClaimPortfolio Purchase. Store in helper variable Current_Balance_of_Portfolio_Purchase_Purchase_Price
			// 3.	Similarly, read current balance of “[Provider]:[Portfolio Purchase]: Factor Fee Receivable” account in QB and store it in helper variable Current_Balance_of_Portfolio_Purchase_Factor_Fee_Receivable
			// 4.	Calculate:
			// 	a)	Collection_below_Purchase_Price = least( PMCClaim CollectionAssigned Value, Current_Balance_of_Portfolio_Purchase_Purchase_Price )
			// 	b)	Collection_between_Purchase_Price_and_Hurdle = least( PMCClaim CollectionAssigned Value - Collection_below_Purchase_Price, Current_Balance_of_Portfolio_Purchase_Factor_Fee_Receivable )
			// 	c)	Collection_over_Hurdle = PMCClaim CollectionAssigned Value - Collection_below_Purchase_Price - Collection_between_Purchase_Price_and_Hurdle
			// 5.	If Collection_below_Purchase_Price > 0 then
			// 	a.	Credit the “[Provider]:[Portfolio Purchase]:Purchase Price:Purchase Collection” account with value = Collection_below_Purchase_Price, description = “[CollectionCheck Number]”
			// 	b.	Debit the “Cash” account with value = Collection_below_Purchase_Price, description = “[CollectionCheck Number]”
			// 6.	If Collection_between_Purchase_Price_and_Hurdle > 0 then
			// 	a.	Credit the “[Provider]:[Portfolio Purchase]:Factor Fee Receivable:Factor Fee Collection” account with value = Collection_between_Purchase_Price_and_Hurdle, description = “[CollectionCheck Number]”
			// 	b.	Debit the “Cash” account with value = Collection_between_Purchase_Price_and_Hurdle, description = “[CollectionCheck Number]”
			// 	c.	Credit the “[Portfolio Purchase] Realized Factor Fee” account with value = Collection_between_Purchase_Price_and_Hurdle, description = “[CollectionCheck Number]”
			// 	d.	Debit the “[Portfolio Purchase] Deferred Factor Fee” account with value = Collection_between_Purchase_Price_and_Hurdle, description = “[CollectionCheck Number]”
			// 7.	If Collection_over_Hurdle > 0 then
			// 	a.	Credit the “[Portfolio Purchase] Excess Hurdle Payable” account with value = Collection_over_Hurdle, description = “[CollectionCheck Number]”
			// 	b.	Debit the “Cash” account with value = Collection_over_Hurdle, description = “[CollectionCheck Number]”
			// Set Sent to QB to current date and time if at least one journal entry was created

			$checkNumber = $recordModel->get('check_number');
			$claimCollections = VTWorkflowUtils::getAllRelatedRecords($recordModel, 'ClaimCollections');

			$anySent = false;
			$customerCache = []; // so not too many hits are made to QB
			$vendorCache = []; // so not too many hits are made to QB
			$portfolioPurchaseCache = []; // so not too many hits are made to QB
			foreach ($claimCollections as $claimCollectionRow) {
				$creditLines = [];
				$debitLines = [];

				$claimCollection = Vtiger_Record_Model::getInstanceById($claimCollectionRow['id'], 'ClaimCollections');
				$claim = Vtiger_Record_Model::getInstanceById($claimCollection->get('claim'));
				$claimId = $claim->get('claim_id');
        $claimNumber = $claim->get('claim_number');
				$insuredName = \App\Record::getLabel($claim->get('insured'));
				$portfolioPurchaseId = $claim->get('portfolio_purchase');
				$portfolioPurchase = Vtiger_Record_Model::getInstanceById($portfolioPurchaseId);
				$investor = Vtiger_Record_Model::getInstanceById($portfolioPurchase->get('investor'));
				$investorName = $investor->get('investor_name');
				$data = Investors_Module_Model::prepareData($portfolioPurchase);

				\App\Log::warning("Collections::Workflows::exportCollectionToQuickBooks:claim collection {$claimCollection->getId()}, investor $investorName");
				
				if ($investorName !== 'PMC Funding 2021, LLC') {
					continue;
				}

				$cashAccount = 'Cash';
				$purchasePriceAccount = Investors_Module_Model::processAccountName('[Provider]:[Portfolio Purchase]:Purchase Price', $data);
				$purchasePriceCollectionAccount = Investors_Module_Model::processAccountName('[Provider]:[Portfolio Purchase]:Purchase Price:Purchase Collection', $data);
				$factorFeeReceivableAccount = Investors_Module_Model::processAccountName('[Provider]:[Portfolio Purchase]:Factor Fee Receivable', $data);
				$factorFeeReceivableCollectionAccount = Investors_Module_Model::processAccountName('[Provider]:[Portfolio Purchase]:Factor Fee Receivable:Factor Fee Collection', $data);
				$excessHurdlePayableAccount = Investors_Module_Model::processAccountName('[Portfolio Purchase] Excess Hurdle Payable', $data);
				$deferredFactorFeeAccount = Investors_Module_Model::processAccountName('[Portfolio Purchase] Deferred Factor Fee', $data);
				$realizedFactorFeeAccount = Investors_Module_Model::processAccountName('[Portfolio Purchase] Realized Factor Fee', $data);

				if (!array_key_exists($portfolioPurchaseId, $portfolioPurchaseCache)) {
					Investors_Module_Model::ensureAccounts($portfolioPurchase);

					$portfolioPurchaseCache[$portfolioPurchaseId] = true;
				}

				$currentBalancePurchasePrice = \App\QuickBooks\Api::getAccountBalance($purchasePriceAccount) ?: 0;
				$currentBalanceFactorFeeReceivable = \App\QuickBooks\Api::getAccountBalance($factorFeeReceivableAccount) ?: 0;
				$assignedValue = $claimCollection->get('assigned_value') ?: 0;

				$collectionBelowPurchasePrice = min($assignedValue, $currentBalancePurchasePrice);
				$collectionBetweenPurchasePriceAndHurdle = min($assignedValue - $collectionBelowPurchasePrice, $currentBalanceFactorFeeReceivable);
				$collectionOverHurdle = $assignedValue - $collectionBelowPurchasePrice - $collectionBetweenPurchasePriceAndHurdle;

				if ($collectionBelowPurchasePrice === 0 && $collectionBetweenPurchasePriceAndHurdle === 0 && $collectionOverHurdle === 0) {
					continue;
				}

				if (!array_key_exists($claimId, $customerCache)) {
          $customer = \App\QuickBooks\Api::createCustomer($claimId, $insuredName, "Claim Number: $claimNumber");
          $customerCache[$claimId] = $customer->DisplayName;
        }

				$customer = $customerCache[$claimId];

				if (!array_key_exists($claimId, $vendorCache)) {
          $vendor = \App\QuickBooks\Api::createVendor("$claimId - V", $insuredName, "Claim Number: $claimNumber");
          $vendorCache[$claimId] = $vendor->DisplayName;
        }

        $vendor = $vendorCache[$claimId];

				if ($collectionBelowPurchasePrice > 0) {
					$debitLines[] = [
						'accountName' => $cashAccount,
						'amount' => $collectionBelowPurchasePrice,
						'description' => "$checkNumber",
						'customer' => $customer
					];
					$creditLines[] = [
						'accountName' => $purchasePriceCollectionAccount,
						'amount' => $collectionBelowPurchasePrice,
						'description' => "$checkNumber",
						'customer' => $customer
					];
				}

				if ($collectionBetweenPurchasePriceAndHurdle > 0) {
					$debitLines[] = [
						'accountName' => $cashAccount,
						'amount' => $collectionBetweenPurchasePriceAndHurdle,
						'description' => "$checkNumber",
						'customer' => $customer
					];
					$creditLines[] = [
						'accountName' => $factorFeeReceivableCollectionAccount,
						'amount' => $collectionBetweenPurchasePriceAndHurdle,
						'description' => "$checkNumber",
						'customer' => $customer
					];

					$debitLines[] = [
						'accountName' => $deferredFactorFeeAccount,
						'amount' => $collectionBetweenPurchasePriceAndHurdle,
						'description' => "$checkNumber",
						'customer' => $customer
					];
					$creditLines[] = [
						'accountName' => $realizedFactorFeeAccount,
						'amount' => $collectionBetweenPurchasePriceAndHurdle,
						'description' => "$checkNumber",
						'customer' => $customer
					];
				}

				if ($collectionOverHurdle > 0) {
					$debitLines[] = [
						'accountName' => $cashAccount,
						'amount' => $collectionOverHurdle,
						'description' => "$checkNumber",
						'customer' => $customer
					];
					$creditLines[] = [
						'accountName' => $excessHurdlePayableAccount,
						'amount' => $collectionOverHurdle,
						'description' => "$checkNumber",
						'vendor' => $vendor
					];
				}

				if (count($debitLines) > 0 && count($creditLines) > 0) {
					\App\QuickBooks\Api::createJournalEntry($creditLines, $debitLines);
					$anySent = true;
				}
			}

			if ($anySent) {
				$recordModel->set('sent_to_qb', date('Y-m-d H:i:s'));
				$recordModel->save();
			}
    } catch (\Exception $e) {
      \App\Log::warning("Collections::Workflows::exportCollectionToQuickBooks:$id - " . $e->getMessage());
			// try to add BatchError, do not rethrow exception to allow WF to continue
			\VTWorkflowUtils::createBatchErrorEntryRaw('Export Collection to QuickBooks', '', 'PortfolioPurchases', 'Failed to export to QuickBooks', $id, $e->getMessage());
		}
  }
}

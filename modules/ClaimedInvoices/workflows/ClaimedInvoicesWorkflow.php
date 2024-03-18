<?php

/**
 * ClaimedInvoicesWorkflow.
 *
 * @package   Workflow
 *
 * @copyright DOT Systems
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Kamiński <mkaminski@dotsystems.pl>
 */
class ClaimedInvoicesWorkflow
{
  /**
	 * Recalculate financial summary
	 *
	 * @param \ClaimedInvoices_Record_Model $recordModel
	 */
	public static function recalculateFinancialSummary(Vtiger_Record_Model $recordModel)
	{
		$id = $recordModel->getId();

		\App\Log::warning("ClaimedInvoices::Workflows::recalculateFinancialSummary:" . $id);
    
    // RECALCULATE_FINANCIAL_SUMMARY
    // Adjusted Invoice Value = Total Invoice Amount - Prior Collections - Overhead and Profit - Adjustment
    // Purchase Price = if Special Purchase Price is empty, then Adjusted Invoice Value * Portfolio Purchase.Program.Purchase Price %, else Special Purchase Price

    $adjustedInvoiceValue = 0;
    $purchasePrice = 0;

    $totalInvoiceAmount = $recordModel->get('invoice_value') ?: 0;
    $priorCollections = $recordModel->get('prior_collections') ?: 0;
    $overheadAndProfit = $recordModel->get('overhead_and_profit') ?: 0;
    $adjustment = $recordModel->get('adjustment') ?: 0;

    $specialPurchasePrice = $recordModel->get('special_purchase_price');
    $purchasePricePct = 0;
    $purchasePriceCap = PHP_INT_MAX;
    $portfolioPurchaseId = $recordModel->get('portfolio_purchase');
    if (empty($portfolioPurchaseId) && \App\Record::isExists($recordModel->get('claim'))) {
      $portfolioPurchaseId = Vtiger_Record_Model::getInstanceById($recordModel->get('claim'))->get('portfolio_purchase');
      $recordModel->set('portfolio_purchase', $portfolioPurchaseId);
    }
    // Portfolio Purchase.Program.Purchase Price %, else Special Purchase Price
    if ($portfolioPurchaseId) {
      $portfolioPurchase = Vtiger_Record_Model::getInstanceById($portfolioPurchaseId);

      $programId = $portfolioPurchase->get('program');
      if ($programId) {
        $program = Vtiger_Record_Model::getInstanceById($programId);

        $purchasePricePct = $program->get('purchase_price_perc') ?: 0;
        $purchasePriceCap = $program->get('purchase_price_cap') ?: PHP_INT_MAX;
      }
    }

    $adjustedInvoiceValue = $totalInvoiceAmount - $priorCollections - $overheadAndProfit - $adjustment;
    $purchasePrice = $specialPurchasePrice ?: min(($adjustedInvoiceValue * $purchasePricePct / 100), $purchasePriceCap);

    \App\Log::trace("ClaimedInvoices::recalculateFinancialSummary:adjusted_invoice_value = $adjustedInvoiceValue");
    $recordModel->set('adjusted_invoice_value', round($adjustedInvoiceValue, 2));

    \App\Log::trace("ClaimedInvoices::recalculateFinancialSummary:purchase_price = $purchasePrice");
    $recordModel->set('purchase_price', round($purchasePrice, 2));

    $recordModel->save();
  }
}

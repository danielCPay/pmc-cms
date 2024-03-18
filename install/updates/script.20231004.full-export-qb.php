<?php
require_once 'include/main/WebUI.php';

\App\User::setCurrentUserId(\App\User::getActiveAdminId());

require_once 'modules/PortfolioPurchases/workflows/PortfolioPurchasesWorkflow.php';
require_once 'modules/Collections/workflows/CollectionsWorkflow.php';

$portfolioPurchases = (new \App\QueryGenerator('PortfolioPurchases'))->addCondition('investor', 'Funding', 'a')->addCondition('portfolio_purchase_status', 'Funded', 'e')->createQuery()->all();

foreach ($portfolioPurchases as $portfolioPurchaseRow) {
  $portfolioPurchase = Vtiger_Record_Model::getInstanceById($portfolioPurchaseRow['portfoliopurchasesid'], 'PortfolioPurchases');
  echo "Processing Portfolio Purchase {$portfolioPurchase->getName()} ({$portfolioPurchase->getId()}; {$portfolioPurchase->getDetailViewUrl()})" . PHP_EOL;

  Investors_Module_Model::ensureAccounts($portfolioPurchase);
  echo "  Created Accounts" . PHP_EOL;
  PortfolioPurchasesWorkflow::exportPurchaseToQuickBooks($portfolioPurchase);
  echo "  Exported Portfolio Purchase" . PHP_EOL;

  $collections = [];
  $claimCollections = VTWorkflowUtils::getAllRelatedRecords($portfolioPurchase, 'ClaimCollections');
  foreach ($claimCollections as $claimCollection) {
    if (!in_array($claimCollection['collection'], $collections)) {
      $collections[] = $claimCollection['collection'];
    }
  }
  
  foreach ($collections as $collectionId) {
    $collection = Vtiger_Record_Model::getInstanceById($collectionId);
    echo "  Processing Collection {$collection->getName()} ({$collection->getId()}; {$collection->getDetailViewUrl()})" . PHP_EOL;
    CollectionsWorkflow::exportCollectionToQuickBooks($collection);
    echo "  Exported Collection" . PHP_EOL;
  }
}

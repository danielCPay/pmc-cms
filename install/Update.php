<?php
chdir(__DIR__ . '/../');
set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . '/../');

require_once 'include/main/WebUI.php';

// Workflow custom functions
echo "Ensuring workflow custom functions\n";
require_once 'modules/com_vtiger_workflow/VTEntityMethodManager.php';

$customFunctions = [
  'Cases' => [
    [ 'methodName' => 'recalculateFromClaims', 'functionPath' => 'modules/Cases/workflows/CasesWorkflow.php', 'functionName' => 'CasesWorkflow' ],
    [ 'methodName' => 'recalculateFromCollections', 'functionPath' => 'modules/Cases/workflows/CasesWorkflow.php', 'functionName' => 'CasesWorkflow' ],
    [ 'methodName' => 'recalculateFromOthers', 'functionPath' => 'modules/Cases/workflows/CasesWorkflow.php', 'functionName' => 'CasesWorkflow' ],
    [ 'methodName' => 'recalculateSettlementNegotiations', 'functionPath' => 'modules/Cases/workflows/CasesWorkflow.php', 'functionName' => 'CasesWorkflow' ],
    [ 'methodName' => 'recalculateFromCase', 'functionPath' => 'modules/Cases/workflows/CasesWorkflow.php', 'functionName' => 'CasesWorkflow' ],
    [ 'methodName' => 'recalculateAll', 'functionPath' => 'modules/Cases/workflows/CasesWorkflow.php', 'functionName' => 'CasesWorkflow' ],
    [ 'methodName' => 'updateNextHearingDate', 'functionPath' => 'modules/Cases/workflows/CasesWorkflow.php', 'functionName' => 'CasesWorkflow' ],
    [ 'methodName' => 'calculateStatusAge', 'functionPath' => 'modules/Cases/workflows/CasesWorkflow.php', 'functionName' => 'CasesWorkflow' ],
    [ 'methodName' => 'findSimilarCases', 'functionPath' => 'modules/Cases/workflows/CasesWorkflow.php', 'functionName' => 'CasesWorkflow' ],
    [ 'methodName' => 'setNewCaseId', 'functionPath' => 'modules/Cases/workflows/CasesWorkflow.php', 'functionName' => 'CasesWorkflow' ],
    [ 'methodName' => 'setAttorneyByAssignedTo', 'functionPath' => 'modules/Cases/workflows/CasesWorkflow.php', 'functionName' => 'CasesWorkflow' ],
    [ 'methodName' => 'revertToPreviousStatus', 'functionPath' => 'modules/Cases/workflows/CasesWorkflow.php', 'functionName' => 'CasesWorkflow' ],
  ],
  'TexasCases' => [
    [ 'methodName' => 'recalculateFromClaims', 'functionPath' => 'modules/TexasCases/workflows/TexasCasesWorkflow.php', 'functionName' => 'TexasCasesWorkflow' ],
    [ 'methodName' => 'recalculateFromCollections', 'functionPath' => 'modules/TexasCases/workflows/TexasCasesWorkflow.php', 'functionName' => 'TexasCasesWorkflow' ],
    [ 'methodName' => 'recalculateFromOthers', 'functionPath' => 'modules/TexasCases/workflows/TexasCasesWorkflow.php', 'functionName' => 'TexasCasesWorkflow' ],
    [ 'methodName' => 'recalculateSettlementNegotiations', 'functionPath' => 'modules/TexasCases/workflows/TexasCasesWorkflow.php', 'functionName' => 'TexasCasesWorkflow' ],
    [ 'methodName' => 'recalculateFromCase', 'functionPath' => 'modules/TexasCases/workflows/TexasCasesWorkflow.php', 'functionName' => 'TexasCasesWorkflow' ],
    [ 'methodName' => 'recalculateAll', 'functionPath' => 'modules/TexasCases/workflows/TexasCasesWorkflow.php', 'functionName' => 'TexasCasesWorkflow' ],
    [ 'methodName' => 'updateNextHearingDate', 'functionPath' => 'modules/TexasCases/workflows/TexasCasesWorkflow.php', 'functionName' => 'TexasCasesWorkflow' ],
    [ 'methodName' => 'calculateStatusAge', 'functionPath' => 'modules/TexasCases/workflows/TexasCasesWorkflow.php', 'functionName' => 'TexasCasesWorkflow' ],
    [ 'methodName' => 'findSimilarTexasCases', 'functionPath' => 'modules/TexasCases/workflows/TexasCasesWorkflow.php', 'functionName' => 'TexasCasesWorkflow' ],
    [ 'methodName' => 'setNewCaseId', 'functionPath' => 'modules/TexasCases/workflows/TexasCasesWorkflow.php', 'functionName' => 'TexasCasesWorkflow' ],
    [ 'methodName' => 'setAttorneyByAssignedTo', 'functionPath' => 'modules/TexasCases/workflows/TexasCasesWorkflow.php', 'functionName' => 'TexasCasesWorkflow' ],
    [ 'methodName' => 'revertToPreviousStatus', 'functionPath' => 'modules/TexasCases/workflows/TexasCasesWorkflow.php', 'functionName' => 'TexasCasesWorkflow' ],
  ],
  'ClaimedInvoices' => [
    [ 'methodName' => 'recalculateFinancialSummary', 'functionPath' => 'modules/ClaimedInvoices/workflows/ClaimedInvoicesWorkflow.php', 'functionName' => 'ClaimedInvoicesWorkflow' ],
  ],
  'Claims' => [
    [ 'methodName' => 'recalculateFromClaimCollections', 'functionPath' => 'modules/Claims/workflows/ClaimsWorkflow.php', 'functionName' => 'ClaimsWorkflow' ],
    [ 'methodName' => 'recalculateFinancialSummary', 'functionPath' => 'modules/Claims/workflows/ClaimsWorkflow.php', 'functionName' => 'ClaimsWorkflow' ],
    [ 'methodName' => 'findRelatedCase', 'functionPath' => 'modules/Claims/workflows/ClaimsWorkflow.php', 'functionName' => 'ClaimsWorkflow' ],
    [ 'methodName' => 'onPurchasedCondCreateCase', 'functionPath' => 'modules/Claims/workflows/ClaimsWorkflow.php', 'functionName' => 'ClaimsWorkflow' ],
    [ 'methodName' => 'findSimilarClaims', 'functionPath' => 'modules/Claims/workflows/ClaimsWorkflow.php', 'functionName' => 'ClaimsWorkflow' ],
    [ 'methodName' => 'verifyOnbData', 'functionPath' => 'modules/Claims/workflows/ClaimsWorkflow.php', 'functionName' => 'ClaimsWorkflow' ],
    [ 'methodName' => 'updateInsuranceCompanyComments', 'functionPath' => 'modules/Claims/workflows/ClaimsWorkflow.php', 'functionName' => 'ClaimsWorkflow' ],
  ],
  'ClaimCollections' => [],
  'Collections' => [
    [ 'methodName' => 'applyCollectionToClaims', 'functionPath' => 'modules/Collections/workflows/CollectionsWorkflow.php', 'functionName' => 'CollectionsWorkflow' ],
    [ 'methodName' => 'disburseCollection', 'functionPath' => 'modules/Collections/workflows/CollectionsWorkflow.php', 'functionName' => 'CollectionsWorkflow' ],
    [ 'methodName' => 'exportCollectionToQuickBooks', 'functionPath' => 'modules/Collections/workflows/CollectionsWorkflow.php', 'functionName' => 'CollectionsWorkflow' ],
  ],
  'Documents' => [
    [ 'methodName' => 'import_claims_from_excel', 'functionPath' => 'modules/Documents/workflows/ImportClaims.php', 'functionName' => 'ImportClaims' ],
    [ 'methodName' => 'assignCaseToCollection', 'functionPath' => 'modules/Documents/workflows/CaseToCollection.php', 'functionName' => 'CaseToCollection' ],
    [ 'methodName' => 'import_cases_from_excel', 'functionPath' => 'modules/Cases/workflows/ImportHOCases.php', 'functionName' => 'ImportHOCases' ],
  ],
  'Insureds' => [
    [ 'methodName' => 'findCounty', 'functionPath' => 'modules/Insureds/workflows/InsuredsWorkflow.php', 'functionName' => 'InsuredsWorkflow' ],
  ],
  'OutsideCases' => [
    [ 'methodName' => 'recalculateFromClaims', 'functionPath' => 'modules/OutsideCases/workflows/OutsideCasesWorkflow.php', 'functionName' => 'OutsideCasesWorkflow' ],
    [ 'methodName' => 'recalculateFromCollections', 'functionPath' => 'modules/OutsideCases/workflows/OutsideCasesWorkflow.php', 'functionName' => 'OutsideCasesWorkflow' ],
    [ 'methodName' => 'recalculateFromCase', 'functionPath' => 'modules/OutsideCases/workflows/OutsideCasesWorkflow.php', 'functionName' => 'OutsideCasesWorkflow' ],
    [ 'methodName' => 'recalculateAll', 'functionPath' => 'modules/OutsideCases/workflows/OutsideCasesWorkflow.php', 'functionName' => 'OutsideCasesWorkflow' ],
    [ 'methodName' => 'setDateOfService', 'functionPath' => 'modules/OutsideCases/workflows/OutsideCasesWorkflow.php', 'functionName' => 'OutsideCasesWorkflow' ],
    [ 'methodName' => 'calculateDateOfService', 'functionPath' => 'modules/OutsideCases/workflows/OutsideCasesWorkflow.php', 'functionName' => 'OutsideCasesWorkflow' ],
  ],
  'Providers' => [
    [ 'methodName' => 'resetProvidersEligibility', 'functionPath' => 'modules/Providers/workflows/ProvidersWorkflow.php', 'functionName' => 'ProvidersWorkflow' ],
    [ 'methodName' => 'calculateAllEligibilityCriteriaMet', 'functionPath' => 'modules/Providers/workflows/ProvidersWorkflow.php', 'functionName' => 'ProvidersWorkflow' ],
    [ 'methodName' => 'refreshNumberOfContactsWithSameEmail', 'functionPath' => 'modules/Providers/workflows/ProvidersWorkflow.php', 'functionName' => 'ProvidersWorkflow' ],
    [ 'methodName' => 'refreshBuybackWalletValue', 'functionPath' => 'modules/Providers/workflows/ProvidersWorkflow.php', 'functionName' => 'ProvidersWorkflow' ],
    [ 'methodName' => 'calculateKPIS', 'functionPath' => 'modules/Providers/workflows/ProvidersWorkflow.php', 'functionName' => 'ProvidersWorkflow' ],
    [ 'methodName' => 'calculateYearsInBusiness', 'functionPath' => 'modules/Providers/workflows/ProvidersWorkflow.php', 'functionName' => 'ProvidersWorkflow' ],
    [ 'methodName' => 'calculateKPISFromPortfolios', 'functionPath' => 'modules/Providers/workflows/ProvidersWorkflow.php', 'functionName' => 'ProvidersWorkflow' ],
    [ 'methodName' => 'calculateKPISFromClaims', 'functionPath' => 'modules/Providers/workflows/ProvidersWorkflow.php', 'functionName' => 'ProvidersWorkflow' ],
    [ 'methodName' => 'requestEmailConfirmation', 'functionPath' => 'modules/Providers/workflows/ProvidersWorkflow.php', 'functionName' => 'ProvidersWorkflow' ],
    [ 'methodName' => 'createActivationLink', 'functionPath' => 'modules/Providers/workflows/ProvidersWorkflow.php', 'functionName' => 'ProvidersWorkflow' ],
    [ 'methodName' => 'createResetPasswordLink', 'functionPath' => 'modules/Providers/workflows/ProvidersWorkflow.php', 'functionName' => 'ProvidersWorkflow' ],
    [ 'methodName' => 'createPortfolio', 'functionPath' => 'modules/Providers/workflows/ProvidersWorkflow.php', 'functionName' => 'ProvidersWorkflow' ],
    [ 'methodName' => 'createOnetimePassword', 'functionPath' => 'modules/Providers/workflows/ProvidersWorkflow.php', 'functionName' => 'ProvidersWorkflow' ],
  ],
  'Portfolios' => [
    [ 'methodName' => 'generatePortfolioId', 'functionPath' => 'modules/Portfolios/workflows/PortfoliosWorkflow.php', 'functionName' => 'PortfoliosWorkflow' ],
    [ 'methodName' => 'recalculateFromClaims', 'functionPath' => 'modules/Portfolios/workflows/PortfoliosWorkflow.php', 'functionName' => 'PortfoliosWorkflow' ],
    [ 'methodName' => 'releaseReservesToProvider', 'functionPath' => 'modules/Portfolios/workflows/PortfoliosWorkflow.php', 'functionName' => 'PortfoliosWorkflow' ],
    [ 'methodName' => 'stopIfMoreClaimsToUnderwrite', 'functionPath' => 'modules/Portfolios/workflows/PortfoliosWorkflow.php', 'functionName' => 'PortfoliosWorkflow' ],
    [ 'methodName' => 'createHOAttorneyConfirmationRequests', 'functionPath' => 'modules/Portfolios/workflows/PortfoliosWorkflow.php', 'functionName' => 'PortfoliosWorkflow' ],
    [ 'methodName' => 'resetToNew', 'functionPath' => 'modules/Portfolios/workflows/PortfoliosWorkflow.php', 'functionName' => 'PortfoliosWorkflow' ],
  ],
  'PortfolioPurchases' => [
    [ 'methodName' => 'generatePortfolioPurchaseName', 'functionPath' => 'modules/PortfolioPurchases/workflows/PortfolioPurchasesWorkflow.php', 'functionName' => 'PortfolioPurchasesWorkflow' ],
    [ 'methodName' => 'recalculateFromClaims', 'functionPath' => 'modules/PortfolioPurchases/workflows/PortfolioPurchasesWorkflow.php', 'functionName' => 'PortfolioPurchasesWorkflow' ],
    [ 'methodName' => 'checkClaimsApproved', 'functionPath' => 'modules/PortfolioPurchases/workflows/PortfolioPurchasesWorkflow.php', 'functionName' => 'PortfolioPurchasesWorkflow' ],
    [ 'methodName' => 'exportPurchaseToQuickBooks', 'functionPath' => 'modules/PortfolioPurchases/workflows/PortfolioPurchasesWorkflow.php', 'functionName' => 'PortfolioPurchasesWorkflow' ],
    [ 'methodName' => 'recalculateFromBuybackClaims', 'functionPath' => 'modules/PortfolioPurchases/workflows/PortfolioPurchasesWorkflow.php', 'functionName' => 'PortfolioPurchasesWorkflow' ],
    [ 'methodName' => 'checkNoPurchaseDocument', 'functionPath' => 'modules/PortfolioPurchases/workflows/PortfolioPurchasesWorkflow.php', 'functionName' => 'PortfolioPurchasesWorkflow' ],
    [ 'methodName' => 'sendForSigning', 'functionPath' => 'modules/PortfolioPurchases/workflows/PortfolioPurchasesWorkflow.php', 'functionName' => 'PortfolioPurchasesWorkflow' ],
  ],
  'DocumentTypes' => [
    [ 'methodName' => 'refreshDocumentTypesPaths', 'functionPath' => 'modules/DocumentTypes/workflows/DocumentTypesWorkflow.php', 'functionName' => 'DocumentTypesWorkflow' ],
  ],
  'Programs' => [
    [ 'methodName' => 'verifyAlgorithmParameters', 'functionPath' => 'modules/Programs/workflows/ProgramsWorkflow.php', 'functionName' => 'ProgramsWorkflow' ],
    [ 'methodName' => 'debugTemporalDataCron', 'functionPath' => 'modules/Programs/workflows/ProgramsWorkflow.php', 'functionName' => 'ProgramsWorkflow' ],
  ],
  'LawFirms' => [
    [ 'methodName' => 'createActivationLink', 'functionPath' => 'modules/LawFirms/workflows/LawFirmsWorkflow.php', 'functionName' => 'LawFirmsWorkflow' ],
    [ 'methodName' => 'createResetPasswordLink', 'functionPath' => 'modules/LawFirms/workflows/LawFirmsWorkflow.php', 'functionName' => 'LawFirmsWorkflow' ],
    [ 'methodName' => 'createOnetimePassword', 'functionPath' => 'modules/LawFirms/workflows/LawFirmsWorkflow.php', 'functionName' => 'LawFirmsWorkflow' ],
  ],
];

$manager = new VTEntityMethodManager();
$existingMethods = [];

$modules = Settings_ModuleManager_Module_Model::getAll();
foreach($modules as $id => $module) {
  $moduleName = $module->getName();
  if (!in_array($moduleName, ['HelpDesk', 'ModComments'])) {
    $existingMethods[$moduleName] = $manager->methodsForModule($moduleName);
  }
}

foreach($existingMethods as $moduleName => $methodNames) {
  if (array_key_exists($moduleName, $customFunctions)) {
    $customMethods = $customFunctions[$moduleName];
    $customMethodNames = array_column($customMethods, 'methodName');
  } else {
    $customMethods = [];
    $customMethodNames = [];
  }
  foreach ($customMethods as $customMethod) {
    ['methodName' => $methodName, 'functionPath' => $functionPath, 'functionName' => $functionName] = $customMethod;
    
    if (!in_array($methodName, $methodNames)) {
      echo "$moduleName.$methodName" . PHP_EOL;
      $manager->addEntityMethod($moduleName, $methodName, $functionPath, $functionName);
      echo "  added\n";
    }
  }
  foreach ($methodNames as $methodName) {
    if (!in_array($methodName, $customMethodNames)) {
      echo "$moduleName.$methodName" . PHP_EOL;
      $manager->removeEntityMethod($moduleName, $methodName);
      echo "  removed\n";
    }
  }
}

// Workflow task types
echo "Ensuring workflow task types\n";
require_once 'modules/com_vtiger_workflow/VTTaskType.php';
$taskTypes = [
  'VTGeneratePdf' => ['modules' => ['include' => [], 'exclude' => []], 'name' => 'VTGeneratePdf', 'label' => 'Generate PDF', 'classname' => 'VTGeneratePdf', 'classpath' => 'modules/com_vtiger_workflow/tasks/VTGeneratePdf.php', 'templatepath' => 'com_vtiger_workflow/taskforms/VTGeneratePdf.tpl', 'sourcemodule' => NULL],
  'VTEntityWorkflow' => ['modules' => ['include' => [], 'exclude' => []], 'name' => 'VTEntityWorkflow', 'label' => 'Call Workflow', 'classname' => 'VTEntityWorkflow', 'classpath' => 'modules/com_vtiger_workflow/tasks/VTEntityWorkflow.php', 'templatepath' => 'com_vtiger_workflow/taskforms/VTEntityWorkflow.tpl', 'sourcemodule' => NULL],
  'VTGenerateTemplate' => ['modules' => ['include' => [], 'exclude' => []], 'name' => 'VTGenerateTemplate', 'label' => 'Generate Template', 'classname' => 'VTGenerateTemplate', 'classpath' => 'modules/com_vtiger_workflow/tasks/VTGenerateTemplate.php', 'templatepath' => 'com_vtiger_workflow/taskforms/VTGenerateTemplate.tpl', 'sourcemodule' => NULL],
  'VTGeneratePackage' => ['modules' => ['include' => [], 'exclude' => []], 'name' => 'VTGeneratePackage', 'label' => 'Generate Package', 'classname' => 'VTGeneratePackage', 'classpath' => 'modules/com_vtiger_workflow/tasks/VTGeneratePackage.php', 'templatepath' => 'com_vtiger_workflow/taskforms/VTGeneratePackage.tpl', 'sourcemodule' => NULL],
  'VTToast' => ['modules' => ['include' => [], 'exclude' => []], 'name' => 'VTToast', 'label' => 'Toast', 'classname' => 'VTToast', 'classpath' => 'modules/com_vtiger_workflow/tasks/VTToast.php', 'templatepath' => 'com_vtiger_workflow/taskforms/VTToast.tpl', 'sourcemodule' => NULL],
];
foreach($taskTypes as $taskTypeName => $taskType) {
  if (!empty(VTTaskType::getInstanceFromTaskType($taskTypeName)->get('name'))) {
    echo "  exists\n";
  } else {
    VTTaskType::registerTaskType($taskType);
    echo "  added\n";
  }
}

// Event Handlers
echo "Ensuring event handlers\n";
// [event] => [ 'className', 'includeModules', 'excludeModules' ]
$eventHandlers = [
  'EditViewPreSave' => [
    [ 'className' => 'Cases_DuplicateId_Handler', 'includeModules' => 'Cases', 'excludeModules' => ''],
    [ 'className' => 'TexasCases_DuplicateId_Handler', 'includeModules' => 'TexasCases', 'excludeModules' => ''],
    [ 'className' => 'Claims_DuplicateId_Handler', 'includeModules' => 'Claims', 'excludeModules' => ''],
    [ 'className' => 'DocumentTypes_Loop_Handler', 'includeModules' => 'DocumentTypes', 'excludeModules' => ''],
  ],
  'UserAfterSave' => [
    [ 'className' => 'ModTracker_ModTrackerHandler_Handler', 'includeModules' => 'Users', 'excludeModules' => ''],
  ],
];

foreach($eventHandlers as $event => $handlers) {
  echo "  $event\n";
  foreach($handlers as $handler) {
    echo "    " . $handler['className'] . "\n";
    $result = \App\EventHandler::registerHandler($event, $handler['className'], $handler['includeModules'], $handler['excludeModules']);
    echo "    Status: " . ($result ? "true" : "false") . PHP_EOL;
  }
}

// ensure crons
echo "Ensuring crons\n";
$crons = [ 
  [
    'name' => 'LBL_BATCH_TASKS_QUEUE_HANDLER', 'handler_class' => 'BatchTasks_HandleQueue_Cron',
    'frequency' => 60, 'module' => 'BatchTasks', 'status' => 1, 'sequence' => 0,
    'description' => 'Processes queue of batch tasks'
  ],
  [
    'name' => 'LBL_QUICKBOOKS_TOKEN_REFRESH_HANDLER', 'handler_class' => 'Vtiger_QuickBooksTokenRefresh_Cron',
    'frequency' => 60 * 60 * 24, 'module' => 'Vtiger', 'status' => 1, 'sequence' => 0,
    'description' => 'Refreshes QuickBooks refresh token'
  ],
  [
    'name' => 'LBL_OSSMAIL_TOKEN_REFRESH_HANDLER', 'handler_class' => 'OSSMail_RefreshTokens_Cron',
    'frequency' => 60 * 60 * 24, 'module' => 'OSSMail', 'status' => 1, 'sequence' => 0,
    'description' => 'Refreshes OAuth refresh tokens'
  ],
  [
    'name' => 'LBL_PORTFOLIO_PURCHASE_EMAIL_HANDLER', 'handler_class' => 'PortfolioPurchases_PurchasedPortfolios_Cron',
    'frequency' => 60, 'module' => 'PortfolioPurchases', 'status' => 1, 'sequence' => 0,
    'description' => 'Sends e-mail report with list of portfolio purchases from current day to investors. Handled by real cron, frequency set here doesn\'t matter'
  ],
  [
    'name' => 'LBL_DOTS_REPORTS_HANDLER', 'handler_class' => 'Vtiger_DotsReports_Cron',
    'frequency' => 60, 'module' => 'Vtiger', 'status' => 1, 'sequence' => 0,
    'description' => 'Processes DOTS reports. Handled by real cron, frequency set here doesn\'t matter'
  ],
  [
    'name' => 'LBL_DOTS_DATA_DUMP_HANDLER', 'handler_class' => 'Vtiger_DotsDataDump_Cron',
    'frequency' => 60, 'module' => 'Vtiger', 'status' => 1, 'sequence' => 0,
    'description' => 'Processes DOTS data dumps for PowerBI. Handled by real cron, frequency set here doesn\'t matter'
  ],
  [
    'name' => 'LBL_GCAL_SYNC_HANDLER', 'handler_class' => 'Vtiger_GCal_Cron',
    'frequency' => 60 * 15, 'module' => 'Vtiger', 'status' => 1, 'sequence' => 0,
    'description' => 'Handles Google Calendar synchronization'
  ],
  [
    'name' => 'LBL_CASES_STATUS_AGE', 'handler_class' => 'Cases_StatusAge_Cron',
    'frequency' => 60, 'module' => 'Cases', 'status' => 1, 'sequence' => 0,
    'description' => 'Handles case aging. Handled by real cron, frequency set here doesn\'t matter'
  ],
  [
    'name' => 'LBL_TEXASCASES_STATUS_AGE', 'handler_class' => 'TexasCases_StatusAge_Cron',
    'frequency' => 60, 'module' => 'TexasCases', 'status' => 1, 'sequence' => 0,
    'description' => 'Handles Texas case aging. Handled by real cron, frequency set here doesn\'t matter'
  ],
  [
    'name' => 'LBL_OSSMAIL_QUOTA_RESEND', 'handler_class' => 'OSSMail_ResendDueToQuota_Cron',
    'frequency' => 60 * 60 * 24, 'module' => 'OSSMail', 'status' => 1, 'sequence' => 0,
    'description' => 'Resends e-mails that failed to send due to quota limit'
  ],
  [
    'name' => 'LBL_BATCHERRORS_REPORT', 'handler_class' => 'BatchErrors_Report_Cron',
    'frequency' => 60, 'module' => 'BatchErrors', 'status' => 1, 'sequence' => 0,
    'description' => 'Sends report about BatchErrors. Handled by real cron, frequency set here doesn\'t matter'
  ],
  [
    'name' => 'LBL_PORTFOLIO_PURCHASE_FAILED_SIGNINGS_CRON', 'handler_class' => 'PortfolioPurchases_FailedSignings_Cron',
    'frequency' => 60 * 60, 'module' => 'PortfolioPurchases', 'status' => 1, 'sequence' => 0,
    'description' => 'Attempts to resend failed DocuSign signings'
  ],
  [
    'name' => 'LBL_DOCUMENT_PACKAGES_FAILED_DROPBOX_CRON', 'handler_class' => 'DocumentPackages_FailedDropbox_Cron',
    'frequency' => 60 * 60, 'module' => 'DocumentPackages', 'status' => 1, 'sequence' => 0,
    'description' => 'Attempts to resend failed Dropbox uploads'
  ],
  [
    'name' => 'LBL_PROGRAMS_TEMPORALDATA_HANDLER', 'handler_class' => 'Programs_TemporalData_Cron',
    'frequency' => 60, 'module' => 'Programs', 'status' => 1, 'sequence' => 0,
    'description' => 'Processes Temporal Data for Claims. Handled by real cron, frequency set here doesn\'t matter'
  ],
  [
    'name' => 'LBL_OSSMAIL_RETRY_HANGED', 'handler_class' => 'OSSMail_RetryHanged_Cron',
    'frequency' => 60 * 60, 'module' => 'OSSMail', 'status' => 1, 'sequence' => 0,
    'description' => 'Retries or marks as failed mails that stay in processing state'
  ],
  [
    'name' => 'LBL_OSSMAIL_DAILY_SEND', 'handler_class' => 'OSSMail_DailySend_Cron',
    'frequency' => 60 * 60, 'module' => 'OSSMail', 'status' => 1, 'sequence' => 0,
    'description' => 'Sends e-mail from SMTP to it\'s address if it hasn\'t sent one in last 24 hours'
  ],
  [
    'name' => 'LBL_VTIGER_HELP_INFO', 'handler_class' => 'Vtiger_HelpInfo_Cron',
    'frequency' => 60 * 60 * 24, 'module' => 'Vtiger', 'status' => 0, 'sequence' => 0,
    'description' => 'Synchronizes Help Info module with system help info'
  ],
  [
    'name' => 'LBL_ACTIVITY_REPORTS', 'handler_class' => 'Documents_activityReports_Cron',
    'frequency' => 60, 'module' => 'Documents', 'status' => 1, 'sequence' => 0,
    'description' => 'Creates user\'s activity reports as Excel files'
  ],
  [
    'name' => 'LBL_PORTFOLIOS_RESET_TO_NEW', 'handler_class' => 'Portfolios_ResetToNew_Cron',
    'frequency' => 60, 'module' => 'Portfolios', 'status' => 1, 'sequence' => 0,
    'description' => 'Resets "In Underwriting" Portfolios to "New" if they don\'t have Claims in specific statuses. Handled by real cron, frequency set here doesn\'t matter'
  ],
];

foreach($crons as $cron) {
  $isExists = (new \App\Db\Query())->from('vtiger_cron_task')->where(['name' => $cron['name'], 'handler_class' => $cron['handler_class']])->exists();
  if (!$isExists) {
    \vtlib\Cron::register(
      $cron['name'], $cron['handler_class'], $cron['frequency'], 
      $cron['module'], $cron['status'], $cron['sequence'], $cron['description']);
  }
}

// ensure files
echo "Ensure file overrides\n";
function rcopy($src, $dst, $level = 0) {
  echo "  " . str_pad('', $level * 2, ' ') . "$src -> $dst\n";
  if (is_dir($src)) {
    if (!file_exists($dst)) {
      mkdir($dst);
    }
    $files = scandir($src);
    foreach ($files as $file) {
      if ($file != "." && $file != "..") {
        rcopy("$src/$file", "$dst/$file", $level + 1);
      }
    }
  }
  else if (file_exists($src)) {
    copy($src, $dst);
  }
}

$src = "./install/update-file-overrides";
$files = scandir($src);
foreach ($files as $file) {
  if ($file != "." && $file != "..") {
    rcopy("$src/$file", "./$file");
  }
}

// refresh modules
\App\Module::createModuleMetaFile();

// refresh menu
$directory = ROOT_DIRECTORY . '/user_privileges';
$files = scandir($directory);
foreach ($files as $file) {
  if (strpos($file, 'menu_') === 0 && $file !== 'menu_0.php') {
    unlink($directory . '/' . $file);
  }
}

$allRoles = (new \App\Db\Query())->from('yetiforce_menu')->select('role')->distinct(true)->column();
$menuRecordModel = new \Settings_Menu_Record_Model();
foreach ($allRoles as $role) {
  $menuRecordModel->generateFileMenu($role);
}

// refresh fields
\App\Db\Fixer::profileField();

// refresh CSS
\App\Colors::generate('all');

// fix import directory
$path = App\Fields\File::getTmpPath();

// user_privileges_*
\App\UserPrivilegesFile::recalculateAll();

chown($path, "www-data");
chgrp($path, "www-data");

// enable tracking for Users
CRMEntity::getInstance('ModTracker')->enableTrackingForModule(\App\Module::getModuleId('Users'));

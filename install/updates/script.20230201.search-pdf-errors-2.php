<?php

$qg = new \App\QueryGenerator('Documents');
$qg->addCondition('filetype', 'application/pdf', 'e');
$qg->addCondition('filename', 'Demand_for_Pre_Suit_Payment', 'c', false);
$qg->addCondition('filename', 'Limited_Endorsement_Authorization_Request', 'c', false);
$qg->addCondition('filename', 'Summons.pdf', 'c', false);
$qg->addCondition('filename', 'Complaint.pdf', 'c', false);
$qg->addCondition('filename', 'Complaint_w_AOB', 'c', false);
$qg->addCondition('filename', 'FIGA - 1st Letter', 'c', false);
$qg->addCondition('filename', 'Universal_Pre_Suit_Demand_PAckage', 'c', false);
$qg->addCondition('filename', 'Notice of Intent to Litigate', 'c', false);
$qg->addCondition('filename', 'Notice_of_Intent_to_Litigate', 'c', false);
$qg->addCondition('filename', 'Settlement Demand Letter', 'c', false);
$qg->addCondition('filename', 'Settlement_Demand_Letter', 'c', false);
$qg->addCondition('filename', 'Complaint_Package_County', 'c', false);
$qg->addCondition('filename', '10_Day_Demand_Letter_Package', 'c', false);
$qg->addCondition('filename', 'Discovery_Requests_LSOP', 'c', false);

$num = 0;
foreach ($qg->createQuery()->all() as $record) {
  $num++;

  /** @var Documents_Record_Model $fileModel */
  $fileModel = Vtiger_Record_Model::getInstanceById($record['notesid']);
  $fileDetails = $fileModel->getFileDetails();
  $documentPath = "{$fileDetails['path']}{$fileDetails['attachmentsid']}";
  
  try {
    \App\Utils::process("pdfinfo $documentPath", '/var/www/html', false) . PHP_EOL;
  } catch (\Error $e) {
    echo "INVALID FILE " . $fileModel->get('filename') . " ($documentPath) - document id {$record['notesid']}, relations " . json_encode(['case' => $fileModel->get('case'), 'claim' => $fileModel->get('claim')]) . PHP_EOL;
  }
}

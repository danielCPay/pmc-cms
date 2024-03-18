<?php 

require_once 'include/main/WebUI.php';

\App\User::setCurrentUserId(\App\User::getActiveAdminId());

$moduleModel = Vtiger_Module_Model::getInstance('Insureds');
$fields = $moduleModel->getFields();

$recordGroups = [];
// run query
$cursor = \App\Db::getInstance()->createCommand('SELECT * FROM (
  SELECT i.*, 
    ROW_NUMBER() over (PARTITION BY insured_name COLLATE utf8_bin, street COLLATE utf8_bin, zip, city COLLATE utf8_bin, e_mail COLLATE utf8_bin, phone, insured1_first_name COLLATE utf8_bin, insured1_last_name COLLATE utf8_bin, insured2_first_name COLLATE utf8_bin, insured2_last_name COLLATE utf8_bin, county, state, build_name_automatically ORDER BY insuredsid) AS rn,
    MIN(insuredsid) over (PARTITION BY insured_name COLLATE utf8_bin, street COLLATE utf8_bin, zip, city COLLATE utf8_bin, e_mail COLLATE utf8_bin, phone, insured1_first_name COLLATE utf8_bin, insured1_last_name COLLATE utf8_bin, insured2_first_name COLLATE utf8_bin, insured2_last_name COLLATE utf8_bin, county, state, build_name_automatically ORDER BY insuredsid) AS groupid,
    COUNT(*) over (PARTITION BY insured_name COLLATE utf8_bin, street COLLATE utf8_bin, zip, city COLLATE utf8_bin, e_mail COLLATE utf8_bin, phone, insured1_first_name COLLATE utf8_bin, insured1_last_name COLLATE utf8_bin, insured2_first_name COLLATE utf8_bin, insured2_last_name COLLATE utf8_bin, county, state, build_name_automatically) as cnt FROM vw_insureds i
  ) a WHERE a.cnt > 1
  ORDER BY cnt DESC, insured_name, street, zip, city, e_mail, phone, insured1_first_name, insured1_last_name, county, state, 1')->queryAll();
foreach ($cursor as $row) {
  if ($row['rn'] == 1) {
    $recordGroups[$row['groupid']] = [];
  } else {
    $recordGroups[$row['groupid']][] = $row['insured'];
  }
}

echo 'Groups:' . PHP_EOL . var_export($recordGroups, true) . PHP_EOL;

// foreach group id simulate merge records actions
foreach ($recordGroups as $primaryRecord => $records) {
  $migrate = [];
  foreach ($records as $record) {
    if ($record !== $primaryRecord) {
      $migrate[$record] = [];
    }
  }

  try {
    echo "\tPrimary = $primaryRecord, Rest = [" . implode(', ', $records) . "]" . PHP_EOL;
    \App\RecordTransfer::transfer($primaryRecord, $migrate);
    foreach (array_keys($migrate) as $recordId) {
      $recordModel = \Vtiger_Record_Model::getInstanceById($recordId);
      $recordModel->ext['modificationType'] = ModTracker_Record_Model::TRANSFER_DELETE;
      $recordModel->changeState('Trash');
    }
    echo "\tDone" . PHP_EOL;
  } catch (\Throwable $ex) {
    echo $ex->__toString();
    var_export($ex);
    throw $ex;
  }
}

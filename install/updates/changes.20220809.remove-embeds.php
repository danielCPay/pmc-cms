<?php
chdir(__DIR__ . '/../../');
set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . '/../../');

require_once 'include/main/WebUI.php';
require_once 'modules/com_vtiger_workflow/include.php';

// check distinct folders and file types, min, avg and max size for all files with embed* names
// SELECT folderid, filetype, COUNT(*), MIN(filesize), AVG(filesize), MAX(filesize) FROM `vtiger_notes` INNER JOIN `vtiger_crmentity` ON vtiger_notes.notesid = vtiger_crmentity.crmid WHERE (`vtiger_crmentity`.`deleted`=0) AND (`vtiger_notes`.`title` LIKE '%embed%') GROUP BY folderid, filetype

// delete all image embeds in Mails folder
$query = (new \App\QueryGenerator('Documents'))
  // skip? ->addCondition('notes_title', 'embed', 'a')
  ->addCondition('folderid', 'T2', 'e')
  ->addCondition('filesize', 10000, 'l')
  ->addCondition('filetype', 'image/', 'a');

$documentRows = $query->createQuery()->all();
foreach($documentRows as $documentRow) {
  $document = Vtiger_Record_Model::getInstanceById($documentRow['notesid']);
  $document->delete();
}

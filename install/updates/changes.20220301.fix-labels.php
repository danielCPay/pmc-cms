<?php
chdir(__DIR__ . '/../../');
set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . '/../../');

require_once 'include/main/WebUI.php';
require_once 'modules/com_vtiger_workflow/include.php';

$query = (new App\Db\Query())->select(['vtiger_crmentity.crmid', 'vtiger_crmentity.setype',
  'u_#__crmentity_label.label', 'u_#__crmentity_search_label.searchlabel', ])
  ->from('vtiger_crmentity')
  ->leftJoin('u_#__crmentity_label', ' u_#__crmentity_label.crmid = vtiger_crmentity.crmid')
  ->leftJoin('u_#__crmentity_search_label', 'u_#__crmentity_search_label.crmid = vtiger_crmentity.crmid')
  ->where(['and',
    ['vtiger_crmentity.deleted' => 0],
    ['or',
      ['u_#__crmentity_label.label' => null],
      ['u_#__crmentity_label.label' => ''],
      ['u_#__crmentity_search_label.searchlabel' => null],
      ['u_#__crmentity_search_label.searchlabel' => '']
    ],
  ]);
$skipModules = (new App\Db\Query())->select(['vtiger_tab.name'])->from('vtiger_tab')
  ->leftJoin('vtiger_entityname', 'vtiger_tab.tabid = vtiger_entityname.tabid')
  ->where(['vtiger_tab.isentitytype' => 1])
  ->andWhere([
    'or',
    ['vtiger_tab.presence' => 1],
    ['vtiger_entityname.modulename' => null],
    ['vtiger_entityname.fieldname' => '', 'vtiger_entityname.searchcolumn' => ''],
  ])
  ->column();
if ($skipModules) {
  $query->andWhere(['not in', 'vtiger_crmentity.setype', $skipModules]);
}
foreach ($query->batch(100) as $rows) {
  foreach ($rows as $row) {
    $updater = false;
    if (empty($row['label']) && !empty($row['searchlabel'])) {
      $updater = 'label';
    } elseif (empty($row['searchlabel']) && !empty($row['label'])) {
      $updater = 'searchlabel';
    }
    \App\Record::updateLabel($row['setype'], $row['crmid'], true, $updater);
  }
}

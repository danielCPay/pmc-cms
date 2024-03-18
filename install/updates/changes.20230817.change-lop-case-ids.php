<?php 

require_once 'include/main/WebUI.php';

\App\User::setCurrentUserId(\App\User::getActiveAdminId());

$caseIdsToChange = (new \App\QueryGenerator('Cases'))->setField('id')->addCondition('type_of_claim', 'LOP/DTP', 'e')->addCondition('case_id', 'PDC', 'sw')->createQuery()->column();

foreach ($caseIdsToChange as $caseId) {
  $case = Vtiger_Record_Model::getInstanceById($caseId, 'Cases');
  $case->set('case_id', str_replace('PDC', 'LOP', $case->get('number')));
  $case->save();
}

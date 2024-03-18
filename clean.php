<?php

require_once 'include/main/WebUI.php';

function cleanData(array $modulesToClean)
{
  $recordsToClean = [];
  foreach ($modulesToClean as $moduleToClean) {
    $entityModel = \CRMEntity::getInstance($moduleToClean);
    echo $moduleToClean . PHP_EOL;
    $query = (new \App\Db\Query())->select(['id' => $entityModel->tab_name_index[$entityModel->table_name]])->from($entityModel->table_name);
    echo "\t" . $query->createCommand()->getRawSql() . PHP_EOL;
    $recordsToClean[$moduleToClean] = ['tables' => $entityModel->tab_name];
    $recordsToClean[$moduleToClean]['ids'] = $query->column();
  }
  var_export($recordsToClean);
  var_export(\array_map(function ($value) {
    return count($value);
  }, $recordsToClean));
  echo PHP_EOL;

  foreach ($recordsToClean as $module => $data) {
    echo $module . PHP_EOL;
    foreach ($data['ids'] as $recordId) {
      echo "\t$recordId" . PHP_EOL;
      $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $module);
      $recordModel->setHandlerExceptions(['disableHandlerClasses' => ['Vtiger_Workflow_Handler']]);
      $recordModel->delete();
    }
    foreach ($data['tables'] as $tableName) {
      if ($tableName === 'vtiger_crmentity') {
        continue;
      }
      echo "\toptimize $tableName" . PHP_EOL;
      \App\Db::getInstance()->createCommand("OPTIMIZE TABLE $tableName")->execute();
    }
  }

  // vtiger_ossmails_logs
  echo "clean vtiger_ossmails_logs" . PHP_EOL;
  \App\Db::getInstance()->createCommand()->delete('vtiger_ossmails_logs')->execute();
  echo "optimize vtiger_ossmails_logs" . PHP_EOL;
  \App\Db::getInstance()->createCommand('OPTIMIZE TABLE vtiger_ossmails_logs')->execute();

  // u_yf_browsinghistory
  echo "clean u_yf_browsinghistory" . PHP_EOL;
  \App\Db::getInstance()->createCommand()->delete('u_yf_browsinghistory')->execute();
  echo "optimize u_yf_browsinghistory" . PHP_EOL;
  \App\Db::getInstance()->createCommand('OPTIMIZE TABLE u_yf_browsinghistory')->execute();

  // vtiger_loginhistory
  echo "clean vtiger_loginhistory" . PHP_EOL;
  \App\Db::getInstance()->createCommand()->delete('vtiger_loginhistory')->execute();
  echo "optimize vtiger_loginhistory" . PHP_EOL;
  \App\Db::getInstance()->createCommand('OPTIMIZE TABLE vtiger_loginhistory')->execute();

  // u_yf_crmentity_label
  echo "clean u_yf_crmentity_label" . PHP_EOL;
  \App\Db::getInstance()->createCommand()->delete('u_yf_crmentity_label', 'crmid NOT IN ( SELECT crmid FROM vtiger_crmentity )')->execute();
  echo "optimize u_yf_crmentity_label" . PHP_EOL;
  \App\Db::getInstance()->createCommand('OPTIMIZE TABLE u_yf_crmentity_label')->execute();

  // u_yf_crmentity_search_label
  echo "clean u_yf_crmentity_search_label" . PHP_EOL;
  \App\Db::getInstance()->createCommand()->delete('u_yf_crmentity_search_label', 'crmid NOT IN ( SELECT crmid FROM vtiger_crmentity )')->execute();
  echo "optimize u_yf_crmentity_search_label" . PHP_EOL;
  \App\Db::getInstance()->createCommand('OPTIMIZE TABLE u_yf_crmentity_search_label')->execute();

  // modtracker
  echo "clean vtiger_modtracker_basic" . PHP_EOL;
  \App\Db::getInstance()->createCommand()->delete('vtiger_modtracker_basic', 'crmid NOT IN ( SELECT crmid FROM vtiger_crmentity )')->execute();
  echo "clean vtiger_modtracker_detail" . PHP_EOL;
  \App\Db::getInstance()->createCommand()->delete('vtiger_modtracker_detail', 'id NOT IN ( SELECT id FROM vtiger_modtracker_basic )')->execute();
  echo "clean vtiger_modtracker_relations" . PHP_EOL;
  \App\Db::getInstance()->createCommand()->delete('vtiger_modtracker_relations', 'id NOT IN ( SELECT id FROM vtiger_modtracker_basic )')->execute();
  echo "optimize modtracker" . PHP_EOL;
  \App\Db::getInstance()->createCommand('OPTIMIZE TABLE vtiger_modtracker_basic, vtiger_modtracker_detail, vtiger_modtracker_relations')->execute();

  // u_yf_mail_autologin
  echo "clean u_yf_mail_autologin" . PHP_EOL;
  \App\Db::getInstance()->createCommand()->delete('u_yf_mail_autologin')->execute();
  
  // s_yf_mail_queue
  echo "clean s_yf_mail_queue" . PHP_EOL;
  \App\Db::getInstance()->createCommand()->delete('s_yf_mail_queue')->execute();
  
  // s_yf_mail_rbl_list
  echo "clean s_yf_mail_rbl_list" . PHP_EOL;
  \App\Db::getInstance()->createCommand()->delete('s_yf_mail_rbl_list')->execute();
  
  // l_yf_mail
  echo "clean l_yf_mail" . PHP_EOL;
  \App\Db::getInstance()->createCommand()->delete('l_yf_mail')->execute();
  
  // Documents
  $recordsToClean = [];
  $entityModel = \CRMEntity::getInstance('Documents');
  echo 'Documents' . PHP_EOL;
  $query = (new \App\Db\Query())->select(['id' => $entityModel->tab_name_index[$entityModel->table_name]])->from($entityModel->table_name);
  echo "\t" . $query->createCommand()->getRawSql() . PHP_EOL;
  $recordsToClean['Documents'] = ['tables' => $entityModel->tab_name];
  $recordsToClean['Documents']['ids'] = $query->column();

  foreach ($recordsToClean as $module => $data) {
    echo $module . PHP_EOL;
    foreach ($data['ids'] as $recordId) {
      echo "\t$recordId" . PHP_EOL;
      $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $module);
      // vtiger_crmentityrel - check if exists crmid with document id = relcrmid or exists relcrmid with document id = crmid
      $cnt = \App\Db::getInstance()->createCommand("select count(*) from vtiger_crmentityrel where (crmid = $recordId and relcrmid in ( select crmid from vtiger_crmentity )) or (relcrmid = $recordId and crmid in ( select crmid from vtiger_crmentity ))")->queryScalar();
      if ($cnt > 0) {
        echo "\tfound relation in vtiger_crmentityrel" . PHP_EOL;
        continue;
      }
      // vtiger_fieldmodulerel - find which modules have relation to Documents, check field identified by fieldid for documentid
      $fields = \App\Db::getInstance()->createCommand("select module, tablename, columnname, relmodule from vtiger_fieldmodulerel r join vtiger_field f on f.fieldid = r.fieldid where r.module = 'Documents' or r.relmodule = 'Documents'")->queryAll();
      foreach ($fields as $row) {
        $command = null;
        if ($row['module'] == 'Documents') {
          $command = \App\Db::getInstance()->createCommand("select count(*) from " . $row['tablename'] . " join vtiger_crmentity on vtiger_crmentity.crmid = " . $row['columnname'] . " where notesid = $recordId");
          $cnt = $command->queryScalar();
        } else {
          $entityModel = \CRMEntity::getInstance($row['module']);
          $command = \App\Db::getInstance()->createCommand("select count(*) from " . $row['tablename'] . " join vtiger_notes on vtiger_notes.notesid = " . $row['columnname'] . " where " . $row['columnname'] . " = $recordId");
          $cnt = $command->queryScalar();
        }
        if ($cnt > 0) {
          echo $command->getRawSql();
          echo "\tfound relation in vtiger_fieldmodulerel" . PHP_EOL;
          continue 2;
        }
      }
      // vtiger_senotesrel - check crmid matched by notesid = document id
      $cnt = \App\Db::getInstance()->createCommand("select count(*) from vtiger_senotesrel where notesid = $recordId and crmid in ( select crmid from vtiger_crmentity )")->queryScalar();
      if ($cnt > 0) {
        echo "\tfound relation in vtiger_senotesrel" . PHP_EOL;
        continue;
      }
      $recordModel->setHandlerExceptions(['disableHandlerClasses' => ['Vtiger_Workflow_Handler']]);
      $recordModel->delete();
    }
    foreach ($data['tables'] as $tableName) {
      if ($tableName === 'vtiger_crmentity') {
        continue;
      }
      echo "\toptimize $tableName" . PHP_EOL;
      \App\Db::getInstance()->createCommand("OPTIMIZE TABLE $tableName")->execute();
    }
  }

  // vtiger_crmentity
  echo "optimize vtiger_crmentity" . PHP_EOL;
  \App\Db::getInstance()->createCommand("OPTIMIZE TABLE vtiger_crmentity")->execute();

  // clear notebooks
  echo "reset notebooks" . PHP_EOL;
  \App\Db::getInstance()->createCommand("UPDATE vtiger_module_dashboard_widgets SET data = '{\"contents\":\"\",\"lastSavedOn\":\"2021-03-25 15:30:55\"}' WHERE linkid IN ( SELECT linkid FROM vtiger_links WHERE linktype = 'DASHBOARDWIDGET' AND linkurl = 'index.php?module=Home&view=ShowWidget&name=Notebook')")->execute();

  // clear record numbering
  echo "reset prefixes" . PHP_EOL;
  \App\Db::getInstance()->createCommand("UPDATE vtiger_modentity_num SET cur_id = start_id where cur_id != start_id")->execute();
}

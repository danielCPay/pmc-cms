<?php

class Table {
  public string $name;
  public array $columns = [];
  public string $primaryKey = '';
  public array $keys = [];

  private const NAME_RE = '/^CREATE TABLE `([0-9a-zA-Z][^`]*)` \($/';
  private const COLUMN_RE = '/`([0-9a-zA-Z][^`]*)` (.+),/';
  private const PK_RE = '/PRIMARY KEY \(((?:`[^`]+`(, ?)?)+)\),?/';
  private const KEY_RE = '/KEY `([0-9a-zA-Z].*)` \(((?:`[^`]+`(, ?)?)+)\)( USING BTREE)?,/';

  public function __construct(array $file) {
    // read table name
    $line = array_shift($file);
    if (!str_starts_with($line, 'CREATE TABLE')) {
      throw new Error("Invalid file format, expected to start with CREATE TABLE");
    }

    preg_match(self::NAME_RE, $line, $matches);
    $this->name = $matches[1];

    while (str_starts_with($line = trim(array_shift($file)), '`')) {
      preg_match(self::COLUMN_RE, $line, $matches);
      $this->columns[$matches[1]] = $matches[2];
    }

    if (count($this->columns) === 0) {
      throw new Error("No columns found");
    }

    preg_match(self::PK_RE, $line, $matches);
    if (empty($matches[1])) {
      ; // ok
    } else {
      $this->primaryKey = $matches[1];

      if (strlen($this->primaryKey) === 0) {
        throw new Error("No columns in PRIMARY KEY");
      }
    }
    
    while (str_starts_with($line = trim(array_shift($file)), 'KEY')) {
      preg_match(self::KEY_RE, $line, $matches);
      $this->keys[$matches[1]] = $matches[2];
    }
  }
}

function readData($file, $primaryKey = []) {
  $columns = [];
  $content = [];
  $cnt = 0;
  if (($handle = fopen("$file", "r")) !== FALSE) {
    while (($data = fgetcsv($handle)) !== FALSE) {
      if (count($columns) === 0) {
        $columns = $data;
      } else {
        $row = [];
        for ($c=0; $c < count($data); $c++) {
          $row[$columns[$c]] = $data[$c];
        }
        $pkValues = [];
        if (empty($primaryKey)) {
          $pkValues = $cnt;
        } else {
          foreach ($primaryKey as $column) {
            $pkValues[] = $row[$column];
          }
        }
        $content[implode('|', $pkValues)] = $row;
      }
      $cnt++;
    }
    fclose($handle);
  }

  return [$columns, $content];
}

function generateSchemaUpgradeScript($dir) {
  $db = \App\Db::getInstance();
  $files = scandir($dir);
  $script = '';
  foreach( $files as $file) {
    if ($file === 'ddl.sql' || !str_ends_with($file, '.sql') || str_starts_with($file, 'vw')) {
      continue;
    }
    echo "Processing $dir/$file" . PHP_EOL;

    // read new version of table
    $content = file("$dir/$file", FILE_IGNORE_NEW_LINES );
    $newTable = new Table($content);

    // read current version from DB
    $createTable = false;
    try {
      $createTable = preg_replace('/ ?AUTO_INCREMENT=\d+/i', '', \App\Db::getInstance()->createCommand('SHOW CREATE TABLE ' . $newTable->name)->queryOne()['Create Table']);
    } catch (yii\db\Exception $e) {
    }

    if ($createTable) {
      $currentTable = new Table(explode(PHP_EOL, $createTable));

      $changes = '';
      // find all columns that are in current and not in new - DELETE
      // find all column that are in both, but attributes are different - MODIFY
      foreach ($currentTable->columns as $column => $attributes) {
        if (!$column) {
          continue;
        }
        $newColumn = $newTable->columns[$column];
        if ($newColumn) {
          if ($newColumn !== $attributes) {
            // MODIFY
            $changes .= "ALTER TABLE `$currentTable->name` change COLUMN `$column` `$column` $newColumn;" . PHP_EOL;
          }
        } else {
          // DELETE
          $changes .= "ALTER TABLE `$currentTable->name` DROP COLUMN `$column`;" . PHP_EOL;
        }
      }
      // find all columns that are in new and not in current - ADD
      foreach ($newTable->columns as $column => $attributes) { 
        if (!$column) {
          continue;
        }
        $currentColumn = $currentTable->columns[$column];
        if (!$currentColumn) {
          // ADD
          $changes .= "ALTER TABLE `$currentTable->name` ADD COLUMN `$column` $attributes;" . PHP_EOL;
        }
      }
      // compare columns for primary key
      if ($currentTable->primaryKey !== $newTable->primaryKey) {
        $changes .= "ALTER TABLE `$currentTable->name` DROP PRIMARY KEY;" . PHP_EOL;
        $changes .= "ALTER TABLE `$currentTable->name` ADD PRIMARY KEY ( $newTable->primaryKey );". PHP_EOL;
      }
      // find all keys that are in current and not in new - DELETE
      // find all keys that are in both, but column are different - MODIFY
      foreach ($currentTable->keys as $key => $columns) {
        if (!$key) {
          continue;
        }

        $newKey = $newTable->keys[$key];
        if ($newKey) {
          if ($newKey !== $columns) {
            // MODIFY
            $changes .= "ALTER TABLE `$currentTable->name` DROP KEY `$key`;" . PHP_EOL;
            $changes .= "ALTER TABLE `$currentTable->name` ADD KEY `$key` ($newKey);" . PHP_EOL;
          }
        } else {
          // DELETE
          $changes .= "ALTER TABLE `$currentTable->name` DROP KEY `$key`;" . PHP_EOL;
        }
      }
      // find all keys that are in new and not in current - ADD
      foreach ($newTable->keys as $key => $columns) {
        if (!$key) {
          continue;
        }
        $currentKey = $currentTable->keys[$key];
        if (!$currentKey) {
          // ADD
          $changes .= "ALTER TABLE `$currentTable->name` ADD KEY `$key` ($columns);" . PHP_EOL;
        }
      }
      
      if (strlen($changes) > 0) {
        $script .= PHP_EOL;
        $script .= $changes;
      }
    } else {
      $script .= PHP_EOL;
      $script .= implode(PHP_EOL, $content) . ';' . PHP_EOL;
    }
  }

  file_put_contents('ddl.sql', $script);
}

function generateDataUpgradeScript($dir) {
  $db = \App\Db::getInstance();
  $files = scandir($dir);
  $script = '';
  $ignore = ['vtiger_customview.csv', 'vtiger_cvcolumnlist.csv', 
    'vtiger_currency_info.csv',
    'yetiforce_currencyupdate.csv', 'w_yf_portal_user.csv', 'vtiger_users.csv', 'vtiger_user2role.csv',
    'vtiger_tmp_write_user_sharing_per.csv', 'vtiger_tmp_write_group_sharing_per.csv',
    'vtiger_tmp_read_user_sharing_per.csv', 'vtiger_tmp_read_group_sharing_per.csv',
    'vtiger_senotesrel.csv', 'vtiger_seattachmentsrel.csv', 'vtiger_notes.csv', 'vtiger_notescf.csv',
    'vtiger_cron_task.csv', 'vtiger_crmentityrel.csv', 'vtiger_crmentity.csv', 
    'vtiger_attachments.csv', 'vtiger_activity_reminder_popup.csv', 'vtiger_users2group.csv',
    'vtiger_users_seq.csv', 'vtiger_widgets.csv', 'vtiger_module_dashboard_widgets.csv',
    'vtiger_groups.csv', 'vtiger_group2modules.csv', 'vtiger_currency_grouping_separator.csv',
    'vtiger_activity_reminder.csv',
    's_yf_companies.csv',
    's_yf_mail_smtp.csv', 's_yf_mail_queue.csv', 's_yf_mail_rbl_request.csv', 's_yf_mail_rbl_list.csv',
    'o_yf_csrf.csv', 'com_vtiger_workflow_activatedonce.csv', 'a_yf_signing_skip.csv',
    'a_yf_bruteforce_blocked.csv',
    'vtiger_modcomments.csv', 'vtiger_modcommentscf.csv',
    'vtiger_activity.csv', 'vtiger_activitycf.csv',
    'vtiger_entity_stats.csv',
    'vtiger_account.csv', 'vtiger_accountscf.csv', 'vtiger_accountaddress.csv',
    'vtiger_leadaddress.csv', 'vtiger_leaddetails.csv', 'vtiger_leadscf.csv', 'vtiger_leadsubdetails.csv', 
    'vtiger_osstimecontrol.csv', 'vtiger_osstimecontrolcf.csv',
    'vtiger_paymentsin.csv', 'vtiger_paymentsincf.csv',
    'vtiger_ticketcf.csv', 'vtiger_troubletickets.csv', 'vtiger_troubletickets_state_history.csv',
    'vtiger_user_module_preferences.csv',
    'rep_history_statuses.csv',
    'com_vtiger_workflowtask_queue.csv',
    'vtiger_contacts.csv', 'vtiger_contactscf.csv', 'vtiger_contactaddress.csv', 'vtiger_contactdetails.csv',
    'vtiger_contactsubdetails.csv', 'vtiger_customerdetails.csv',
    's_yf_toasts.csv',
    'u_yf_documenttypes.csv', 'u_yf_documenttemplates.csv',
    'vtiger_import_maps.csv', 'vtiger_import_queue.csv', 'vtiger_import_locks.csv',
    's_yf_privileges_updater.csv',
    ];
  $manualPK = [
    'u_yf_emailtemplates' => ['number'],
    'u_yf_documenttemplates' => ['number'],
    'u_yf_programalgorithms' => ['number'],
    'vtiger_profile' => ['profileid'],
    'vtiger_profile2tab' => ['profileid', 'tabid'],
    'vtiger_picklist_dependency' => ['id'],
    'vtiger_tab_info' => ['tabid', 'prefname'],
    'vtiger_fieldmodulerel' => ['fieldid', 'module', 'relmodule'],
    'vtiger_customview' => ['viewname', 'entitytype'],
    'vtiger_trees_templates_data' => ['templateid'],
    'com_vtiger_workflow_tasktypes' => ['id'],
    'vtiger_court_type' => ['court_typeid'],
    'vtiger_type_of_claim' => ['type_of_claimid'],
    'vtiger_settlement_status' => ['settlement_statusid'],
    'vtiger_service_types' => ['service_typesid'],
    'vtiger_state' => ['stateid'],
    'vtiger_collection_type' => ['collection_typeid'],
    'vtiger_portfolio_purchase_status' => ['portfolio_purchase_statusid'],
    'vtiger_provider_contact_type' => ['provider_contact_typeid'],
    'vtiger_task_type' => ['task_typeid'],
    'vtiger_attorney_contract_on_file' => ['attorney_contract_on_fileid'],
    'vtiger_plaintiff_discovery_status' => ['plaintiff_discovery_statusid'],
    'vtiger_pre_litigation_status' => ['pre_litigation_statusid'],
    'u_yf_helpinfo' => ['module_label', 'field_label'],
  ];
  $ignoreColumns = [
    'u_yf_emailtemplates' => ['emailtemplatesid'],
    'u_yf_documenttemplates' => ['documenttemplatesid'],
    'u_yf_documenttypes' => ['documenttypesid'],
    'vtiger_customview' => ['cvid', 'userid'],
    'vtiger_modentity_num' => ['cur_id', 'cur_sequence'],
    'com_vtiger_workflows' => ['nexttrigger_time'],
    'u_yf_helpinfo' => ['helpinfoid', 'number'],
    'u_yf_programalgorithms' => ['programalgorithmsid'],
  ];

  $picklists = $db->createCommand('select name from vtiger_picklist')->queryAll();
  foreach ($picklists as $picklist) {
    ['name' => $name] = $picklist;
    if (!\array_key_exists("vtiger_$name", $manualPK)) {
      $manualPK["vitger_$name"] = ["{$name}id"];
    }
  }

  $picklists = $db->createCommand(<<<SQL
SELECT 
  REPLACE(t.table_name, 'vtiger_', '') name
FROM 
  information_schema.tables t 
WHERE 
  table_catalog = 'def' 
  AND table_schema = 'yetiforce' 
  AND TABLE_NAME LIKE 'vtiger_%' 
  AND EXISTS ( 
    SELECT 
      NULL 
    FROM 
      vtiger_field f
    where
      f.columnname = REPLACE(t.table_name, 'vtiger_', '')
      AND f.uitype = 16
  )
SQL
  )->queryAll();
  foreach ($picklists as $picklist) {
    ['name' => $name] = $picklist;
    if (!\array_key_exists("vtiger_$name", $manualPK)) {
      $manualPK["vtiger_$name"] = ["{$name}id"];
    }
  }

  $modifiedWorkflows = [];
  $addedEmailTemplates = [];
  $modifiedEmailTemplates = [];
  foreach($files as $file) {
    if ($file === 'dml.sql' || !str_ends_with($file, '.csv') 
    || (
      str_starts_with($file, 'u_yf') 
      && !\in_array($file, array_merge(array_map(function ($el) { return "$el.csv";}, array_keys($manualPK)), ['u_yf_documenttypes.csv'])) 
      && strpos($file, '_invfield') === false && strpos($file, '_invmap') === false
    )
    || str_starts_with($file, 'vtiger_ossmail') || str_starts_with($file, 'vw')
    || \in_array($file, $ignore)) {
      continue;
    }
    echo "Processing $dir/$file" . PHP_EOL;

    $tableName = basename($file, '.csv');

    // read pk
    if (array_key_exists($tableName, $manualPK)) {
      $primaryKey = $manualPK[$tableName];
    } else {
      $createTable = preg_replace('/ ?AUTO_INCREMENT=\d+/i', '', $db->createCommand('SHOW CREATE TABLE ' . $tableName)->queryOne()['Create Table']);
      $table = new Table(explode(PHP_EOL, $createTable), $tableName === 'vtiger_picklistvalues_seq' ? true : false);
      $primaryKey = array_map(function ($column) { return trim(trim($column), '`'); }, explode(',', $table->primaryKey));
    }

    // read new version of table
    [$columns, $content] = readData("$dir/$file", $primaryKey);

    // read current version of table
    $currentContent = [];
    $currentData = $db->createCommand('SELECT * FROM ' . $tableName)->queryAll();
    foreach ($currentData as $row) {
      $pkValues = [];
      foreach ($primaryKey as $column) {
        $pkValues[] = $row[$column];
      }
      foreach ($row as $key => $value) {
        $row[$key] = $value === null ? 'NULL' : $value;
      }
      $currentContent[implode('|', $pkValues)] = $row;
    }

    $getPK = function ($row) use ($primaryKey) {
      $where = '';
      foreach ($primaryKey as $column) {
        if ($where) {
          $where .= ' AND ';
        }
        $where .= "`$column` = '" . $row[$column] . "'";
      }
      return $where;
    };

    $getPKComma = function ($row) use ($primaryKey) {
      $values = [];
      foreach ($primaryKey as $column) {
        $values[] = "'{$row[$column]}'";
      }

      return '( ' . implode(', ', $values) . ' )';
    };
    
    // compare for differences, new and removed rows
    // SPECIAL
    // ignore column with timestamp for com_vtiger_workflows
    // only new rows for vtiger_modentity_num
    // report workflow_id for all modified and new WF and tasks
    // report u_yf_emailtemplates.number for all modified email templates
    $commands = '';
    $insertColumns = "(" . implode(', ', array_map(function ($val) { return "`$val`"; }, $columns)) . ")";
    $insertValues = [];
    foreach ($content as $pk => $newValues) {
      $currentValues = $currentContent[$pk];
      if (empty($currentValues)) {
        if ($tableName == 'com_vtiger_workflows' || $tableName == 'com_vtiger_workflowtasks') {
          $modifiedWorkflows[] = $newValues['workflow_id'];
        } else if ($tableName == 'u_yf_emailtemplates') {
          $addedEmailTemplates[] = $newValues['number'];
        } else {
          // ADD row
          $values = '';
          foreach ($columns as $column) {
            if (!empty($values)) {
              $values .= ', ';
            }
            $values .= $newValues[$column] === 'NULL' ? "null" : $db->quoteValue($newValues[$column]);
          }
          $insertValues[] = "( {$values} )";
        }
      } else if ($currentValues !== $newValues) {
        // UPDATE row
        // find columns to update
        $values = '';
        foreach ($columns as $column) {
          if (\array_key_exists($tableName, $ignoreColumns) && \in_array($column, $ignoreColumns[$tableName])) {
            continue;
          }
          $currentValue = $currentValues[$column];
          $newValue = $newValues[$column];
          if ($currentValue != $newValue) {
            if (!empty($values)) {
              $values .= ', ';
            }
            $values .= "`$column` = " . ($newValue === 'NULL' ? "null" : $db->quoteValue($newValue));
          }
        }
        if (empty($values)) {
          continue;
        }
        if ($tableName == 'com_vtiger_workflows' || $tableName == 'com_vtiger_workflowtasks') {
          $modifiedWorkflows[] = $newValues['workflow_id'];
        } else if ($tableName == 'u_yf_emailtemplates') {
          $columns = [];
          foreach (explode(', ', $values) as $columnWithValue) {
            [$columnName] = explode(' = ', $columnWithValue);
            $columns[] = $columnName;
          }
          $data = $newValues['number'] . ' (' . implode(', ', $columns) . ')';
          
          $modifiedEmailTemplates[] = $data;
        } else {
          if (!empty($pk)) {
            // prepare pk
            $where = $getPK($newValues);
            $commands .= "UPDATE `$tableName` SET $values WHERE $where;" . PHP_EOL;
          } else {
            $commands .= "UPDATE `$tableName` SET $values;" . PHP_EOL;
          }
        }
      }
    }
    if (count($insertValues)) {
      $batches = array_chunk($insertValues, 1000);
      foreach ($batches as $batch) {
        $commands .= "INSERT INTO `$tableName` $insertColumns VALUES " . PHP_EOL . "\t" . implode( "," . PHP_EOL . "\t", $batch) . ";" . PHP_EOL;
      }
    }
    $wheres = [];
    foreach ($currentContent as $pk => $currentValues) {
      $newValues = $content[$pk];
      if (empty($newValues)) {
        // DELETE row
        if (!empty($pk)) {
          $wheres[] = $getPKComma($currentValues);
        } else {
          $wheres[] = '( 1 = 1 )';
        }
      }
    }
    if (count($wheres)) {
      if (!empty($pk)) {
        $batches = array_chunk($wheres, 1000);
        foreach ($batches as $batch) {
          $commands .= "DELETE FROM `$tableName` WHERE (" . implode(', ', array_map(function ($c) { return "`{$c}`"; }, $primaryKey)) . ") in ( " . implode( ', ', $batch ) . ");" . PHP_EOL;
        }
      } else {
        $commands .= "DELETE FROM `$tableName`;" . PHP_EOL;
      }
    }

    if (!empty($commands)) {
      $script .= $commands . PHP_EOL;
    }
  }

  if (!empty($modifiedWorkflows)) {
    $modifiedWorkflows = array_unique($modifiedWorkflows);
    sort($modifiedWorkflows);
    $script = '-- MODIFIED WORKFLOWS, REPLACE THROUGH SQL REPLACES = ' . implode(', ', $modifiedWorkflows) . PHP_EOL . PHP_EOL . $script;
  }
  if (!empty($addedEmailTemplates)) {
    $addedEmailTemplates = array_unique($addedEmailTemplates);
    sort($addedEmailTemplates);
    $script = '-- ADDED E-MAIL TEMPLATES, ADD THROUGH YF EXPORT/IMPORT = ' . implode(', ', $addedEmailTemplates) . PHP_EOL . PHP_EOL . $script;
  }
  if (!empty($modifiedEmailTemplates)) {
    $modifiedEmailTemplates = array_unique($modifiedEmailTemplates);
    sort($modifiedEmailTemplates);
    $script = '-- MODIFIED E-MAIL TEMPLATES, UPDATE USING MANUAL subject/content UPDATES = ' . implode(', ', $modifiedEmailTemplates) . PHP_EOL . PHP_EOL . $script;
  }

  file_put_contents('dml.sql', $script);
}

function generateUpgradeScript($schemaDir, $dataDir) {
  if (!empty($schemaDir)) {
    generateSchemaUpgradeScript($schemaDir);
  }
  if (!empty($dataDir)) {
    generateDataUpgradeScript($dataDir);
  }
}

function groupByColumn($data, $column, $single = false) {
  $grouped = [];
  foreach ($data as $row) {
    $value = array_map(function ($el) { return $el === null || $el === 'NULL' ? '' : $el; }, $row);
    if ($single) {
      $grouped[$row[$column]] = $value;
    } else {
      $grouped[$row[$column]][] = $value;
    }
  }

  return $grouped;
}

function conditionsToString($groupInfos, $filter) {
  foreach ($groupInfos as $groupInfo) {
    if (!empty($groupInfo['condition'])) {
      if (!empty($filter)) {
        $filter .= " {$groupInfo['group']['condition']} ";
      }
      $filter .= "({$groupInfo['condition']})";
    }
  }

  return $filter;
}

function groupToString($group, $hierarchy, $data) {
  $filter = '';

  $groupData = $data[$group];

  // same level conditions
  if ($group > 0) { // not parent
    $filter = conditionsToString($groupData, $filter);
  } 

  // sub level conditions
  if (!empty($hierarchy[$group])) {
    foreach ($hierarchy[$group] as $childId) {
      if (!empty($filter)) {
        $groupData = reset($groupData);
        $filter .= " {$groupData['group']['condition']} ";
      }
      $filter .= groupToString($childId, $hierarchy, $data);
    }
  }

  return $filter;
}

function filterToString($groups, $allSourceConditions) {
  $hierarchy = [];
  $data = [];
  foreach ($groups as $group) {
    $groupId = $group['id'];
    $conditions = 
      array_map(
        function ($el) { 
          return "{$el['source_field_name']}:{$el['module_name']}:{$el['field_name']} {$el['operator']} {$el['value']}"; 
        }, 
        $allSourceConditions[$groupId] ?? []
      );

    $filter = implode(" {$group['condition']} ", $conditions);
    $hierarchy[$group['parent_id']][] = $group['id'];
    $data[$group['id']][$group['index']] = ['group' => $group, 'condition' => $filter];
  }

  return groupToString(0, $hierarchy, $data);
}

function filterKeys($array, $keys) {
  return 
    is_array(reset($array)) 
      ? array_map(function ($el) use ($keys) { return array_intersect_key($el, array_flip($keys)); }, $array) : array_intersect_key($array, array_flip($keys));
}

function generateCustomViewScript($dataDir) {
  $db = \App\Db::getInstance();
  $script = '';

  $allLocalFilters = groupByColumn($db->createCommand('SELECT concat(entitytype, \'|\', viewname) grp, vtiger_customview.* FROM vtiger_customview WHERE status in (0,3)')->queryAll(), 'grp', true);
  [, $allSourceFiltersRaw] = readData("$dataDir/vtiger_customview.csv", ["cvid"]);
  foreach ($allSourceFiltersRaw as $key => $value) {
    if (!\in_array($value['status'], [0, 3])) {
      unset($allSourceFiltersRaw[$key]);
    }
  }
  $allSourceFilters = groupByColumn($allSourceFiltersRaw, 'cvid', true);

  $allLocalColumns = groupByColumn($db->createCommand('SELECT vtiger_cvcolumnlist.* FROM vtiger_cvcolumnlist order by cvid, columnindex')->queryAll(), 'cvid');
  [, $allSourceColumnsRaw] = readData("$dataDir/vtiger_cvcolumnlist.csv", ["cvid", "columnindex"]);
  $allSourceColumns = groupByColumn($allSourceColumnsRaw, 'cvid');

  $allLocalGroups = groupByColumn($db->createCommand('SELECT u_yf_cv_condition_group.* FROM u_yf_cv_condition_group')->queryAll(), 'cvid');
  [, $allSourceGroupsRaw] = readData("$dataDir/u_yf_cv_condition_group.csv", ["cvid", "id"]);
  $allSourceGroups = groupByColumn($allSourceGroupsRaw, 'cvid');

  $allLocalConditions = groupByColumn($db->createCommand('SELECT u_yf_cv_condition.* FROM u_yf_cv_condition')->queryAll(), 'group_id');
  [, $allSourceConditionsRaw] = readData("$dataDir/u_yf_cv_condition.csv", ["group_id", "id"]);
  $allSourceConditions = groupByColumn($allSourceConditionsRaw, 'group_id');

  foreach ($allSourceFilters as $cvId => $sourceFilter) {
    if (!\in_array($sourceFilter['status'], ['0', '3', 0, 3])) {
      continue;
    }
    $key = "{$sourceFilter['entitytype']}|{$sourceFilter['viewname']}";
    $localFilter = array_key_exists($key, $allLocalFilters) ? $allLocalFilters[$key] : [];

    $sourceColumns = $allSourceColumns[$cvId];
    $localColumns = $allLocalColumns[$localFilter['cvid']] ?? [];

    // merge groups and conditions for filter
    $sourceGroups = array_key_exists($cvId, $allSourceGroups) ? $allSourceGroups[$cvId] : [];
    $localGroups = array_key_exists($localFilter['cvid'], $allLocalGroups) ? $allLocalGroups[$localFilter['cvid']] : [];

    $sourceFilterString = $sourceGroups ? filterToString($sourceGroups, $allSourceConditions) : '';
    $localFilterString = $localGroups ? filterToString($localGroups, $allLocalConditions) : '';
    
    // do comparisons
    $filterCompare = ['setdefault', 'setmetrics', 'status', 'privileges', 'featured', 'sequence', 'presence', 'description', 'sort', 'color'];
    $columnCompare = ['field_name', 'module_name', 'source_field_name'];
    
    if (!empty($localFilter)) {
      $filterDiffs = array_diff_assoc(filterKeys($sourceFilter, $filterCompare), filterKeys($localFilter, $filterCompare));
      foreach ($filterDiffs as $col => $value) {
        if (($localFilter[$col] == 0 || $localFilter[$col] == '') && ($sourceFilter[$col] == 0 || $sourceFilter[$col] == '')) {
          unset($filterDiffs[$col]);
        }
      }
    } else {
      $filterDiffs = filterKeys($sourceFilter, $filterCompare);
    }
    
    
    $columnAdds = [];
    $columnMods = [];
    $columnDels = [];
    foreach ($sourceColumns as $sourceColumnIndex => $sourceColumn) {
      $localColumn = $localColumns[$sourceColumnIndex];
      if ($localColumn) {
        foreach ($columnCompare as $compareKey) {
          if ($localColumn[$compareKey] != $sourceColumn[$compareKey]) {
            $columnMods[$sourceColumnIndex][] = $compareKey;
          }
        }
      } else {
        $columnAdds[$sourceColumnIndex] = true;
      }
    }
    foreach ($localColumns as $localColumnIndex => $localColumn) {
      if (!array_key_exists($localColumnIndex, $sourceColumns)) {
        $columnDels[$localColumnIndex] = true;
      }
    }

    $conditionDiffs = $sourceFilterString != $localFilterString;

    if (!empty($filterDiffs) || !empty($columnAdds) || !empty($columnMods) || !empty($columnDels) || $conditionDiffs) {
      $script .= "-- Detected changes in $key ({$localFilter['cvid']} -> $cvId)..." . PHP_EOL;

      $sqls = [];
      
      if (!empty($localFilter)) {
        $sqls[] = "set @filter = {$localFilter['cvid']};";
      }

      if (!empty($filterDiffs)) {
        if (!empty($localFilter)) {
          $script .= "-- \tFilter has differences:" . PHP_EOL;
          $set = [];
          foreach (array_keys($filterDiffs) as $key) {
            $script .= "-- \t- $key: {$localFilter[$key]} -> {$sourceFilter[$key]}" . PHP_EOL;
            $set[] = "$key = '{$sourceFilter[$key]}'";
          }
          $set = implode(', ', $set);
          $sqls[] = "update vtiger_customview set $set where cvid = {$localFilter['cvid']};";
        } else {
          $sqls[] = "insert into vtiger_customview (viewname, setdefault, setmetrics, entitytype, status, userid, privileges, featured, sequence, presence, description, sort, color) " . 
            "values ('{$sourceFilter['viewname']}', '{$sourceFilter['setdefault']}', '{$sourceFilter['setmetrics']}', '{$sourceFilter['entitytype']}', '{$sourceFilter['status']}'" .
            ", '{$sourceFilter['userid']}', '{$sourceFilter['privileges']}', " . ($sourceFilter['featured'] ? "'{$sourceFilter['featured']}'" : 'NULL') . ", '{$sourceFilter['sequence']}', '{$sourceFilter['presence']}'" .
            ", '{$sourceFilter['description']}', '{$sourceFilter['sort']}', '{$sourceFilter['color']}');";
          $sqls[] = "set @filter = last_insert_id();";
        }
      }

      if (!empty($columnAdds)) {
        $script .= "-- \tColumns added:" . PHP_EOL;
        foreach ($columnAdds as $index => $dummy) {
          $sourceColumn = $sourceColumns[$index];
          $script .= "-- \t- $index: {$sourceColumn['field_name']}" . PHP_EOL;
          $sqls[] = "insert into vtiger_cvcolumnlist (cvid, columnindex, field_name, module_name, source_field_name) values (@filter, $index, '{$sourceColumn['field_name']}', '{$sourceColumn['module_name']}', '{$sourceColumn['source_field_name']}');";
        }
      }
      if (!empty($columnMods)) {
        $script .= "-- \tColumns have differences:" . PHP_EOL;
        foreach ($columnMods as $index => $keys) {
          $set = [];
          foreach ($keys as $key) {
            $script .= "-- \t- $index/$key: {$localColumns[$index][$key]} -> {$sourceColumns[$index][$key]}" . PHP_EOL;
            $set[] = "$key = '{$sourceColumns[$index][$key]}'";
          }
          $set = implode(', ', $set);
          $sqls[] = "update vtiger_cvcolumnlist set $set where cvid = {$localFilter['cvid']} and columnindex = $index;";
        }
      }
      if (!empty($columnDels)) {
        $script .= "-- \tColumns deleted:" . PHP_EOL;
        foreach ($columnDels as $index => $dummy) {
          $localColumn = $localColumns[$index];
          $script .= "-- \t- $index: {$localColumn['field_name']}" . PHP_EOL;
          $sqls[] = "delete from vtiger_cvcolumnlist where cvid = {$localFilter['cvid']} and columnindex = $index;";
        }
      }

      if ($conditionDiffs) {
        $script .= "-- \tConditions have differences:" . PHP_EOL;
        $script .= "-- \t- $localFilterString -> $sourceFilterString" . PHP_EOL;

        if (!empty($localFilter)) {
          $sqls[] = "delete from u_yf_cv_condition_group where cvid = {$localFilter['cvid']};";
        }
        foreach ($sourceGroups as $sourceGroup) {
          $groupId = $sourceGroup['id'];
          $parent = $sourceGroup['parent_id'] == '0' ? '0' : "@group{$sourceGroup['parent_id']}";
          $sqls[] = "insert into u_yf_cv_condition_group (`cvid`, `condition`, `parent_id`, `index`) values (@filter, '{$sourceGroup['condition']}', $parent, '{$sourceGroup['index']}');";
          $sqls[] = "set @group$groupId = last_insert_id();";
          foreach ($allSourceConditions[$groupId] ?? [] as $condition) {
            $sqls[] = "insert into u_yf_cv_condition (`group_id`, `field_name`, `module_name`, `source_field_name`, `operator`, `value`, `index`) values (@group$groupId, '{$condition['field_name']}', '{$condition['module_name']}', '{$condition['source_field_name']}', '{$condition['operator']}', '{$condition['value']}', '{$condition['index']}');";
          }
        }
      }

      $script .= "-- \tSQL:" . PHP_EOL . "\t\t" . implode(PHP_EOL . "\t\t", $sqls) . PHP_EOL;
    }

    file_put_contents('cv.sql', $script);
  }
}

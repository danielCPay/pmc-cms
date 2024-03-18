<?php

/**
 * Synchronizes Help Info.
 *
 * @package   Cron
 *
 * @copyright DOT Systems sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Nichał Kamiński <mkaminski@dotsystems.pl>
 */

/**
 * Vtiger_HelpInfo_Cron class.
 */
class Vtiger_HelpInfo_Cron extends \App\CronHandler
{
  /**
   * Save HelpInfo file and enable help info for fields.
   */
  private function updateHelpInfoFile() {
    \App\Log::warning("Vtiger::cron::Vtiger_HelpInfo_Cron::updateHelpInfoFile:saving help info file");
    // prepare dictionaries
    $moduleMapLabelToName = [];
    $moduleMapLabelToId = [];
    foreach (Settings_ModuleManager_Module_Model::getAll() as $module) {
      $moduleMapLabelToName[$module->get('label')] = $module->get('name');
      $moduleMapLabelToId[$module->get('label')] = $module->get('id');
    }

    // load translation file, convert to object
    $helpInfoFile = 'custom/languages/en-US/Other/HelpInfo.json';
    $helpInfoContents = \App\Json::decode(file_get_contents($helpInfoFile), true) ?? [];
    $helpInfo = &$helpInfoContents['php'];

    // get all entries in HelpInfo module
    $allEntries = (new \App\QueryGenerator('HelpInfo'))->setFields(['module_label', 'field_label', 'help_info'])->createQuery()->all();
    // add/update entries with values, delete ones with '---' value
    $command = \App\Db::getInstance()->createCommand();
    foreach ($allEntries as $entry) {
      $module = $moduleMapLabelToName[$entry['module_label']];
	    $moduleId = $moduleMapLabelToId[$entry['module_label']];
      $fieldName = $entry['field_label'];
      $helpInfoKey = "$module|$fieldName";
      $fieldHelp = $entry['help_info'];

      if ($fieldHelp && $fieldHelp !== '---') {
        $helpInfo[$helpInfoKey] = $fieldHelp;
        // set helpinfo to 'Edit,Detail,QuickCreateAjax'
        $command->update('vtiger_field', ['helpinfo' => 'Edit,Detail,QuickCreateAjax'], ['tabid' => $moduleId, 'fieldlabel' => $fieldName])->execute();
      } else {
        unset($helpInfo[$helpInfoKey]);
        $command->update('vtiger_field', ['helpinfo' => ''], ['tabid' => $moduleId, 'fieldlabel' => $fieldName])->execute();
      }
    }

    \App\Log::warning("Vtiger::cron::Vtiger_HelpInfo_Cron::updateHelpInfoFile:saving help info file");
    \App\Json::save($helpInfoFile, $helpInfoContents);
    \App\Log::warning("Vtiger::cron::Vtiger_HelpInfo_Cron::updateHelpInfoFile:saved help info file");
  }

  private function refreshFields() {
    \App\Log::warning("Vtiger::cron::Vtiger_HelpInfo_Cron:refreshFields");

    $fieldsToAdd = \App\Db::getInstance()->createCommand("SELECT
f.*,
case when hi.field_label IS NULL then 'NEW' ELSE 'MOVED' END status,
hi.helpinfoid
FROM 
(
  SELECT t.tablabel, 
    concat(LPAD(b.sequence, 2, 0), '. ', CASE WHEN b.blocklabel = 'LBL_BASIC_INFORMATION' THEN 'Basic Information' WHEN b.blocklabel = 'LBL_CUSTOM_INFORMATION' THEN 'Custom Information' WHEN b.blocklabel NOT LIKE 'LBL%' THEN b.blocklabel ELSE '' END, ' - ', lpad(f.sequence, 2, 0)) AS field_location, 
    f.fieldlabel, 
          '' as help_info
  FROM vtiger_field f 
    JOIN vtiger_tab t ON ( t.tabid = f.tabid )
    JOIN vtiger_blocks b ON ( b.blockid = f.block )
  WHERE t.presence = 0
    AND ( t.customized <> 0 OR t.name IN ( 'Documents', 'EmailTemplates' ) )
    AND t.name NOT IN ('Testadv')
     AND f.tablename <> 'vtiger_crmentity'
     AND f.fieldname <> 'number'
     AND f.fieldname <> 'phone_extra'
     AND f.fieldname NOT LIKE 'spacer%'
     AND f.fieldname NOT LIKE '%dummy%'
) f
LEFT JOIN u_yf_helpinfo hi ON ( hi.module_label = f.tablabel AND hi.field_label = f.fieldlabel )
WHERE
hi.field_label IS NULL OR hi.field_location != f.field_location
ORDER BY 1, 2, 3")->queryAll();

    foreach ($fieldsToAdd as $fieldToAdd) {
      \App\Log::warning("Vtiger::cron::Vtiger_HelpInfo_Cron:refreshFields:processing field: " . var_export($fieldToAdd, true));
      if ($fieldToAdd['status'] === 'NEW') {
        $record = Vtiger_Record_Model::getCleanInstance('HelpInfo');
        $record->set('module_label', $fieldToAdd['tablabel']);
        $record->set('field_label', $fieldToAdd['fieldlabel']);
        $record->set('field_location', $fieldToAdd['field_location']);
        $record->save();
      } else if ($fieldToAdd['status'] === 'MOVED') {
        $record = Vtiger_Record_Model::getInstanceById($fieldToAdd['helpinfoid'], 'HelpInfo');
        $record->set('field_location', $fieldToAdd['field_location']);
        $record->save();
      } else {
        throw new \Exception('Unknown status: ' . var_export($fieldToAdd, true));
      }
    }
  }

  private function clearRemovedFields() {
    \App\Log::warning("Vtiger::cron::Vtiger_HelpInfo_Cron:clearRemovedFields");

    $fieldsToRemove = \App\Db::getInstance()->createCommand("SELECT
  hi.*
FROM 
  u_yf_helpinfo hi
  LEFT JOIN (
    SELECT t.tablabel, 
      concat(LPAD(b.sequence, 2, 0), '. ', CASE WHEN b.blocklabel = 'LBL_BASIC_INFORMATION' THEN 'Basic Information' WHEN b.blocklabel = 'LBL_CUSTOM_INFORMATION' THEN 'Custom Information' WHEN b.blocklabel NOT LIKE 'LBL%' THEN b.blocklabel ELSE '' END, ' - ', lpad(f.sequence, 2, 0)) AS field_location, 
      f.fieldlabel, 
            '' as help_info
      -- , b.*, f.*, t.*
    FROM vtiger_field f 
      JOIN vtiger_tab t ON ( t.tabid = f.tabid )
      JOIN vtiger_blocks b ON ( b.blockid = f.block )
    WHERE t.presence = 0
      AND ( t.customized <> 0 OR t.name IN ( 'Documents', 'EmailTemplates' ) )
      AND t.name NOT IN ('Testadv')
        AND f.tablename <> 'vtiger_crmentity'
        AND f.fieldname <> 'number'
        AND f.fieldname <> 'phone_extra'
        AND f.fieldname NOT LIKE 'spacer%'
        AND f.fieldname NOT LIKE '%dummy%'
  ) f ON ( hi.module_label = f.tablabel AND hi.field_label = f.fieldlabel )
WHERE
  f.fieldlabel IS NULL 
ORDER BY 1")->queryAll();
    
    foreach($fieldsToRemove as $fieldToRemove) {
      if (!\App\Record::isExists($fieldToRemove['helpinfoid'], 'HelpInfo')) {
        continue;
      }
      \App\Log::warning("Vtiger::cron::Vtiger_HelpInfo_Cron:clearRemovedFields:processing field: " . var_export($fieldToRemove, true));
      $record = Vtiger_Record_Model::getInstanceById($fieldToRemove['helpinfoid'], 'HelpInfo');
      $record->changeState('Trash');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function process()
  {
    \App\Log::warning("Vtiger::cron::Vtiger_HelpInfo_Cron:process");

    // add missing fields and update blocks for moved fields
    $this->refreshFields();

    // delete fields that no longer exist or have been renamed
    $this->clearRemovedFields();

    // save HelpInfo file and enable help info for fields
    $this->updateHelpInfoFile();
  }
}

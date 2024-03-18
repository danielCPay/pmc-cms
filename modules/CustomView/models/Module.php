<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

/**
 * Custom View Module Model Class.
 */
class CustomView_Module_Model extends Vtiger_Module_Model
{
  public static function prepareRelatedFields(\Vtiger_Module_Model $sourceModuleModel, $maxDepth = false) {
		$maxReferenceDepth = $maxDepth ?: \App\Config::performance('MAX_REFERENCES_DEPTH', 2);
		$recordStructureModulesField = [];

		// init
		$modulesToProcess = [];
		$referenceFields = $sourceModuleModel->getFieldsByReference();
		foreach ($referenceFields as $referenceField) {
			if (!$referenceField->isActiveField()) {
				continue;
			}

			foreach ($referenceField->getReferenceList() as $relatedModuleName) {
				$modulesToProcess[] = [
					'sourceField' => $referenceField->getName(), 
					'baseLabel' => $referenceField->getFullLabelTranslation() . " - " . \App\Language::translate($relatedModuleName, $relatedModuleName) . " - ", 
					'module' => $relatedModuleName];
			}
		}

		// process
		for ($currentDepth = 1; $currentDepth <= $maxReferenceDepth; $currentDepth++) {
			$newModulesToProcess = [];
			foreach ($modulesToProcess as $moduleToProcess) {
				['sourceField' => $sourceField, 'baseLabel' => $baseLabel, 'module' => $sourceModuleNameInner] = $moduleToProcess;
				$sourceModuleModelInner = \Vtiger_Module_Model::getInstance($sourceModuleNameInner);
		
				// get all fields from module and add them to $recordStructureModulesField
				foreach (Vtiger_RecordStructure_Model::getInstanceForModule($sourceModuleModelInner)->getStructure() as $blockLabel => $blockFields) {
					$recordStructureModulesField[$baseLabel . \App\Language::translate($blockLabel, $relatedModuleName)][$sourceField] = $blockFields;
				}
		
				// foreach reference field in module get all reference fields and add them to $newModulesToProcess
				foreach ($sourceModuleModelInner->getFieldsByReference() as $referenceField) {
					if (!$referenceField->isActiveField()) {
						continue;
					}
		
					foreach ($referenceField->getReferenceList() as $relatedModuleName) {
						$newModulesToProcess[] = [
							'sourceField' => trim("$sourceField:{$referenceField->getName()}", ':'), 
							'baseLabel' => $baseLabel . $referenceField->getFullLabelTranslation() . " - " . \App\Language::translate($relatedModuleName, $relatedModuleName) . " - ", 
							'module' => $relatedModuleName];
					}
				}
			}
			$modulesToProcess = array_unique($newModulesToProcess, SORT_REGULAR);
		}

		return $recordStructureModulesField;
	}
}

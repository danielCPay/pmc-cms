<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce Sp. z o.o.
 * ********************************************************************************** */
require_once 'include/main/WebUI.php';

class Settings_LayoutEditor_ViewRelated_Action extends Settings_Vtiger_Index_Action
{
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getFields');
	}

	public function getFields(App\Request $request)
	{
		$moduleName = $request->getByType('sourceModule', 'Alnum');
		$fieldName = $request->getByType('rfield', 'Alnum');
		
		\App\Log::warning("Settings::LayoutEditor::ViewRelated::getFields:$moduleName/$fieldName");

		$module = Vtiger_Module_Model::getInstance($moduleName);
		$field = $module->getFieldByName($fieldName);

		$blocks = [];
		foreach ($field->getReferenceList() as $relatedModuleName) {
			$relatedModule = Vtiger_Module_Model::getInstance($relatedModuleName);
			foreach ($relatedModule->getFieldsByBlocks() as $blockName => $blockFields) {
				$fields = [];

				foreach ($blockFields as $blockField) {
					$fields[] = [
						'name' => $blockField->getName(),
						'label' => $blockField->getFullLabelTranslation(),
					];
				}

				$blocks[] = [ 
					'label' => \App\Language::translate($blockName, $relatedModuleName), 
					'module' => \App\Language::translate($relatedModuleName, $relatedModuleName), 
					'fields' => $fields
				];
			}
		}

		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		$response->setResult($blocks);
		$response->emit();
	}
}

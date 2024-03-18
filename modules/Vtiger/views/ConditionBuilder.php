<?php

/**
 * View to display row with fields, operators and value.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */
class Vtiger_ConditionBuilder_View extends Vtiger_IndexAjax_View
{
	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(App\Request $request)
	{
		if (!Users_Privileges_Model::getCurrentUserPrivilegesModel()->hasModulePermission($request->getByType('sourceModuleName', 2))) {
			throw new \App\Exceptions\NoPermitted('ERR_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$sourceModuleName = $request->getByType('sourceModuleName', 2);
		$sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModuleName);
		$recordStructureModulesField = CustomView_Module_Model::prepareRelatedFields($sourceModuleModel);
		$fieldInfo = false;
		if ($request->isEmpty('fieldname')) {
			$fieldModel = current($sourceModuleModel->getFields());
		} else if ($request->get('fieldname') == 'special-current-role') {
			$fieldInfo = 'special-current-role';
			$fieldName = 'special-current-role';
		} else {
			$fieldInfo = $request->getForSql('fieldname', false);
			[$fieldName, $fieldModuleName, $sourceFieldName] = array_pad(explode(':', $fieldInfo), 3, false);
			if (!empty($sourceFieldName)) {
				$fieldModel = Vtiger_Field_Model::getInstance($fieldName, Vtiger_Module_Model::getInstance($fieldModuleName));
			} else if ($fieldName != 'special-current-role') {
				$fieldModel = Vtiger_Field_Model::getInstance($fieldName, $sourceModuleModel);
			}
		}
		if ($fieldName == 'special-current-role') {
			$operators = [ 'e' => 'LBL_EQUALS', 'n' => 'LBL_NOT_EQUAL_TO' ];
			if ($request->isEmpty('operator', true)) {
				$selectedOperator = 'e';
			} else {
				$selectedOperator = $request->getByType('operator', 'Alnum');
			}
		} else {
			$operators = $request->isEmpty('parent', 1) ? $fieldModel->getQueryOperators() : $fieldModel->getRecordOperators();
			if ($request->isEmpty('operator', true)) {
					$selectedOperator = key($operators);
			} else {
					$selectedOperator = $request->getByType('operator', 'Alnum');
			}
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('OPERATORS', $operators);
		$viewer->assign('SELECTED_OPERATOR', $selectedOperator);
		$viewer->assign('SELECTED_FIELD_MODEL', $fieldModel);
		$viewer->assign('RECORD_STRUCTURE_RELATED_MODULES', $recordStructureModulesField);
		$recordStructureRaw = Vtiger_RecordStructure_Model::getInstanceForModule($sourceModuleModel)->getStructure();
		$recordStructure = [];
		foreach ($recordStructureRaw as $blockLabel => $blockFieldsRaw) {
			$blockFields = [];
			foreach ($blockFieldsRaw as $fieldName => $fieldModel) {
				if (!\in_array($fieldModel->getFieldDataType(), ['ViewRelatedField'])) {
					$blockFields[$fieldName] = $fieldModel;
				}
			}

			if (!empty($blockFields)) {
				$recordStructure[$blockLabel] = $blockFields;
			}
		}
		$viewer->assign('RECORD_STRUCTURE', $recordStructure);
		$viewer->assign('FIELD_INFO', $fieldInfo);
		$viewer->assign('SOURCE_MODULE', $sourceModuleName);
		$viewer->view('ConditionBuilderRow.tpl', $sourceModuleName);
	}
}

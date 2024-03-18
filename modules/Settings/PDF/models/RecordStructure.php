<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

/**
 * class settings workflows recordstructure model.
 */
class Settings_PDF_RecordStructure_Model extends Vtiger_RecordStructure_Model
{
	/**
	 * Record structure default mode.
	 *
	 * @var string
	 */
	const RECORD_STRUCTURE_MODE_DEFAULT = '';

	/**
	 * Record structure mode filter.
	 *
	 * @var string
	 */
	const RECORD_STRUCTURE_MODE_FILTER = 'Filter';

	/**
	 * @inheritdoc
	 */
	public function getStructure()
	{
		if (!empty($this->structuredValues)) {
			return $this->structuredValues;
		}
		$moduleModel = $this->getModule();
		$recordModel = $this->getRecord();
		$recordExists = !empty($recordModel);
		
		$blockModelList = $moduleModel->getBlocks();
		foreach ($blockModelList as $blockLabel => $blockModel) {
			$fieldModelList = $blockModel->getFields();
			if (!empty($fieldModelList)) {
				$values[$blockLabel] = [];
				foreach ($fieldModelList as $fieldName => $fieldModel) {
					if ($fieldModel->isViewable()) {
						if ('Calendar' === $moduleModel->getName() && 3 == $fieldModel->getDisplayType()) {
							continue;
						}
						if ($recordExists) {
							//Set the fieldModel with the valuetype for the client side.
							$fieldValueType = $recordModel->getFieldFilterValueType($fieldName);
							$fieldInfo = $fieldModel->getFieldInfo();
							$fieldInfo['workflow_valuetype'] = $fieldValueType;
							$fieldModel->setFieldInfo($fieldInfo);
						}
						$fieldInfo['field_params'] = $fieldModel->getFieldParams();
						// This will be used during editing task like email, sms etc
						$fieldModel->set('workflow_columnname', $fieldName);
						$this->structuredValues[$blockLabel][$fieldName] = clone $fieldModel;
					}
				}
			}
		}

		//All the reference fields should also be sent
		$fields = $moduleModel->getFieldsByType(['reference', 'owner', 'multireference']);
		foreach ($fields as $parentFieldName => $field) {
			$type = $field->getFieldDataType();
			$referenceModules = $field->getReferenceList();
			if ('owner' == $type) {
				$referenceModules = ['Users'];
			}
			foreach ($referenceModules as $refModule) {
				$moduleModel = Vtiger_Module_Model::getInstance($refModule);
				$blockModelList = $moduleModel->getBlocks();
				foreach ($blockModelList as $blockLabel => $blockModel) {
					$fieldModelList = $blockModel->getFields();
					if (!empty($fieldModelList)) {
						foreach ($fieldModelList as $fieldName => $fieldModel) {
							if ($fieldModel->isViewable()) {
								$name = "($parentFieldName : ($refModule) $fieldName)";
								$fieldModel->set('workflow_columnname', $name);
								if ($recordExists) {
									$fieldValueType = $recordModel->getFieldFilterValueType($name);
									$fieldInfo = $fieldModel->getFieldInfo();
									$fieldInfo['workflow_valuetype'] = $fieldValueType;
									$fieldModel->setFieldInfo($fieldInfo);
								}
								$this->structuredValues[$field->get('label')][$name] = clone $fieldModel;
							}
						}
					}
				}
			}
		}
		return $this->structuredValues;
	}

	/**
	 * Get instance for PDF module.
	 *
	 * @param object $pdfModel
	 *
	 * @return object
	 */
	public static function getInstanceForPDFModule($pdfModel)
	{
		$instance = new Settings_PDF_RecordStructure_Model();
		$instance->setModule($pdfModel);

		return $instance;
	}
}

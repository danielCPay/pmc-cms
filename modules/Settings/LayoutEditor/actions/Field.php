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

require_once 'modules/com_vtiger_workflow/include.php';
class Settings_LayoutEditor_Field_Action extends Settings_Vtiger_Index_Action
{
	/**
	 * @var string[] List of fields in edit view modal
	 */
	const EDIT_FIELDS_FORM = [
		'presence', 'quickcreate', 'summaryfield', 'generatedtype', 'masseditable', 'header_field', 'displaytype', 'maxlengthtext', 'maxwidthcolumn', 'tabindex', 'mandatory'
	];

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('add');
		$this->exposeMethod('save');
		$this->exposeMethod('delete');
		$this->exposeMethod('move');
		$this->exposeMethod('unHide');
		$this->exposeMethod('getPicklist');
		$this->exposeMethod('checkPicklistExist');
		Settings_Vtiger_Tracker_Model::addBasic('save');
	}

	public function add(App\Request $request)
	{
		$type = $request->getByType('fieldType', 'Alnum');
		$moduleName = $request->getByType('sourceModule', 'Alnum');
		$blockId = $request->getInteger('blockid');
		$moduleModel = Settings_LayoutEditor_Module_Model::getInstanceByName($moduleName);
		$filter = $request->get('referenceModuleFilter');
		$response = new Vtiger_Response();
		try {
			$fieldModel = $moduleModel->addField($type, $blockId, $request->getAll());

			if(isset($filter) && $filter != -1 && $filter != '') {

				$fieldParams = $fieldModel->get('fieldparams') ? \App\Json::decode($fieldModel->get('fieldparams')) : [];
				$fieldParams['filterId'] = $filter;
				$fieldModel->set('fieldparams', \App\Json::encode($fieldParams));
				$fieldModel->save();
			}

			if ($type === 'ViewRelatedField') {
				$fieldParams = $fieldModel->get('fieldparams') ? \App\Json::decode($fieldModel->get('fieldparams')) : [];

				$referredField = $request->get('referredField');
				$viewField = $request->get('viewField');

				$fieldParams['referredField'] = $referredField;
				$fieldParams['viewField'] = $viewField;
				$fieldModel->set('fieldparams', \App\Json::encode($fieldParams));
				$fieldModel->save();
			}
			
			$fieldInfo = $fieldModel->getFieldInfo();
			$responseData = array_merge([
				'id' => $fieldModel->getId(),
				'name' => $fieldModel->get('name'),
				'blockid' => $blockId,
				'customField' => $fieldModel->isCustomField(), ], $fieldInfo);
			$response->setResult($responseData);
		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}

	/**
	 * Save field.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\AppException
	 * @throws \App\Exceptions\IllegalValue
	 */
	public function save(App\Request $request)
	{
		$fieldId = $request->getInteger('fieldid');
		if (empty($fieldId)) {
			throw new \App\Exceptions\AppException('Empty field ID: ' . $fieldId);
		}
		$fieldInstance = Vtiger_Field_Model::getInstance($fieldId);
		$uitypeModel = $fieldInstance->getUITypeModel();
		foreach (self::EDIT_FIELDS_FORM as $field) {
			if ($request->has($field)) {
				switch ($field) {
					case 'mandatory':
						$fieldInstance->updateTypeofDataFromMandatory($request->getByType($field, 'Standard'));
						break;
					case 'header_field':
						if ($request->getBoolean($field)) {
							if (!\in_array($request->getByType('header_type', 'Standard'), $uitypeModel->getHeaderTypes())) {
								throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE||' . 'header_type', 406);
							}
							$data['type'] = $request->getByType('header_type', 'Standard');
							if ('highlights' === $data['type']) {
								$data['class'] = $request->getByType('header_class', 'Standard');
							} elseif ('value' === $data['type'] && $fieldInstance->isReferenceField() && ($relFields = $request->getArray('header_rel_fields', \App\Purifier::ALNUM))) {
								$relModuleModel = \Vtiger_Module_Model::getInstance(current($fieldInstance->getReferenceList()));
								foreach ($relFields as $fieldName) {
									if ($relModuleModel->getFieldByName($fieldName)->isViewableInDetailView()) {
										$data['rel_fields'][] = $fieldName;
									}
								}
							}
							$value = \App\Json::encode($data);
						} else {
							$value = '';
						}
						$fieldInstance->set($field, $value);
						break;
					case 'quickcreate':
						$quickCreateValue = $request->getInteger($field);
						if ($fieldInstance->get('quickcreate') !== $quickCreateValue && $quickCreateValue > 0) {
							$fieldInstance->set('quicksequence', $fieldInstance->__getNextQuickCreateSequence());
						}
						$fieldInstance->set($field, $quickCreateValue);
						break;
					default:
						$fieldInstance->set($field, $request->getInteger($field));
						break;
				}
			}
		}
		if ($request->has('fieldMask')) {
			$params = $fieldInstance->getFieldParams();
			$params['mask'] = $request->getByType('fieldMask', 'Text');
			if (empty($params['mask'])) {
				unset($params['mask']);
			}
			$fieldInstance->set('fieldparams', $params ? \App\Json::encode($params) : '');
		}
		$fieldInstance->set('anonymizationTarget', $request->getArray('anonymizationTarget', \App\Purifier::STANDARD));

		$filter = $request->get('referenceModuleFilter');
		$params = $fieldInstance->getFieldParams();
		if($fieldInstance->getFieldDataType() == 'reference' && isset($filter) && $filter != -1 && $filter != '') {
			$params['filterId'] = $request->get('referenceModuleFilter');
		}
		else{
			unset($params['filterId']);
		}

		$referredField = $request->get('referredField');
		$viewField = $request->get('viewField');
		if($fieldInstance->getFieldDataType() == 'ViewRelatedField' && !empty($referredField) && !empty($viewField)) {
			$params['referredField'] = $referredField;
			$params['viewField'] = $viewField;
		}
		else{
			unset($params['referredField']);
			unset($params['viewField']);
		}

		$fieldInstance->set('fieldparams', $params ? \App\Json::encode($params) : '');
		
		$response = new Vtiger_Response();
		try {
			if ($request->getBoolean('defaultvalue')) {
				$uitypeModel->setDefaultValueFromRequest($request);
			} else {
				$fieldInstance->set('defaultvalue', '');
			}
			$fieldInstance->save();
			$response->setResult([
				'success' => true,
				'presence' => $request->getBoolean('presence') ? '1' : '0',
				'mandatory' => $fieldInstance->isMandatory(),
				'label' => \App\Language::translate($fieldInstance->get('label'), $request->getByType('sourceModule', 2)), ]);
		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		} catch (Error $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}

	public function delete(App\Request $request)
	{
		$fieldInstance = Settings_LayoutEditor_Field_Model::getInstance($request->getInteger('fieldid'));
		$response = new Vtiger_Response();
		if (!$fieldInstance->isCustomField()) {
			$response->setResult(['success' => false, 'message' => \App\Language::translate('LBL_NON_CUSTOM_FIELD_CANNOT_DELETE', 'Settings::LayoutEditor')]);
		} else if (($result = Settings_FieldsDependency_Module_Model::isFieldUsed($fieldInstance->getModule()->getId(), $fieldInstance->getName())) !== false) {
			$response->setResult(['success' => false, 'message' => \App\Language::translateArgs('LBL_FIELD_USED_IN_FIELDS_DEPENDENCY', 'Settings::LayoutEditor', $result)]);
		} else if (($result = Workflow::isFieldUsed($fieldInstance->getModule()->getName(), $fieldInstance->getName())) !== false) {
			$response->setResult(['success' => false, 'message' => \App\Language::translateArgs('LBL_FIELD_USED_IN_WORKFLOWS', 'Settings::LayoutEditor', $result)]);
		} else {
			try {
				$fieldInstance->delete();
				$response->setResult(['success' => true]);
			} catch (Exception $e) {
				$response->setError($e->getCode(), $e->getMessage());
			}
		}
		$response->emit();
	}

	public function move(App\Request $request)
	{
		$updatedFieldsList = $request->getMultiDimensionArray('updatedFields',
			[
				'block' => 'Integer',
				'fieldid' => 'Integer',
				'sequence' => 'Integer'
			]);
		//This will update the fields sequence for the updated blocks
		Settings_LayoutEditor_Block_Model::updateFieldSequenceNumber($updatedFieldsList);
		$response = new Vtiger_Response();
		$response->setResult(['success' => true]);
		$response->emit();
	}

	public function unHide(App\Request $request)
	{
		$response = new Vtiger_Response();
		try {
			$fieldIds = $request->getArray('fieldIdList', 'Integer');
			if (!empty($fieldIds)) {
				Settings_LayoutEditor_Field_Model::makeFieldActive($fieldIds, $request->getInteger('blockId'));
				$responseData = [];
				foreach ($fieldIds as $fieldId) {
					$fieldModel = Settings_LayoutEditor_Field_Model::getInstance($fieldId);
					$fieldInfo = $fieldModel->getFieldInfo();
					$responseData[] = array_merge(['id' => $fieldModel->getId(), 'blockid' => $fieldModel->get('block')->id, 'customField' => $fieldModel->isCustomField()], $fieldInfo);
				}
				$response->setResult($responseData);
			}
		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}

	/**
	 * Check if picklist exist.
	 *
	 * @param App\Request $request
	 */
	public function checkPicklistExist(App\Request $request)
	{
		$response = new Vtiger_Response();
		$response->setResult(\App\Fields\Picklist::isPicklistExist($request->getByType('fieldName', 'Alnum')));
		$response->emit();
	}

	public function getPicklist(App\Request $request)
	{
		$response = new Vtiger_Response();
		$fieldName = $request->getByType('rfield', 'Alnum');
		$moduleName = $request->getByType('rmodule', 'Alnum');
		$picklistValues = [];
		if (!empty($fieldName) && !empty($moduleName) && '-' != $fieldName) {
			$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
			$fieldInstance = Vtiger_Field_Model::getInstance($fieldName, $moduleModel);
			$picklistValues = $fieldInstance->getPicklistValues();
			if (null === $picklistValues) {
				$picklistValues = [];
			}
		}
		$response->setResult($picklistValues);
		$response->emit();
	}
}

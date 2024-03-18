<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * Contributor(s): DOT Systems sp. z o.o.
 * *********************************************************************************** */

class Vtiger_ViewRelatedField_UIType extends Vtiger_Base_UIType
{
	/**
	 * @inheritdoc
	 */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if (!empty($record) || !empty($recordModel)) {
			if (empty($recordModel)) {
				$recordModel = Vtiger_Record_Model::getInstanceById($record);
			}

			/** @var Vtiger_Field_Model $fieldModel */
			$fieldModel = $this->get('field');
			['referredField' => $referredField, 'viewField' => $viewField] = $fieldModel->getFieldParams();

			$referenceId = $recordModel->get($referredField);
			if (\App\Record::isExists($referenceId)) {
				$reference = Vtiger_Record_Model::getInstanceById($referenceId);
				return $reference->getDisplayValue($viewField);
			}
		}

		return parent::getDisplayValue($value, $record, $recordModel, $rawText, $length);
	}

	public function isActiveSearchView()
	{
		return false;
	}

	public function isAjaxEditable()
	{
		return false;
	}

	/**
	 * @inheritdoc
	 */
	public function isListviewSortable()
	{
		return false;
	}
}

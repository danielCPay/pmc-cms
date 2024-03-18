<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com.
 * ********************************************************************************** */
require_once 'modules/com_vtiger_workflow/VTWorkflowUtils.php';

class VTCreateEntityTask extends VTTask
{
	public $executeImmediately = true;

	public function getFieldNames()
	{
		return ['entity_type', 'reference_field', 'field_value_mapping', 'mappingPanel', 'verifyIfExists',
			'addMostRecentDocumentFromParent'];
	}

	/**
	 * Execute task.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function doTask($recordModel, $originalRecordModel = null)
	{
		$moduleName = $recordModel->getModuleName();
		$recordId = $recordModel->getId();
		$entityType = $this->entity_type;
		if (!\App\Module::isModuleActive($entityType)) {
			return;
		}
		$relationListView = Vtiger_RelationListView_Model::getInstance($recordModel, $entityType, false);
		if (!empty($this->verifyIfExists) && $relationListView && (int) $relationListView->getRelatedEntriesCount() > 0) {
			return true;
		}
		$fieldValueMapping = [];
		if (!empty($this->field_value_mapping)) {
			$fieldValueMapping = \App\Json::decode($this->field_value_mapping);
		}
		if (!$this->mappingPanel && !empty($entityType) && !empty($fieldValueMapping) && \count($fieldValueMapping) > 0) {
			$newRecordModel = Vtiger_Record_Model::getCleanInstance($entityType);
			$ownerFields = array_keys($newRecordModel->getModule()->getFieldsByType(['owner', 'sharedOwner']));
			foreach ($fieldValueMapping as $fieldInfo) {
				$fieldName = $fieldInfo['fieldname'];
				$referenceModule = $fieldInfo['modulename'];
				$fieldValueType = $fieldInfo['valuetype'];
				$fieldValue = trim($fieldInfo['value']);

				if ('fieldname' === $fieldValueType) {
					if ($referenceModule === $entityType) {
						$fieldValue = $newRecordModel->get($fieldValue);
					} else {
						$fieldValue = $recordModel->get($fieldValue);
					}
				} elseif ('expression' === $fieldValueType) {
					if ($referenceModule === $entityType) {
						$parsedFieldValue = \App\TextParser::getInstanceByModel($newRecordModel)->setGlobalPermissions(false)->setContent($fieldValue)->parse()->getContent();
						if ($parsedFieldValue) {
							if ($parsedFieldValue === "''") {
								$fieldValue = '';
							} else {
								require_once 'modules/com_vtiger_workflow/expression_engine/include.php';
								$parser = new VTExpressionParser(new VTExpressionSpaceFilter(new VTExpressionTokenizer($parsedFieldValue)));
								$expression = $parser->expression();
								$exprEvaluater = new VTFieldExpressionEvaluater($expression);
								$fieldValue = $exprEvaluater->evaluate($newRecordModel);
							}
						} else {
							$fieldValue = $parsedFieldValue;
						}
					} else {
						$parsedFieldValue = \App\TextParser::getInstanceByModel($recordModel)->setGlobalPermissions(false)->setContent($fieldValue)->parse()->getContent();
						if ($parsedFieldValue) {
							if ($parsedFieldValue === "''") {
								$fieldValue = '';
							} else {
								require_once 'modules/com_vtiger_workflow/expression_engine/include.php';
								$parser = new VTExpressionParser(new VTExpressionSpaceFilter(new VTExpressionTokenizer($parsedFieldValue)));
								$expression = $parser->expression();
								$exprEvaluater = new VTFieldExpressionEvaluater($expression);
								$fieldValue = $exprEvaluater->evaluate($recordModel);
							}
						} else {
							$fieldValue = $parsedFieldValue;
						}
					}
				} elseif (preg_match('/([^:]+):boolean$/', $fieldValue, $match)) {
					$fieldValue = $match[1];
					if ('true' == $fieldValue) {
						$fieldValue = '1';
					} else {
						$fieldValue = '0';
					}
				} elseif (!\in_array($fieldName, $ownerFields)) {
					$fieldValue = $newRecordModel->getField($fieldName)->getUITypeModel()->getDBValue($fieldValue);
				}
				if (\in_array($fieldName, $ownerFields)) {
					if ('triggerUser' === $fieldValue) {
						$fieldValue = $recordModel->executeUser;
					} elseif( ($tmp = VTWorkflowUtils::processSpecialFromField($recordModel, $fieldValue)) !== false && ($fieldValue = $tmp) !== false) {
						// done in condition
					} elseif (!is_numeric($fieldValue)) {
						$userId = App\User::getUserIdByName($fieldValue);
						$groupId = \App\Fields\Owner::getGroupId($fieldValue);
						if (!$userId && !$groupId) {
							$fieldValue = $recordModel->get($fieldName);
						} else {
							$fieldValue = (!$userId) ? $groupId : $userId;
						}
					}
				}
				$newRecordModel->set($fieldName, $fieldValue);
			}
			if (!\in_array($entityType, ['Calendar', 'Customers', 'Notification'])) {
				$newRecordModel->set($this->reference_field, $recordId);
			}
			$newRecordModel->save();
			$relationModel = \Vtiger_Relation_Model::getInstance($recordModel->getModule(), $newRecordModel->getModule());
			if ($relationModel) {
				$relationModel->addRelation($recordModel->getId(), $newRecordModel->getId());
			}
		} elseif ($this->mappingPanel && $entityType) {
			$saveContinue = true;
			$newRecordModel = Vtiger_Record_Model::getCleanInstance($entityType);
			$parentRecordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);

			$newRecordModel->setRecordFieldValues($parentRecordModel);
			$mandatoryFields = $newRecordModel->getModule()->getMandatoryFieldModels();
			if (!empty($fieldValueMapping) && \is_array($fieldValueMapping)) {
				$newRecordModel = $this->setFieldMapping($fieldValueMapping, $newRecordModel, $parentRecordModel);
			}

			foreach ($mandatoryFields as $field) {
				if ('' === $newRecordModel->get($field->getName()) || null === $newRecordModel->get($field->getName())) {
					$saveContinue = false;
				}
			}
			if ($saveContinue) {
				$newRecordModel->save();
			}
		}

		if ($newRecordModel && $this->addMostRecentDocumentFromParent) {
			// find relation
			$relationModel = \Vtiger_Relation_Model::getInstance($newRecordModel->getModule(), Vtiger_Module_Model::getInstance('Documents'));
			if ($relationModel && $relationModel->get('name') == 'getAttachments') {
				// find most recent document related to parent record model
				$relatedDocuments = VTWorkflowUtils::getAllRelatedRecords($recordModel, 'Documents', false, false, false, ['createdtime' => SORT_DESC]);
				if (!empty($relatedDocuments)) {
					$document = $relatedDocuments[0];

					// make relation with current record model
					$relationModel->addRelation($newRecordModel->getId(), $document['id']);
				}
			}
		}
	}

	public function setFieldMapping($fieldValueMapping, $recordModel, $parentRecordModel)
	{
		$ownerFields = [];
		$entityType = $this->entity_type;
		foreach ($recordModel->getModule()->getFields() as $name => $fieldModel) {
			if ('owner' === $fieldModel->getFieldDataType()) {
				$ownerFields[] = $name;
			}
		}
		foreach ($fieldValueMapping as $fieldInfo) {
			$fieldName = $fieldInfo['fieldname'];
			$referenceModule = $fieldInfo['modulename'];
			$fieldValueType = $fieldInfo['valuetype'];
			$fieldValue = trim($fieldInfo['value']);

			if ('fieldname' === $fieldValueType) {
				if ($referenceModule === $entityType) {
					$fieldValue = $recordModel->get($fieldValue);
				} else {
					$fieldValue = $parentRecordModel->get($fieldValue);
				}
			} elseif ('expression' == $fieldValueType) {
				require_once 'modules/com_vtiger_workflow/expression_engine/include.php';

				$parser = new VTExpressionParser(new VTExpressionSpaceFilter(new VTExpressionTokenizer($fieldValue)));
				$expression = $parser->expression();
				$exprEvaluater = new VTFieldExpressionEvaluater($expression);
				if ($referenceModule === $entityType) {
					$fieldValue = $exprEvaluater->evaluate($recordModel);
				} else {
					$fieldValue = $exprEvaluater->evaluate($parentRecordModel);
				}
			} elseif (preg_match('/([^:]+):boolean$/', $fieldValue, $match)) {
				$fieldValue = $match[1];
				if ('true' == $fieldValue) {
					$fieldValue = 1;
				} else {
					$fieldValue = 0;
				}
			} elseif (!\in_array($fieldName, $ownerFields)) {
				$fieldValue = $recordModel->getField($fieldName)->getUITypeModel()->getDBValue($fieldValue);
			}
			if (\in_array($fieldName, $ownerFields) && !is_numeric($fieldValue)) {
				$userId = App\User::getUserIdByName($fieldValue);
				$groupId = \App\Fields\Owner::getGroupId($fieldValue);
				if (!$userId && !$groupId) {
					$fieldValue = $parentRecordModel->get($fieldName);
				} else {
					$fieldValue = (!$userId) ? $groupId : $userId;
				}
			}
			$recordModel->set($fieldName, $fieldValue);
		}
		return $recordModel;
	}
}

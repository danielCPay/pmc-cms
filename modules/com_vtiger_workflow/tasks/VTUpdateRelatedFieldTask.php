<?php
/**
 * Update Related Field Task Handler Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
require_once 'modules/com_vtiger_workflow/VTWorkflowUtils.php';

class VTUpdateRelatedFieldTask extends VTTask
{
	public $executeImmediately = true;

	public function getFieldNames()
	{
		return ['field_value_mapping'];
	}

	/**
	 * Execute task.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function doTask($recordModel, $originalRecordModel = null)
	{
		$fieldValueMapping = [];
		if (!empty($this->field_value_mapping)) {
			$fieldValueMapping = \App\Json::decode($this->field_value_mapping);
		}
		$entityCache = [];
		if (!empty($fieldValueMapping)) {
			foreach ($fieldValueMapping as $fieldInfo) {
				$fieldValue = trim($fieldInfo['value']);
				switch ($fieldInfo['valuetype']) {
					case 'fieldname':
						$fieldValue = $recordModel->get($fieldValue);
						break;
					case 'expression':
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
						break;
					default:
						if (preg_match('/([^:]+):boolean$/', $fieldValue, $match)) {
							$fieldValue = $match[1];
							if ('true' == $fieldValue) {
								$fieldValue = '1';
							} else {
								$fieldValue = '0';
							}
						} elseif( ($tmp = VTWorkflowUtils::processSpecialFromField($recordModel, $fieldValue)) !== false && ($fieldValue = $tmp) !== false) {
							// done in condition
						} elseif ($fieldValue == 'special-clear') {
							$fieldValue = NULL;
						}
						break;
				}
				$relatedData = explode('::', $fieldInfo['fieldname']);
				if (2 === \count($relatedData)) {
					if (!empty($fieldValue) || 0 == $fieldValue) {
						$this->updateRecords($recordModel, $relatedData, $fieldValue);
					}
				} else {
					$recordId = $recordModel->get($relatedData[0]);
					if ($recordId) {
						$relRecordModel = array_key_exists($recordId, $entityCache) ? $entityCache[$recordId] : Vtiger_Record_Model::getInstanceById($recordId, $relatedData[1]);
						$entityCache[$recordId] = $relRecordModel;
						$fieldModel = $relRecordModel->getField($relatedData[2]);
						$fieldModel->getUITypeModel()->validate($fieldValue);
						$relRecordModel->set($relatedData[2], $fieldValue);
					}
				}
			}

			foreach($entityCache as $recordId => $record) {
				$record->save();
			}
		}
	}

	/**
	 * Update related records by releted module.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 * @param string[]            $relatedData
	 * @param string              $fieldValue
	 *
	 * @return bool
	 */
	private function updateRecords($recordModel, $relatedData, $fieldValue)
	{
		$relatedModuleName = $relatedData[0];
		$relatedFieldName = $relatedData[1];
		$targetModel = Vtiger_RelationListView_Model::getInstance($recordModel, $relatedModuleName);
		if (!$targetModel || !$targetModel->getRelationModel()) {
			return false;
		}
		$dataReader = $targetModel->getRelationQuery()->select(['vtiger_crmentity.crmid'])
			->createCommand()->query();
		while ($recordId = $dataReader->readColumn(0)) {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $relatedModuleName);
			$fieldModel = $recordModel->getField($relatedFieldName);
			$fieldModel->getUITypeModel()->validate($fieldValue);
			$recordModel->set($relatedFieldName, $fieldValue);
			$recordModel->save();
		}
	}

	/**
	 * Function to get contents of this task.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return bool contents
	 */
	public function getContents($recordModel)
	{
		$this->contents = true;

		return $this->contents;
	}
}

<?php

/**
 * Call Workflow from another Entity Task Class.
 *
 * @copyright DOT Systems Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Michał Kamiński <mkaminski@dotsystems.pl>
 */
class VTEntityWorkflow extends VTTask
{
	public $executeImmediately = true;

	/**
	 * Get field names.
	 *
	 * @return string[]
	 */
	public function getFieldNames()
	{
		return ['workflowModule', 'otherWorkflowId', 'otherWorkflowField', 'otherWorkflowFieldValueVersion'];
	}

	/**
	 * Execute task.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function doTask($recordModel, $originalRecordModel = null)
	{
		$wfs = new VTWorkflowManager();

		[$parentSpecifier, $relatedModuleName] = explode('||', $this->workflowModule);

		if (\is_numeric($this->otherWorkflowId)) {
			$workflow = $wfs->retrieve($this->otherWorkflowId);
		} else {
			$workflow = $wfs->retrieveByName($this->otherWorkflowId, $relatedModuleName);
		}

		$recordModels = [];
		if ($parentSpecifier === 'CURRENT') {
			$recordModels[] = $recordModel;
		} else if ($parentSpecifier === 'PARENT') {
			if (empty($this->otherWorkflowFieldValueVersion) || $this->otherWorkflowFieldValueVersion != 'Old') {
				$newId = $recordModel->get($this->otherWorkflowField);
				if (!empty($newId) && \App\Record::isExists($newId)) {
					$recordModels[] = Vtiger_Record_Model::getInstanceById($newId);
				}
			}
			if (!empty($this->otherWorkflowFieldValueVersion) && $this->otherWorkflowFieldValueVersion != 'New') {
				$oldId = $originalRecordModel->getChanges()[$this->otherWorkflowField];
				if (!empty($oldId) && \App\Record::isExists($oldId)) {
					$found = false;
					foreach ($recordModels as $recordModel) {
						if ($recordModel->getId() == $oldId) {
							$found = true;
							break;
						}
					}
					if (!$found) {
						$recordModels[] = Vtiger_Record_Model::getInstanceById($oldId);
					}
				}
			}
		} else {
			$childIds = (new \App\QueryGenerator($relatedModuleName))
				->setField('id')
				->addCondition($this->otherWorkflowField, $recordModel->getId(), 'eid')
				->createQuery()
				->column();

			foreach ($childIds as $childId) {
				$recordModels[] = Vtiger_Record_Model::getInstanceById($childId);
			}
		}

		foreach ($recordModels as $recordModel) {
			if ($workflow->evaluate($recordModel)) {
				$workflow->performTasks($recordModel);
			}
		}
	}
}

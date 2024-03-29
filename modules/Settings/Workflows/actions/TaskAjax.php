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

class Settings_Workflows_TaskAjax_Action extends Settings_Vtiger_Basic_Action
{
	use \App\Controller\ExposeMethod;

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('delete');
		$this->exposeMethod('changeStatus');
		$this->exposeMethod('changeStatusAllTasks');
		$this->exposeMethod('save');
		$this->exposeMethod('VTEntityWorkflowGetFieldsAndWorkflows');
	}

	public function delete(App\Request $request)
	{
		$record = $request->get('task_id');
		if (!empty($record)) {
			$taskRecordModel = Settings_Workflows_TaskRecord_Model::getInstance($record);
			$taskRecordModel->delete();
			$response = new Vtiger_Response();
			$response->setResult(['ok']);
			$response->emit();
		}
	}

	public function changeStatus(App\Request $request)
	{
		$record = $request->get('task_id');
		if (!empty($record)) {
			$taskRecordModel = Settings_Workflows_TaskRecord_Model::getInstance($record);
			$taskObject = $taskRecordModel->getTaskObject();
			if ('true' == $request->get('status')) {
				$taskObject->active = true;
			} else {
				$taskObject->active = false;
			}
			$taskRecordModel->save();
			$response = new Vtiger_Response();
			$response->setResult(['ok']);
			$response->emit();
		}
	}

	public function changeStatusAllTasks(App\Request $request)
	{
		$record = $request->get('record');
		$status = $request->get('status');
		if (!empty($record)) {
			$workflowModel = Settings_Workflows_Record_Model::getInstance($record);
			$taskList = $workflowModel->getTasks();
			foreach ($taskList as $task) {
				$taskRecordModel = Settings_Workflows_TaskRecord_Model::getInstance($task->getId());
				$taskObject = $taskRecordModel->getTaskObject();
				if ('true' == $status) {
					$taskObject->active = true;
				} else {
					$taskObject->active = false;
				}
				$taskRecordModel->save();
			}
			$response = new Vtiger_Response();
			$response->setResult(['success' => true, 'count' => \count($taskList)]);
			$response->emit();
		}
	}

	public function save(App\Request $request)
	{
		$workflowId = $request->get('for_workflow');
		if (!empty($workflowId)) {
			$record = $request->get('task_id');
			if ($record) {
				$taskRecordModel = Settings_Workflows_TaskRecord_Model::getInstance($record);
			} else {
				$workflowModel = Settings_Workflows_Record_Model::getInstance($workflowId);
				$taskRecordModel = Settings_Workflows_TaskRecord_Model::getCleanInstance($workflowModel, $request->get('taskType'));
			}
			/** @var Settings_Workflows_TaskRecord_Model $taskRecordModel */

			$taskObject = $taskRecordModel->getTaskObject();
			$taskObject->summary = htmlspecialchars($request->get('summary'));
			$active = $request->get('active');
			if ('true' == $active) {
				$taskObject->active = true;
			} elseif ('false' == $active) {
				$taskObject->active = false;
			}
			$checkSelectDate = $request->get('check_select_date');

			if (!empty($checkSelectDate)) {
				$trigger = [
					'days' => ('after' == $request->get('select_date_direction') ? 1 : -1) * (int) $request->get('select_date_days'),
					'field' => $request->get('select_date_field'),
				];
				$taskObject->trigger = $trigger;
			} else {
				$taskObject->trigger = null;
			}

			$fieldNames = $taskObject->getFieldNames();

			foreach ($fieldNames as $fieldName) {
				if ('field_value_mapping' == $fieldName || 'content' == $fieldName) {
					$values = \App\Json::decode($request->getRaw($fieldName));
					if (\is_array($values)) {
						foreach ($values as $index => $value) {
							$values[$index]['value'] = htmlspecialchars($value['value']);
						}

						$taskObject->{$fieldName} = \App\Json::encode($values);
					} else {
						$taskObject->{$fieldName} = $request->getRaw($fieldName);
					}
				} else {
					$taskObject->{$fieldName} = 
						($request->get('taskType') === 'VTWatchdog' && $fieldName === 'message') 
						|| ($fieldName === 'conditionString')
							? $request->getRaw($fieldName) : $request->get($fieldName);
				}
			}

			$taskType = \get_class($taskObject);
			if ('VTCreateEntityTask' === $taskType && $taskObject->field_value_mapping) {
				$relationModuleModel = Vtiger_Module_Model::getInstance($taskObject->entity_type);
				$ownerFieldModels = $relationModuleModel->getFieldsByType('owner');

				$fieldMapping = \App\Json::decode($taskObject->field_value_mapping);
				foreach ($fieldMapping as $key => $mappingInfo) {
					if (\array_key_exists($mappingInfo['fieldname'], $ownerFieldModels)) {
						if ('assigned_user_id' == $mappingInfo['value']) {
							$fieldMapping[$key]['valuetype'] = 'fieldname';
						} elseif(strpos($mappingInfo['value'], 'fromField') === 0) {
							$fieldMapping[$key]['valuetype'] = 'rawtext';
						} elseif(strpos($mappingInfo['value'], 'fromRole') === 0) {
							$fieldMapping[$key]['valuetype'] = 'rawtext';
						} elseif ('triggerUser' !== $mappingInfo['value']) {
							try {
								$userRecordModel = Users_Record_Model::getInstanceById($mappingInfo['value'], 'Users');
								$ownerName = $userRecordModel->get('user_name');
							} catch (\App\Exceptions\NoPermittedToRecord $e) { // catch exception to allow checking of group
								if (strpos($e->getMessage(), 'ERR_RECORD_NOT_FOUND') === false) {
									throw $e; // rethrow if not expected type of exception
								}

								$groupRecordModel = Settings_Groups_Record_Model::getInstance($mappingInfo['value']);
								$ownerName = $groupRecordModel->getName();
							}

							if (!$ownerName) {
								throw new \App\Exceptions\NoPermittedToRecord('ERR_RECORD_NOT_FOUND||' . $record);
							}
							$fieldMapping[$key]['value'] = $ownerName;
						}
					}
				}
				$taskObject->field_value_mapping = \App\Json::encode($fieldMapping);
			}
			if ('SumFieldFromDependent' === $taskType && $taskObject->conditions) {
				$taskObject->conditions = \App\Condition::getConditionsFromRequest($taskObject->conditions);
			}
			$taskRecordModel->save();
			$response = new Vtiger_Response();
			$response->setResult(['for_workflow' => $workflowId]);
			$response->emit();
		}
	}

	public function VTEntityWorkflowGetFieldsAndWorkflows(App\Request $request)
	{
		$workflowId = $request->get('for_workflow');
		$relatedModule = $request->get('relatedModule');
		$fields = [];
		$workflows = [];
		if (!empty($workflowId) && !empty($relatedModule)) {
			$workflowModel = Settings_Workflows_Record_Model::getInstance($workflowId);
			$moduleModel = $workflowModel->getModule();

			$wfs = new VTWorkflowManager();
			[$parentSpecifier, $relatedModuleName] = explode('||', $relatedModule);
			$workflowModels = $wfs->getWorkflowsForModule($relatedModuleName);
			foreach($workflowModels as $workflow) {
				$workflows[] = [ 'id' => $workflow->description, 'name' => \App\Language::translate($workflow->description, $relatedModule) ];
			}

			$fields = $workflowModel->getRelatedFields($relatedModule);
		} 

		$response = new Vtiger_Response();
		$response->setResult([ 'fields' => $fields, 'workflows' => $workflows ]);
		$response->emit();
	}
}

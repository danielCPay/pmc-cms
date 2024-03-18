<?php

/**
 * Vtiger Workflow action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Vtiger_MassWorkflow_Action extends Vtiger_Mass_Action
{
	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(App\Request $request)
	{
		$moduleModel = Vtiger_Module_Model::getInstance($request->getModule());
		if (!$moduleModel->isPermitted('WorkflowTrigger')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$records = self::getRecordsListFromRequest($request);
		$user = $request->getInteger('user');
		$workflows = $request->getArray('tasks', 'Integer');
		$isSync = !$request->has('sync') || $request->getInteger('sync') === 1;

		\App\Log::warning("WorkflowMulti::execute:$moduleName/". var_export($records, true) ."/{$user}/" .var_export($workflows, true));

		try {
			\Vtiger_Loader::includeOnce('~~modules/com_vtiger_workflow/include.php');
			$wfs = new VTWorkflowManager();

			$problems = false;
			$num = 0;
			foreach ($workflows as $id => $tasks) {
				/** @var Workflow $workflow */
				$workflow = $wfs->retrieve($id);

				foreach ($records as $record) {
					$num++;
					if (!\App\Privilege::isPermitted($request->getModule(), 'DetailView', $record)) {
						VTWorkflowUtils::createBatchErrorEntry($workflow, $moduleName, "Record access not permitted", $record, "No permission for 'DetailView' for this record");
						$problems = true;

						continue;
					}
					try {
						$recordModel = \Vtiger_Record_Model::getInstanceById($record, $moduleName);
					} catch (Exception $e) {
						VTWorkflowUtils::createBatchErrorEntry($workflow, $moduleName, "Invalid record", $record, "Error occurred while fetching record - {$e->getMessage()}");
						$problems = true;

						continue;
					}

					if ($isSync) {
						if ($user) {
							$recordModel->executeUser = $user;
						}
						
						if ($workflow->evaluate($recordModel)) {
							if (!$workflow->params || !($params = \App\Json::decode($workflow->params)) || empty($params['showTasks']) || empty($params['enableTasks'])) {
								$tasks = null;
							}
							try {
								$workflow->performTasks($recordModel, $tasks);
							} catch (\App\Exceptions\BatchErrorHandledWorkflowException $e) {
								// do not write BatchError, already handled
								$problems = true;
	
								continue;
							} catch (\App\Exceptions\BatchErrorHandledNoRethrowWorkflowException $e) {
								continue;
							} catch (\App\Exceptions\NoRethrowWorkflowException $e) {
								VTWorkflowUtils::createBatchErrorEntry($workflow, $moduleName, "Error during processing", $record, "Error occurred while processing record - {$e->getMessage()}");
								continue;
							} catch (Exception $e) {
								VTWorkflowUtils::createBatchErrorEntry($workflow, $moduleName, "Error during processing", $record, "Error occurred while processing record - {$e->getMessage()}");
								$problems = true;
	
								continue;
							}
						} else {
							VTWorkflowUtils::createBatchErrorEntry($workflow, $moduleName, "Workflow conditions not met", $record);
							$problems = true;
	
							continue;
						}
					} else {
						$task = Vtiger_Record_Model::getCleanInstance('BatchTasks');
						$task->set('batch_task_type', 'Workflow');
						$task->set('batch_task_name', "Execute workflow '" . \App\Purifier::decodeHtml($workflow->description) . "' on record '" . \App\Purifier::decodeHtml($recordModel->getDisplayName()) . "'");
						$task->set('mod_name', $moduleName);
						$task->set('item', $record);
						$task->set('workflow', $id);
						$task->set('workflow_name', $workflow->description);

						if ($user) {
							$task->set('assigned_user_id', $user);
						}
						$task->save();
					}
				}
			}
		} catch (Exception $e) {
			\App\Log::warning("WorkflowMulti::ERROR " . $e->getMessage());
			\App\Log::error(var_export($e, true));
			throw $e;
		}

		\App\Log::warning("Finished after $num iterations");

		try {
			$text = $isSync ? \App\Language::translate('LBL_WORKFLOWS_TRIGGERED') : \App\Language::translate('LBL_WORKFLOWS_ENQUEUED');
			$type = 'success';
			if ($problems) {
				$type = 'info';
				$text .= PHP_EOL . \App\Language::translate('LBL_WORKFLOWS_TRIGGERED_FAILED');
			}
			$response = new Vtiger_Response();
			$response->setResult(['notify' => ['text' => $text, 'type' => $type]]);
			$response->emit();
		} catch (Exception $e) {
			\App\Log::warning("WorkflowMulti::ERROR2 " . $e->getMessage());
			\App\Log::error(var_export($e, true));
			throw $e;
		}
	}
}

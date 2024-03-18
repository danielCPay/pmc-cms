<?php
/**
 * WorkflowTrigger model class.
 *
 * @package Model
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
 /**
  * Vtiger_WorkflowTrigger_Model class.
  */
 class Vtiger_WorkflowTrigger_Model
 {
 	/**
 	 * Function executes workflow tasks.
 	 *
 	 * @param string $moduleName
 	 * @param int    $record
 	 * @param int    $userId
 	 * @param array  $actions
 	 */
 	public static function execute(string $moduleName, int $record, $userId, array $actions = [])
 	{
 		\Vtiger_Loader::includeOnce('~~modules/com_vtiger_workflow/include.php');
 		$recordModel = \Vtiger_Record_Model::getInstanceById($record, $moduleName);
 		if ($userId) {
 			$recordModel->executeUser = $userId;
 		}
 		$wfs = new VTWorkflowManager();
 		foreach ($actions as $id => $tasks) {
 			$workflow = $wfs->retrieve($id);
 			if ($workflow->evaluate($recordModel)) {
 				if (!$workflow->params || !($params = \App\Json::decode($workflow->params)) || empty($params['showTasks']) || empty($params['enableTasks'])) {
 					$tasks = null;
 				}
				try {
 					$workflow->performTasks($recordModel, $tasks);
				} catch (\App\Exceptions\BatchErrorHandledWorkflowException $e) {
					// do not write BatchError, already handled
					throw $e;
				} catch (\App\Exceptions\BatchErrorHandledNoRethrowWorkflowException $e) {
					continue;
				} catch (\App\Exceptions\NoRethrowWorkflowException $e) {
					VTWorkflowUtils::createBatchErrorEntry($workflow, $moduleName, "Error during processing", $record, "Error occurred while processing record - {$e->getMessage()}");
					continue;
				} catch (Exception $e) {
					VTWorkflowUtils::createBatchErrorEntry($workflow, $moduleName, "Error during processing", $record, "Error occurred while processing record - {$e->getMessage()}");

					throw $e;
				}
 			} else {
				VTWorkflowUtils::createBatchErrorEntry($workflow, $moduleName, "Workflow conditions not met", $record);
			 }
 		}
 	}

 	/**
 	 * Gets workflow action tree.
 	 *
 	 * @param string $moduleName
 	 * @param int    $recordId
 	 *
 	 * @return array
 	 */
 	public static function getTreeWorkflows(string $moduleName, ?int $recordId = null): array
 	{
 		\Vtiger_Loader::includeOnce('~~modules/com_vtiger_workflow/include.php');
 		$tree = [];
 		$taskManager = new VTTaskManager();
 		$workflowModuleName = 'Settings:Workflows';
 		$recordModel = $recordId ? Vtiger_Record_Model::getInstanceById($recordId) : null;
 		$workflows = (new VTWorkflowManager())->getWorkflowsForModule($moduleName, VTWorkflowManager::$TRIGGER);
 		$index = !empty($workflows) ? max(array_column($workflows, 'id')) : 0;
 		foreach ($workflows as $workflow) {
 			if ($recordModel && !$workflow->evaluate($recordModel)) {
 				continue;
 			}
 			$tree[] = [
 				'id' => $workflow->id,
 				'type' => 'category',
 				'attr' => 'record',
 				'record_id' => $workflow->id,
 				'parent' => '#',
 				'text' => '&nbsp;' . \App\Language::translate($workflow->description, $workflowModuleName),
 				'state' => ['selected' => false, 'disabled' => false, 'loaded' => true, 'opened' => false],
 				'category' => ['checked' => false]
 			];
 			$params = $workflow->params ? \App\Json::decode($workflow->params) : [];
 			if (empty($params['showTasks'])) {
 				continue;
 			}
 			foreach ($taskManager->getTasksForWorkflow($workflow->id) as $task) {
 				if (!$task->active) {
 					continue;
 				}
 				$tree[] = [
 					'id' => ++$index,
 					'type' => 'category',
 					'attr' => 'task',
 					'record_id' => $task->id,
 					'parent' => $workflow->id,
 					'text' => '&nbsp;' . \App\Language::translate($task->summary, $workflowModuleName),
 					'state' => ['selected' => false, 'disabled' => empty($params['enableTasks'])],
 					'category' => ['checked' => false]
 				];
 			}
 		}
 		return $tree;
 	}
 }

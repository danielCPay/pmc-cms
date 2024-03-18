<?php

use App\Exceptions\BatchErrorHandledWorkflowException;

Vtiger_Loader::includeOnce('~modules/com_vtiger_workflow/VTWorkflowManager.php');

/**
 * Workflow handler.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_Workflow_Handler
{
	private $workflows;

	/**
	 * EntityChangeState handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityChangeState(App\EventHandler $eventHandler)
	{
		$this->performTasks($eventHandler, [
			VTWorkflowManager::$ON_EVERY_SAVE
		]);
	}

	/**
	 * EntityAfterDelete handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterDelete(App\EventHandler $eventHandler)
	{
		$this->performTasks($eventHandler, [
			VTWorkflowManager::$ON_DELETE
		]);
	}

	/**
	 * EntityAfterSave function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterSave(App\EventHandler $eventHandler)
	{
		$this->performTasks($eventHandler, [
			VTWorkflowManager::$ON_FIRST_SAVE,
			VTWorkflowManager::$ONCE,
			VTWorkflowManager::$ON_EVERY_SAVE,
			VTWorkflowManager::$ON_MODIFY
		]);
	}

	/**
	 * EntityAfterLink handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterLink(App\EventHandler $eventHandler)
	{
    $this->performTasks($eventHandler, [
			VTWorkflowManager::$ON_RELATED,
		]);
	}

	/**
	 * UserAfterSave function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function userAfterSave(App\EventHandler $eventHandler)
	{
		$this->entityAfterSave($eventHandler);
	}

	/**
	 * Checks if workflow has tasks of specified types.
	 * 
	 * @param Workflow $workflow
	 * @param string[] $taskTypes
	 * 
	 * @return bool
	 */
	public static function checkWorkflowHasTasks(Workflow $workflow, $taskTypes = []) {
		$key = $workflow->id . '-' . implode('_', $taskTypes);
		if (\App\Cache::staticHas('checkWorkflowHasTasks', $key)) {
			return \App\Cache::staticGet('checkWorkflowHasTasks', $key);
		}

		require_once 'modules/com_vtiger_workflow/VTTaskManager.php';
		require_once 'modules/com_vtiger_workflow/VTTaskQueue.php';

		$tm = new VTTaskManager();
		$result = !empty(array_filter($tm->getTasksForWorkflow($workflow->id), function ($task) use ($taskTypes) { return $task->active && \in_array(get_class($task), $taskTypes); }));

		\App\Cache::staticSave('checkWorkflowHasTasks', $key, $result);

		return $result;
	}

	/**
	 * Perform workflow tasks.
	 *
	 * @param \App\EventHandler $eventHandler
	 * @param int[]             $condition
	 *
	 * @throws \Exception
	 */
	private function performTasks(App\EventHandler $eventHandler, $condition = [])
	{
		$recordModel = $eventHandler->getRecordModel();
		$moduleName = $eventHandler->getModuleName();
		if (!isset($this->workflows[$moduleName])) {
			$wfs = new VTWorkflowManager();
			$this->workflows[$moduleName] = $wfs->getWorkflowsForModule($moduleName);
		}
		
		foreach ($this->workflows[$moduleName] as &$workflow) {
			if ($condition && !\in_array($workflow->executionCondition, $condition)) {
				continue;
			}
			$recordModel->shouldTriggerChanged = false;
			switch ($workflow->executionCondition) {
				case VTWorkflowManager::$ON_FIRST_SAVE:
					if ($recordModel->isNew()) {
						$doEvaluate = true;
					} else {
						$doEvaluate = false;
					}
					break;
				case VTWorkflowManager::$ONCE:
					if ($workflow->isCompletedForRecord($recordModel->getId())) {
						$doEvaluate = false;
					} else {
						$doEvaluate = true;
					}
					break;
				case VTWorkflowManager::$ON_EVERY_SAVE:
					$doEvaluate = true;
					if ($recordModel->isMovedToTrash() && self::checkWorkflowHasTasks($workflow, ['VTEntityWorkflow', 'SumFieldFromDependent'])) {
						$recordModel->shouldTriggerChanged = true;
						\App\Log::warning("Set trigger for $workflow->executionCondition");
					}
					break;
				case VTWorkflowManager::$ON_MODIFY:
					$doEvaluate = !$recordModel->isNew() && !empty($recordModel->getPreviousValue());
					break;
				case VTWorkflowManager::$MANUAL:
					$doEvaluate = false;
					break;
				case VTWorkflowManager::$ON_SCHEDULE:
					$doEvaluate = false;
					break;
				case VTWorkflowManager::$ON_DELETE:
					$doEvaluate = true;
					break;
				case VTWorkflowManager::$TRIGGER:
					$doEvaluate = false;
					break;
				case VTWorkflowManager::$BLOCK_EDIT:
					$doEvaluate = false;
					break;
				case VTWorkflowManager::$ON_RELATED:
					$recordModel = Vtiger_Record_Model::getInstanceById($eventHandler->getParams()['sourceRecordId']);
					$doEvaluate = true;
					break;
				default:
					throw new \App\Exceptions\AppException('Should never come here! Execution Condition:' . $workflow->executionCondition);
			}
			if ($doEvaluate && $workflow->evaluate($recordModel, $recordModel->getId())) {
				if (VTWorkflowManager::$ONCE == $workflow->executionCondition) {
					$workflow->markAsCompletedForRecord($recordModel->getId());
				}
				try {
					$workflow->performTasks($recordModel);
				} catch (\App\Exceptions\BatchErrorHandledWorkflowException $e) {
					// do not write BatchError, already handled
					throw $e;
				} catch (\App\Exceptions\BatchErrorHandledNoRethrowWorkflowException $e) {
					continue;
				} catch (\App\Exceptions\NoRethrowWorkflowException $e) {
					VTWorkflowUtils::createBatchErrorEntry($workflow, $moduleName, "Error during processing", $recordModel->getId(), "Error occurred while processing record - {$e->getMessage()}");
					continue;
				} catch (\Exception $e) {
					VTWorkflowUtils::createBatchErrorEntry($workflow, $moduleName, "Error during processing", $recordModel->getId(), "Error occurred while processing record - {$e->getMessage()}");

					throw $e;
				}
			}
		}
	}
}

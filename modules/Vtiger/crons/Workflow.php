<?php
/**
 * Cron for workflow.
 *
 * @package   Cron
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */

/**
 * Vtiger_Workflow_Cron class.
 */
class Vtiger_Workflow_Cron extends \App\CronHandler
{
	/**
	 * {@inheritdoc}
	 */
	public function process()
	{
		require_once 'include/Webservices/Utils.php';
		require_once 'include/Webservices/WebServiceError.php';
		require_once 'include/utils/VtlibUtils.php';
		require_once 'modules/com_vtiger_workflow/VTWorkflowUtils.php';
		require_once 'modules/com_vtiger_workflow/include.php';
		require_once 'modules/com_vtiger_workflow/WorkFlowScheduler.php';

		$workflowScheduler = new WorkFlowScheduler();
		$workflowScheduler->queueScheduledWorkflowTasks();
		$readyTasks = (new VTTaskQueue())->getReadyTasks();
		$tm = new VTTaskManager();
		foreach ($readyTasks as $taskDetails) {
			[$taskId, $entityId, $taskContents] = $taskDetails;
			\App\Log::warning("Executing queued task $taskId for $entityId");
			$task = $tm->retrieveTask($taskId);
			\App\Log::warning(print_r($task, true));
			//If task is not there then continue
			if (empty($task) || !\App\Record::isExists($entityId)) {
				continue;
			}
			print_r($task);
			$task->setContents($taskContents);
			$recordModel = Vtiger_Record_Model::getInstanceById($entityId);
			$recordModel->ext['isFromWorkflow'] = true;
			$task->doTask($recordModel, $recordModel);
			unset($recordModel->ext['isFromWorkflow']);
			if ($this->checkTimeout()) {
				return;
			}
		}
	}
}

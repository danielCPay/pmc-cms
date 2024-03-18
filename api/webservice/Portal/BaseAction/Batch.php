<?php

/**
 * Get elements of menu.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */

namespace Api\Portal\BaseAction;

use OpenApi\Annotations as OA;

/**
 * Action to get menu.
 */
class Batch extends \Api\Core\BaseAction
{
  /**
   * {@inheritdoc}
   */
  public $allowedMethod = ['POST'];

  private $allowedTasks = [
    ['verb' => 'POST', 'action' => 'Record', 'requiredFields' => ['moduleName', 'data']],
    ['verb' => 'PUT', 'action' => 'Record', 'requiredFields' => ['moduleName', 'recordId', 'data']],
    // ['verb' => 'DELETE', 'action' => 'Record', 'requiredFields' => ['moduleName', 'recordId']],
    ['verb' => 'PUT', 'action' => 'Workflow', 'requiredFields' => ['moduleName', 'recordId', 'data']],
  ];

  /**
   * {@inheritdoc}
   */
  public function checkPermission()
  {
    $result = parent::checkPermission();

    $tasks = $this->controller->request->getArray('tasks');
    foreach ($tasks as $task) {
      $verb = $task['verb'];
      $action = $task['action'];
      $taskSpecification =
        array_filter(
          $this->allowedTasks,
          function ($value) use ($verb, $action) {
            return $value['verb'] === $verb && $value['action'] == $action;
          }
        );

      // check task is in $allowedTasks
      if (empty($taskSpecification)) {
        throw new \Api\Core\Exception('Unsupported task ' . $verb . ' ' . $action, 405);
      } else {
        $taskSpecification = array_shift($taskSpecification);
      }

      // check each task has required fields
      if (array_key_exists('requiredFields', $taskSpecification)) {
        $missingFields = array_diff($taskSpecification['requiredFields'], array_keys($task));
        if (!empty($missingFields)) {
          throw new \Api\Core\Exception('Missing fields for task ' . $verb . ' ' . $action . ': ' . join(', ', $missingFields), 400);
        }
      }

      // check each action is valid
      switch ("$verb $action") {
        case "POST Record":
          $recordModel = \Vtiger_Record_Model::getCleanInstance($task['moduleName']);
          if (!$recordModel->isCreateable()) {
            throw new \Api\Core\Exception('No permissions to create record in ' . $task['moduleName'], 403);
          }
          break;
        case "PUT Record":
          if (!\App\Record::isExists($task['recordId'], $task['moduleName'])) {
            throw new \Api\Core\Exception('Record ' . $task['moduleName'] . '/' . $task['recordId'] . ' doesn\'t exist', 404);
          }
          $recordModel = \Vtiger_Record_Model::getInstanceById($task['recordId'], $task['moduleName']);
          if (!$recordModel->isEditable()) {
            throw new \Api\Core\Exception('No permissions to edit record ' . $task['moduleName'] . '/' . $task['recordId'], 403);
          }
          break;
        case "DELETE Record":
          if (!\App\Record::isExists($task['recordId'], $task['moduleName'])) {
            throw new \Api\Core\Exception('Record ' . $task['moduleName'] . '/' . $task['recordId'] . ' doesn\'t exist', 404);
          }
          $recordModel = \Vtiger_Record_Model::getInstanceById($task['recordId'], $task['moduleName']);
          if (!$recordModel->privilegeToMoveToTrash()) {
            throw new \Api\Core\Exception('No permissions to remove record ' . $task['moduleName'] . '/' . $task['recordId'], 403);
          }
          break;
        case "PUT Workflow":
          if (!\Api\Portal\Privilege::isPermitted($task['moduleName'], 'WorkflowTrigger')) {
            throw new \Api\Core\Exception('No permissions for action ' . $task['moduleName'] . ':WorkflowTrigger', 405);
          }
          if (!\App\Record::isExists($task['recordId'], $task['moduleName'])) {
            throw new \Api\Core\Exception('Record ' . $task['moduleName'] . '/' . $task['recordId'] . ' doesn\'t exist', 404);
          }
          $recordModel = \Vtiger_Record_Model::getInstanceById($task['recordId'], $task['moduleName']);
          if (!$recordModel->isViewable()) {
            throw new \Api\Core\Exception('No permissions to view record ' . $task['moduleName'] . '/' . $task['recordId'], 403);
          }
          break;
      }
    }

    return $result;
  }

  /**
   * Post method - executes provided methods in batch.
   *
   * @return array
   */
  public function post()
  {
    $return = [];
    $tasks = $this->controller->request->getArray('tasks');

    \App\Log::warning('API::Batch:' . var_export($tasks, true));
    
    // perform actions
    foreach ($tasks as $task) {
      \App\Log::warning('API::Batch:Task:' . var_export($task, true));

      $verb = $task['verb'];
      $action = $task['action'];

      switch ("$verb $action") {
        case "POST Record":
          $recordModel = \Vtiger_Record_Model::getCleanInstance($task['moduleName']);

          ['record' => $recordModel, 'skippedData' => $skippedData] = $this->saveRecordModelFromTask($task, $recordModel);

          $result = ['id' => $recordModel->getId()];
          if ($skippedData) {
            $result['skippedData'] = $skippedData;
          }
          $return[] = $result;
          break;
        case "PUT Record":
          $recordModel = \Vtiger_Record_Model::getInstanceById($task['recordId'], $task['moduleName']);

          ['record' => $recordModel, 'skippedData' => $skippedData] = $this->saveRecordModelFromTask($task, $recordModel);

          $result = ['id' => $recordModel->getId()];
          if ($skippedData) {
            $result['skippedData'] = $skippedData;
          }
          $return[] = $result;
          break;
        case "PUT Workflow":
          $result = $this->processWorkflow($task);

          $return[] = $result;
          break;
      }
    }

    return $return;
  }

  private function saveRecordModelFromTask($task, \Vtiger_Record_Model $recordModel)
  {
    $fieldModelList = $recordModel->getModule()->getFields();
    $request = new \App\Request($task['data']);
    $requestKeys = $task['data'];
    foreach ($fieldModelList as $fieldName => $fieldModel) {
      if (!$fieldModel->isWritable()) {
        continue;
      }
      if ($request->has($fieldName)) {
        $fieldModel->getUITypeModel()->setValueFromRequest($request, $recordModel);
        unset($requestKeys[$fieldName]);
      }
    }
    if ($request->has('inventory') && $recordModel->getModule()->isInventory()) {
      $recordModel->initInventoryDataFromRequest($request);
      unset($requestKeys['inventory']);
    }
    $fieldInfo = \Api\Core\Module::getApiFieldPermission($request->getModule(), $this->controller->app['id']);
    if ($fieldInfo) {
      $recordModel->setDataForSave([$fieldInfo['tablename'] => [$fieldInfo['columnname'] => 1]]);
    }
    $skippedData = array_keys($requestKeys);

    $eventHandler = $recordModel->getEventHandler();
    foreach ($eventHandler->getHandlers(\App\EventHandler::EDIT_VIEW_PRE_SAVE) as $handler) {
      if (!(($response = $eventHandler->triggerHandler($handler))['result'] ?? null)) {
        throw new \App\Exceptions\NoPermittedToRecord($response['message'], 406);
      }
    }
    $recordModel->save();

    return ['record' => $recordModel, 'skippedData' => $skippedData];
  }

  private function processWorkflow($task) {
    $workflows = $task['data']['workflows'];
		$moduleName = $task['moduleName'];

		require_once 'modules/com_vtiger_workflow/include.php';
		$wfs = new \VTWorkflowManager();

    $recordModel = \Vtiger_Record_Model::getInstanceById($task['recordId']);
		foreach ($workflows as $workflowName) {
			$workflow = $wfs->retrieveByName($workflowName, $moduleName);
			if (!$workflow || !$workflow->evaluate($recordModel)) {
				continue;
			}
			$workflow->performTasks($recordModel);
		}

		$return = ['id' => $recordModel->getId()];
		return $return;
  }
}

<?php

 /* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): DOT Systems
 * *********************************************************************************** */



/**
 * Class BatchTasks_Module_Model.
 */
class BatchTasks_Module_Model extends Vtiger_Module_Model
{
  public static function processTask(Vtiger_Record_Model $task, bool $overrideUser = false) {
    \App\Log::warning("BatchTasks::processTask:task id = " . $task->getId() . ", overrideUser = " . ($overrideUser ? "TRUE" : "FALSE") . ", currentUser = " . \App\User::getCurrentUserId());

    try {
      if ($overrideUser) {
        $currentUserId = \App\User::getCurrentUserId();
        $currentBaseUserId = \App\Session::has('baseUserId') && \App\Session::get('baseUserId') ? \App\Session::get('baseUserId') : null;
        $targetUserId = $task->get('assigned_user_id');

        \App\Log::warning("BatchTasks::processTask:user is $currentUserId, should be $targetUserId");
        if ($targetUserId != $currentUserId) {
          \App\Log::warning("BatchTasks::processTask:setting user to $targetUserId");
          \App\User::setCurrentUserId($targetUserId);
          if (\App\Session::has('baseUserId') && \App\Session::get('baseUserId')) {
            \App\Session::delete('baseUserId');
          }
        }
      }

      // process task according to type
      $taskType = $task->get('batch_task_type');
      switch ($taskType) {
        case 'Document Package':
          $result = self::processDocumentPackage($task);
          break;
        case 'Document Template':
          $result = self::processDocumentTemplate($task);
          break;
        case 'Email Template':
          $result = self::processEmailTemplate($task);
          break;
        case 'Workflow':
          $result = self::processWorkflow($task);
          break;
      }

      $task->set('batch_task_status', $result ? 'Done' : 'Error');
      $task->save();
    } catch (Exception $e) {
      self::handleError($task, $e);
    } catch (Error $e) {
      self::handleError($task, $e);
    } finally {
      if ($overrideUser) {
        if ($targetUserId != $currentUserId) {
          \App\Log::warning("BatchTasks::processTask:resetting user to $currentUserId");
          \App\User::setCurrentUserId($currentUserId);
          if ($currentBaseUserId) {
            \App\Log::warning("BatchTasks::processTask:resetting base user to $currentBaseUserId");
            \App\Session::set('baseUserId', $currentBaseUserId);
          }
        }
      }
    }
  }

	/**
   * Gets oldest pending task.
   * 
   * @return Vtiger_Record_Model
   */
  public static function getOldestPendingTask() {
    \App\Log::warning("BatchTasks::getOldestPendingTask");

    // get task using querygenerator
    $taskId = 
      (new \App\QueryGenerator('BatchTasks'))
        ->setField('id')
        ->addCondition('batch_task_status', 'Pending', 'e')
        ->setOrder('createdtime')
        ->createQuery()
        ->scalar();

    if ($taskId) {
      $task = Vtiger_Record_Model::getInstanceById($taskId);
    }

    \App\Log::warning("BatchTasks::getOldestPendingTask:$taskId");
    return $task;
  }

  public static function processDocumentPackage(Vtiger_Record_Model $batchTask) {
    \App\Log::warning("BatchTasks::processDocumentPackage:{$batchTask->getId()}/{$batchTask->get('mod_name')}.{$batchTask->get('item')}");

    /** @var DocumentPackages_Record_Model $package */
    $package = Vtiger_Record_Model::getInstanceById($batchTask->get('document_package'));
    $recordModel = Vtiger_Record_Model::getInstanceById($batchTask->get('item'));

    \App\Log::warning("BatchTasks::processDocumentPackage:generating package {$package->getId()} for {$recordModel->getModuleName()}.{$recordModel->getId()}");

    $documentId = $package->generate($recordModel);

    \App\Log::warning("BatchTasks::processDocumentPackage:generated document $documentId");

    $package->send($recordModel, $documentId, $batchTask->get('redirect_email_to_test_mailbox') == 1);

    \App\Log::warning("BatchTasks::processDocumentPackage:processed email, redirect_email_to_test_mailbox = {$batchTask->get('redirect_email_to_test_mailbox')}");

    // handle dropbox
    $package->dropbox($recordModel, $documentId, $batchTask->get('send_to_dropbox'));

    \App\Log::warning("BatchTasks::processDocumentPackage:processed dropbox, send_to_dropbox = {$batchTask->get('send_to_dropbox')}");

    return true;
  }

  public static function processDocumentTemplate(Vtiger_Record_Model $batchTask) {
    \App\Log::warning("BatchTasks::processDocumentTemplate:{$batchTask->getId()}/{$batchTask->get('mod_name')}.{$batchTask->get('item')}");

    /** @var DocumentTemplates_Record_Model $template */
    $template = Vtiger_Record_Model::getInstanceById($batchTask->get('document_template'));
    $recordModel = Vtiger_Record_Model::getInstanceById($batchTask->get('item'));

    \App\Log::warning("BatchTasks::processDocumentTemplate:generating template {$template->getId()} for {$recordModel->getModuleName()}.{$recordModel->getId()}");

    $documentId = $template->generateDocument($recordModel, $batchTask->get('assigned_user_id'));

    // handle dropbox
    $template->dropbox($recordModel, $documentId, $batchTask->get('send_to_dropbox'));

    \App\Log::warning("BatchTasks::processDocumentTemplate:processed dropbox, send_to_dropbox = {$batchTask->get('send_to_dropbox')}");

    return true;
  }

  public static function processEmailTemplate(Vtiger_Record_Model $batchTask) {
    \App\Log::warning("BatchTasks::processEmailTemplate:{$batchTask->getId()}/{$batchTask->get('mod_name')}.{$batchTask->get('item')}/" . $batchTask->get('redirect_email_to_test_mailbox'));

    /** @var EmailTemplates_Record_Model $template */
    $template = Vtiger_Record_Model::getInstanceById($batchTask->get('email_template'));
    $recordModel = Vtiger_Record_Model::getInstanceById($batchTask->get('item'));

    \App\Log::warning("BatchTasks::processEmailTemplate:sending template {$template->getId()} for {$recordModel->getModuleName()}.{$recordModel->getId()}");

    $template->send($recordModel, $batchTask->get('redirect_email_to_test_mailbox') == 1);

    return true;
  }

  public static function processWorkflow(Vtiger_Record_Model $batchTask) {
    \App\Log::warning("BatchTasks::processWorkflow:{$batchTask->getId()}/{$batchTask->get('mod_name')}.{$batchTask->get('item')}/" . $batchTask->get('workflow') . "-" . $batchTask->get('workflow_name'));

    $result = false;

    $moduleName = $batchTask->get('mod_name');
    $record = $batchTask->get('item');

    $recordModel = Vtiger_Record_Model::getInstanceById($record);
    $recordModel->executeUser = $batchTask->get('assigned_user_id');

    \Vtiger_Loader::includeOnce('~~modules/com_vtiger_workflow/include.php');
    $wfs = new VTWorkflowManager();

    /** @var Workflow $workflow */
    $workflow = $wfs->retrieve($batchTask->get('workflow'));

    if ($workflow->evaluate($recordModel)) {
      try {
        $workflow->performTasks($recordModel);

        $result = true;
      } catch (\App\Exceptions\BatchErrorHandledWorkflowException $e) {
        $error = $e->batchError;
      } catch (\App\Exceptions\BatchErrorHandledNoRethrowWorkflowException $e) {
        $error = $e->batchError;
      } catch (\App\Exceptions\NoRethrowWorkflowException $e) {
        $error = VTWorkflowUtils::createBatchErrorEntry($workflow, $moduleName, "Error during processing", $record, "Error occurred while processing record - {$e->getMessage()}");
      } catch (Exception $e) {
        $error = VTWorkflowUtils::createBatchErrorEntry($workflow, $moduleName, "Error during processing", $record, "Error occurred while processing record - {$e->getMessage()}");
      }
    } else {
      $error = VTWorkflowUtils::createBatchErrorEntry($workflow, $moduleName, "Workflow conditions not met", $record);
    }

    if ($error) {
      $batchTask->set('batch_error', $error->getId());
    }

    return $result;
  }

  public static function handleError(Vtiger_Record_Model $batchTask, $error) {
    \App\Log::warning("BatchTasks::handleError:" . var_export($error, true));

    try {
      $entry = Vtiger_Record_Model::getCleanInstance('BatchErrors');

      $entry->set('task_type', $batchTask->get('batch_task_type'));
      $entry->set('task_name', $batchTask->get('batch_task_name'));
      $entry->set('mod_name', $batchTask->get('mod_name'));
      $entry->set('item', $batchTask->get('item'));

      $entry->set('document_package', $batchTask->get('document_package'));
      $entry->set('document_template', $batchTask->get('document_template'));
      $entry->set('email_template', $batchTask->get('email_template'));

      $entry->set('error_message', \App\TextParser::textTruncate($error->getMessage(), 250, true));
      $entry->set('error_description', $error->getMessage());

      $entry->save();

      $batchTask->set('batch_error', $entry->getId());
    } catch (\Exception $e) {
      \App\Log::error(var_export($e, true));
    }

    $batchTask->set('batch_task_status', 'Error');
    $batchTask->save();
  }
}

<?php

/**
 * Tries to resend failed documents to Dropbox
 *
 * @package   Cron
 *
 * @copyright DOT Systems sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Nichał Kamiński <mkaminski@dotsystems.pl>
 */

 require_once 'modules/com_vtiger_workflow/include.php';

/**
 * DocumentPackages_FailedDropbox_Cron class.
 */
class DocumentPackages_FailedDropbox_Cron extends \App\CronHandler
{
  /**
   * {@inheritdoc}
   */
  public function process()
  {
    \App\Log::warning("DocumentPackages::cron::FailedDropbox");

    $today = date('Y-m-d');

    $batchErrors =
      (new \App\QueryGenerator('BatchErrors'))
        ->setFields(['id', 'createdtime', 'item'])
        ->addCondition('createdtime', $today, 'e')
        ->addCondition('error_message', 'Dropbox error: too_many', 's')
        ->createQuery()->all();

    foreach ($batchErrors as $batchErrorRow) {
      $batchError = Vtiger_Record_Model::getInstanceById($batchErrorRow['id'], 'BatchErrors');

      \App\Log::warning("DocumentPackages::cron::FailedDropbox:Processing " . $batchError->getId() . " " . $batchError->get('error_message') . "/" . $batchError->get('error_description'));

      // check if exists same type of task for same item, but without error for same or later time - if yes, skip; match error by name + item + document package + [time + n minutes], because there is no relation
      // if not, find document by result type and maybe document name and try to upload to dropbox

      $checkDate = date('Y-m-d H:i:s', strtotime('+1 minute', strtotime($batchError->get('createdtime'))));
			$checkDateEnd = date('Y-m-d 23:59:59', strtotime($batchError->get('createdtime')));

      $batchTask = (new \App\QueryGenerator('BatchTasks'))
        ->setFields(['id', 'createdtime'])
        ->addCondition('createdtime', "$checkDate,$checkDateEnd", 'bw')
        ->addCondition('item', $batchError->get('item'), 'eid')
        ->addCondition('document_package', $batchError->get('document_package'), 'eid')
        ->addCondition('batch_task_status', 'Done', 'e')
        ->createQuery()
        ->one();

      if (!empty($batchTask) && \App\Record::isExists($batchTask['id'], 'BatchTasks')) {
        // check if no error matching this task; if not exists, skip
        $batchErrorExists = (new \App\QueryGenerator('BatchErrors'))
          ->addCondition('item', $batchError->get('item'), 'eid')
          ->addCondition('document_package', $batchError->get('document_package'), 'eid')
          ->addCondition('createdtime', "{$batchTask['createdtime']}," . (date('Y-m-d', strtotime('+5 minutes', strtotime($batchTask['createdtime'])))), 'bw')
          ->addCondition('error_message', 'Dropbox error: too_many', 's')
          ->createQuery()
          ->exists();
        if (!$batchErrorExists) {
          \App\Log::warning("DocumentPackages::cron::FailedDropbox:Skip because successful task found - ${$batchTask['id']}");
          continue;
        }
      }

      // find document by type and relation
      /** @var DocumentPackages_Record_Model $documentPackage */
      $documentPackage = Vtiger_Record_Model::getInstanceById($batchError->get('document_package'), 'DocumentPackages');

      $item = Vtiger_Record_Model::getInstanceById($batchError->get('item'));
      $documents = VTWorkflowUtils::getAllRelatedRecords($item, 'Documents', ['filename' => $documentPackage->getFileName($item), 'document_type' => $documentPackage->get('result_document_type')], false, false, ['createdtime' => SORT_DESC]);
      if (!empty($documents) && \App\Record::isExists($documents[0]['id'], 'Documents')) {
        $document = Vtiger_Record_Model::getInstanceById($documents[0]['id'], 'Documents');

        // resend
        \App\Log::warning("DocumentPackages::cron::FailedDropbox:Resend " . $document->getId());
        try {
          $documentPackage->dropbox($item, $document->getId());
        } catch (\Exception $e) {
          \App\Log::error("DocumentPackages::cron::FailedDropbox:Error during sending - " . $e->getMessage());
          \App\Log::error(var_export($e, true));
        }
        finally {
          $batchError->changeState('Trash');
        }
      } else {
        \App\Log::warning("DocumentPackages::cron::FailedDropbox:Document not found");
      }
    }
  }
}

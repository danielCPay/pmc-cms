<?php

/**
 * Tries to resend failed documents for signing
 *
 * @package   Cron
 *
 * @copyright DOT Systems sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Nichał Kamiński <mkaminski@dotsystems.pl>
 */

 require_once 'modules/com_vtiger_workflow/include.php';

/**
 * PortfolioPurchases_FailedSignings_Cron class.
 */
class PortfolioPurchases_FailedSignings_Cron extends \App\CronHandler
{
  /**
   * {@inheritdoc}
   */
  public function process()
  {
    \App\Log::warning("PortfolioPurchases::cron::FailedSignings");

    $wfs = new VTWorkflowManager();
    $workflow = $wfs->retrieve(462);

    $documentTypeId = (new \App\QueryGenerator('DocumentTypes'))->setFields(['id'])->addCondition('document_type', 'Portfolio Purchase Documents', 'e')->setOrder('createdtime', 'DESC')->createQuery()->scalar();

    $batchErrors =
      (new \App\QueryGenerator('BatchErrors'))
        ->setFields(['id', 'createdtime', 'item'])
        ->addCondition('createdtime', date('Y-m-d'), 'e')
        ->addCondition('error_description', 'failed, but for an unknown reason', 'c')
        ->createQuery()->all();

    foreach ($batchErrors as $batchErrorRow) {
      $batchError = Vtiger_Record_Model::getInstanceById($batchErrorRow['id'], 'BatchErrors');

      \App\Log::warning("PortfolioPurchases::cron::FailedSignings:Processing " . $batchError->getId() . " " . $batchError->get('error_message') . "/" . $batchError->get('error_description'));

      $ppId = $batchError->get('item');
      if (!\App\Record::isExists($ppId)) {
        \App\Log::warning("PortfolioPurchases::cron::FailedSignings:PP removed");
        continue;
      } else if (($moduleName = \App\Record::getType($ppId)) !== 'PortfolioPurchases') {
        \App\Log::warning("PortfolioPurchases::cron::FailedSignings:Invalid item type - $moduleName");
        continue;
      }

      $pp = Vtiger_Record_Model::getInstanceById($ppId, 'PortfolioPurchases');
      \App\Log::warning("PortfolioPurchases::cron::FailedSignings:PP " . $pp->getId() . " " . $pp->getDisplayName());
      
      // validate docusign_envelope_id is empty
      if (!empty($pp->get('docusign_envelope_id'))) {
        \App\Log::warning("PortfolioPurchases::cron::FailedSignings:DocuSign Envelope Id = " . $pp->get('docusign_envelope_id'));
        continue;
      }

      // find document
      $documents = VTWorkflowUtils::getAllRelatedRecords($pp, 'Documents', ['document_type' => $documentTypeId]);
      if (!empty($documents) && \App\Record::isExists($documents[0]['id'], 'Documents')) {
        $document = Vtiger_Record_Model::getInstanceById($documents[0]['id'], 'Documents');

        // resend
        \App\Log::warning("PortfolioPurchases::cron::FailedSignings:Resend " . $document->getId());
        try {
          $workflow->performTasks($document);
        } catch (\Exception $e) {
          \App\Log::error("PortfolioPurchases::cron::FailedSignings:Error during sending - " . $e->getMessage());
          \App\Log::error(var_export($e, true));
        }
        finally {
          $batchError->changeState('Trash');
        }
      } else {
        \App\Log::warning("PortfolioPurchases::cron::FailedSignings:Document not found");
      }
    }
  }
}

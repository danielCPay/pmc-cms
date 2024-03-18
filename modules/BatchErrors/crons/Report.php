<?php
/**
 * Cron for sending BatchErrors report
 *
 * @package   Cron
 *
 * @copyright DOT Systems sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Kamiński <mkaminski@dotsystems.pl>
 */

/**
 * BatchErrors_Report_Cron class.
 */
class BatchErrors_Report_Cron extends \App\CronHandler
{
  const SERVICE_NAME = 'LBL_BATCHERRORS_REPORT';
  const REPORTS = ['Batch errors for Claim Management' => 'Batch errors for Claim Management', 'Batch errors for Litigation' => 'Batch errors for Litigation'];

  /**
   * {@inheritdoc}
   */
  public function process()
  {
    if (\App\Request::_get('service') === self::SERVICE_NAME) {
      foreach (self::REPORTS as $filterName => $templateName) {
        \App\Log::warning("BatchErrors_Report_Cron::processing $filterName and sending using template $templateName");

        try {
          $mailerContent = [];

          $moduleName = 'BatchErrors';
          $moduleModel = Vtiger_Module_Model::getInstance($moduleName);

          // get CV id by name to prevent problems with sync
          $cvInstance = \App\CustomView::getInstance($moduleName);
          $cvId = $cvInstance->getViewIdByName($filterName);

          $customViewModel = CustomView_Record_Model::getInstanceById($cvId);
          $customViewModel->set('entityState', 'Active');
          $queryGenerator = $customViewModel->getRecordsListQuery([], $moduleName);

          $cntRecords = $queryGenerator->createQuery()->count();

          if ($cntRecords) {
            $generatedFile = \Vtiger_QuickExport_Action::createExcelExport($moduleModel, $cvId, $queryGenerator);
            $mailerContent['attachments'] = [$generatedFile => 'Batch Errors Report.xlsx'];
          }

          $mailerContent['cntRecords'] = $cntRecords;

          // send by e-mail using placeholder
          $templateId = \VTWorkflowUtils::getEmailTemplatesByName($templateName)[0];
          
          /** @var EmailTemplates_Record_Model $template */
          $template = Vtiger_Record_Model::getInstanceById($templateId);

          $mailerContent['template'] = $templateId;

          if (!empty($template->get('email_from')) && \App\Record::isExists($template->get('email_from'))) {
            $smtp = Vtiger_Record_Model::getInstanceById($template->get('email_from'));
            $mailerContent['header'] = $smtp->get('email_header');
            $mailerContent['footer'] = $smtp->get('email_footer');
            $mailerContent['smtp_id'] = $smtp->get('smtp');
          } else {
            throw new \Exception("SMTP in From in template (" . $template->getDisplayName() . ") is empty");
          }

          $emailParser = \App\EmailParser::getInstance('BatchErrors');
          $mailerContent['to'] = [];
          if (!empty($template->get('email_to'))) {
            $to = $emailParser->setContent(Vtiger_Record_Model::getInstanceById($template->get('email_to'))->get('result_text'))->parse()->getContent(true);
            if (empty($to)) {
              throw new \Exception("To field in email template generated empty string");
            } else {
              $mailerContent['to'] = $to;
            }
          } else {
            throw new \Exception("Email To is not set");
          }

          if (!empty($template->get('email_cc'))) {
            $cc = $emailParser->setContent(Vtiger_Record_Model::getInstanceById($template->get('email_cc'))->get('result_text'))->parse()->getContent(true);
            if (!empty($cc)) {
              $mailerContent['cc'] = $cc;
            }
          }

          
          \App\Mailer::sendFromTemplate($mailerContent);
        } catch (\Exception $e) {
          \App\Log::error("BatchErrors_Report_Cron::error during generation: " . var_export($e, true));
          \VTWorkflowUtils::createBatchErrorEntryRaw("Batch Errors Sync", '', 'BatchErrors', "Batch Errors report generation failed", null, "Please contact your administrator");
        }
      }
    }
  }
}

<?php

/**
 * Sends e-mails to investors with list of purchased portfolios.
 *
 * @package   Cron
 *
 * @copyright DOT Systems sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Nichał Kamiński <mkaminski@dotsystems.pl>
 */

/**
 * PortfolioPurchases_PurchasedPortfolios_Cron class.
 */
class PortfolioPurchases_PurchasedPortfolios_Cron extends \App\CronHandler
{
  const SERVICE_NAME = 'LBL_PORTFOLIO_PURCHASE_EMAIL_HANDLER';

  /**
   * {@inheritdoc}
   */
  public function process()
  {
    $service = \App\Request::_get('service');
    $period = \App\Request::_get('period');
    if ($service === self::SERVICE_NAME) {
      // get portfolio purchases from current day, grouped by investor
      switch ($period) {
        case 'period1':
          $date = strtotime('-1 day');
          $from = date('Y-m-d 10:00:00', $date);
          $to = date('Y-m-d 23:59:59', $date);
          break;
        case 'period2':
          $from = date('Y-m-d 00:00:00');
          $to = date('Y-m-d 09:59:59');
          break;
        default:
          throw new \Exception("Unknown period $period");
      }
      $condition = ['between', 'purchase_date', $from, $to];
      $purchasePeriod = \App\Fields\DateTime::formatToDisplay($from) . '-' . \App\Fields\DateTime::formatToDisplay($to);

      $portfolioPurchasesByInvestor = (new \App\QueryGenerator('PortfolioPurchases'))
        ->setFields(['investor', 'id'])
        ->addNativeCondition($condition)
        ->createQuery()
        ->createCommand()
        ->queryAllByGroup(2);

      \App\Log::warning("PortfolioPurchases::cron::PortfolioPurchases_PurchasedPortfolios_Cron:$period/$purchasePeriod/" . count($portfolioPurchasesByInvestor));

      if (!empty($portfolioPurchasesByInvestor)) {
        // get and parse email template
        $templateId = VTWorkflowUtils::getEmailTemplateByNumber('N45');
        $template = \App\Mail::getTemplate($templateId);
        $templateModel = Vtiger_Record_Model::getInstanceById($templateId);

        // for each investor, prepare list of portfolio purchases and send it using template N45 with 
        //      #newPortfolioPurchases# replaced with prepared list
        foreach ($portfolioPurchasesByInvestor as $investorId => $purchaseIds) {
          \App\Log::warning("PortfolioPurchases::cron::PortfolioPurchases_PurchasedPortfolios_Cron:$investorId/" . count($purchaseIds));

          $purchaseList = '';
          foreach ($purchaseIds as $purchaseId) {
            if (\App\Record::isExists($purchaseId)) {
              $purchase = Vtiger_Record_Model::getInstanceById($purchaseId);
              $url = \App\Config::main('site_URL') . $purchase->getDetailViewUrl();
              $purchaseList .= "<li><a href='$url'>{$purchase->get('portfolio_purchase_name')}</a> ($url)</li>\n";
            }
          }
          if (empty($purchaseList)) {
            continue;
          }
          $purchaseList = "<ul>\n$purchaseList</ul>\n";

          $investor = Vtiger_Record_Model::getInstanceById($investorId);

          if (empty($templateModel->get('email_from')) || !\App\Record::isExists($templateModel->get('email_from'))) {
            \App\Log::warning("Cron::PortfolioPurchases::PurchasedPortfolios:SMTP in From in template (" . $templateModel->getDisplayName() . ") is empty");
            $entry = \VTWorkflowUtils::createBatchErrorEntryRaw("Purchased Portfolios Notification", '', 'Investors', "SMTP in From in template (" . $templateModel->getDisplayName() . ") is empty", $investorId, "Following portfolio purchases were supposed to be sent:<br/>$purchaseList");
            $entry->set('email_template', $templateId);
            $entry->set('assigned_user_id', $investor->get('assigned_user_id'));
            $entry->save();
            continue;
          }
          $smtp = Vtiger_Record_Model::getInstanceById($templateModel->get('email_from'));

          $investorEmail = $investor->get('e_mail');
          if (empty($investorEmail)) {
            \App\Log::warning("PortfolioPurchases::cron::PortfolioPurchases_PurchasedPortfolios_Cron:empty investor email");
            $entry = \VTWorkflowUtils::createBatchErrorEntryRaw("Purchased Portfolios Notification", '', 'Investor', "Email for investor of Portfolio Purchase is empty", $investorId, "Following portfolio purchases were supposed to be sent:<br/>$purchaseList");
            $entry->set('email_template', $templateId);
            $entry->set('assigned_user_id', $investor->get('assigned_user_id'));
            $entry->save();
            continue;
          }

          if (!empty($purchaseList)) {
            $textParser = \App\TextParser::getInstanceByModel($investor);
            $subject = $template['subject'];
            $subject = \App\Utils\Completions::processIfs($subject, $textParser);
            $subject = $textParser->setContent($subject)->parse()->getContent();
            $subject = str_replace('#newPortfolioPurchasesDate#', $purchasePeriod, $subject);

            $content = $template['content'];
            $content = \App\Utils\Completions::processIfs($content, $textParser);
            $content = $textParser->setContent($content)->parse()->getContent();
            // replace #newPortfolioPurchases# with list of purchases
            $content = str_replace('#newPortfolioPurchases#', $purchaseList, $content);
            $content = str_replace('#newPortfolioPurchasesDate#', $purchasePeriod, $content);
            $content = \App\Utils\Completions::decode(\App\Purifier::purifyHtml($content));

            // send email
            $mailerContent = [
              'smtp_id' => $smtp->get('smtp'),
              'to' => $investorEmail,
              'subject' => $subject,
              'content' => $content
            ];

            $additionalAddresses = $investor->get('email_addresses');
            if (!empty($additionalAddresses)) {
              $emails = array_map(function ($e) { return $e['e']; }, array_filter(\App\Json::decode($additionalAddresses), function ($e) { return $e['o'] === 1; }));
              $mailerContent['cc'] = $emails;
            }

            try {
              if (!\App\Mailer::sendDirect($mailerContent)) {
                throw new \Exception("Sending od purchased portfolio notification failed");
              }
            } catch (\Exception $e) {
              \App\Log::warning("PortfolioPurchases::cron::PortfolioPurchases_PurchasedPortfolios_Cron:email sending failure:" . var_export($e, true));
              $entry = \VTWorkflowUtils::createBatchErrorEntryRaw("Purchased Portfolios Notification", '', 'Investor', "Sending of portfolio purchase notification failed", $investorId, "Following portfolio purchases were supposed to be sent:<br/>$purchaseList");
              $entry->set('email_template', $templateId);
              $entry->set('assigned_user_id', $investor->get('assigned_user_id'));
              $entry->save();
              continue;
            }
          }
        }
      }
    }
  }
}

<?php
/**
 * Cron task to send e-mails from every SMTP at least once a day.
 *
 * @package   Cron
 *
 * @copyright DOT Systems Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Kamiński <mkaminski@dotsystems.pl>
 */

/**
 * OSSMail_DailySend_Cron class.
 */
class OSSMail_DailySend_Cron extends \App\CronHandler
{
  /**
	 * {@inheritdoc}
	 */
  public function process()
  {
    \App\Log::warning("OSSMail::cron::OSSMail_DailySend_Cron");

    /*
    For each SMTP in system, check if there was any sent mail in last 24 hours. 
    If not, send test e-mail to same address.
    */
    $sentAddresses = [];
    $allSmtps = (new \App\Db\Query())->from('s_#__mail_smtp')->all(\App\Db::getInstance('admin'));
    foreach ($allSmtps as $smtp) {
      if (\in_array($smtp['from_email'], $sentAddresses)) {
        continue;
      }
      \App\Log::warning("OSSMail::cron::OSSMail_DailySend_Cron:checking {$smtp['id']} ({$smtp['from_email']})");
      $mailExists = (new \App\QueryGenerator('OSSMailView'))
        ->addCondition('date', date('Y-m-d H:i:s', strtotime('-24 hours')) . ',' . date('Y-m-d H:i:s'), 'bw')
        ->addCondition('from_email', $smtp['from_email'], 'e')
        ->addCondition('type', 2, 'e')
        ->createQuery()->exists();

      if (!$mailExists) {
        $mailerContent = [ 
          'smtp_id' => $smtp['id'],
          'to' => $smtp['from_email'], 
          'subject' => 'Daily test e-mail',
          'content' => 'This is a daily e-mail to check mailboxes availability and configuration. Please ignore.',
        ];

        \App\Log::warning("OSSMail::cron::OSSMail_DailySend_Cron:{$smtp['id']} ({$smtp['from_email']}) has not sent any mail in last 24 hours, sending " . var_export($mailerContent, true));

        if (\App\Mailer::sendDirect($mailerContent, $error)) {
          $sentAddresses[] = $smtp['from_email'];
        }
      }
    }
  }
}

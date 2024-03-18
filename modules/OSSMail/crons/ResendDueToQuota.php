<?php
/**
 * Cron task to resend e-mails failed due to quota errors.
 *
 * @package   Cron
 *
 * @copyright DOT Systems Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Kamiński <mkaminski@dotsystems.pl>
 */

/**
 * OSSMail_ResendDueToQuota_Cron class.
 */
class OSSMail_ResendDueToQuota_Cron extends \App\CronHandler
{
  /**
	 * {@inheritdoc}
	 */
  public function process()
  {
    \App\Log::warning("OSSMail::cron::OSSMail_ResendDueToQuota_Cron");

    foreach (\App\Mailer::$retriableErrors as $category => $patterns) {
      $where = join(' and ', array_map(function ($pattern) { return "error like '%$pattern%'"; }, $patterns));
      $numRows = \App\Db::getInstance()->createCommand("update s_yf_mail_queue m set STATUS = 1, ERROR = '' 
      WHERE STATUS = 2 and $where AND SUBJECT not LIKE '%!!!%'")->execute();

      \App\Log::warning("OSSMail::cron::OSSMail_ResendDueToQuota_Cron:requeued $numRows e-mails with '$category' error and '$where' condition");
    }
  }
}

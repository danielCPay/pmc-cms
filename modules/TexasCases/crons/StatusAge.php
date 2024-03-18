<?php

/**
 * Ages records
 *
 * @package   Cron
 *
 * @copyright DOT Systems sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Nichał Kamiński <mkaminski@dotsystems.pl>
 */

/**
 * TexasCases_StatusAge_Cron class.
 */
class TexasCases_StatusAge_Cron extends \App\CronHandler
{
  const SERVICE_NAME = 'LBL_TEXASCASES_STATUS_AGE';

  /**
   * {@inheritdoc}
   */
  public function process()
  {
    if (\App\Request::_get('service') === self::SERVICE_NAME) {
      \App\Log::warning("TexasCases::cron::TexasCases_StatusAge_Cron");
      
      $db = \App\Db::getInstance();

      \App\Log::warning("TexasCases::cron::TexasCases_StatusAge_Cron:updating status age");
      $numRows = $db->createCommand('UPDATE u_yf_texascases SET status_age = case when final_status = \'CLOSED\' then null else datediff(now(), status_date) end WHERE texascasesid IN ( SELECT crmid FROM vtiger_crmentity WHERE vtiger_crmentity.setype = \'TexasCases\' AND deleted = 0 ) and status_age != coalesce(case when final_status = \'CLOSED\' then null else datediff(now(), status_date) end, -1)')->execute();
      \App\Log::warning("TexasCases::cron::TexasCases_StatusAge_Cron:updating $numRows rows");

      \App\Log::warning("TexasCases::cron::TexasCases_StatusAge_Cron:updating case age");
      $numRows = $db->createCommand('UPDATE u_yf_texascases c SET c.case_age = datediff(NOW(), (SELECT createdtime FROM vtiger_crmentity WHERE crmid = c.texascasesid)) WHERE c.final_status != \'CLOSED\' AND c.texascasesid IN ( SELECT crmid FROM vtiger_crmentity WHERE vtiger_crmentity.setype = \'TexasCases\' AND deleted = 0 )')->execute();
      \App\Log::warning("TexasCases::cron::TexasCases_StatusAge_Cron:updating $numRows rows");
    }
  }
}

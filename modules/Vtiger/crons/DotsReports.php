<?php

/**
 * Generates report data.
 *
 * @package   Cron
 *
 * @copyright DOT Systems sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Nichał Kamiński <mkaminski@dotsystems.pl>
 */

/**
 * Vtiger_DotsReports_Cron class.
 */
class Vtiger_DotsReports_Cron extends \App\CronHandler
{
  const SERVICE_NAME = 'LBL_DOTS_REPORTS_HANDLER';

  /**
   * {@inheritdoc}
   */
  public function process()
  {
    if (\App\Request::_get('service') === self::SERVICE_NAME) {
      \App\Log::warning("Vtiger::cron::Vtiger_DotsReports_Cron");
      
      $db = \App\Db::getInstance();

      $db->createCommand('DELETE FROM rep_history_statuses WHERE snapshot_date = CURDATE()-1')->execute();
      $db->createCommand('INSERT INTO rep_history_statuses SELECT ADDDATE(CURDATE(), -1), status_area, status_value, number_of_elements FROM vw_current_statuses')->execute();
      $db->createCommand('DELETE FROM rep_history_statuses WHERE snapshot_date < ADDDATE(CURDATE(), -10000)')->execute();
    }
  }
}

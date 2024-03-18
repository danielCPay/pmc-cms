<?php

/**
 * Resets portfolios to New status if they have no claims in one of specific statuses.
 *
 * @package   Cron
 *
 * @copyright DOT Systems sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Nichał Kamiński <mkaminski@dotsystems.pl>
 */

/**
 * Portfolios_ResetToNew_Cron class.
 */
class Portfolios_ResetToNew_Cron extends \App\CronHandler
{
  const SERVICE_NAME = 'LBL_PORTFOLIOS_RESET_TO_NEW';

  /**
   * {@inheritdoc}
   */
  public function process()
  {
    if (\App\Request::_get('service') === self::SERVICE_NAME) {
      \App\Log::warning("Portfolios::cron::Portfolios_ResetToNew_Cron");
      
      Portfolios_Module_Model::resetToNew();
    }
  }
}

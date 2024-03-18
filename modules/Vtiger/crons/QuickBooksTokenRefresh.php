<?php
/**
 * Refresh QuickBooks refresh token
 *
 * @package   Cron
 *
 * @copyright DOT Systems sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Michał Kamiński <mkaminski@dotsystems.pl>
 */

/**
 * Vtiger_QuickBooksTokenRefresh_Cron class.
 */
class Vtiger_QuickBooksTokenRefresh_Cron extends \App\CronHandler
{
	/**
	 * {@inheritdoc}
	 */
	public function process()
	{
		\App\Log::warning("Vtiger::cron::Vtiger_QuickBooksTokenRefresh_Cron");
		\App\QuickBooks\Api::refreshToken();
	}
}

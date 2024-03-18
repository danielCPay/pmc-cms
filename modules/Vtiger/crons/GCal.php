<?php
/**
 * Google Calendar integeration
 *
 * @package   Cron
 *
 * @copyright DOT Systems sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Kamiński <mkaminski@dotsystems.pl>
 */

/**
 * Vtiger_GCal_Cron class.
 */
class Vtiger_GCal_Cron extends \App\CronHandler
{
	/**
	 * {@inheritdoc}
	 */
	public function process()
	{
		if (!empty(\App\Config::gcal('credentialsFile')) && file_exists(\App\Config::gcal('credentialsFile'))) {
			try {
				\App\GCal\Api::sync();
			} catch (\Exception $e) {
				\App\Log::error("Vtiger_GCal_Cron::error during sync: " . var_export($e, true));
				\VTWorkflowUtils::createBatchErrorEntryRaw("Google Calendar Sync", '', 'Calendar', "Google Calendar Synchronization failed", null, "Please contact your administrator");
				throw $e;
			}
		}
	}
}

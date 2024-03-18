<?php
/**
 * Cron task to handle queued batch tasks
 *
 * @package   Cron
 *
 * @copyright DOT Systems Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Kamiński <mkaminski@dotsystems.pl>
 */

/**
 * BatchTasks_HandleQueue_Cron class.
 */
class BatchTasks_HandleQueue_Cron extends \App\CronHandler
{
  public const MAX_TASKS_PER_RUN = 1000;
  public const MAX_RUN_TIME = 60;

  /**
	 * {@inheritdoc}
	 */
  public function process()
  {
    \App\Log::warning("BatchTasks::cron::BatchTasks_HandleQueue_Cron");

    $iterations = 0;
    $startTime = time();
    while ($iterations++ < self::MAX_TASKS_PER_RUN && (time() - $startTime) < self::MAX_RUN_TIME && ($task = BatchTasks_Module_Model::getOldestPendingTask())) {
      BatchTasks_Module_Model::processTask($task, true);
    }

    \App\Log::warning("BatchTasks::cron::BatchTasks_HandleQueue_Cron:processed $iterations iterations in " . (time() - $startTime) . " seconds");
  }
}

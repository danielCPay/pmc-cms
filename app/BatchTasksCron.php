<?php

/**
 * Batch tasks cron.
 *
 * @package   App
 *
 * @copyright DOT Systems sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Kamiński <mkaminski@dotsystems.pl>
 */

namespace App;

class BatchTasksCron extends \App\Cron {
  /** {@inheritdoc} */
  public $logPath = \ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'cache' . \DIRECTORY_SEPARATOR . 'logs' . \DIRECTORY_SEPARATOR . 'batchtasks' . \DIRECTORY_SEPARATOR;
  /** {@inheritdoc} */
  protected static $maxExecutionCronTime = 30 * 60; // seconds

  public const MAX_TASKS_PER_RUN = 1000;
  public const MAX_RUN_TIME = 30 * 60;

  /** Maximum number of e-mails to send during single run */
  public $limit;
  /** Batch size for single sending session */
  public $batchSize;
  
	public function __construct() {
    static::$scriptTimeStart = microtime(true);

    $this->limit = static::MAX_TASKS_PER_RUN;
    static::$maxExecutionCronTime = static::MAX_RUN_TIME; // seconds

    assert(static::$maxExecutionCronTime == $this->getMaxExecutionTime());

    if (!(static::$logActive = \App\Config::debug('DEBUG_CRON'))) {
			return;
		}
		if (!is_dir($this->logPath) && !mkdir($this->logPath, 0777, true) && !is_dir($this->logPath)) {
			static::$logActive = false;
			\App\Log::error("The mechanism of cron logs has been disabled !!!. No access to the log directory '{$this->logPath}'");
		}
		if (!$this->logFile) {
			$this->logFile = date('Ymd_Hi') . '.log';
		}
		$this->log('File start', 'info', false);
  }

  public function process() {
    $this->log("Will process a maximum of {$this->limit} tasks. Timeout is {$this->getMaxExecutionTime()} seconds.");

    $limit = $this->limit;
    $this->log("Max execution time: " . $this->getMaxExecutionTime() . " seconds");

    while ($task = \BatchTasks_Module_Model::getOldestPendingTask()) {
      if (self::checkCronLocked()) {
        $this->log("Cron is locked. Stopping.");
        return;
      }

      $this->log("Processing task '{$task->getDisplayName()}' ({$task->getId()})");

      \BatchTasks_Module_Model::processTask($task, true);

      --$limit;
      if (0 >= $limit) {
        $this->log("Reached limit of {$this->limit} tasks. Stopping.");
        return;
      }
      if ($this->checkCronTimeout()) {
        $this->log("Nearing or exceeded timeout of {$this->getMaxExecutionTime()} seconds (execution time = {$this->getCronExecutionTime()}). Stopping.");
        return;
      }
    }

    $this->log("Finished processing all tasks.");
  }

  /** {@inheritdoc} */
  public function checkCronTimeout(): bool {
    return time() >= (self::getMaxExecutionTime() * 0.9 + self::$cronTimeStart);
  }

  /** {@inheritdoc} */
	public function log(string $message, string $level = 'info', bool $indent = true)
	{
		parent::log($message, $level, $indent);

    if ($indent) {
			$message = '   ' . $message;
		}
		echo "BT Cron: " . date('Y-m-d H:i:s') . " [{$level}] - {$message}" . PHP_EOL;
	}
}

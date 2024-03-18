<?php

/**
 * Mailer cron.
 *
 * @package   App
 *
 * @copyright DOT Systems sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Kamiński <mkaminski@dotsystems.pl>
 */

namespace App;

class MailerCron extends \App\Cron {
  /** {@inheritdoc} */
  public $logPath = \ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'cache' . \DIRECTORY_SEPARATOR . 'logs' . \DIRECTORY_SEPARATOR . 'mailer' . \DIRECTORY_SEPARATOR;
  /** {@inheritdoc} */
  protected static $maxExecutionCronTime;

  /** Maximum number of e-mails to send during single run */
  public $limit;
  /** Batch size for single sending session */
  public $batchSize;
  
	public function __construct() {
    static::$scriptTimeStart = microtime(true);

    $this->limit = \App\Config::performance('CRON_MAX_NUMBERS_SENDING_MAILS', 1000);
    $this->batchSize = \App\Config::performance('CRON_BATCH_SIZE_SENDING_MAILS', 10);
    static::$maxExecutionCronTime = \App\Config::main('maxExecutionMailerCronTime', 30 * 60); // seconds

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
    $this->log("Will send a maximum of {$this->limit} e-mails in batches of {$this->batchSize}. Timeout is {$this->getMaxExecutionTime()} seconds.");
		
    $limit = $this->limit;

    $this->log("Max execution time: " . $this->getMaxExecutionTime() . " seconds");

    $query = (new \App\Db\Query())->from('s_#__mail_queue')->where(['status' => 1])->orderBy(['priority' => SORT_DESC, 'id' => SORT_ASC])->limit($this->batchSize);
		$db = \App\Db::getInstance('admin');
		while ($rows = $query->all($db)) {
			foreach ($rows as $row) {
        if (self::checkCronLocked()) {
          $this->log("Cron is locked. Stopping.");
          return;
        }

        $this->log("Sending e-mail :" . var_export(array_diff_key($row, array_flip(['content'])), true));

				\App\Mailer::sendByRowQueue($row);
				--$limit;
				if (0 >= $limit) {
          $this->log("Reached limit of {$this->limit} e-mails. Stopping.");
					return;
				}
        if ($this->checkCronTimeout()) {
          $this->log("Nearing or exceeded timeout of {$this->getMaxExecutionTime()} seconds (execution time = {$this->getCronExecutionTime()}). Stopping.");
          return;
        }
			}
		}

    $this->log("Finished sending all e-mails.");
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
		echo "Mail Cron: " . date('Y-m-d H:i:s') . " [{$level}] - {$message}" . PHP_EOL;
	}
}

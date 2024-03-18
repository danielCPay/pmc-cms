<?php

chdir(__DIR__);
require_once __DIR__ . '/include/main/WebUI.php';

$lockFile = __DIR__ . '/cache/mailCron.lock';

$cronInstance = new \App\MailerCron();
$cronInstance->log('Cron start', 'info', false);
$cronInstance::$cronTimeStart = microtime(true);

if (file_exists($lockFile) && filemtime($lockFile) > time() - $cronInstance->getMaxExecutionTime()) {
  // Lock file exists and was modified less than $maxRuntime seconds ago,
  // so the script is still running from a previous cron call.
  $cronInstance->log('Lock file exists, exiting.', 'warning');
  exit;
}

// Create lock file.
if (touch($lockFile)) {
  // Set script execution time limit (doesn't include sleep, db, system, streams and some other things, 
  // add separate timeout based on real time in loop)
  set_time_limit($maxRuntime);

  try {
    \App\Process::$requestMode = 'Cron';
    \App\Utils\ConfReport::$sapi = 'cron';

    \App\User::setCurrentUserId(\App\User::getActiveAdminId());

    $cronInstance->process();
  } finally {
    // Remove lock file.
    unlink($lockFile);
  }
} else {
  $cronInstance->log('Could not create lock file, exiting.', 'error');
}

?>

<?php

define('ROOT_DIRECTORY', __DIR__ !== DIRECTORY_SEPARATOR ? __DIR__ : '');

require __DIR__ . '/include/main/WebUI.php';

\App\Process::$requestMode = 'QBCallback';
try {
  $request = \App\Request::init();
  App\Session::init();
  
  \App\QuickBooks\Api::handleAuthorizationCode($request->getRaw('code'), $request->getRaw('realmId'));
  
  header('location: ' . \App\Config::main('site_URL'), true, 301);

  echo "Authorized";
} catch (Exception $e) {
	\App\Log::error($e->getMessage() . ' => ' . $e->getFile() . ':' . $e->getLine());
	header('location: ' . \App\Config::main('site_URL'), true, 301);

  echo "Authorization failed";
}

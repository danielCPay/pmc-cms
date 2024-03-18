<?php

define('ROOT_DIRECTORY', __DIR__ !== DIRECTORY_SEPARATOR ? __DIR__ : '');

require __DIR__ . '/include/main/WebUI.php';

\App\Process::$requestMode = 'DBCallback';
try {
  \App\Log::warning('DB CALLBACK');

  $request = \App\Request::init();
  App\Session::init();

  if ($request->getRequestMethod() === 'GET' && $request->has('code') && $request->has('state')) {
    $code = $request->get('code');
    $state = $request->get('state');

    \App\Dropbox\Api::handleCallback($state, $code);

    header('location: ' . \App\Config::main('site_URL'), true, 301);
    echo "Authorized";
  }
} catch (Exception $e) {
	\App\Log::error($e->getMessage() . ' => ' . $e->getFile() . ':' . $e->getLine());
	header('location: ' . \App\Config::main('site_URL'), true, 301);
}

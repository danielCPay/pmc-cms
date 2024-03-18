<?php

define('ROOT_DIRECTORY', __DIR__ !== DIRECTORY_SEPARATOR ? __DIR__ : '');

require __DIR__ . '/include/main/WebUI.php';

\App\Process::$requestMode = 'DSCallback';
try {
  \App\Log::warning('DS CALLBACK');

  $request = \App\Request::init();
  App\Session::init();

  if ($request->getRequestMethod() === 'GET') {
    $state = $request->getRaw('state') ?: \App\Config::main('site_URL');
    header('location: ' . $state, true, 301);
    echo "Authorized";
  } else {
    \App\User::setCurrentUserId(\App\User::getActiveAdminId());

    $requestBody = file_get_contents('php://input');

    // Buffer all upcoming output...
    ob_start();

    $response = new Vtiger_Response();
    $response->setResult([]);
		$response->emit();

    // Get the size of the output.
    $size = ob_get_length();

    // Disable compression (in case content length is compressed).
    header("Content-Encoding: none");

    // Set the content length of the response.
    header("Content-Length: {$size}");

    // Close the connection.
    header("Connection: close");

    // Flush all output.
    ob_end_flush();
    @ob_flush();
    flush();

    // Close current session (if it exists).
    if(session_id()) session_write_close();

    fastcgi_finish_request();

    \App\DocuSign\Api::callbackHandler($request->getHeaders(), $requestBody);

    die();
  }
} catch (Exception $e) {
	\App\Log::error($e->getMessage() . ' => ' . $e->getFile() . ':' . $e->getLine());
	header('location: ' . \App\Config::main('site_URL'), true, 301);
}

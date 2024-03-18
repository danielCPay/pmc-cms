<?php

define('ROOT_DIRECTORY', __DIR__ !== DIRECTORY_SEPARATOR ? __DIR__ : '');

require __DIR__ . '/include/main/WebUI.php';

\App\Process::$requestMode = 'GmailCallback';
try {
  $request = \App\Request::init();
  App\Session::init();
  
  $providerName = 'google';
  $clientId = \App\Config::module('OSSMail', 'oauth_client_id');
  $clientSecret = \App\Config::module('OSSMail', 'oauth_client_secret');

  $params = [
      'clientId' => $clientId,
      'clientSecret' => $clientSecret,
      'urlAuthorize' => \App\Config::module('OSSMail', 'oauth_auth_uri'),
      'urlAccessToken' => \App\Config::module('OSSMail', 'oauth_token_uri'),
      'urlResourceOwnerDetails' => \App\Config::module('OSSMail', 'oauth_identity_uri'),
      'redirectUri' => \App\Config::main('site_URL') . 'gmailCallback.php',
      'accessType' => 'offline',
  ];

  $options = [];

  $provider = new \League\OAuth2\Client\Provider\GenericProvider($params);
  $options = [
      'scope' => [
          'https://mail.google.com/'
      ],
      'access_type' => 'offline',
      'prompt' => 'consent',
      'approval_prompt' => null,
  ];

  if (!isset($_GET['code'])) {
    //If we don't have an authorization code then get one
    $authUrl = $provider->getAuthorizationUrl($options);
    \App\Session::set('oauth2state', $provider->getState());
    header('Location: ' . $authUrl);
    exit;
    //Check given state against previously stored one to mitigate CSRF attack
  } elseif (empty($_GET['state']) || ($_GET['state'] !== \App\Session::get('oauth2state'))) {
    \App\Session::delete('oauth2state');
    exit('Invalid state');
  } else {
    //Try to get an access token (using the authorization code grant)
    $token = $provider->getAccessToken(
        'authorization_code',
        [
            'code' => $_GET['code']
        ]
    );
    \App\Log::warning(var_export($token, true));
    //Use this to get a new access token if the old one expires
    echo 'Refresh Token: ', htmlspecialchars($token->getRefreshToken());
  }
} catch (Exception $e) {
	\App\Log::error($e->getMessage() . ' => ' . $e->getFile() . ':' . $e->getLine());
	header('location: ' . \App\Config::main('site_URL'), true, 301);

  echo "Authorization failed";
}

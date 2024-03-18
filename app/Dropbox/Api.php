<?php
/**
 * Dropbox API wrapper class.
 *
 * @package   App\Dropbox
 *
 * @copyright DOT Systems Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Kamiński <mkaminski@dotsystems.pl>
 */

namespace App\Dropbox;

require_once 'modules/com_vtiger_workflow/VTWorkflowUtils.php';

use Kunnu\Dropbox\Authentication\DropboxAuthHelper;
use Kunnu\Dropbox\DropboxApp;
use Kunnu\Dropbox\Dropbox;
use Kunnu\Dropbox\Exceptions\DropboxClientException;
use Kunnu\Dropbox\Models\AccessToken;

class Api
{
  public static function getClient(string $account, $raw = false) {
    \App\Log::warning("App::Dropbox::Api::getClient:$account/" . ($raw ? 'TRUE' : 'FALSE'));

    $app = new DropboxApp(\App\Config::dropbox('clientId'), \App\Config::dropbox('clientSecret'));
    $dropbox = new Dropbox($app);

    if (!$raw) {
      $sessionKey = "dropbox-token-$account";
      $sessionKeyExpiration = "dropbox-token-expiry-$account";
      $refreshToken = \App\Config::dropbox('refreshTokens')[$account];

      if (!empty($refreshToken) && \App\Session::has($sessionKey) && \App\Session::get($sessionKeyExpiration) > (time() + 600)) { 
        // test token
        $token = \App\Session::get($sessionKey);
        try {
          $dropbox->setAccessToken($token);
          $account = $dropbox->getCurrentAccount();
        } catch (DropboxClientException $e) {
          \App\Log::warning("App::Dropbox::Api::getClient:$account token expired (" . $e->getMessage() . ")");
          
          $token = null;
          \App\Session::delete($sessionKey);
          \App\Session::delete($sessionKeyExpiration);
        }
      } 
      

      if (empty($token)) {
        $authHelper = $dropbox->getAuthHelper();
        
        if (!empty($refreshToken)) {
          try {
            $accessToken = $authHelper->getRefreshedAccessToken(new AccessToken(['refresh_token' => $refreshToken]));
            $token = $accessToken->getToken();
            $expiration = strtotime('+' . $accessToken->getExpiryTime() . ' seconds' );
            $dropbox->setAccessToken($token);

            // save in session
            \App\Session::set($sessionKey, $token);
            \App\Session::set($sessionKeyExpiration, $expiration);
          } catch (\Throwable $t) {
            \App\Log::error("App::Dropbox::Api::getClient:" . $t->getMessage());
            \App\Log::error(var_export($t, true));

            self::saveConfig('', '');
            \App\Session::delete($sessionKey);
            \App\Session::delete($sessionKeyExpiration);

            self::createLoginNotification($authHelper, $account);

            throw new \Exception("Please login to Dropbox, see notification");
          }
        } else {
          self::createLoginNotification($authHelper, $account);

          throw new \Exception("Please login to Dropbox, see notification");
        }
      }
    }

    return $dropbox;
  }

  private static function createLoginNotification(DropboxAuthHelper $authHelper, string $account) {
    $authUrl = $authHelper->getAuthUrl(\App\Config::dropbox('redirectUri'), [], $account, 'offline');

    \App\Log::error("App::Dropbox::Api::getClient:Dropbox login required: $authUrl");
    \VTWorkflowUtils::createNotificationRaw([\App\User::getCurrentUserId()], 'Dropbox login required', 'Please go to <a href="' . $authUrl . '">DropBox</a> to authorize CMS access.', 'PLL_USERS');
  }

  public static function upload(string $account, string $filePath, string $targetPath, string $fileName) {
    \App\Log::warning("App::Dropbox::Api::upload:$filePath to $targetPath/$fileName on $account");

    // check file exists
    if (!file_exists($filePath)) {
      throw new \Exception("File $filePath is missing!");
    }

    $targetPath = self::cleanPath($targetPath);

    $dropbox = self::getClient($account);

    // ensure target path exists
    if (!self::checkFolderExists($dropbox, $targetPath)) {
      \App\Log::warning("App::Dropbox::Api::upload:creating folder $targetPath");
      $dropbox->createfolder($targetPath);
    }

    // upload file with overwrite and mute
    $dropbox->upload($filePath, self::join_paths($targetPath, $fileName), ['mute' => true, 'mode' => 'overwrite']);
  }

  public static function handleCallback(string $state, string $code) {
    \App\Log::warning("App::Dropbox::Api::handleCallback:$state/$code");

    [$state, $account] = explode('|', $state);

    $app = new DropboxApp(\App\Config::dropbox('clientId'), \App\Config::dropbox('clientSecret'));
    $dropbox = new Dropbox($app);
    $authHelper = $dropbox->getAuthHelper();

    $token = $authHelper->getAccessToken($code, $state, \App\Config::dropbox('redirectUri'));

    self::saveConfig($account, $token->getRefreshToken());

    return $token;
  }

  private static function saveConfig(string $account, string $refreshToken) {
    $config = new \App\ConfigFile('dropbox');
    $refreshTokens = \App\Config::dropbox('refreshTokens');
    if (!is_array($refreshTokens)) {
      $refreshTokens = [];
    }
    $refreshTokens[$account] = $refreshToken;
    $config->set('refreshTokens', $refreshTokens);
    $config->create();
  }

  // src: https://stackoverflow.com/a/39796579/126980
  public static function cleanPath($pathString) {
    $path = [];
    foreach(explode('/', $pathString) as $part) {
      // ignore parts that have no value
      if ($part === '' || $part === '.') continue;
  
      if ($part !== '..') {
        // cool, we found a new part
        array_push($path, $part);
      }
      else if (count($path) > 0) {
        // going back up? sure
        array_pop($path);
      } else {
        // now, here we don't like
        throw new \Exception('Climbing above the root is not permitted.');
      }
    }
  
    return '/' . join('/', $path);
  }

  // src: https://stackoverflow.com/a/15575293/126980
  private static function join_paths() {
    $paths = array();

    foreach (func_get_args() as $arg) {
        if ($arg !== '') { $paths[] = $arg; }
    }

    return preg_replace('#/+#','/',join('/', $paths));
  }

  public static function checkFolderExists(Dropbox $dropbox, string $path) {
    $cleanPath = self::cleanPath($path);
    $pathElements = explode('/', trim($cleanPath, '/'));
    $currentPath = '/';
    
    while(!empty($element = array_shift($pathElements))) {
      // check if $element exists in $currentPath
      $folderContents = $dropbox->listFolder($currentPath);
      $folderItems = $folderContents->getItems();
      while ($folderContents->hasMoreItems()) {
        $folderContents = $dropbox->listFolderContinue($folderContents->getCursor());
        $folderItems->push(...$folderContents->getItems());
      }
      $folderExists = $folderItems->contains(function ($val) use ($element) { return $val->getTag() === 'folder' && $val->getName() === $element; });
  
      if (!$folderExists) {
        return false;
      }
  
      $currentPath .= "$element/";
    }
  
    return true;
  }
}

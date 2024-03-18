<?php
/**
 * Cron task to refresh saved OAuth tokens
 *
 * @package   Cron
 *
 * @copyright DOT Systems Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Kamiński <mkaminski@dotsystems.pl>
 */

/**
 * OSSMail_RefreshTokens_Cron class.
 */
class OSSMail_RefreshTokens_Cron extends \App\CronHandler
{
  /**
	 * {@inheritdoc}
	 */
  public function process()
  {
    \App\Log::warning("OSSMail::cron::OSSMail_RefreshTokens_Cron");

    // read config
    $oauth_token_uri     = \App\Config::module('OSSMail', 'oauth_token_uri');
    $oauth_client_id     = \App\Config::module('OSSMail', 'oauth_client_id');
    $oauth_client_secret = \App\Config::module('OSSMail', 'oauth_client_secret');

    if (!empty($oauth_token_uri) && !empty($oauth_client_secret)) {
      // read refresh tokens
      $db = \App\Db::getInstance();
      $tokens = (new \App\Db\Query())->select(['password', 'user_id'])->from('roundcube_users')->all();
      $encryption = \App\Encryption::getInstance();

      \App\Log::warning("OSSMail::cron::OSSMail_RefreshTokens_Cron:processing " . count($tokens) . " tokens");
      
      foreach ($tokens as $token) {
        try {
          $client = new \GuzzleHttp\Client([
            'timeout' => 10.0,
            'verify' => true,
          ]);

          $response = $client->post($oauth_token_uri, [
            'form_params' => [
              'client_id'     => $oauth_client_id,
              'client_secret' => $oauth_client_secret,
              'refresh_token' => $encryption->decrypt($token['password']),
              'grant_type'    => 'refresh_token',
            ],
          ]);

          $data = \GuzzleHttp\json_decode($response->getBody(), true);

          // auth success
          if (!empty($data['access_token'])) {
            // update access token stored as password
            $refreshToken = $encryption->encrypt($data['refresh_token']);

            //  refresh token
            $db->createCommand()->update('roundcube_users', ['password' => $refreshToken], ['user_id' => $token['user_id']]);

            \App\Log::warning("OSSMail::cron::OSSMail_RefreshTokens_Cron:{$token['user_id']} - refreshed");
          }
        }
        catch (\Exception $e) {
          \App\Log::error("OSSMail::cron::OSSMail_RefreshTokens_Cron:{$token['user_id']} - " . $e->getMessage());
        }
      }
    } else {
      \App\Log::warning("OSSMail::cron::OSSMail_RefreshTokens_Cron - oauth disabled");
    }
  }
}

<?php
/**
 * DocuSign API wrapper class.
 *
 * @package   App\Utils
 *
 * @copyright DOT Systems Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Kamiński <mkaminski@dotsystems.pl>
 */

namespace App\DocuSign;

require_once 'modules/com_vtiger_workflow/include.php';
require_once 'modules/com_vtiger_workflow/VTWorkflowUtils.php';

class Api {
  private const DS_TOKEN_KEY = 'ds-token';
  private const DS_TOKEN_EXPIRATION_KEY = 'ds-token-expiration';
  private const DS_ACCOUNT_ID_KEY = 'ds-account-id';
  private const DS_BASE_URL_KEY = 'ds-base-url';
  private const DS_SIGNER_EMAIL_KEY = 'ds-signer-email';
  private const DS_SIGNER_NAME_KEY = 'ds-signer-name';

  private static $client;

  /**
   * Returns \DocuSign\eSign\Client\ApiClient instance, optionally refreshing JWT 
   * token and setting default headers.
   * 
   * @param bool $withLogin Get/refresh JWT token and set up default headers
   * @param bool $forceNew Force new client
   * 
   * @return \DocuSign\eSign\Client\ApiClient API client
   */
  public static function getClient($withLogin = false, $forceNew = false) {
    if (!self::$client || $forceNew) {
      $config = new  \DocuSign\eSign\Configuration();
      $config->setDebug(true);
      $config->setDebugFile('cache/logs/docusign.log');

      self::$client = new \DocuSign\eSign\Client\ApiClient($config);
      self::$client->getOAuth()->setOAuthBasePath(\App\Config::docusign('authorizationServer'));
    }
    if ($withLogin) {
      self::getJWTToken();
    }
    return self::$client;
  }

  /**
   * Read JWT token from session or request new one, if expired or not available.
   * 
   * Note: If consent for user being impersonated has not been given will report notification and throw exception.
   * 
   * @return string Access token
   */
  public static function getJWTToken() {
    \App\Log::warning("App::DocuSign::Api::getJWTToken");

    $accessToken = \App\Session::get(self::DS_TOKEN_KEY);
    $tokenExpiration = \App\Session::get(self::DS_TOKEN_EXPIRATION_KEY);

    $client = self::getClient();
    if (empty($accessToken) || $tokenExpiration < time()) {
      \App\Log::warning("App::DocuSign::Api::getJWTToken:requesting from DS");

      $scopes = 'signature impersonation';

      try {
        $response = $client->requestJWTUserToken(
          \App\Config::docusign('clientId'), 
          \App\Config::docusign('impersonatedUserId'), 
          \App\Config::docusign('privateKey'), 
          $scopes, 
          60
        );
        $accessToken = $response[0]['access_token'];

        \App\Session::set(self::DS_TOKEN_KEY, $accessToken);
        \App\Session::set(self::DS_TOKEN_EXPIRATION_KEY, strtotime('+45 minutes'));

        [
          'signerEmail' => $signerEmail,
          'signerName' => $signerName,
          'accountId' => $accountId,
          'baseUrl' => $baseUrl,
        ] = self::getUserInfo();
        
        \App\Session::set(self::DS_BASE_URL_KEY, $baseUrl);
        \App\Session::set(self::DS_ACCOUNT_ID_KEY, $accountId);
        \App\Session::set(self::DS_SIGNER_EMAIL_KEY, $signerEmail);
        \App\Session::set(self::DS_SIGNER_NAME_KEY, $signerName);

        $config = $client->getConfig();
        $config->setHost("$baseUrl/restapi");
        $config->addDefaultHeader('Authorization', "Bearer $accessToken");
      } catch (\Throwable $th) {
        \App\Log::error("App::DocuSign::Api::getJWTToken:" . var_export($th, true));
      
        if (strpos($th->getMessage(), "consent_required") !== false) {
          $authorizationURL = 'https://' . \App\Config::docusign('authorizationServer') . '/oauth/auth?' . http_build_query([
              'scope'         => $scopes,
              'redirect_uri'  => \App\Config::docusign('callbackUrl'),
              'client_id'     => \App\Config::docusign('clientId'),
              'response_type' => 'code',
              'state' => PHP_SAPI !== 'cli' ? (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) ? 'https' : 'http') . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}" : \App\Config::main('site_URL'),
          ]);

          \App\Log::error("App::DocuSign::Api::getJWTToken:consent required - $authorizationURL");

          \VTWorkflowUtils::createNotificationRaw(
            [\App\User::getCurrentUserId()], 
            'Consent required', 
            "It appears that you are using this integration key for the first time.  Opening the following link in a browser window:\n" . $authorizationURL . "\n\n", 
            'PLL_USERS'
          );
        }
      
        throw $th;
      }
    } else {
      \App\Log::warning("App::DocuSign::Api::getJWTToken:read from session");

      $config = $client->getConfig();
      $config->setHost(\App\Session::get(self::DS_BASE_URL_KEY . "/restapi"));
      $config->addDefaultHeader('Authorization', "Bearer $accessToken");
    }

    return $accessToken;
  }

  /**
   * Uses access token to return user info.
   * 
   * @return array Array with user data.
   */
  public static function getUserInfo() {
    \App\Log::warning("App::DocuSign::Api::getUserInfo");

    $accessToken = self::getJWTToken();
    $apiClient = self::getClient();

    $info = $apiClient->getUserInfo($accessToken);

    $signerEmail = $info[0]['email'];
    $signerName = $info[0]['name'];
    $account = $info[0]['accounts'][0];
    $accountId = $account['account_id'];
    $baseUrl = $account['base_uri'];
    $organization = $account['organization'] ?? $account['account_name'];

    return [
      'signerEmail' => $signerEmail,
      'signerName' => $signerName,
      'accountId' => $accountId,
      'baseUrl' => $baseUrl,
      'organization' => $organization
    ];
  }

  /**
   * Sends documents for signing by email.
   * 
   * @param string $emailSubject Subject of e-mail sent
   * @param array $documents Array with documents to sign, each element should have 'path', 'id' 
   * and optional 'name'
   * @param array $signers Array with signers for documents, each element should have 'email', 
   * 'name', 'id', optional 'order' and 'tabs', which in turn should have tab definitions
   * 
   * @return string Envelope id
   */
  public static function sendForSigning(string $emailSubject, array $documents, array $signers, ?array $ccs = null) {
    \App\Log::warning("App::DocuSign::Api::sendForSigning:$emailSubject/" . count($documents) . "/" . count($signers) . "/" . ($ccs ? count($ccs) : 0));

    $envelopeDefinition = self::createEnvelope($emailSubject, $documents, $signers, $ccs);

    $sent = false;
    $retries = 0;
    $maxRetries = 3;
    $waits = [0, 15, 30, 60];
    while (!$sent && $retries <= $maxRetries) {
      try {
        $client = Api::getClient(true, true);
        $accountId = \App\Session::get(self::DS_ACCOUNT_ID_KEY);
        $envelopeApi = new \DocuSign\eSign\Api\EnvelopesApi($client);

        $envelope = $envelopeApi->createEnvelope($accountId, $envelopeDefinition);
        $envelopeId = $envelope->getEnvelopeId();

        $sent = true;

        \App\Log::warning("App::DocuSign::Api::sendForSigning:sent envelope $envelopeId");
      } catch (\Exception $e) {
        \App\Log::warning("App::DocuSign::Api::sendForSigning:error " . $e->getMessage());
        \App\Log::warning("App::DocuSign::Api::sendForSigning:config report " . $client->getConfig()->toDebugReport());
        if ($retries++ >= $maxRetries) {
          throw $e;
        }
        \App\Log::warning("App::DocuSign::Api::sendForSigning:sleeping for {$waits[$retries]} before retry $retries");
        sleep($waits[$retries]);
      }
    }

    return $envelopeId;
  }

  private static function createEnvelope(string $emailSubject, array $documents, array $signers, ?array $ccs = null) {
    \App\Log::warning("App::DocuSign::Api::createEnvelope:$emailSubject/" . count($documents) . "/" . count($signers) . "/" . ($ccs ? count($ccs) : 0));

    $envelopeDefinition = new \DocuSign\eSign\Model\EnvelopeDefinition();
    $envelopeDefinition->setEmailSubject($emailSubject);

    $envelopeDocuments = [];
    foreach ($documents as $document) {
      ['path' => $path, 'id' => $id, 'name' => $name] = $document;
      \App\Log::warning("App::DocuSign::Api::createEnvelope:adding document $id ($path)");
      
      ['filename' => $filename, 'extension' => $extension] = pathinfo($path);
      $content = file_get_contents($path);
      $b64 = base64_encode($content);

      $envelopeDocuments[] = new \DocuSign\eSign\Model\Document([
        'document_base64' => $b64,
        'name' => $name ?? $filename,
        'file_extension' => $extension,
        'document_id' => $id,
      ]);
    }
    $envelopeDefinition->setDocuments($envelopeDocuments);

    $envelopeSigners = [];
    foreach ($signers as $signer) {
      [
        'email' => $email, 'id' => $id, 'name' => $name, 'order' => $order,
        'tabs' => $tabs
      ] = $signer;
      \App\Log::warning("App::DocuSign::Api::createEnvelope:adding signers $id ($name/$email/" . count($tabs) . ")");

      $signer = new \DocuSign\eSign\Model\Signer([
        'email' => $email,
        'name' => $name,
        'recipient_id' => $id,
        'routing_order' => $order ?? 1
      ]);

      $signerTabs = [];
      foreach ($tabs as $group => $tabs) {
        $signerTabs[$group] = [];
        switch ($group) {
          case 'sign_here_tabs':
            $tabClass = "\\DocuSign\\eSign\\Model\\SignHere";
            break;
          case 'text_tabs':
            $tabClass = "\\DocuSign\\eSign\\Model\\Text";
            break;
          case 'date_signed_tabs':
            $tabClass = "\\DocuSign\\eSign\\Model\\DateSigned";
            break;
          case 'full_name_tabs':
            $tabClass = "\\DocuSign\\eSign\\Model\\FullName";
            break;
          default:
            throw new \Exception("Unknown tab class - $group");
        }
        
        foreach ($tabs as $tab) {
          [
            'placeholder' => $placeholder, 
            'units' => $units, 
            'y_offset' => $yOffset, 
            'x_offset' => $xOffset, 
            'tab_label' => $tabLabel,
            'id' => $tabId,
            'font' => $font,
            'font_size' => $fontSize,
          ] = $tab;
          \App\Log::warning("App::DocuSign::Api::createEnvelope:adding tabs for signer $id - $group/$placeholder");


          $signerTabs[$group][] = new $tabClass([
            'anchor_string' => $placeholder, 'anchor_units' => $units ?? 'pixels',
            'anchor_y_offset' => $yOffset ?? 10, 'anchor_x_offset' => $xOffset ?? 20,
            'name' => $tabLabel, 'tab_label' => $tabLabel, 'required' => 'true', 'tab_id' => $tabId,
            'font' => $font, 'font_size' => $fontSize,
          ]);
        }
      }

      $signer->setTabs(new \DocuSign\eSign\Model\Tabs($signerTabs));

      $envelopeSigners[] = $signer;
    }
    $recipients = ['signers' => $envelopeSigners];

    if (!empty($ccs)) {
      $envelopeCcs = [];
      foreach ($ccs as $cc) {
        [
          'email' => $email, 'id' => $id, 'name' => $name, 'order' => $order,
          'tabs' => $tabs
        ] = $cc;
        \App\Log::warning("App::DocuSign::Api::createEnvelope:adding CC $id ($name/$email)");

        $cc = new \DocuSign\eSign\Model\CarbonCopy([
          'email' => $email,
          'name' => $name,
          'recipient_id' => $id,
          'routing_order' => $order ?? 1
        ]);

        $envelopeCcs[] = $cc;
      }
      $recipients['carbon_copies'] = $envelopeCcs;
    }

    $envelopeDefinition->setRecipients(new \DocuSign\eSign\Model\Recipients($recipients));

    $envelopeDefinition->setEventNotification(new \DocuSign\eSign\Model\EventNotification([
      'delivery_mode' => 'SIM',
      'events' => ['envelope-completed', 'envelope-declined', 'envelope-voided'],
      'event_data' => [ 'version' => 'restv2.1', 'format' => 'json', 'includeData' => ['recipients'] ],
      'logging_enabled' => 'true',
      'require_acknowledgment' => 'true',
      'include_envelope_void_reason' => 'true',
      'include_hmac' => 'true',
      'url' => \App\Config::docusign('callbackUrl')
    ]));

    $envelopeDefinition->setStatus('sent');

    return $envelopeDefinition;
  }

  public static function callbackHandler($headers, $rawResponse) {
    \App\Log::warning("App::DocuSign::Api::callbackHandler");

    try {
      $hashMatch = empty(\App\Config::docusign('hmacKey'));
      if ($headers['x-authorization-digest'] == 'HMACSHA256') {
        $hash = base64_encode(hex2bin(hash_hmac('sha256', $rawResponse, utf8_encode(\App\Config::docusign('hmacKey')))));
        $i = 1;
        while (\array_key_exists("x-docusign-signature-$i", $headers)) {
          $headerHash = $headers["x-docusign-signature-$i"];
          $hashMatch = hash_equals($headerHash, $hash);
          break;
        }
      }
      if (!$hashMatch) {
        throw new \Exception('Message integrity check failed');
      }

      $response = \App\Json::decode($rawResponse);
      \App\Log::warning("App::DocuSign::Api::callbackHandler:" . var_export($response, true));

      ['data' => ['envelopeId' => $envelopeId, 'accountId' => $accountId]] = $response;

      $portfolioPurchaseId = self::getPortfolioPurchaseIdByEnvelopeId($envelopeId);
      if (empty($portfolioPurchaseId)) {
        throw new \Exception("Unmatched DocuSign document with id $envelopeId");
      }
      $portfolioPurchase = \Vtiger_Record_Model::getInstanceById($portfolioPurchaseId);
      $assignedUserId = $portfolioPurchase->get('assigned_user_id');
      $currentUserId = \App\User::getCurrentUserId();
      \App\User::setCurrentUserId($assignedUserId);

      $wfs = new \VTWorkflowManager();

      switch($response['event']) {
        case 'envelope-completed':
          $relationModel = \Vtiger_Relation_Model::getInstance($portfolioPurchase->getModule(), \Vtiger_Module_Model::getInstance('Documents'));
          ['typeId' => $documentTypeId, 'areaId' => $documentAreaId] = self::getDocumentTypeAndAreaIdByName('Portfolio Purchase Documents');

          $client = Api::getClient(true);
          $envelopeApi = new \DocuSign\eSign\Api\EnvelopesApi($client);
          $documentList = $envelopeApi->listDocuments($accountId, $envelopeId);
          $fileName = $documentList->getEnvelopeDocuments()[0]->getName();
          $combinedDocument = $envelopeApi->getDocument($accountId, 'combined', $envelopeId);
          $filePath = $combinedDocument->getPathname();
          $signerName = $response['data']['envelopeSummary']['recipients']['signers'][0]['name'];
          $signingDateTime = date('Y-m-d H:i:s', strtotime($response['data']['envelopeSummary']['recipients']['signers'][0]['signedDateTime']));

          $message = "Signed by $signerName on $signingDateTime";

          $params = [
            'document_area' => $documentAreaId,
            'document_type' => $documentTypeId,
            'notecontent' => $message,
          ];
          if ($relationModel->getRelationType() == \Vtiger_Relation_Model::RELATION_O2M && !empty($relationModel->getRelationField())) {
            $params[$relationModel->getRelationField()->getName()] = $portfolioPurchase->getId();
          }
          $file = \App\Fields\File::loadFromPath($filePath);
          $file->name = "Signed $fileName";
          ['crmid' => $fileId, 'attachmentsId' => $attachmentId] = \App\Fields\File::saveFromContent($file, $params);
          // add relation to current module
          if ($relationModel->getRelationType() != \Vtiger_Relation_Model::RELATION_O2M || empty($relationModel->getRelationField())) {
            $relationModel->addRelation($portfolioPurchase->getId(), $fileId);
          }
          $combinedDocument = null;

          $status = $portfolioPurchase->get('portfolio_purchase_status');
          if ($status === 'Sent to Seller to be signed') {
            $workflow = $wfs->retrieveByName('Purchased', 'PortfolioPurchases');
            $workflow->performTasks($portfolioPurchase);

            $workflow = $wfs->retrieveByName('Signed document', 'PortfolioPurchases');
            if (!empty($workflow)) {
              $workflow->performTasks($portfolioPurchase);
            }
          } else {
            self::createBatchError(
              'PortfolioPurchases', 
              'Addendum document was signed by Seller, but Portfolio Purchase has an unexpected Status',
              $portfolioPurchaseId,
              "Current status is \"$status\", but expected \"Sent to Seller to be signed\""
            );
          }

          \App\Cache::staticDelete('RecordModel', "$portfolioPurchaseId:PortfolioPurchases");
          $portfolioPurchase = \Vtiger_Record_Model::getInstanceById($portfolioPurchaseId);
          $portfolioPurchase->set('docusign_envelope_id', "completed-$envelopeId");
          $portfolioPurchase->save();
          break;
        case 'envelope-voided':
          $voidedDateTime = date('Y-m-d H:i:s', strtotime($response['data']['envelopeSummary']['voidedDateTime']));
          $voidedReason = $response['data']['envelopeSummary']['voidedReason'];

          $message = "Voided on $voidedDateTime with reason: \"$voidedReason\"";
          self::createBatchError('PortfolioPurchases', 'Document has been voided', $portfolioPurchaseId, $message);

          $portfolioPurchase->set('docusign_envelope_id', "voided-$envelopeId");
          $portfolioPurchase->save();

          $workflow = $wfs->retrieveByName('Document rejected/not signed', 'PortfolioPurchases');
          if (!empty($workflow)) {
            $workflow->performTasks($portfolioPurchase);
          }
          break;
        case 'envelope-declined':
          $signerName = $response['data']['envelopeSummary']['recipients']['signers'][0]['name'];
          $declinedDateTime = date('Y-m-d H:i:s', strtotime($response['data']['envelopeSummary']['recipients']['signers'][0]['declinedDateTime']));
          $declinedReason = $response['data']['envelopeSummary']['recipients']['signers'][0]['declinedReason'];

          \App\Log::warning("Declined $declinedDateTime/$declinedReason");
          $message = "$signerName declined to sign on $declinedDateTime providing following reason: \"$declinedReason\"";
          self::createBatchError('PortfolioPurchases', 'Provider refused to sign document', $portfolioPurchaseId, $message);

          $portfolioPurchase->set('docusign_envelope_id', "declined-$envelopeId");
          $portfolioPurchase->save();

          $workflow = $wfs->retrieveByName('Document rejected/not signed', 'PortfolioPurchases');
          if (!empty($workflow)) {
            $workflow->performTasks($portfolioPurchase);
          }
          break;
        default:
          throw new \Exception("Unexpected status");
      }
    } catch (\Throwable $t) {
      \App\Log::error("App::DocuSign::Api::callbackHandler:ERROR " . var_export(['response' => $rawResponse, 'error' => $t], true));
      
      $message = $t->getMessage();
      self::createBatchError('PortfolioPurchases', 'Unexpected error when signing document', $portfolioPurchaseId, $message);
    } finally {
      if (!empty($currentUserId)) {
        \App\User::setCurrentUserId($currentUserId);
      }
    }
  }

  private static function createBatchError(string $moduleName, string $error, ?int $item = null, ?string $nameription = null) {
		\App\Log::warning("App::DocuSign::Api::createBatchErrorEntryRaw:$moduleName/$item/$error");

		try {
			$entry = \Vtiger_Record_Model::getCleanInstance('BatchErrors');
			$entry->set('task_type', 'DocuSign Interface');
			$entry->set('mod_name', $moduleName);
			$entry->set('error_message', \App\Purifier::encodeHtml($error));
			if ($item) {
				$entry->set('item', $item);
			}
			if ($nameription) {
				$entry->set('error_description', \App\Purifier::encodeHtml($nameription));
			}
			$entry->save();
		} catch (\Exception $e) {
			\App\Log::error("VTWorkflowUtils::createBatchErrorEntryRaw:$moduleName/$item/$error/$nameription");
		}
	}

  private static function getPortfolioPurchaseIdByEnvelopeId(string $envelopeId) {
    $portfolioPurchaseId = (new \App\QueryGenerator('PortfolioPurchases'))->addCondition('docusign_envelope_id', $envelopeId, 'e')->setField('id')->createQuery()->scalar();

    return $portfolioPurchaseId;
  }

  public static function getDocumentTypeAndAreaIdByName(string $typeName) {
    $result = (new \App\QueryGenerator('DocumentTypes'))->addCondition('document_type', $typeName, 'e')->setFields(['id', 'document_area'])->createQuery()->one();

    return ['typeId' => $result['id'], 'areaId' => $result['document_area']];
  }
}

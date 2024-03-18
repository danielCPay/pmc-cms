<?php
/**
 * Quickbooks API wrapper class.
 *
 * @package   App\Utils
 *
 * @copyright DOT Systems Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Kamiński <mkaminski@dotsystems.pl>
 */

namespace App\QuickBooks;

require_once 'modules/com_vtiger_workflow/VTWorkflowUtils.php';

use QuickBooksOnline\API\DataService\DataService;

class Api
{
  public static function getDataService($raw = false) {
    $dataService = DataService::Configure([
      'auth_mode' => 'oauth2',
      'ClientID' => \App\Config::quickbooks('clientId'),
      'ClientSecret' => \App\Config::quickbooks('clientSecret'),
      'RedirectURI' => \App\Config::quickbooks('redirectUri'),
      'scope' => 'com.intuit.quickbooks.accounting',
      'baseUrl' => \App\Config::quickbooks('baseUrl')
    ]);

    if (!$raw) {
      if (\App\Session::has('qbAccessToken')) {
        /** @var \QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2AccessToken $token */
        $token = \App\Session::get('qbAccessToken');
        $accessTokenExpirationString = $token->getAccessTokenExpiresAt();
        $accessTokenExpiration = strtotime($accessTokenExpirationString);
        // refresh token 10 minutes before expiration
        if (!$accessTokenExpiration || $accessTokenExpiration < (time() + 600)) {
          if (!self::refreshToken($dataService)) {
            throw new \Exception("Please login to QuickBooks, see notification");
          }
        }
        $dataService->updateOAuth2Token($token);
      } else if (!self::refreshToken($dataService)) {
        throw new \Exception("Please login to QuickBooks, see notification");
      }
    }

    return $dataService;
  }

  public static function hasAccessToken() {
    $dataService = \App\QuickBooks\Api::getDataService();
    $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();

    try {
      $OAuth2LoginHelper->getAccessToken();
    } catch (\Exception $e) {
      return false;
    }

    return true;
  }

  public static function handleAuthorizationCode(string $code, string $realmId) {
    \App\Log::warning("App::QuickBooks::Api::handleAuthorizationCode:$realmId");

    $dataService = self::getDataService(true);

    $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
    $accessToken = $OAuth2LoginHelper->exchangeAuthorizationCodeForToken($code, $realmId);
    $dataService->updateOAuth2Token($accessToken);

    self::saveConfig($accessToken, $realmId);

    return $dataService;
  }

  public static function refreshToken($dataService = null) {
    \App\Log::warning("App::QuickBooks::Api::refreshToken");

    $dataService = $dataService ?? self::getDataService();
    $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();

    $refreshToken = \App\Config::quickbooks('refreshToken');
    $realmId = \App\Config::quickbooks('realmId');

    $error = true;

    // detect failure or missing refresh token, print auth url to log and create notification
    if ($refreshToken) {
      try {
        $accessToken = $OAuth2LoginHelper->refreshAccessTokenWithRefreshToken($refreshToken);

        if (empty($accessToken->getRealmID())) {
          $accessToken->setRealmID($realmId);
        }

        $dataService->updateOAuth2Token($accessToken);

        self::saveConfig($accessToken, $realmId);

        $error = false;
      } catch (\QuickBooksOnline\API\Exception\IdsException $e) {
        \App\Log::error("App::QuickBooks::Api::refreshToken:" . $e->getMessage());
        \App\Log::error(var_export($e, true));
        
        self::saveConfig('', '');
      }
    } 

    if ($error) {
      $authUrl = $OAuth2LoginHelper->getAuthorizationCodeURL();

      \App\Log::error("QuickBooks login required: $authUrl");
      
      \VTWorkflowUtils::createNotificationRaw([\App\User::getCurrentUserId()], 'QuickBooks login required', 'Please go to <a href="' . $authUrl . '">QuickBooks</a> to authorize CMS access.', 'PLL_USERS');
    }

    return !$error;
  }

  public static function revokeAccessToken() {
    \App\Log::warning("App::QuickBooks::Api::revokeAccessToken");

    if (self::hasAccessToken()) {
      $oauth2LoginHelper = self::getDataService()->getOAuth2LoginHelper();
      $accessToken = $oauth2LoginHelper->getAccessToken();

      if ($accessToken) {
        $oauth2LoginHelper->revokeToken($accessToken->getAccessToken());
      }
    }
  }

  public static function getCompanyInfo() {
    $dataService = self::getDataService();

    return $dataService->getCompanyInfo();
  }

  /**
   * @return \QuickBooksOnline\API\Data\IPPJournalEntry
   */
  public static function createJournalEntry(array $creditLines, array $debitLines) {
    \App\Log::warning("App::QuickBooks::Api::createJournalEntry:" . var_export(['creditLines' => $creditLines, 'debitLines' => $debitLines], true));

    if (empty($creditLines) || empty($debitLines)) {
      throw new \Exception("At least one line of each type required");
    }

    $dataService = self::getDataService();
    $dataService->throwExceptionOnError(true);

    $plainJournalRequest = [ 'Line' => [] ];

    $handleLine = function($line, $type) use ($dataService) {
      $account = self::getAccountByName($dataService, $line['accountName']);
      if (!$account) {
        throw new \Exception("$type account '{$line['accountName']}' not found");
      }

      $entity = null;
      if ($line['customer']) {
        $customer = self::getCustomerByName($dataService, $line['customer']);

        if (!$customer) {
          throw new \Exception("Customer '{$line['customer']}' not found");
        }

        $entity = [
          'EntityRef' => ['value' => $customer->Id, 'name' => $customer->DisplayName],
          'Type' => 'Customer'
        ];
      } else if ($line['vendor']) {
        $vendor = self::getVendorByName($dataService, $line['vendor']);
        
        if (!$vendor) {
          throw new \Exception("Vendor '{$line['vendor']}' not found");
        }

        $entity = [
          'EntityRef' => ['value' => $vendor->Id, 'name' => $vendor->DisplayName],
          'Type' => 'Vendor'
        ];
      }

      return [
        'Id' => '0',
        'Description' => $line['description'],
        'Amount' => $line['amount'],
        'DetailType' => 'JournalEntryLineDetail',
        'JournalEntryLineDetail' => [
          'PostingType' => $type,
          'AccountRef' => [ 'value' => $account->Id ],
          'Entity' => $entity
        ]
      ];
    };

    foreach ($creditLines as $creditLine) {
      $plainJournalRequest['Line'][] = $handleLine($creditLine, 'Credit');
    }

    foreach ($debitLines as $debitLine) {
      $plainJournalRequest['Line'][] = $handleLine($debitLine, 'Debit');
    }

    \App\Log::warning("App::QuickBooks::Api::createJournalEntry - prepared facade " . var_export($plainJournalRequest, true));
    
    $journalRequest = \QuickBooksOnline\API\Facades\JournalEntry::create($plainJournalRequest);

    \App\Log::info("App::QuickBooks::Api::createJournalEntry - creating entry using " . var_export($journalRequest, true));

    $journal = $dataService->Add($journalRequest);

    $error = $dataService->getLastError();
    if ($error) {
      // log and report error
      throw new \Exception($error);
    } else {
      \App\Log::warning("App::QuickBooks::Api::createJournalEntry - created entry {$journal->Id}");
    }

    return $journal;
  }

  /**
   * @return \QuickBooksOnline\API\Data\IPPAccount
   */
  public static function createAccount(string $accountName, string $accountType, ?string $accountSubType, string $parentAccountName = '', ?string $description) {
    \App\Log::warning("App::QuickBooks::Api::createAccount:$accountName/$accountType/$accountSubType/$parentAccountName/$description");

    $dataService = self::getDataService();
    $dataService->throwExceptionOnError(true);

    $parentAccountId = null;
    if ($parentAccountName) {
      $result = self::getAccountByName($dataService, $parentAccountName);
      if ($result) {
        $parentAccountId = $result->Id;
      } else {
        throw new \Exception("Parent account not found");
      }
    }

    $account = self::getAccountByName($dataService, trim("$parentAccountName:$accountName", ':'));
    if ($account) {
      /** @var \QuickBooksOnline\API\Data\IPPAccount $account */

      // already exists
      \App\Log::warning("App::QuickBooks::Api::createAccount - already exists {$account->Id}/{$account->AccountType}/{$account->AccountSubType}/" . var_export($account->ParentRef, true));

      // check if matches, update if necessary
      if ($account->AccountType != $accountType 
        || $account->AccountSubType != $accountSubType 
        || ($parentAccountId && $account->ParentRef != $parentAccountId)
        || (!$parentAccountId && $account->ParentRef)
      ) {
        // update
        $plainAccountRequest = [
          'sparse' => true,
          'AccountType' => $accountType,
          'AccountSubType' => $accountSubType,
          'ParentRef' => $parentAccountId ? ['value' => $parentAccountId] : null,
          'Description' => $description,
        ];
        
        \App\Log::warning("App::QuickBooks::Api::createAccount - prepared update facade " . var_export($plainAccountRequest, true));
        
        $accountRequest = \QuickBooksOnline\API\Facades\Account::update($account, array_filter($plainAccountRequest));

        \App\Log::info("App::QuickBooks::Api::createAccount - updating account using " . var_export($accountRequest, true));

        $account = $dataService->Update($accountRequest);
        $error = $dataService->getLastError();
        if ($error) {
          // log and report error
          throw new \Exception($error);
        } else {
          // created account $account->Id;
          \App\Log::warning("App::QuickBooks::Api::createAccount - updated account {$account->Id}");
        }
      }

      return $account;
    } else {
      $plainAccountRequest = [
        'AccountType' => $accountType,
        'AccountSubType' => $accountSubType,
        'Name' => $accountName,
        'ParentRef' => $parentAccountId ? ['value' => $parentAccountId] : null,
        'Description' => $description,
      ];

      \App\Log::warning("App::QuickBooks::Api::createAccount - prepared facade " . var_export($plainAccountRequest, true));

      $accountRequest = \QuickBooksOnline\API\Facades\Account::create(array_filter($plainAccountRequest));

      \App\Log::info("App::QuickBooks::Api::createAccount - creating account using " . var_export($accountRequest, true));

      $account = $dataService->Add($accountRequest);
      $error = $dataService->getLastError();
      if ($error) {
        // log and report error
        throw new \Exception($error);
      } else {
        // created account $account->Id;
        \App\Log::warning("App::QuickBooks::Api::createAccount - created account {$account->Id}");
      }
    }

    return $account;
  }

  /**
   * @return \QuickBooksOnline\API\Data\IPPCustomer
   */
  public static function createCustomer(string $customerName, string $companyName, string $notes) {
    \App\Log::warning("App::QuickBooks::Api::createCustomer:$customerName/$companyName/$notes");

    $dataService = self::getDataService();
    $dataService->throwExceptionOnError(true);

    $customer = self::getCustomerByName($dataService, $customerName);
    if ($customer) {
      /** @var \QuickBooksOnline\API\Data\IPPCustomer $customer */

      // already exists
      \App\Log::warning("App::QuickBooks::Api::createCustomer - already exists {$customer->Id}/{$customer->CompanyName}/{$customer->Notes}");

      // check if matches, update if necessary
      if ($customer->CompanyName != $companyName 
        || $customer->Notes != $notes
      ) {
        // update
        $plainCustomerRequest = [
          'sparse' => true,
          'CompanyName' => $companyName,
          'Notes' => $notes,
        ];
        
        \App\Log::warning("App::QuickBooks::Api::createCustomer - prepared update facade " . var_export($plainCustomerRequest, true));
        
        $customerRequest = \QuickBooksOnline\API\Facades\Customer::update($customer, $plainCustomerRequest);

        \App\Log::info("App::QuickBooks::Api::createCustomer - updating customer using " . var_export($customerRequest, true));

        $customer = $dataService->Update($customerRequest);
        $error = $dataService->getLastError();
        if ($error) {
          // log and report error
          throw new \Exception($error);
        } else {
          \App\Log::warning("App::QuickBooks::Api::createCustomer - updated account {$customer->Id}");
        }
      }

      return $customer;
    } else {
      $plainCustomerRequest = [
        'DisplayName' => $customerName,
        'CompanyName' => $companyName,
        'Notes' => $notes,
      ];

      \App\Log::warning("App::QuickBooks::Api::createCustomer - prepared facade " . var_export($plainCustomerRequest, true));

      $customerRequest = \QuickBooksOnline\API\Facades\Customer::create($plainCustomerRequest);

      \App\Log::info("App::QuickBooks::Api::createCustomer - creating customer using " . var_export($customerRequest, true));

      $customer = $dataService->Add($customerRequest);
      $error = $dataService->getLastError();
      if ($error) {
        // log and report error
        throw new \Exception($error);
      } else {
        \App\Log::warning("App::QuickBooks::Api::createCustomer - created customer {$customer->Id}");
      }
    }

    return $customer;
  }

  /**
   * @return \QuickBooksOnline\API\Data\IPPVendor
   */
  public static function createVendor(string $vendorName, string $companyName, string $notes) {
    \App\Log::warning("App::QuickBooks::Api::createVendor:$vendorName/$companyName/$notes");

    $dataService = self::getDataService();
    $dataService->throwExceptionOnError(true);

    $vendor = self::getVendorByName($dataService, $vendorName);
    if ($vendor) {
      /** @var \QuickBooksOnline\API\Data\IPPVendor $vendor */

      // already exists
      \App\Log::warning("App::QuickBooks::Api::createvendor - already exists {$vendor->Id}/{$vendor->CompanyName}/{$vendor->Notes}");

      // check if matches, update if necessary
      if ($vendor->CompanyName != $companyName 
        || $vendor->Notes != $notes
      ) {
        // update
        $plainVendorRequest = [
          'sparse' => true,
          'CompanyName' => $companyName,
          'Notes' => $notes,
        ];
        
        \App\Log::warning("App::QuickBooks::Api::createVendor - prepared update facade " . var_export($plainVendorRequest, true));
        
        $vendorRequest = \QuickBooksOnline\API\Facades\Vendor::update($vendor, $plainVendorRequest);

        \App\Log::info("App::QuickBooks::Api::createVendor - updating vendor using " . var_export($vendorRequest, true));

        $vendor = $dataService->Update($vendorRequest);
        $error = $dataService->getLastError();
        if ($error) {
          // log and report error
          throw new \Exception($error);
        } else {
          \App\Log::warning("App::QuickBooks::Api::createVendor - updated account {$vendor->Id}");
        }
      }

      return $vendor;
    } else {
      $plainVendorRequest = [
        'DisplayName' => $vendorName,
        'CompanyName' => $companyName,
        'Notes' => $notes,
      ];

      \App\Log::warning("App::QuickBooks::Api::createVendor - prepared facade " . var_export($plainVendorRequest, true));

      $vendorRequest = \QuickBooksOnline\API\Facades\Vendor::create($plainVendorRequest);

      \App\Log::info("App::QuickBooks::Api::createVendor - creating vendor using " . var_export($vendorRequest, true));

      $vendor = $dataService->Add($vendorRequest);
      $error = $dataService->getLastError();
      if ($error) {
        // log and report error
        throw new \Exception($error);
      } else {
        \App\Log::warning("App::QuickBooks::Api::createVendor - created vendor {$vendor->Id}");
      }
    }

    return $vendor;
  }

  /**
   * @return \QuickBooksOnline\API\Data\IPPAccount
   */
  private static function getAccountByName($dataService, $accountName) {
    $accountArray = $dataService->Query("select * from Account where FullyQualifiedName='" . $accountName . "'");
    $error = $dataService->getLastError();
    if ($error) {
      throw new \Exception($error);
    } else if (is_array($accountArray) && sizeof($accountArray) > 0) {
      return current($accountArray);
    }
  }

  /**
   * @return \QuickBooksOnline\API\Data\IPPCustomer
   */
  private static function getCustomerByName($dataService, $customerName) {
    $customerArray = $dataService->Query("select * from Customer where DisplayName='" . $customerName . "'");
    $error = $dataService->getLastError();
    if ($error) {
      throw new \Exception($error);
    } else if (is_array($customerArray) && sizeof($customerArray) > 0) {
      return current($customerArray);
    }
  }

  /**
   * @return \QuickBooksOnline\API\Data\IPPVendor
   */
  private static function getVendorByName($dataService, $vendorName) {
    $vendorArray = $dataService->Query("select * from Vendor where DisplayName='" . $vendorName . "'");
    $error = $dataService->getLastError();
    if ($error) {
      throw new \Exception($error);
    } else if (is_array($vendorArray) && sizeof($vendorArray) > 0) {
      return current($vendorArray);
    }
  }

  public static function getAccountBalance(string $accountName) {
    \App\Log::warning("App::QuickBooks::Api::getAccountBalance:$accountName");

    $dataService = self::getDataService();
    $dataService->throwExceptionOnError(true);

    $response = $dataService->Query("select CurrentBalanceWithSubAccounts from Account where FullyQualifiedName = '$accountName'");
    $error = $dataService->getLastError();
    if ($error) {
      throw new \Exception($error);
    } else if (is_array($response) && sizeof($response) > 0) {
      return floatval(current($response)->CurrentBalanceWithSubAccounts);
    }

    return 0;
  }

  private static function saveConfig($accessToken, $realmId = null) {
    // save refresh token and realm
    $config = new \App\ConfigFile('quickbooks');
    $config->set('realmId', $realmId ?? ($accessToken ? $accessToken->getRealmID() : ''));
    $config->set('refreshToken', $accessToken ? (\is_string($accessToken) ? $accessToken : $accessToken->getRefreshToken()) : '');
    $config->create();

    // save access token in session
    \App\Session::set('qbAccessToken', $accessToken);
  }
}

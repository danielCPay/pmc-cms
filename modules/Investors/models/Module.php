<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): DOT Systems sp. z o.o..
 * *********************************************************************************** */

class Investors_Module_Model extends Vtiger_Module_Model
{
  public static $accountStructure = [
    'PMC Funding 2021, LLC' => [
      'accounts' => [
        'Cash' => [
          'type' => 'Bank',
          'detailType' => 'CashOnHand',
          'description' => 'Cash the business keeps on hand, like petty cash',
        ],
        '[Provider]' => [
          'type' => 'Accounts Receivable',
          'detailType' => 'AccountsReceivable',
          'accounts' => [
            '[Portfolio Purchase]' => [
              'type' => 'Accounts Receivable',
              'detailType' => 'AccountsReceivable',
              'accounts' => [
                'Purchase Price' => [
                  'type' => 'Accounts Receivable',
                  'detailType' => 'AccountsReceivable',
                  'accounts' => [
                    'Purchase Collection' => [
                      'type' => 'Accounts Receivable',
                      'detailType' => 'AccountsReceivable',
                    ],
                  ]
                ],
                'Factor Fee Receivable' => [
                  'type' => 'Accounts Receivable',
                  'detailType' => 'AccountsReceivable',
                  'description' => 'Factor Fee Receivable',
                  'accounts' => [
                    'Factor Fee Collection' => [
                      'type' => 'Accounts Receivable',
                      'detailType' => 'AccountsReceivable',
                    ],
                  ]
                ],
              ]
            ]
          ],
        ],
        '[Portfolio Purchase] Deferred Factor Fee' => [
          'type' => 'Other Current Liability',
          'detailType' => 'DeferredRevenue',
        ],
        '[Portfolio Purchase] Realized Factor Fee' => [
          'type' => 'Income',
          'detailType' => 'ServiceFeeIncome',
        ],
        '[Portfolio Purchase] Excess Hurdle Payable' => [
          'type' => 'Accounts Payable',
          'detailType' => 'AccountsPayable',
          'description' => 'Excess Hurdle Payable'
        ],
      ]
    ]
  ];

  public static function prepareData(PortfolioPurchases_Record_Model $portfolioPurchase)
  {
    $portfolioPurchaseName = $portfolioPurchase->get('portfolio_purchase_name');
    $providerId = $portfolioPurchase->get('provider');

    if (\App\Record::isExists($providerId, 'Providers')) {
      $provider = Vtiger_Record_Model::getInstanceById($providerId, 'Providers');
    } else {
      throw new \Exception("Provider $providerId does not exist");
    }

    $providerName = $provider->get('provider_name');

    return [
      '[Provider]' => $providerName,
      '[Portfolio Purchase]' => $portfolioPurchaseName
    ];
  }

  public static function processAccountName(string $accountName, array $data)
  {
    return str_replace(array_keys($data), array_values($data), $accountName);
  }

  public static function ensureAccounts(PortfolioPurchases_Record_Model $portfolioPurchase)
  {
    \App\Log::warning("Investors::ensureAccounts({$portfolioPurchase->getId()})");

    $data = self::prepareData($portfolioPurchase);

    $investorId = $portfolioPurchase->get('investor');
    if (\App\Record::isExists($investorId, 'Investors')) {
      $investor = Vtiger_Record_Model::getInstanceById($investorId, 'Investors');
    } else {
      throw new \Exception("Investor $investorId does not exist");
    }
    $investorName = $investor->get('investor_name');
   
    $accounts = self::$accountStructure[$investorName];
    if (empty($accounts)) {
      throw new \Exception("Investor $investorName is not supported");
    }

    self::ensureAccount($accounts['accounts'], $data);
  }

  private static function ensureAccount(array $accounts, $data, string $parentAccount = '')
  {
    foreach ($accounts as $accountName => $account) {
      ['type' => $type, 'detailType' => $detailType] = $account;
      $description = $account['description'] ?? null;
      $subAccounts = $account['accounts'] ?? null;

      $processedAccountName = self::processAccountName($accountName, $data);

      \App\Log::warning("Investors::ensureAccount('$accountName'/'$processedAccountName', $type, $detailType, $description, $parentAccount)");

      \App\QuickBooks\Api::createAccount($processedAccountName, $type, $detailType, $parentAccount, $description);

      if (!empty($subAccounts)) {
        self::ensureAccount($subAccounts, $data, trim("$parentAccount:$processedAccountName", ':'));
      }
    }
  }
}

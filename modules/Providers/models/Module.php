<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): DOT Systems Sp. z o.o
 * *********************************************************************************** */

class Providers_Module_Model extends Vtiger_Module_Model
{
  /**
   * Refreshes number_of_contacts_with_same_email column for all Providers.
   */
	public static function refreshNumberOfContactsWithSameEmail() {
    \App\Log::warning("Providers::refreshNumberOfContactsWithSameEmail");

    $providers = (new \App\QueryGenerator('Providers'))
      ->setFields(['id', 'email', 'number_of_contacts_same_email'])
      ->createQuery()
      ->all();

    $providersToCheck = [];
    foreach ($providers as &$provider) {
      $emails = [$provider['email']];
      // read contact emails
      $contacts = (new \App\QueryGenerator('ProviderContacts'))->setFields(['email'])->createQuery()->andWhere(['provider' => $provider['id']])->all();
      foreach($contacts as $contact) {
        $emails[] = $contact['email'];
      }

      $emails = array_filter($emails);

      if (!empty($emails)) {
        $providersToCheck[] = ['id' => $provider['id'], 'emails' => $emails, 'number_of_contacts_same_email' => $provider['number_of_contacts_same_email']];
      }
    }

    foreach ($providersToCheck as $provider) {
      $duplicates = 0;
      foreach ($providersToCheck as $secondProvider) {
        if ($secondProvider['id'] === $provider['id']) {
          continue;
        }
        foreach ($provider['emails'] as $email) {
          if (\in_array($email, $secondProvider['emails'])) {
            $duplicates++;
          }
        }
      }

      if ($duplicates != $provider['number_of_contacts_same_email']) {
        echo "DIFFERENCE $duplicates for {$provider['id']}" . PHP_EOL;
        $recordModel = Vtiger_Record_Model::getInstanceById($provider['id']);
        $recordModel->set('number_of_contacts_same_email', $duplicates > 0 ? $duplicates : null);
        $recordModel->save();
      }
    }
  }
}

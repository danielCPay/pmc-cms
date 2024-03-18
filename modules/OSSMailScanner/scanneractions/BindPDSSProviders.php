<?php

require_once 'modules/com_vtiger_workflow/include.php';

/**
 * Mail scanner action bind Bizon modules.
 *
 * @copyright DOT Systems Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Michał Kamiński <mkaminski@dotsystems.pl>
 */
class OSSMailScanner_BindPDSSProviders_ScannerAction extends OSSMailScanner_PrefixScannerAction_Model
{
  /**
	 * Module name.
	 *
	 * @var string
	 */
	public $moduleName = 'Providers';

	/**
	 * Table name.
	 *
	 * @var string
	 */
	public $tableName = 'u_yf_providers';

  public function process(OSSMail_Mail_Model $mail)
	{
		$this->mail = $mail;

    \App\Log::warning("OSSMailScanner::BindPDSSProviders:". print_r([
      'uniqueId' => $this->mail->getUniqueId(), 
      'crmId' => $this->mail->getMailCrmId(), 
      'folder' => $this->mail->getFolder(), 
      'type' => $this->mail->getTypeEmail(), 
      'owner' => $this->mail->getAccountOwner(), 
      'to' => $this->mail->get('to_email'), 
      'from' => $this->mail->get('from_email'), 
      'subject' => $this->mail->get('subject'), 
      'date' => $this->mail->get('date')
    ], true));

    // active by name
    $recordIds = $this->findAndBindByPrefix($this->tableName, 'provider_name', ["$this->tableName.is_active" => 1]);
    // active by abbreviation
    $recordIds = array_merge($recordIds, $this->findAndBindByPrefix($this->tableName, 'provider_abbreviation', ["$this->tableName.is_active" => 1]));
    
    if (empty($recordIds)) {
      // active by e-mail or contact e-mail
      $recordIds = $this->addByEmail(1);

      if (empty($recordIds)) {
        // inactive by name
        $recordIds = $this->findAndBindByPrefix($this->tableName, 'provider_name', ["$this->tableName.is_active" => 0]);
        // inactive by abbreviation
        $recordIds = array_merge($recordIds, $this->findAndBindByPrefix($this->tableName, 'provider_abbreviation', ["$this->tableName.is_active" => 0]));

        if (empty($recordIds)) {
          // inactive by e-mail or contact e-mail
          $recordIds = $this->addByEmail(0);
        }
      }
    }

		if (!empty($recordIds)) {
      $mail = Vtiger_Record_Model::getInstanceById($this->mail->getMailCrmId());
			$shareWith = explode(',', $mail->get('shownerid'));

			foreach ($recordIds as $matchId) {
				$match = Vtiger_Record_Model::getInstanceById($matchId);
				
				// get Assigned To, append to Share with
				$shareWith[] = $match->get('assigned_user_id');
			}
			
			if (!empty($shareWith)) {
				$mail->set('shownerid', array_unique(array_filter($shareWith)));
				$mail->save();
			}

			if ($this->mail->getTypeEmail() != 'Sent') {
				$wfs = new VTWorkflowManager();

				$workflow = $wfs->retrieveByName('New e-mail received (Provider)', 'Providers');
				
				foreach (array_unique($recordIds) as $pId) {
					$recordModel = Vtiger_Record_Model::getInstanceById($pId);

					\App\Log::info("New mail received for Provider $pId");

					$workflow->performTasks($recordModel);
				}
			}
    }

		\App\Log::info("OSSMailScanner::BindPDSSProviders:matched". print_r($recordIds, true));

    return $recordIds;
	}

  private function findAndBindByPrefix($tableName, $tableColumn, $additionalWhere)
	{
		$mailId = $this->mail->getMailCrmId();
		$recordId = [];
		if ($mailId) {
			$query = (new \App\Db\Query())->select(['vtiger_ossmailview_relation.crmid'])
				->from('vtiger_ossmailview_relation')
				->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = vtiger_ossmailview_relation.crmid')
				->where(['ossmailviewid' => $mailId, 'setype' => $this->moduleName])
				->andWhere(['<>', 'vtiger_crmentity.deleted', 1])
				->orderBy(['modifiedtime' => \SORT_DESC]);
			$returnIds = $query->column();
			if (!empty($returnIds)) {
				$recordId = $returnIds;
			} else {
				$this->prefix = \App\Mail\RecordFinder::getRecordNumberFromString($this->mail->get('subject'), $this->moduleName, false, false);
				if ($this->prefix) {
					$recordId = $this->addByPrefix($tableName, $tableColumn, $additionalWhere);
				}
			}
		}
		return $recordId;
	}

  private function addByPrefix($tableName, $tableColumn, $additionalWhere)
	{
		$returnIds = [];
		$crmId = false;
		if (\App\Cache::has('getRecordByPrefix', $this->prefix)) {
			$crmId = \App\Cache::get('getRecordByPrefix', $this->prefix);
		} else {
			$moduleObject = CRMEntity::getInstance($this->moduleName);
			$tableIndex = $moduleObject->tab_name_index[$tableName];
			$query = (new \App\Db\Query())
				->select([$tableIndex])
				->from($tableName)
				->innerJoin('vtiger_crmentity', "$tableName.$tableIndex = vtiger_crmentity.crmid")
				->where([$tableName . '.' . $tableColumn => $this->prefix]);
			if (!\in_array($this->moduleName, ['Claims', 'ClaimOpportunities'])) {
				$query = $query->andWhere(array_merge($additionalWhere, ['vtiger_crmentity.deleted' => 0]));
			}
			$query = $query->orderBy(['modifiedtime' => \SORT_DESC]);
			$crmId = $query->scalar();
			if ($crmId) {
				\App\Cache::save('getRecordByPrefix', $this->prefix, $crmId, \App\Cache::LONG);
			}
		}
		if ($crmId) {
			$status = (new OSSMailView_Relation_Model())->addRelation($this->mail->getMailCrmId(), $crmId, $this->mail->get('date'));
			if ($status) {
				$returnIds[] = $crmId;
			}
		}
		return $returnIds;
	}

  private function addByEmail($isActive) {
    $recordIds = [];

    // active by e-mail
    $providerIds = $this->mail->findEmailAdress('from_email', 'Providers', true);
    $providerIds = array_merge($this->mail->findEmailAdress('to_email', 'Providers', true), $providerIds);
    // active by contact e-mail
    $providerContactIds = $this->mail->findEmailAdress('from_email', 'ProviderContacts', true);
    $providerContactIds = array_merge($this->mail->findEmailAdress('to_email', 'ProviderContacts', true), $providerContactIds);
    foreach($providerContactIds as $providerContactId) {
      $recordModel = Vtiger_Record_Model::getInstanceById($providerContactId);
      $providerIds[] = $recordModel->get('provider');
    }
    foreach(array_unique($providerIds) as $providerId) {
      if (!empty($providerId)) {
        $recordModel = Vtiger_Record_Model::getInstanceById($providerId);
        if ($recordModel->get('is_active') == $isActive) {
          $status = (new OSSMailView_Relation_Model())->addRelation($this->mail->getMailCrmId(), $providerId, $this->mail->get('date'));
          if ($status) {
            $recordIds[] = $providerId;
          }
        }
      }
    }

    return $recordIds;
  }
}

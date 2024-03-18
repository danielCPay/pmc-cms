<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

//A collection of util functions for the workflow module

/**
 * Class vTWorkflowUtils.
 */
class VTWorkflowUtils
{
	/**
	 * User stack.
	 *
	 * @var array
	 */
	public static $userStack;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		if (empty(self::$userStack)) {
			self::$userStack = [];
		}
	}

	/**
	 * Check whether the given identifier is valid.
	 *
	 * @param string $identifier Description
	 */
	public function validIdentifier($identifier)
	{
		if (\is_string($identifier)) {
			return preg_match('/^[a-zA-Z][a-zA-Z_0-9]+$/', $identifier);
		}
		return false;
	}

	/** function to check if the module has workflow.
	 * @param string $modulename - name of the module
	 */
	public static function checkModuleWorkflow($modulename)
	{
		return (new \App\Db\Query())->from('vtiger_tab')->where(['NOT IN', 'name', ['Calendar', 'Faq', 'Users']])->andWhere(['isentitytype' => 1, 'presence' => 0, 'tabid' => \App\Module::getModuleId($modulename)])->exists();
	}

	/**
	 * Get modules.
	 *
	 * @return array
	 */
	public function vtGetModules()
	{
		$query = (new \App\Db\Query())->select(['vtiger_field.tabid', 'name'])->from('vtiger_field')->innerJoin('vtiger_tab', 'vtiger_field.tabid=vtiger_tab.tabid')->where(['vtiger_tab.isentitytype' => 1, 'vtiger_tab.presence' => [0, 2]])->distinct();
		$modules = [];
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$modules[] = $row['name'];
		}
		return $modules;
	}

	/**
	 * Common function for generating a document from PDF template and properly relating it with record
	 * 
	 * @param Vtiger_Record_Model $recordModel
	 * @param int|string $pdfTemplate
	 * 
	 * @return int File id in CRM
	 */
	public static function generateDocument(Vtiger_Record_Model $recordModel, $pdfTemplate): int {
		\App\Log::warning("VTWorkflowUtils::generateDocument:" . $recordModel->getModule()->getName() . "/" . $recordModel->getId() . ":" . $pdfTemplate);
		if (!\is_numeric($pdfTemplate)) {
			$pdfTemplate = Vtiger_PDF_Model::getIdByName($pdfTemplate, $recordModel->getId());
		}
		\App\Log::warning("VTWorkflowUtils::generateDocument:template $pdfTemplate");
		
		$filePath = 'cache' . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR;
		$tmpFileName = tempnam($filePath, 'PDF' . hrtime(true));
		$filePath .= basename($tmpFileName);
		Vtiger_PDF_Model::exportToPdf($recordModel->getId(), $pdfTemplate, $filePath, 'F');
		if (!file_exists($filePath)) {
			App\Log::error('An error occurred while generating PFD file, the file doesn\'t exist. Sending email with PDF has been blocked.');
			return false;
		}
		$templateRecord = Vtiger_PDF_Model::getInstanceById($pdfTemplate);
		$templateRecord->setVariable('recordId', $recordModel->getId());
		if (!$templateRecord->isEmpty('filename')) {
			$fileName = \App\Fields\File::sanitizeUploadFileName($templateRecord->parseVariables($templateRecord->get('filename'))) . '.pdf';
		} else {
			$fileName = time() . '.pdf';
		}

		$documentType = $templateRecord->get('document_type');
		if (!empty($documentType)) {
			$areaDependents = \App\Fields\Picklist::getPicklistDependencyDatasource('Documents')['document_area'];
			$documentArea = \App\Utils::recursive_array_search($documentType, $areaDependents);
		}

		try {
			$relationModel = Vtiger_Relation_Model::getInstance($recordModel->getModule(), Vtiger_Module_Model::getInstance('Documents'));

			$params = [
				'document_area' => $documentArea,
				'document_type' => $documentType,
				'source' => 'Generated automaticall',
				'generate_from_template' => $templateRecord->get('primary_name'),
				'access_through_cp' => 'Not visible'
			];

			if ($relationModel->getRelationType() == Vtiger_Relation_Model::RELATION_O2M && !empty($relationModel->getRelationField())) {
				$params[$relationModel->getRelationField()->getName()] = $recordModel->getId();
			}

			// create document
			$file = \App\Fields\File::loadFromPath($filePath);
			$file->name = $fileName;
			['crmid' => $fileId, 'attachmentsId' => $attachmentId] = \App\Fields\File::saveFromContent($file, $params);
			// add relation to current module
			if ($relationModel->getRelationType() != Vtiger_Relation_Model::RELATION_O2M || empty($relationModel->getRelationField())) {
				$relationModel->addRelation($recordModel->getId(), $fileId);
			}

			$fileIds[] = $fileId;
		} catch (Error $e) {
			\App\Log::error('Problem creating relation for file ' . $filePath . ' (' . $fileName . ') with id ' . $fileId);
			throw $e;
		}
		\App\Log::warning("VTWorkflowUtils::generateDocument:file $fileId");

		return $fileId;
	}

	/**
	 * Common function for generating multiple documents and sending them using email template.
	 * 
	 * @param Vtiger_Record_Model $recordModel
	 * @param int $emailTemplate
	 * @param array $pdfTemplates
	 * @param string $toAddress
	 */
	public static function prepareAndSendDocuments(Vtiger_Record_Model $recordModel, int $emailTemplate, array $pdfTemplates, string $toAddress) {
		\App\Log::warning("VTWorkflowUtils::prepareAndSendDocument:" . $recordModel->getModule()->getName() . "/" . $recordModel->getId() . ":$emailTemplate:" . var_export($pdfTemplates, true) . ":$toAddress");
		$fileIds = [];
		$pdfTemplateIds = [];
		foreach($pdfTemplates as $templateId) {
			if (!\is_numeric($templateId)) {
				$templateId = Vtiger_PDF_Model::getIdByName($templateId, $recordModel->getId());
			}
			$pdfTemplateIds[] = $templateId;
		}
		// generate PDFs
		foreach ($pdfTemplateIds as $templateId) {
			$fileIds[] = self::generateDocument($recordModel, $templateId);
		}

		// send e-mail
		$mailerContent = [];
		$emailParser = \App\EmailParser::getInstanceByModel($recordModel);
		$mailerContent['to'] = $emailParser->setContent($toAddress)->parse()->getContent(true);
		unset($emailParser);
		$mailerContent['template'] = $emailTemplate;
		$mailerContent['recordModel'] = $recordModel;

		$mailerContent['attachments'] = ['ids' => $fileIds];

		\App\Mailer::sendFromTemplate($mailerContent);
	}

	public static function processSpecialFromField(?Vtiger_Record_Model $recordModel, string $fieldValue) {
		$explodedFieldValues = explode(',', $fieldValue);
		$fieldValues = [];
		foreach ($explodedFieldValues as $fieldValue) {
			if(strpos($fieldValue, 'fromField') === 0) {
				$fieldName = substr($fieldValue, 10);
				$isRelated = strpos($fieldName, 'relatedRecordId');
				if ($isRelated !== false && $isRelated >= 0) {
					$fieldName = '$(relatedRecordId : ' . implode('|', array_slice(explode(':', $fieldName), 1)). ')$';
					$fieldValues[] = \App\TextParser::getInstanceByModel($recordModel)->setGlobalPermissions(false)->setContent($fieldName)->parse()->getContent();
				} else {
					$fieldValues[] = $recordModel->get($fieldName);
				}
			} elseif(strpos($fieldValue, 'fromRole') === 0) {
				$role = substr($fieldValue, 9);
				// get users by role
				$users = \App\PrivilegeUtil::getActiveUsersByRole($role);
				// select next user
				$i = 0;
				$fp = fopen("cache/$role.queue", "c+");
				try {
					$content = fgets($fp);
					if ((!empty($content) || $content === '0') && is_numeric($content)) {
						$i = ($content + 1) % count($users);
					}
					ftruncate($fp, 0);
					fseek($fp, 0);
					fwrite($fp, $i);
				} finally {
					fclose($fp);
				}
				$fieldValues[] = $users[$i];
			} elseif(strpos($fieldValue, 'fromUser-current') === 0) {
				$fieldValues[] = \App\User::getCurrentUserId();
			} elseif(strpos($fieldValue, 'fromUserGroup') === 0) {
				$group = substr($fieldValue, 14);
				// get users by role
				$users = (new \App\QueryGenerator('UserGroups'))
					->addJoin(['LEFT JOIN', 'vtiger_users', 'vtiger_users.id = u_yf_usergroups.user AND vtiger_users.deleted = 0 AND vtiger_users.status = \'Active\''])
					->setField('user')
					->addCondition('user_group_id', $group, 'e')
					->addCondition('is_active', 1, 'e')
					->setOrder('user')
					->createQuery()
					->andWhere([
						'or', 'vtiger_users.id IS NOT NULL', 'vtiger_groups.groupid IS NOT NULL'
					])
					->column();
				// select next user
				$i = 0;
				$fp = fopen("cache/$group.queue", "c+");
				try {
					$content = fgets($fp);
					if ((!empty($content) || $content === '0') && is_numeric($content)) {
						$i = ($content + 1) % count($users);
					}
					ftruncate($fp, 0);
					fseek($fp, 0);
					fwrite($fp, $i);
				} finally {
					fclose($fp);
				}
				$fieldValues[] = $users[$i];
			} elseif (!is_numeric($fieldValue)) {
				$userId = App\User::getUserIdByName($fieldValue);
				$groupId = \App\Fields\Owner::getGroupId($fieldValue);
				if ($userId || $groupId) {
					$fieldValues[] = (!$userId) ? $groupId : $userId;
				}
			} elseif (is_numeric($fieldValue)) {
				$fieldValues[] = $fieldValue;
			}
		}
		// remove empty values from $fieldValues
		$fieldValues = array_filter($fieldValues);
		return !empty($fieldValues) ? implode(',', $fieldValues) : false;
	}

	public static function getOwnerFields(Vtiger_Module_Model $moduleModel, bool $withPrefix = false) {
		$options = [];
		$label = \App\Language::translate('LBL_USER_FROM_FIELD','Settings:Workflows');

		foreach($moduleModel->getFieldsByType('owner', true) as $fieldName => $fieldModel) {
			$options[($withPrefix ? 'fromField-' : '') . $fieldName] = 
			\App\Language::translate('LBL_USER_FROM_FIELD','Settings:Workflows') . ' ' . $fieldModel->getFullLabelTranslation($fieldModel->getModule());
		}

		// get related modules and get their owner fields
		foreach ($moduleModel->getFieldsByType(\Vtiger_Field_Model::$referenceTypes) as $parentFieldName => $field) {
			$relatedModules = $field->getReferenceList();
			$parentFieldNameLabel = \App\Language::translate($field->getFieldLabel(), $moduleModel->getName());
			foreach ($relatedModules as $relatedModule) {
				$relatedModuleLang = \App\Language::translate($relatedModule, $relatedModule);
				foreach (\Vtiger_Module_Model::getInstance($relatedModule)->getBlocks() as $blockModel) {
					foreach ($blockModel->getFields() as $fieldName => $fieldModel) {
						if ($fieldModel->isViewable() && $fieldModel->getFieldDataType() == 'owner') {
							$options[($withPrefix ? 'fromField-' : '') . "relatedRecordId:$parentFieldName:".$fieldModel->getName().":$relatedModule"] = 
								$label . ' ' . "$parentFieldNameLabel: ($relatedModuleLang) " . \App\Language::translate($fieldModel->getFieldLabel(), $relatedModule);
						}
					}
				}
			}
		}

		return $options;
	}

	public static function getAllRelatedRecords(Vtiger_Record_Model $recordModel, string $relatedModule, $additionalWhere = false, $additionalSelect = false, $additionalJoins = false, $orderBy = false) {
		$rows = [];
		$ids = [];
		$all = \App\Relation::getByModule($recordModel->getModule()->getName(), true, $relatedModule);
		foreach(array_keys($all) as $relationId) {
			$relationModel = Vtiger_RelationListView_Model::getInstance($recordModel, $relatedModule, $relationId);
			if ($relationModel) {
				$query = $relationModel->getRelationQuery();
				if (!empty($additionalWhere)) {
					$query = $query->andWhere($additionalWhere);
				}
				if (!empty($additionalSelect)) {
					$query = $query->addSelect($additionalSelect);
				}
				if (!empty($additionalJoins) && \is_array($additionalJoins)) {
					foreach($additionalJoins as $additionalJoin) {
						if (empty(array_filter(array_column($query->join, 1), function($value) use ($additionalJoin) { return preg_match('/\b' . $additionalJoin['table'] . '\b/', $value) === 1; }))) {
							$query = $query->join($additionalJoin['type'], $additionalJoin['table'], $additionalJoin['on']);
						}
					}
				}

				if ($orderBy) {
					$query->orderBy($orderBy);
				}

				foreach ($query->all() as $row) {
					if (!\in_array($row['id'], $ids)) {
						$ids[] = $row['id'];
						$rows[] = $row;
					}
				}
			}
		}

		return $rows;
	}

	public static function createInvoice($from, $to, ?string $category, string $subject, 
		string $status, string $dueDate, array $services, $claimId = false, string $paymentStatus = 'Not Paid') {
		$recordModel = Vtiger_Record_Model::getCleanInstance('FInvoice');

		$getProviderByName = function($name) {
			return (new \App\QueryGenerator('Providers'))->addCondition('provider_name', $name, 'e')->createQuery()->scalar();
		};

		$fromId = $from;
		if (!is_numeric($fromId)) {
			// locate from in Providers
			$fromId = $getProviderByName($fromId);
		}

		$toId = $to;
		if (!is_numeric($toId)) {
			// locate to in Providers
			$toId = $getProviderByName($toId);
		}

		$currentDate = date('Y-m-d');

		$recordModel->set('seller', $fromId);
		$recordModel->set('buyer', $toId);
		$recordModel->set('invoice_category', $category);
		$recordModel->set('finvoice_status', $status);
		$recordModel->set('payment_status', $paymentStatus);
		$recordModel->set('subject', $subject);
		$recordModel->set('issue_time', $currentDate);
		$recordModel->set('saledate', $currentDate);
		$recordModel->set('paymentdate', $dueDate);
		if ($claimId) {
			$recordModel->set('claim', $claimId);
		}

		$currencies = \App\Fields\Currency::getAll(true);
		$currencyParam = [];
		foreach ($currencies as $currency) {
			if (!isset($currencyParam[$currency['id']])) {
				$currencyParam[$currency['id']] = vtlib\Functions::getConversionRateInfo($currency['id']);
			}
		}

		$taxes = Vtiger_Inventory_Model::getGlobalTaxes();
		$defaultTax = Vtiger_Inventory_Model::getDefaultGlobalTax();

		$items = [];
		$sumTotal = 0;
		$sumGross = 0;
		foreach ($services as $service) {
			// items
			$invModel = Vtiger_Record_Model::getInstanceById($service['id']);

			$unitPriceRaw = \App\Json::decode($invModel->get('unit_price'));
			$unitPrice = $service['price'] ?: ($unitPriceRaw['currencies']['1']['price']);
			$qty = $service['qty'] ?: 1;
			$taxRate = $taxes[$invModel->get('taxes')]['value'];

			$total = $qty * $unitPrice;
			$gross = $total * ($taxRate + 100) / 100;

			$items[] = [
				'currency' => 1,
				'currencyparam' => \App\Json::encode($currencyParam),
				'discountmode' => 1,
				'taxmode' => 1,
				'name' => $service['id'],
				'qty' => $qty,
				'price' => $unitPrice,
				'total' => $total,
				'discount' => 0,
				'net' => $total,
				'tax' => $total * $taxRate / 100,
				'taxparam' => '{"aggregationType":"group","groupTax":"' . number_format($taxRate, 2) . '"}',
				'gross' => $gross,
				'comment1' => $service['comment'],
			];

			$sumTotal += $total;
			$sumGross += $gross;
		}
		$recordModel->initInventoryData($items);

		$recordModel->set('sum_total', $sumTotal);
		$recordModel->set('sum_gross', $sumGross);

		$recordModel->save();

		return $recordModel->getId();
	}

	public static function getServiceByName(string $name, $tax) {
		return (new \App\QueryGenerator('Services'))
			->addJoin(['INNER JOIN', 'a_yf_taxes_global', 'a_yf_taxes_global.id = vtiger_service.taxes'])
			->addCondition('servicename', $name, 'e')
			->createQuery()
			->andWhere(['a_yf_taxes_global.name' => $tax])
			->scalar();
	}

	public static function getClaimTypeParametersByType(string $type) {
		return (new \App\QueryGenerator('ClaimTypeParameters'))->addCondition('type_of_claim', $type, 'e')->createQuery()->scalar();
	}

	public static function getEmailTemplateByName(string $name) {
		return (new \App\QueryGenerator('EmailTemplates'))->addCondition('name', $name, 'e')->createQuery()->scalar();
	}

	public static function getEmailTemplatesByName(string $name) {
		return (new \App\QueryGenerator('EmailTemplates'))->addCondition('name', $name, 'e')->createQuery()->column();
	}

	public static function getEmailTemplateByNumber(string $number) {
		return (new \App\QueryGenerator('EmailTemplates'))->addCondition('number', $number, 'e')->createQuery()->scalar();
	}

	public static function getDocumentTemplateByNumber(string $number) {
		return (new \App\QueryGenerator('DocumentTemplates'))->addCondition('number', $number, 'e')->createQuery()->scalar();
	}

	public static function getDocumentPackageByName(string $name) {
		return (new \App\QueryGenerator('DocumentPackages'))->addCondition('document_package_name', $name, 'e')->createQuery()->scalar();
	}

	public static function getSmtpByName(string $name) {
		return (new \App\Db\Query())->select(['id'])->from('s_yf_mail_smtp')->where(['name' => $name])->scalar();
	}

	public static function getUserGroups() {
		return (new \App\QueryGenerator('UserGroups'))
			->addCondition('is_active', 1, 'e')
			->setField('user_group_id')
			->setDistinct('user_group_id')
			->createQuery()
			->column();
	}

	public static function createNotification(Vtiger_Record_Model $recordModel, string $moduleName, 
		$users, string $title, string $nameription, $type) {
		\App\Log::warning('VTWorkflowUtils::createNotification:' . print_r(['id' => $recordModel->getId(), 'moduleName' => $moduleName, 'users' => $users, 'title' => $title], true));

		try {
			if (!\is_array($users)) {
				$users = [$users];
			}
			$textParser = \App\TextParser::getInstanceByModel($recordModel);
			$relatedField = \App\ModuleHierarchy::getMappingRelatedField($moduleName);
			$notification = Vtiger_Record_Model::getCleanInstance('Notification');
			$notification->set('shownerid', implode(',', $users));
			$notification->set($relatedField, $recordModel->getId());
			$notification->set('title', $textParser->setContent($title)->parse()->getContent(), 'Text');
			$notification->set('description', $textParser->setContent($nameription)->parse()->getContent(), 'Text');
			$notification->set('notification_type', $type);
			$notification->set('notification_status', 'PLL_UNREAD');

			if (\in_array($moduleName, ['BatchTasks', 'BatchErrors'])) {
				$id = $recordModel->get('item');
				$moduleName = \App\Record::getType($id);
			} else if ($moduleName == 'Tasks') {
				$moduleName = 'Cases';
				$id = $recordModel->get('case');
			} else {
				$id = $recordModel->getId();
			}
			switch($moduleName) {
				case 'Portfolios':
					$notification->set('portfolio', $id);
					break;
				case 'Claims':
					$notification->set('claim', $id);
					break;
				case 'PortfolioPurchases':
					$notification->set('portfolio_purchase', $id);
					break;
				case 'Providers':
					$notification->set('provider', $id);
					break;
				case 'Collections':
					$notification->set('collection', $id);
					break;
				case 'ClaimCollections':
					$notification->set('claim_collection', $id);
					break;
				case 'Insureds':
					$notification->set('insured', $id);
					break;
				case 'Cases':
					$notification->set('case', $id);
					break;
			}

			$notification->setHandlerExceptions(['disableHandlers' => true]);
			$notification->save();
		} catch (Error $e) {
			\App\Log::error('VTWorkflowUtils::createNotification:ERROR ' . print_r($e, true));
			throw $e;
		}
	}

	public static function createNotificationRaw(array $users, string $title, string $nameription, $type) {
		\App\Log::warning('VTWorkflowUtils::createNotificationRaw:' . print_r(['users' => $users, 'title' => $title], true));

		try {
			if (!\is_array($users)) {
				$users = [$users];
			}
			$notification = Vtiger_Record_Model::getCleanInstance('Notification');
			$notification->set('shownerid', implode(',', array_unique($users)));
			$notification->set('title', $title, 'Text');
			$notification->set('description', $nameription, 'Text');
			$notification->set('notification_type', $type);
			$notification->set('notification_status', 'PLL_UNREAD');

			$notification->setHandlerExceptions(['disableHandlers' => true]);
			$notification->save();
		} catch (Error $e) {
			\App\Log::error('VTWorkflowUtils::createNotificationRaw:ERROR ' . print_r($e, true));
			throw $e;
		}
	}

	public static function createBatchErrorEntryRaw(string $taskName, $workflowId, string $moduleName, string $error, ?int $item = null, ?string $description = null, ?int $userId = null) {
		\App\Log::warning("VTWorkflowUtils::createBatchErrorEntryRaw:{$taskName} ({$workflowId})/$moduleName/$item/$error");

		try {
			$entry = Vtiger_Record_Model::getCleanInstance('BatchErrors');
			$entry->set('task_type', 'Workflow');
			$entry->set('task_name', \App\Purifier::decodeHtml(\App\Purifier::purify("{$taskName} ({$workflowId})")));
			$entry->set('mod_name', $moduleName);
			$entry->set('error_message', \App\Purifier::decodeHtml(\App\Purifier::purify($error)));
			if ($item) {
				$entry->set('item', $item);
			}
			if ($description) {
				$entry->set('error_description', \App\Purifier::encodeHtml($description));
			}
			if ($userId) {
				$entry->set('assigned_user_id', $userId);
			}
			$entry->save();

			return $entry;
		} catch (\Exception $e) {
			\App\Log::error("VTWorkflowUtils::createBatchErrorEntryRaw:{$taskName} ({$workflowId})/$moduleName/$item/$error/$description");
			\App\Log::error(print_r($e, true));
		}
	}

	public static function createBatchErrorEntry(Workflow $workflow, string $moduleName, string $error, ?int $item = null, ?string $description = null) {
		\App\Log::warning("VTWorkflowUtils::createBatchErrorEntry:{$workflow->description} ({$workflow->id})/$moduleName/$item/$error");

		return self::createBatchErrorEntryRaw($workflow->description, $workflow->id, $moduleName, $error, $item, $description);
	}
}

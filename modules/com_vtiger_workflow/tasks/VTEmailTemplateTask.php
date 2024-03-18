<?php

/**
 * Email Template Task Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class VTEmailTemplateTask extends VTTask
{
	/** @var bool Sending email takes more time, this should be handled via queue all the time. */
	public $executeImmediately = true;

	/**
	 * Get field names.
	 *
	 * @return string[]
	 */
	public function getFieldNames()
	{
		return ['template', 'email', 'relations_email', 'emailoptout', 'copy_email', 'address_emails', 'attachments', 'filter', 'groups_email', 'conditionString'];
	}

	/**
	 * Execute task.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function doTask($recordModel, $originalRecordModel = null)
	{
		if (!empty($this->template)) {
			$mailerContent = [];
			$mailerContent['recordModel'] = $recordModel;

			$emailParser = \App\EmailParser::getInstanceByModel($recordModel);
			$emailParser->emailoptout = $this->emailoptout ? true : false;

			if (!empty($this->conditionString)) {
				if(!\App\Utils\Completions::processIfCondition(htmlspecialchars_decode($this->conditionString), $emailParser)) {
					\App\Log::warning("Workflows::VTEmailTemplateTask:WF Task level condition not met");
					return false;
				}
			}

			$template = \is_numeric($this->template) ? $this->template : (VTWorkflowUtils::getEmailTemplateByNumber($this->template) ?: VTWorkflowUtils::getEmailTemplateByName($this->template));
			$templateNum = 1;
			if (\is_array($template) ) {
				$templateNum = count($template);
				$selectedTemplates = [];
				$defaultTemplates = [];
				foreach ($template as $templateId) {
					// validate template condition
					$template = Vtiger_Record_Model::getInstanceById($templateId);
					$condition = $template->get('condition');
					if (!empty($condition)) {
						if (\App\Utils\Completions::processIfCondition(htmlspecialchars_decode($condition), $emailParser)) {
							$selectedTemplates[] = $templateId;
						}
					} else {
						$defaultTemplates[] = $templateId;
					}
				}

				if (empty($selectedTemplates) && empty($defaultTemplates)) {
					$error = "No email template matched due to unmet conditions";
				} else if (count($selectedTemplates) > 1 || count($defaultTemplates) > 1) {
					$error = "Multiple email templates matched";
				} else if (count($selectedTemplates) === 1 || count($defaultTemplates) === 1) {
					$template = count($selectedTemplates) === 1 ? $selectedTemplates[0] : $defaultTemplates[0];
				}
	
				if ($error) {
					\App\Log::warning("Workflows::VTEmailTemplateTask:$error");
					\VTWorkflowUtils::createBatchErrorEntryRaw($this->summary, $this->workflowId, $recordModel->getModuleName(), $error, $recordModel->getId());
					return false;
				}
			}

			if (!empty($template) && \App\Record::isExists($template)) {
				$mailerContent['template'] = $template;
				$template = Vtiger_Record_Model::getInstanceById($template);
				$condition = $template->get('condition');
					if (!empty($condition)) {
						if (!\App\Utils\Completions::processIfCondition(htmlspecialchars_decode($condition), $emailParser)) {
							\App\Log::warning("Workflows::VTEmailTemplateTask:No email template matched (out of $templateNum)");
							if ($templateNum > 1) {
								\VTWorkflowUtils::createBatchErrorEntryRaw($this->summary, $this->workflowId, $recordModel->getModuleName(), "No email template matched (out of $templateNum)", $recordModel->getId());
							}
							return false;
						}
					}
			} else {
				\App\Log::warning("Workflows::VTEmailTemplateTask:No email template matched (out of $templateNum)");
				if ($templateNum > 1) {
					\VTWorkflowUtils::createBatchErrorEntryRaw($this->summary, $this->workflowId, $recordModel->getModuleName(), "No email template matched (out of $templateNum)", $recordModel->getId());
				}
				return false;
			}

			if (!empty($template->get('email_from')) && \App\Record::isExists($template->get('email_from'))) {
				$smtp = Vtiger_Record_Model::getInstanceById($template->get('email_from'));

				$mailerContent['header'] = $smtp->get('email_header');
				$mailerContent['footer'] = $smtp->get('email_footer');
				$mailerContent['smtp_id'] = $smtp->get('smtp');
			} else {
				\App\Log::warning("Workflows::VTEmailTemplateTask:SMTP in From in template (" . $template->getDisplayName() . ") is empty");
				\VTWorkflowUtils::createBatchErrorEntryRaw($this->summary, $this->workflowId, $recordModel->getModuleName(), "SMTP in From in email template (" . $template->getDisplayName() . ") is empty", $recordModel->getId());
				return false;
			}

			$mailerContent['to'] = [];
			if (!empty($template->get('email_to'))) {
				$to = $emailParser->setContent(Vtiger_Record_Model::getInstanceById($template->get('email_to'))->get('result_text'))->parse()->getContent(true);
				if (empty($to)) {
					\App\Log::warning("Workflows::VTEmailTemplateTask:To field in email template (" . $template->getDisplayName() . ") generated empty string");
					\VTWorkflowUtils::createBatchErrorEntryRaw($this->summary, $this->workflowId, $recordModel->getModuleName(), "To field in email template (" . $template->getDisplayName() . ") generated empty string", $recordModel->getId());
					return false;
				} else {
					$mailerContent['to'] = $to;
				}
			} else {
				if ($this->email) {
					$email = \is_array($this->email) ? implode(',', $this->email) : $this->email;
					$toEmail = $emailParser->setContent($email)->parse()->getContent(true);
					$mailerContent['to'] = $toEmail;
				}
				if ($this->address_emails) {
					$mailerContent['to'][] = $this->address_emails;
				}
				if ($this->relations_email && '-' !== $this->relations_email) {
					[$relatedModule,$relatedFieldName,$onlyFirst] = array_pad(explode('::', $this->relations_email), 3, false);
					$pagingModel = new Vtiger_Paging_Model();
					$pagingModel->set('limit', 0);
					$relationListView = Vtiger_RelationListView_Model::getInstance($recordModel, $relatedModule);
					$relationListView->setFields(['id', $relatedFieldName]);
					$relationListView->set('search_key', $relatedFieldName);
					$relationListView->set('operator', 'ny');
					if ($onlyFirst) {
						$pagingModel->set('limit', 1);
					}
					foreach ($relationListView->getEntries($pagingModel) as $relatedRecordModel) {
						$mailerContent['to'][] = $relatedRecordModel->get($relatedFieldName);
					}
				}
				if ($this->groups_email && '-' !== $this->groups_email) {
					$userIds = \App\PrivilegeUtil::getUsersByGroup($this->groups_email);
					foreach ($userIds as $userId) {
						$user = \App\User::getUserModel($userId);
						$mailerContent['to'][] = $user->get('details')['email1'];
					}
				}
				if (empty($mailerContent['to'])) {
					\App\Log::warning("Workflows::VTEmailTemplateTask:Workflow task resulted in empty To address (template " . $template->getDisplayName() . ")");
					\VTWorkflowUtils::createBatchErrorEntryRaw($this->summary, $this->workflowId, $recordModel->getModuleName(), "Workflow task resulted in empty To address (template " . $template->getDisplayName() . ")", $recordModel->getId());
					return false;
				}
			}

			if (!empty($template->get('email_cc'))) {
        $cc = $emailParser->setContent(Vtiger_Record_Model::getInstanceById($template->get('email_cc'))->get('result_text'))->parse()->getContent(true);
				if (!empty($cc)) {
					$mailerContent['cc'] = $cc;
				}
      }

			if ('Contacts' === $recordModel->getModuleName() && !$recordModel->isEmpty('notifilanguage')) {
				$mailerContent['language'] = $recordModel->get('notifilanguage');
			}

			if (!empty($this->copy_email)) {
				$mailerContent['bcc'] = $this->copy_email;
			}

			if (!empty($template->get('having_document_type')) || (!empty($template->get('having_email_status') && empty($this->attachments) && (empty($this->filter) || $this->filter == 'All')))) {
				$relationListView = Vtiger_RelationListView_Model::getInstance($recordModel, 'Documents');
				$queryGenerator = $relationListView->getRelationQuery(true);
				$queryGenerator->setFields(['id']);
				if (!empty($template->get('having_document_type'))) {
					$queryGenerator->addCondition('document_type', $template->get('having_document_type'), 'eid');
				}
				if (!empty($template->get('having_email_status'))) {
					$queryGenerator->addCondition('email_status', 'To send', 'e');
				}
				$ids = $queryGenerator->createQuery()->column();
				$mailerContent['attachments'] = ['ids' => $ids];
			} else if (!empty($this->attachments) || ($this->filter && $this->filter != 'All')) {
				$attachmentsInfo = explode('::', $this->attachments);
				$ids = [];
				$relationListView = null;
				if (\count($attachmentsInfo) > 1) {
					if (!$recordModel->isEmpty($attachmentsInfo[1]) && App\Record::isExists($recordModel->get($attachmentsInfo[1]), $attachmentsInfo[0])) {
						$relationListView = Vtiger_RelationListView_Model::getInstance(Vtiger_Record_Model::getInstanceById($recordModel->get($attachmentsInfo[1]), $attachmentsInfo[0]), 'Documents');
					}
				} else {
					$relationListView = Vtiger_RelationListView_Model::getInstance($recordModel, 'Documents');
				}
				if ($relationListView) {
					$queryGenerator = $relationListView->getRelationQuery(true);
					$queryGenerator->setFields(['id']);
					if ($this->filter && $this->filter != 'All') {
						// find document type id by number
						$documentTypeId = (new \App\QueryGenerator('DocumentTypes'))
							->setField('id')
							->addCondition('number', $this->filter, 'e')
							->createQuery()
							->scalar();
						$queryGenerator->addCondition('document_type', $documentTypeId, 'eid');
					}
					if (!empty($template->get('having_email_status'))) {
						$queryGenerator->addCondition('email_status', 'To send', 'e');
					}
					$ids = $queryGenerator->createQuery()->column();
					if (!empty($ids)) {
						$mailerContent['attachments'] = ['ids' => $ids];
					}
				}
			}
			\App\Mailer::sendFromTemplate($mailerContent);
		}
	}
}

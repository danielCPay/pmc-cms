<?php

/**
 * Record model file.
 *
 * @package Model
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * Record class for Email Templates.
 */
class EmailTemplates_Record_Model extends Vtiger_Record_Model
{
	/** {@inheritdoc} */
	public function privilegeToDelete()
	{
		return $this->isEmpty('sys_name') && parent::privilegeToDelete();
	}

	/** {@inheritdoc} */
	public function privilegeToMoveToTrash()
	{
		return $this->isEmpty('sys_name') && parent::privilegeToMoveToTrash();
	}

	/** {@inheritdoc} */
	public function privilegeToArchive()
	{
		return $this->isEmpty('sys_name') && parent::privilegeToArchive();
	}

	public static function getActive($moduleName, Vtiger_Record_Model $recordModel = null)
	{
		$templatesForModule = array_map(function ($element) {
			return Vtiger_Record_Model::getInstanceById($element);
		}, (new \App\QueryGenerator('EmailTemplates'))->setFields(['id','condition'])->addCondition('module_name', $moduleName, 'e')->createQuery()->column());

		if ($recordModel) {
			$textParser = \App\TextParser::getInstanceByModel($recordModel);
			try {
				foreach ($templatesForModule as $key => $template) {
					if (!empty($template->get('condition')) && !\App\Utils\Completions::processIfCondition(htmlspecialchars_decode($template->get('condition')), $textParser)) {
						unset($templatesForModule[$key]);
					}
				}

				$templatesForModule = array_values($templatesForModule);
			} finally {
				unset($textParser);
			}
		}

		return $templatesForModule;
	}

	public static function checkActive($moduleName, Vtiger_Record_Model $recordModel = null)
	{
		$templatesForModule = (new \App\QueryGenerator('EmailTemplates'))->setFields(['id', 'condition'])->addCondition('module_name', $moduleName, 'e')->createQuery()->column();

		if ($recordModel) {
			$textParser = \App\TextParser::getInstanceByModel($recordModel);
			try {
				foreach ($templatesForModule as $templateRow) {
					if (empty($templateRow['condition']) || \App\Utils\Completions::processIfCondition(htmlspecialchars_decode($templateRow['condition']), $textParser)) {
						return true;
					}
				}

				return false;
			} finally {
				unset($textParser);
			}
		}

		return count($templatesForModule) > 0;
	}

	public function send($recordModel, $testMailbox = false) {
		\App\Log::warning("EmailTemplates::send:" . $this->getId() . ":" . $recordModel->getModuleName() . '.' . $recordModel->getId() . ":" . ($testMailbox ? "true" : "false"));

		$mailerContent = [];
		$mailerContent['recordModel'] = $recordModel;

		$emailParser = \App\EmailParser::getInstanceByModel($recordModel);

		$mailerContent['template'] = $this->getId();
		$template = $this;
		$condition = $template->get('condition');
		if (!empty($condition) && !\App\Utils\Completions::processIfCondition(htmlspecialchars_decode($condition), $emailParser)) {
			throw new \Exception("No email template matched");
		}

		if (!empty($template->get('email_from')) && \App\Record::isExists($template->get('email_from'))) {
			$smtp = Vtiger_Record_Model::getInstanceById($template->get('email_from'));
			$mailerContent['header'] = $smtp->get('email_header');
			$mailerContent['footer'] = $smtp->get('email_footer');
			$toOverride = $smtp->get('test_mailbox');
			$mailerContent['smtp_id'] = $smtp->get('smtp');
		} else {
			throw new \Exception("SMTP in From in template (" . $template->getDisplayName() . ") is empty");
		}

		$mailerContent['to'] = [];
		
		if ($testMailbox) {
			if (!empty($toOverride)) {
				$to = $emailParser->setContent($toOverride)->parse()->getContent(true);
			}
			if (empty($to)) {
				throw new \Exception("Test mailbox generated empty string");
			} else {
				$mailerContent['to'] = $to;
			}
		}	else if (!empty($template->get('email_to'))) {
			$to = $emailParser->setContent(Vtiger_Record_Model::getInstanceById($template->get('email_to'))->get('result_text'))->parse()->getContent(true);
			if (empty($to)) {
				throw new \Exception("To field in email template generated empty string");
			} else {
				$mailerContent['to'] = $to;
			}
		} else {
			throw new \Exception("Email To is not set");
		}

		if (!$testMailbox && !empty($template->get('email_cc'))) {
			$cc = $emailParser->setContent(Vtiger_Record_Model::getInstanceById($template->get('email_cc'))->get('result_text'))->parse()->getContent(true);
			if (!empty($cc)) {
				$mailerContent['cc'] = $cc;
			}
		}

		if ('Contacts' === $recordModel->getModuleName() && !$recordModel->isEmpty('notifilanguage')) {
			$mailerContent['language'] = $recordModel->get('notifilanguage');
		}

		if (!empty($template->get('having_document_type')) || !empty($template->get('having_email_status'))) {
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
		}

		\App\Mailer::sendFromTemplate($mailerContent);
	}

	/**
	 * Returns the default email template ID for the specified module and record model.
	 *
	 * @param string $moduleName The name of the module.
	 * @param Vtiger_Record_Model|null $recordModel The record model to use for parsing template conditions.
	 * @return int|false The ID of the default email template, or false if no default template is found.
	 */
	public static function getDefaultTemplateId($moduleName, Vtiger_Record_Model $recordModel = null)
	{
		$templateId = false;

		$templatesForModule = 
			(new \App\QueryGenerator('EmailTemplates'))
				->setFields(['id', 'condition'])
				->addCondition('module_name', $moduleName, 'e')
				->addCondition('is_default', 1, 'e')
				->createQuery()->all();

		if ($recordModel) {
			$textParser = \App\TextParser::getInstanceByModel($recordModel);
			try {
				foreach ($templatesForModule as $templateRow) {
					if (empty($templateRow['condition']) || \App\Utils\Completions::processIfCondition(htmlspecialchars_decode($templateRow['condition']), $textParser)) {
						$templateId = $templateRow['id'];
						break;
					}
				}
			} finally {
				unset($textParser);
			}
		} else if (count($templatesForModule) > 0) {
			$templateId = $templatesForModule[0]['id'];
		}

		return $templateId;
	}
}

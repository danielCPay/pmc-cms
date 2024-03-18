<?php

/**
 * Returns special functions for PDF Settings.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Maciej Stencel <m.stencel@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Adrian Koń <a.kon@yetiforce.com>
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Michał Kamiński <mkaminski@dotsystems.pl>
 */
class Vtiger_DotPDF_Action extends \App\Controller\Action
{
	use \App\Controller\ExposeMethod;

	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(App\Request $request)
	{
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModuleActionPermission($request->getModule(), 'ExportPdf')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('hasValidTemplate');
		$this->exposeMethod('validateRecords');
		$this->exposeMethod('generate');
	}

	/**
	 * Function to validate date.
	 *
	 * @param App\Request $request
	 */
	public function validateRecords(App\Request $request)
	{
		$moduleName = $request->getModule();
		$templates = $request->getArray('templates', \App\Purifier::INTEGER);
		$recordId = $request->getInteger('record');
		$records = $recordId ? [$recordId] : \Vtiger_Mass_Action::getRecordsListFromRequest($request);
		$result = false;
		foreach ($templates as $templateId) {
			$templateRecord = Vtiger_Record_Model::getInstanceById($templateId);
			if ($templateRecord->getModuleName() === 'DocumentPackages') {
				$result = true;
				break;
			} else {
				foreach ($records as $recordId) {
					$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
					$textParser = \App\TextParser::getInstanceByModel($recordModel);
					try {
						if (\App\Privilege::isPermitted($moduleName, 'DetailView', $recordId) && (empty($templateRecord->get('condition')) || \App\Utils\Completions::processIfCondition(htmlspecialchars_decode($templateRecord->get('condition')), $textParser) )) {
							$result = true;
							break 2;
						}
					} finally {
						unset($textParser);
					}
				}
			}
		}
		$response = new Vtiger_Response();
		$response->setResult(['valid' => $result, 'message' => \App\Language::translateArgs('LBL_NO_DATA_AVAILABLE', $moduleName)]);
		$response->emit();
	}

	/**
	 * Generate pdf.
	 *
	 * @param App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function generate(App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->getInteger('record');
		$recordIds = $recordId ? [$recordId] : \Vtiger_Mass_Action::getRecordsListFromRequest($request);
		$templateIds = $request->getArray('templates', 'Integer');
		$redirectEmail = $request->getByType('redirect_email_to_test_mailbox', \App\Purifier::STANDARD) === 'on';
		$sendToDropbox = $request->getByType('send_to_dropbox', \App\Purifier::INTEGER);
		$view = $request->getByType('fromview', \App\Purifier::STANDARD); // List/Detail

		\App\Log::warning("Vtiger::actions::DotPDF:$moduleName/" . implode(',', $recordIds) . "/" . implode(',', $templateIds) . "/$redirectEmail/$view");

		if ($view === 'Detail' || count($recordIds) === 1) {
			$item = Vtiger_Record_Model::getInstanceById($recordIds[0]);
			$done = $fail = 0;
			// sync generation
			foreach ($templateIds as $templateId) {
				$template = Vtiger_Record_Model::getInstanceById($templateId);

				// create BatchTask
				$batchTask = Vtiger_Record_Model::getCleanInstance('BatchTasks');
				$batchTask->set('mod_name', $moduleName);
				$batchTask->set('item', $item->getId());
				$batchTask->set('batch_task_type', $template->getModuleName() === 'DocumentPackages' ? 'Document Package' : 'Document Template');
				$batchTask->set('batch_task_name', "Export " . \App\Purifier::decodeHtml($item->getDisplayName()) . " using " . \App\Purifier::decodeHtml($template->getDisplayName()));
				if ($template->getModuleName() === 'DocumentPackages') {
					$batchTask->set('document_package', $template->getId());
				} else {
					$batchTask->set('document_template', $template->getId());
				}
				$batchTask->set('redirect_email_to_test_mailbox', $redirectEmail);

				switch ($sendToDropbox) {
					case -1:
						$batchTask->set('send_to_dropbox', $template->get('send_to_dropbox'));
						break;
					case -2: /* to store info about do not send, sending using package config is default */
						$batchTask->set('send_to_dropbox', 1);
						break;
					default: /* destination override */
						$batchTask->set('send_to_dropbox', $sendToDropbox);
						break;
				}

				$batchTask->save();

				BatchTasks_Module_Model::processTask($batchTask);

				if ($batchTask->get('batch_task_status') === 'Done') {
					$done++;
				} else {
					$fail++;
				}
			}

			$response = new Vtiger_Response();
			$response->setResult([
				'success' => true,
				'message' => sprintf(\App\Language::translate('LBL_DOCUMENTS_GENERATED', $moduleName), $done, $fail),
			]);
			$response->emit();
		} else {
			foreach ($recordIds as $recordId) {
				$item = Vtiger_Record_Model::getInstanceById($recordId);
				// async generation
				foreach ($templateIds as $templateId) {
					$template = Vtiger_Record_Model::getInstanceById($templateId);

					// create BatchTask
					$batchTask = Vtiger_Record_Model::getCleanInstance('BatchTasks');
					$batchTask->set('mod_name', $moduleName);
					$batchTask->set('item', $item->getId());
					$batchTask->set('batch_task_type', $template->getModuleName() === 'DocumentPackages' ? 'Document Package' : 'Document Template');
					$batchTask->set('batch_task_name', "Export {$item->getDisplayName()} using {$template->getDisplayName()}");
					if ($template->getModuleName() === 'DocumentPackages') {
						$batchTask->set('document_package', $template->getId());
					} else {
						$batchTask->set('document_template', $template->getId());
					}
					$batchTask->set('redirect_email_to_test_mailbox', $redirectEmail);

					switch ($sendToDropbox) {
						case -1:
							$batchTask->set('send_to_dropbox', $template->get('send_to_dropbox'));
							break;
						case -2: /* to store info about do not send, sending using package config is default */
							$batchTask->set('send_to_dropbox', 1);
							break;
						default: /* destination override */
							$batchTask->set('send_to_dropbox', $sendToDropbox);
							break;
					}

					$batchTask->set('batch_task_status', 'Pending');
					$batchTask->save();
				}
			}

			$response = new Vtiger_Response();
			$response->setResult([
				'success' => true,
				'message' => \App\Language::translate('LBL_DOCUMENTS_QUEUED', $moduleName),
			]);
			$response->emit();
		}
	}

	/**
	 * Checks if given record has valid pdf template.
	 *
	 * @param \App\Request $request
	 *
	 * @return bool true if valid template exists for this record
	 */
	public function hasValidTemplate(App\Request $request)
	{
		$recordId = $request->getInteger('record');
		$moduleName = $request->getModule();
		if (!\App\Privilege::isPermitted($moduleName, 'DetailView', $recordId)) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
		$valid = DocumentPackages_Record_Model::checkActive($moduleName, $recordModel) || DocumentTemplates_Record_Model::checkActive($moduleName, $recordModel);

		$output = ['valid' => $valid];

		$response = new Vtiger_Response();
		$response->setResult($output);
		$response->emit();
	}
}

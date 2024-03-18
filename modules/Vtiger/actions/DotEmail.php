<?php

use App\Request;

/**
 * Returns special functions for Send Mail functionality.
 *
 * @copyright DOT Systems sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Kamiński <mkaminski@dotsystems.pl>
 */
class Vtiger_DotEmail_Action extends \App\Controller\Action
{
	use \App\Controller\ExposeMethod;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('hasValidTemplate');
		$this->exposeMethod('validateRecords');
		$this->exposeMethod('send');
	}

  public function checkPermission(Request $request)
  {
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
			foreach ($records as $recordId) {
        $recordModel = Vtiger_Record_Model::getInstanceById($recordId);
        $textParser = \App\TextParser::getInstanceByModel($recordModel);
        try {
          if (\App\Privilege::isPermitted($moduleName, 'DetailView', $recordId) && (empty($templateRecord->get('condition')) || \App\Utils\Completions::processIfCondition(htmlspecialchars_decode($templateRecord->get('condition')), $textParser) )) {
            $result = true;
            break 2;
          } else {
						\App\Log::warning("Vtiger::actions::DotEmail::validateRecords:Record $recordId didn't validate for template $templateId - " 
							. var_export(['isPermitted' => \App\Privilege::isPermitted($moduleName, 'DetailView', $recordId), 
								'condition' => $templateRecord->get('condition'), 
								'conditionResult' => \App\Utils\Completions::processIfCondition(htmlspecialchars_decode($templateRecord->get('condition')), $textParser)
								], true));
					}
				} catch (\Exception $e) {
					\App\Log::warning("Vtiger::actions::DotEmail::validateRecords:Record $recordId didn't validate for template $templateId due to error " . $e->getMessage());
				} finally {
          unset($textParser);
        }
      }
		}
		$response = new Vtiger_Response();
		$response->setResult(['valid' => $result, 'message' => \App\Language::translateArgs('LBL_NO_DATA_AVAILABLE', $moduleName)]);
		$response->emit();
	}

	/**
	 * Send mail
	 *
	 * @param App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function send(App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->getInteger('record');
		$recordIds = $recordId ? [$recordId] : \Vtiger_Mass_Action::getRecordsListFromRequest($request);
		$templateIds = $request->getArray('templates', 'Integer');
		$redirectEmail = $request->getByType('redirect_email_to_test_mailbox', \App\Purifier::STANDARD) === 'on';
		$view = $request->getByType('fromview', \App\Purifier::STANDARD); // List/Detail

		\App\Log::warning("Vtiger::actions::DotEmail:$moduleName/" . implode(',', $recordIds) . "/" . implode(',', $templateIds) . "/$redirectEmail/$view");

		if ($view === 'Detail' || count($recordIds) === 1) {
			$item = Vtiger_Record_Model::getInstanceById($recordIds[0]);
			$done = $fail = 0;
			// sync sending
			foreach ($templateIds as $templateId) {
				$template = Vtiger_Record_Model::getInstanceById($templateId);

				// create BatchTask
				$batchTask = Vtiger_Record_Model::getCleanInstance('BatchTasks');
				$batchTask->set('mod_name', $moduleName);
				$batchTask->set('item', $item->getId());
				$batchTask->set('batch_task_type', 'Email Template');
				$batchTask->set('batch_task_name', "Send {$item->getDisplayName()} using {$template->getDisplayName()}");
				$batchTask->set('email_template', $template->getId());
				$batchTask->set('redirect_email_to_test_mailbox', $redirectEmail);
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
				'message' => sprintf(\App\Language::translate('LBL_DOT_EMAIL_SENT', $moduleName), $done, $fail),
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
					$batchTask->set('batch_task_type', 'Email Template');
					$batchTask->set('batch_task_name', "Send {$item->getDisplayName()} using {$template->getDisplayName()}");
					$batchTask->set('email_template', $template->getId());
					$batchTask->set('redirect_email_to_test_mailbox', $redirectEmail);
					$batchTask->set('batch_task_status', 'Pending');
					$batchTask->save();
				}
			}

			$response = new Vtiger_Response();
			$response->setResult([
				'success' => true,
				'message' => \App\Language::translate('LBL_DOT_EMAIL_QUEUED', $moduleName),
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
		$moduleName = $request->getModule();
		$recordId = $request->getInteger('record');
		$recordIds = $recordId ? [$recordId] : \Vtiger_Mass_Action::getRecordsListFromRequest($request);

		foreach ($recordIds as $recordId) {
			if (!\App\Privilege::isPermitted($moduleName, 'DetailView', $recordId)) {
				throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
			}
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
			$valid = EmailTemplates_Record_Model::checkActive($moduleName, $recordModel);

			if (!$valid) {
				break;
			}
		}

		$output = ['valid' => $valid];

		$response = new Vtiger_Response();
		$response->setResult($output);
		$response->emit();
	}
}

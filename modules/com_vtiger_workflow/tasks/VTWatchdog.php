<?php
/**
 * Watchdog Task Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
require_once 'modules/com_vtiger_workflow/VTWorkflowUtils.php';

class VTWatchdog extends VTTask
{
	public $executeImmediately = true;
	public $srcWatchdogModule = 'Notification';

	public function getFieldNames()
	{
		return ['type', 'message', 'recipients', 'title', 'skipCurrentUser'];
	}

	/**
	 * Execute task.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function doTask($recordModel, $originalRecordModel = null)
	{
		$moduleName = $recordModel->getModuleName();
		$recordId = $recordModel->getId();
		switch ($this->recipients) {
			case 'watchdog':
				$watchdog = Vtiger_Watchdog_Model::getInstanceById($recordId, $moduleName);
				$users = $watchdog->getWatchingUsers();
				break;
			case 'owner':
				$users = [$recordModel->get('assigned_user_id')];
				break;
			case 'owner_and_showner':
				$users = array_merge([$recordModel->get('assigned_user_id')], explode(',', $recordModel->get('shownerid')));
				break;
			case 'special-current-user':
				$users = [\App\User::getCurrentUserId()];
				break;
			default:
				if(strpos($this->recipients, 'fromField') === 0) {
					$users = \VTWorkflowUtils::processSpecialFromField($recordModel, $this->recipients);
				} else {
					$users = \App\PrivilegeUtil::getUserByMember($this->recipients);
				}
				break;
		}
		if (!is_array($users)) {
			$users = [$users];
		}
		if (!empty($this->skipCurrentUser) && false !== ($key = array_search(\App\User::getCurrentUserId(), $users))) {
			unset($users[$key]);
		}

		foreach($users as $key => $user) {
			$group = Settings_Groups_Record_Model::getInstance($user);
			if ($group) {
				unset($users[$key]);
				array_push($users, ...\App\PrivilegeUtil::getUserByMember("Groups:$user"));
			}
		}

		$users = array_values(array_unique($users));

		if (empty($users)) {
			return false;
		}
		else if (!\is_array($users)) {
			$users = [$users];
		}

		VTWorkflowUtils::createNotification($recordModel, $moduleName, $users, $this->title, $this->message, $this->type);
	}
}

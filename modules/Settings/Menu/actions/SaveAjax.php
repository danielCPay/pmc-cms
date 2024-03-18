<?php

/**
 * Settings menu SaveAjax action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_Menu_SaveAjax_Action extends Settings_Vtiger_Basic_Action
{
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('createMenu');
		$this->exposeMethod('updateMenu');
		$this->exposeMethod('removeMenu');
		$this->exposeMethod('updateSequence');
		$this->exposeMethod('copyMenu');
		$this->exposeMethod('refreshMenu');
	}

	public function createMenu(App\Request $request)
	{
		$data = $request->getMultiDimensionArray('mdata', [
			'type' => 'Alnum',
			'module' => 'Alnum',
			'label' => 'Text',
			'newwindow' => 'Integer',
			'hotkey' => 'Text',
			'filters' => ['Integer'],
			'icon' => 'Text',
			'role' => 'Alnum',
			'dataurl' => 'Text',
		]
		);
		$data['source'] = $request->getInteger('source');
		$recordModel = Settings_Menu_Record_Model::getCleanInstance();
		$recordModel->initialize($data);
		$recordModel->save();
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'message' => \App\Language::translate('LBL_ITEM_ADDED_TO_MENU', $request->getModule(false)),
		]);
		$response->emit();
	}

	public function updateMenu(App\Request $request)
	{
		$data = $request->getMultiDimensionArray('mdata', [
			'id' => 'Integer',
			'type' => 'Alnum',
			'module' => 'Alnum',
			'label' => 'Text',
			'newwindow' => 'Integer',
			'hotkey' => 'Text',
			'filters' => ['Integer'],
			'icon' => 'Text',
			'role' => 'Alnum',
			'dataurl' => 'Text',
		]
		);
		$data['source'] = $request->getInteger('source');
		$recordModel = Settings_Menu_Record_Model::getInstanceById($data['id']);
		$recordModel->initialize($data);
		$recordModel->set('edit', true);
		$recordModel->save();
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'message' => \App\Language::translate('LBL_SAVED_MENU', $request->getModule(false)),
		]);
		$response->emit();
	}

	public function removeMenu(App\Request $request)
	{
		$settingsModel = Settings_Menu_Record_Model::getCleanInstance();
		$settingsModel->removeMenu($request->getArray('mdata', 'Integer'));
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'message' => \App\Language::translate('LBL_REMOVED_MENU_ITEM', $request->getModule(false)),
		]);
		$response->emit();
	}

	public function updateSequence(App\Request $request)
	{
		$recordModel = Settings_Menu_Record_Model::getCleanInstance();
		$recordModel->saveSequence($request->getArray('mdata', 'Text'), Settings_Menu_Record_Model::SRC_ROLE === $request->getInteger('source'));
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'message' => \App\Language::translate('LBL_SAVED_MAP_MENU', $request->getModule(false)),
		]);
		$response->emit();
	}

	/**
	 * Function to trigger copying menu.
	 *
	 * @param \App\Request $request
	 */
	public function copyMenu(App\Request $request)
	{
		$roleTo = $request->getByType('toRole', 'Alnum');
		$fromRole = filter_var($request->getByType('fromRole', 'Alnum'), FILTER_SANITIZE_NUMBER_INT);
		$toRole = filter_var($roleTo, FILTER_SANITIZE_NUMBER_INT);
		$recordModel = Settings_Menu_Record_Model::getCleanInstance();
		$recordModel->copyMenu($fromRole, $toRole, $roleTo);
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'message' => \App\Language::translate('LBL_SAVED_MAP_MENU', $request->getModule(false)),
		]);

		$response->emit();
	}

	/**
	 * Function to trigger cleanup and refresh of menu files.
	 *
	 * @param \App\Request $request
	 */
	public function refreshMenu(App\Request $request)
	{
		\App\Log::warning('Settings_Vtiger_Basic_Action::refreshMenu');

		$directory = ROOT_DIRECTORY . '/user_privileges';
		$files = scandir($directory);
		foreach ($files as $file) {
			if (strpos($file, 'menu_') === 0 && $file !== 'menu_0.php') {
				\App\Log::warning('Settings_Vtiger_Basic_Action::refreshMenu:removing ' . $file);
				unlink($directory . '/' . $file);
			}
		}

		$allRoles = (new \App\Db\Query())->from('yetiforce_menu')->select('role')->distinct(true)->column();
		$menuRecordModel = new \Settings_Menu_Record_Model();
		foreach ($allRoles as $role) {
			\App\Log::warning('Settings_Vtiger_Basic_Action::refreshMenu:generating role ' . $role);
			$menuRecordModel->generateFileMenu($role);
		}

		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'message' => \App\Language::translate('LBL_REFRESHED_MENU', $request->getModule(false)),
		]);

		$response->emit();
	}
}

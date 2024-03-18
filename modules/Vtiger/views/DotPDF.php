<?php

/**
 * Export Document Package/Template Modal View Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Maciej Stencel <m.stencel@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author		Michał Kamiński <mkaminski@dotsystems.pl>
 */
class Vtiger_DotPDF_View extends Vtiger_BasicModal_View
{
	public function getSize(App\Request $request)
	{
		return 'modal-fullscreen';
	}

	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function checkPermission(App\Request $request)
	{
		$moduleName = $request->getModule();
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModuleActionPermission($moduleName, 'ExportPdf')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		if (!$request->isEmpty('record') && !\App\Privilege::isPermitted($moduleName, 'DetailView', $request->getInteger('record'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/**
	 * Process.
	 *
	 * @param App\Request $request
	 */
	public function process(App\Request $request)
	{
		$this->preProcess($request);
		$viewer = $this->getViewer($request);

		$moduleName = $request->getModule();
		$recordId = $request->getInteger('record');
		$view = $request->getByType('fromview', 'Standard');

		$active = false;

		// prepare template and package list
		if ($recordId) {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
			$packages = DocumentPackages_Record_Model::getActive($moduleName, $recordModel);
			$templates = DocumentTemplates_Record_Model::getActive($moduleName, $recordModel);
			$records = [$recordId];
		} else {
			$packages = DocumentPackages_Record_Model::getActive($moduleName);
			$templates = DocumentTemplates_Record_Model::getActive($moduleName);
			$records = \Vtiger_Mass_Action::getRecordsListFromRequest($request);
		}

		foreach ($templates as $key => $template) {
			$isTemplateActive = $template->get('default');
			if ($isTemplateActive && !$active) {
				foreach ($records as $record) {
					if ($template->checkFiltersForRecord((int) $record)) {
						$active = true;
						break;
					}
				}
			}
		}

		// fetch dropbox destinations
		$destinations = [];
		if (\App\Config::dropbox('enabled')) {
			foreach ((new \App\QueryGenerator('DropboxDestinations'))->setField('id')->createQuery()->column() as $destinationId) {
				$destinations[$destinationId] = \App\Record::getLabel($destinationId);
			}
		}

		$viewer->assign('PACKAGES', $packages);
		$viewer->assign('TEMPLATES', $templates);
		$viewer->assign('DESTINATIONS', $destinations);
		$viewer->assign('RECORD_ID', $recordId);
		$viewer->assign('FROM_VIEW', $view);
		$viewer->assign('ACTIVE', $active);
		$viewer->assign('OPERATOR', $request->getByType('operator'));
		$viewer->assign('ALPHABET_VALUE', App\Condition::validSearchValue(
			$request->getByType('search_value', \App\Purifier::TEXT),
			$moduleName,
			$request->getByType('search_key', \App\Purifier::ALNUM), $request->getByType('operator')
		));
		$viewer->assign('VIEW_NAME', $request->getByType('viewname', \App\Purifier::ALNUM));
		$viewer->assign('SELECTED_IDS', $request->getArray('selected_ids', \App\Purifier::INTEGER));
		$viewer->assign('EXCLUDED_IDS', $request->getArray('excluded_ids', \App\Purifier::INTEGER));
		$viewer->assign('SEARCH_KEY', $request->getByType('search_key', \App\Purifier::ALNUM));
		$viewer->assign('SEARCH_PARAMS', App\Condition::validSearchParams($moduleName, $request->getArray('search_params'), false));
		$viewer->assign('ORDER_BY', $request->getArray('orderby', \App\Purifier::STANDARD, [], \App\Purifier::SQL));
		$viewer->view('DotPDF.tpl', $moduleName);
		$this->postProcess($request);
	}
}

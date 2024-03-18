<?php

/**
 * Send Mail Modal View Class.
 *
 * @copyright DOT SYstems sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author		Michał Kamiński <mkaminski@dotsystems.pl>
 */
class Vtiger_DotEmail_View extends Vtiger_BasicModal_View
{
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
			$templates = EmailTemplates_Record_Model::getActive($moduleName, $recordModel);
		} else {
			$templates = EmailTemplates_Record_Model::getActive($moduleName);
		}

		$viewer->assign('TEMPLATES', $templates);
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
		$viewer->assign('SELECTED_IDS', $request->getArray('selected_ids', \App\Purifier::ALNUM));
		$viewer->assign('EXCLUDED_IDS', $request->getArray('excluded_ids', \App\Purifier::INTEGER));
		$viewer->assign('SEARCH_KEY', $request->getByType('search_key', \App\Purifier::ALNUM));
		$viewer->assign('SEARCH_PARAMS', App\Condition::validSearchParams($moduleName, $request->getArray('search_params'), false));
		$viewer->assign('ORDER_BY', $request->getArray('orderby', \App\Purifier::STANDARD, [], \App\Purifier::SQL));
		$viewer->view('DotEmail.tpl', $moduleName);
		$this->postProcess($request);
	}
}

<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce Sp. z o.o.
 * ********************************************************************************** */
require_once 'include/main/WebUI.php';

class Settings_LayoutEditor_Filter_Action extends Settings_Vtiger_Index_Action
{
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getFilters');
		$this->exposeMethod('getAllFilters');
	}

	public function getFilters(App\Request $request)
	{
		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		$moduleName = $request->getArray('rmodule', 'Text');

		$filterValues = self::getAllFilters($moduleName[0]);

		$response->setResult($filterValues);
		$response->emit();
	}

	public static function getAllFilters($moduleName){
		$allViews = CustomView_Record_Model::getAll($moduleName);

		$filterValues[] = ['cvid' => '-1', 'viewname' => App\Language::translate('--None--')];

		foreach($allViews as $cvid => $view) {
			if (\in_array($view->get('status'), [0, 3, 4])) {
			  $filterValues[] = ['cvid' => $cvid, 'viewname' => $view->get('viewname')];
			}
		}

		return $filterValues;
	}
}

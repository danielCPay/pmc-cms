<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): DOT Systems sp. z o.o.
 * *********************************************************************************** */

class DotsPBIReports_GetToken_Action extends \App\Controller\Action
{
  /**
	 * {@inheritdoc}
	 */
	public function checkPermission(App\Request $request)
	{
    // validated by call to DotsPBIReports_Module_Model::getToken($recordId);
	}

	/**
	 * Function process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
    $recordId = $request->get('record') ?: DotsPBIReports_Module_Model::getDefaultConfigId();

    $response = DotsPBIReports_Module_Model::getToken($recordId);
    $result = [ 'token' => $response['EmbedToken']];

		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		$response->setResult($result);
		$response->emit();
	}
}

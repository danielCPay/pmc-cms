<?php

/**
 * Settings DotsPBIReports Index view class.
 *
 * @copyright DOT Systems sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_DotsPBIReports_Index_View extends Settings_Vtiger_Index_View
{
	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		// $viewer->assign('RECORD_MODEL', Settings_OSSMail_Config_Model::getInstance());
		$viewer->view('Index.tpl', $qualifiedModuleName);
	}
}

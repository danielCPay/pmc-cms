<?php
/**
 * DotsPBIReports index view class.
 *
 * @copyright DOT Systems sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */

use App\Request;

/**
 * DotsPBIReports index view class.
 */
class DotsPBIReports_Index_View extends Vtiger_Index_View
{
  /**
	 * {@inheritdoc}
	 */
	public function getHeaderScripts(Request $request)
  {
    $jsFileNames = [
      "libraries.powerbi-client.dist.powerbi"
    ];
    
    return array_merge(
      parent::getHeaderScripts($request), 
      $this->checkAndConvertJsScripts($jsFileNames)
    );
  }
  
	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
    $user = \App\User::getCurrentUserModel();
    $role = $user->getRoleInstance()->get('parentrole');

		$moduleName = $request->getModule();
    $recordId = $request->get('record') ?: DotsPBIReports_Module_Model::getDefaultConfigId($role);

    $viewer = $this->getViewer($request);
		
    if ($recordId === false || DotsPBIReports_Module_Model::getConfig($recordId) === false) {
      $count = DotsPBIReports_Module_Model::getConfigCount($role);

      if ($count > 0) {
        // show view with list of configurations
        $allConfigs = DotsPBIReports_Module_Model::getAllConfigs($role);
        $url = Vtiger_Module_Model::getInstance($moduleName)->getDefaultUrl();
        $reports = array_map(function ($el) use ($url) {
          return ['name' => $el['name'], 'url' => "$url&record={$el['dotspbireportsid']}", 'access_level' => $el['access_level']];
        }, $allConfigs);
        $viewer->assign('REPORTS', $reports);
      } else {
        // show view with information about no access
        $viewer->assign('NO_REPORTS', true);
      }
    } else {
      $config = DotsPBIReports_Module_Model::getConfig($recordId);
      try {
        $token = DotsPBIReports_Module_Model::getToken($recordId);
      } catch (\Throwable $t) {
        \App\Log::error($t);
        throw new \App\Exceptions\AppException("Could not connect to PowerBI, please try again or contact your administrator");
      }

      $viewer->assign('RECORD_ID', $recordId);
      $viewer->assign('REPORT_ID', $token['ReportId']);
      $viewer->assign('EMBED_URL', $token['EmbedUrl']);
      $viewer->assign('EMBED_TOKEN', $token['EmbedToken']);
      $viewer->assign('ACCESS_LEVEL', $config['access_level']);
      $viewer->assign('ADDITIONAL_CONFIGURATION', $config['additional_configuration'] ?: '{}');
    }
		
    $viewer->view('Index.tpl', $moduleName);
	}
}

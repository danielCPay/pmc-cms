<?php

/**
 * DotsPBIReports module model class.
 *
 * @copyright DOT Systems sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class DotsPBIReports_Module_Model extends Vtiger_Module_Model
{
	public function getDefaultViewName()
	{
		return 'Index';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSettingLinks(): array
	{
		$settingsLinks = [];
		$layoutEditorImagePath = Vtiger_Theme::getImagePath('LayoutEditor.gif');
		$settingsLinks[] = [
			'linktype' => 'LISTVIEWSETTING',
			'linklabel' => 'LBL_MODULE_CONFIGURATION',
			'linkurl' => 'index.php?module=DotsPBIReports&parent=Settings&view=Index',
			'linkicon' => $layoutEditorImagePath,
		];
		return $settingsLinks;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isQuickCreateSupported()
	{
		return false;
	}

	public static function getQuery($role) {
		$query = (new \App\Db\Query())->from('s_#__dotspbireports');

		$query = $query->innerJoin('vtiger_role', 'vtiger_role.roleid = s_#__dotspbireports.role');
		$query = $query->where("vtiger_role.parentrole LIKE :role", ['role' => "$role%"]);

		return $query;
	}

	public static function getDefaultConfigId($role = false, $ignoreMulti = false) {
		if ($role === false) {
			$user = \App\User::getCurrentUserModel();
    	$role = $user->getRoleInstance()->get('parentrole');
		}

		$query = self::getQuery($role);

		if ($ignoreMulti || $query->count() === 1) {
			return $query->min('dotspbireportsid');
		}

		return false;
	}

	public static function getConfigCount($role) {
		$query = self::getQuery($role);

		return $query->count();
	}

	public static function getConfig($recordId) {
		$user = \App\User::getCurrentUserModel();
		$role = $user->getRoleInstance()->get('parentrole');

		$query = self::getQuery($role);
		$query = $query->andWhere(['dotspbireportsid' => $recordId]);

		return $query->one();
	}

	public static function getAllConfigs($role) {
		$query = self::getQuery($role);

		$query = $query->groupBy(['token', 'access_level', 'additional_configuration']);
		$query = $query->select('min(dotspbireportsid) as dotspbireportsid');

		$configs = [];
		foreach($query->column() as $reportId) {
			$configs[] = self::getConfig($reportId);
		}

		return $configs;
	}

	public static function getToken($recordId) {
		// read code and settings from configuration by record id
		$record = self::getConfig($recordId);

		if ($record === false) {
			throw new \App\Exceptions\NoPermittedToRecord(\App\Language::translate('LBL_RECORD_NOT_FOUND'));
		} else {
			// check if current user role is in specified hierarchy
			$user = \App\User::getCurrentUserModel();
			$role = $record['role'];
			if (!$user->isAdmin() && false === strpos(\Settings_Roles_Record_Model::getInstanceById($role)->get('parentrole'), $user->getRoleInstance()->get('parentrole'))) {
				throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
			}
		}

		$url = $record['token'] . '&a=' . $record['access_level'];

		$config = \App\RequestHttp::getOptions();
		$additionalHeaders = \App\Config::module('DotsPBIReports', 'HEADERS');
		if (!empty($additionalHeaders)) {
			$config = array_merge_recursive($config, [ 'headers' => $additionalHeaders]);
		}

    $request = (new \GuzzleHttp\Client($config))->request('GET', $url);
    if (200 === $request->getStatusCode()) {
      $response = \App\Json::decode($request->getBody());
      if (!isset($response['EmbedToken'])) {
        throw new \App\Exceptions\AppException('Error with response | missing token');
      }
    } else {
      throw new \App\Exceptions\AppException('Error with connection |' . $request->getReasonPhrase());
    }

		return $response;
	}
}

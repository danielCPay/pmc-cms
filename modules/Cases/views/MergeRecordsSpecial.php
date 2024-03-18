<?php
/**
 * Merge cases view.
 *
 * @copyright DOT Systems sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Michał Kamiński <mkaminski@dotsystems.pl>
 */

/**
 * Merge cases class.
 */
class Cases_MergeRecordsSpecial_View extends Vtiger_MergeRecords_View
{
  public const PROVIDER_FIELD_RE = '/^provider(_\\d+)?$/';

  public static function getProviderFields() {
    $key = 'ProviderFields';
    if (\App\Cache::has('Cases', $key)) {
      return \App\Cache::get('Cases', $key);
    }
    $fields = array_keys(Vtiger_Module_Model::getInstance('Cases')->getFields());
    $fields = array_filter($fields, function ($val) { return preg_match(self::PROVIDER_FIELD_RE, $val) === 1; });

    \App\Cache::save('Cases', $key, $fields);

    return $fields;
  }

  /**
	 * {@inheritdoc}
	 */
	public function initializeContent(App\Request $request)
	{
    parent::initializeContent($request);

		$viewer = $this->getViewer($request);

    $viewer->assign('ACTION_OVERRIDE', 'MergeRecordsSpecial');

    $fields = $viewer->getTemplateVars('FIELDS');
    $providerFields = self::getProviderFields();
    $fields = array_diff($fields, ['types_of_services'], $providerFields);
    $viewer->assign('FIELDS', $fields);
	}
}

<?php
/**
 * DotsPBIReports CRMEntity class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */

/**
 * DotsPBIReports CRMEntity class.
 */
class DotsPBIReports
{
	/** Indicator if this is a custom module or standard module */
	public $IsCustomModule = true;

	public $tab_name = ['s_yf_dotspbireports'];

	/**
	 * Module handler.
	 *
	 * @param string $moduleName
	 * @param string $eventType
	 */
	public function moduleHandler($moduleName, $eventType)
	{
		if ('module.postinstall' === $eventType) {
		} elseif ('module.disabled' === $eventType) {
		} elseif ('module.enabled' === $eventType) {
		} elseif ('module.preuninstall' === $eventType) {
		}
	}
}

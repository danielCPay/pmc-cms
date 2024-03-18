<?php
/* +*******************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * **************************************************************************** */

/**
 * Class VTExpressionsManager.
 */
class VTExpressionsManager
{
	/**
	 * Cache array.
	 *
	 * @var array
	 */
	private static $cache = [];

	/**
	 * Add parameter to cache.
	 *
	 * @param string $key
	 * @param mixed  $value
	 */
	public static function addToCache($key, $value)
	{
		self::$cache[$key] = $value;
	}

	/**
	 * Get parameter from cache.
	 *
	 * @param string $key
	 *
	 * @return mixed|bool
	 */
	public static function fromCache($key)
	{
		if (isset(self::$cache[$key])) {
			return self::$cache[$key];
		}
		return false;
	}

	/**
	 * Clear cache array.
	 */
	public static function clearCache()
	{
		self::$cache = [];
	}

	/**
	 * Get fields info.
	 *
	 * @param string $moduleName
	 *
	 * @return array
	 */
	public function fields($moduleName)
	{
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$arr = [];
		foreach ($moduleModel->getFields() as $fieldName => $fieldModel) {
			$arr[$fieldName] = $fieldModel->getFieldLabel();
		}
		return $arr;
	}

	/**
	 * Get expression functions.
	 *
	 * @return array
	 */
	public function expressionFunctions()
	{
		return ['concat' => 'concat(a,b)', 'Substring' => 'substr(string, start, length)', 'Substring to end' => 'substr(string, start)',
			'time_diffmonths(a,b)' => 'time_diffmonths(a,b)', 'time_diffmonths(a)' => 'time_diffmonths(a)', 'time_diffdays(a,b)' => 'time_diffdays(a,b)', 'time_diffdays(a)' => 'time_diffdays(a)', 
			'time_diff(a,b)' => 'time_diff(a,b)', 'time_diff(a)' => 'time_diff(a)', 'add_days' => 'add_days(datefield, noofdays)', 'sub_days' => 'sub_days(datefield, noofdays)', 
			'add_time(timefield, minutes)' => 'add_time(timefield, minutes)', 'sub_time(timefield, minutes)' => 'sub_time(timefield, minutes)',
			'today' => "get_date('today')", 'tomorrow' => "get_date('tomorrow')", 'yesterday' => "get_date('yesterday')", 'nvl' => 'nvl(a, 0)', 'get_datetime()' => 'get_datetime()', 
			'truncdd' => 'truncdd(datefield)', 'truncmm' => 'truncmm(datefield)', 
			'Following workdays' => 'get_workingday(1, 1)',
			'Previous workdays' => 'get_workingday(0, 1)',
			'Closest workday (following)' => 'get_workingday(1, \'2022-04-03\')',
			'Closest workday (previous)' => 'get_workingday(0, \'2022-04-03\')',
			'round' => 'round(number, precision)', 'ceil' => 'ceil(number)',
			'Get id by field' => 'get_id(moduleName, fieldName, fieldValue)',
			'remove_white_chars' => 'remove_white_chars(field)',
			'remove_nonalphanumeric_chars' => 'remove_nonalphanumeric_chars(field)',
			'Get field by field' => 'get_field(moduleName, fieldName, fieldValue, targetField)',
			'Get formatted field by field' => 'get_field_display(moduleName, fieldName, fieldValue, targetField)',
			'Replace' => 'replace(value, searchValue, replaceValue)',
			'Get record id by user group' => 'get_by_user_group(userGroup, module, field)',
		];
	}
}

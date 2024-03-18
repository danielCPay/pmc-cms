<?php

/**
 * Vtiger summary multi column widget class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Vtiger_SummaryMultiColumn_Widget extends Vtiger_Basic_Widget
{
	public function getWidget()
	{
		$this->Config['tpl'] = 'GeneralInfoMultiColumn.tpl';

		return $this->Config;
	}

	public function getConfigTplName()
	{
		return 'SummaryMultiColumnConfig';
	}
}

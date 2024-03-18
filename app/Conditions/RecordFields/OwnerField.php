<?php

namespace App\Conditions\RecordFields;

/**
 * Owner condition record field class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OwnerField extends BaseField
{
	private function getUserIdByName($userName) {
		return \App\User::getUserIdByFullName($userName);
	}

	/**
	 * Equals operator.
	 *
	 * @return array
	 */
	public function operatorE()
	{
		if (!\is_numeric($this->value)) {
			$this->value = $this->getUserIdByName($this->value);
		}

		return parent::operatorE();
	}

	/**
	 * Not equal operator.
	 *
	 * @return array
	 */
	public function operatorN()
	{
		if (!\is_numeric($this->value)) {
			$this->value = $this->getUserIdByName($this->value);
		}

		return parent::operatorN();
	}

	/**
	 * Is watching record operator.
	 *
	 * @return array
	 */
	public function operatorWr()
	{
		return \Vtiger_Watchdog_Model::getInstanceById($this->recordModel->getId(), $this->recordModel->getModuleName())->isWatchingRecord();
	}

	/**
	 * Is not watching record operator.
	 *
	 * @return array
	 */
	public function operatorNwr()
	{
		return !\Vtiger_Watchdog_Model::getInstanceById($this->recordModel->getId(), $this->recordModel->getModuleName())->isWatchingRecord();
	}
}

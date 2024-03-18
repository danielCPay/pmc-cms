<?php

namespace App\Conditions\RecordFields;

/**
 * Reference condition record field class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license		YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author		Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class ReferenceField extends BaseField
{
	/**
	 * Get value from record.
	 *
	 * @return mixed
	 */
	public function getValue()
	{
    $id = $this->recordModel->get($this->fieldModel->getName());
    $name = "";
    if ($id > 0 && \App\Record::isExists($id)) {
      $name = $this->recordModel->getInstanceById($id)->getDisplayName();
    }

    return $name;
	}
}

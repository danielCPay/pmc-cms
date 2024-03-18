<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Claims_ListView_Model extends Vtiger_ListView_Model
{
	public function loadListViewCondition()
	{
		$queryGenerator = $this->get('query_generator');
		$queryGenerator->setField('filetype');
		$folderValue = $this->get('folder_value');
		if (!empty($folderValue)) {
			$queryGenerator->addCondition($this->get('folder_id'), $folderValue, 'e');
		}
		parent::loadListViewCondition();

		$userModel = \App\User::getCurrentUserModel();
		switch ($userModel->getRole()) {
			case Cases_ListView_Model::PRE_SUIT_ROLE:
				if (!isset($_REQUEST['fixed_search_params'])) {
					$queryGenerator = $this->getQueryGenerator()->addNativeCondition('1 = 0');
				}
				break;
		}
	}
}

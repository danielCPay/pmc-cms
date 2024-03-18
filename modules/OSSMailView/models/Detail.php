<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): DOT Systems sp. z o.o.
 * *********************************************************************************** */

/**
 * Class OSSMailView_Detail_View.
 *
 * @package View
 */
class OSSMailView_Detail_View extends Vtiger_Detail_View
{
	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(App\Request $request) {
		parent::checkPermission($request);

		$userModel = \App\User::getCurrentUserModel();
		if ($userModel->getRole() === Cases_ListView_Model::PROVIDER_LITIGATION_ASSISTANCE_ROLE) {
			$providerFields = Cases_MergeRecordsSpecial_View::getProviderFields();
			$providers = Cases_ListView_Model::getProviderLitigationAssists($userModel->getId());

      $additionalWhere = [];
			foreach ($providerFields as $providerField) {
        $additionalWhere[] = [$providerField => $providers];
			}

      $records = \VTWorkflowUtils::getAllRelatedRecords($this->record->getRecord(), 'Cases', ['or', ...$additionalWhere]);

      if (count($records) == 0) {
        throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
			}
		}
	}
}

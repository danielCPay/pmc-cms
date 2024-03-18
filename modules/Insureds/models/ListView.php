<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * Contributor(s): DOT Systems sp. z o.o.
 * *********************************************************************************** */

/**
 * Insureds ListView Model Class.
 */
class Insureds_ListView_Model extends Vtiger_ListView_Model
{
  /**
	 * {@inheritdoc}
	 */
	public function loadListViewCondition()
	{
		parent::loadListViewCondition();

		$userModel = \App\User::getCurrentUserModel();
		if ($userModel->getRole() === Cases_ListView_Model::PROVIDER_LITIGATION_ASSISTANCE_ROLE) {
			$providerFields = Cases_MergeRecordsSpecial_View::getProviderFields();
			$providers = Cases_ListView_Model::getProviderLitigationAssists($userModel->getId());
			$queryGenerator = $this->getQueryGenerator();
			
      $subQuery = (new \App\QueryGenerator('Cases'))->setFields(['insured']);
			foreach ($providerFields as $providerField) {
				$subQuery->addCondition($providerField, $providers, 'eid', false);
			}
      $subQuery = $subQuery->createQuery();

      $queryGenerator->addNativeCondition(['in', 'u_#__insureds.insuredsid', $subQuery]);
		}
	}
}

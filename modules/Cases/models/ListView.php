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
 * Cases ListView Model Class.
 */
class Cases_ListView_Model extends Vtiger_ListView_Model
{
	public const PROVIDER_LITIGATION_ASSISTANCE_ROLE = 'H55';
	public const PRE_SUIT_ROLE = 'H56';

	/**
	 * {@inheritdoc}
	 */
	public function getAdvancedLinks()
	{
		$advancedLinks = parent::getAdvancedLinks();
		$moduleModel = $this->getModule();
		
		if ($moduleModel->isPermitted('Merge')) {
			$advancedLinks[] = [
				'linktype' => 'LISTVIEW',
				'linklabel' => 'LBL_MERGING_SPECIAL',
				'linkicon' => 'yfi yfi-merging-records',
				'linkdata' => ['url' => "index.php?module={$moduleModel->getName()}&view=MergeRecordsSpecial"],
				'linkclass' => 'js-mass-action--merge-special',
			];
		}

		return $advancedLinks;
	}

	public static function getProviderLitigationAssists($userId) {
		$key = "LitigationAssists-$userId";
    if (\App\Cache::has('Cases', $key)) {
      return \App\Cache::get('Cases', $key);
    }
		
		$providers = (new \App\QueryGenerator('ProvidersLitigationAssist'))->addCondition('assigned_user_id', $userId, 'e')
			->setFields(['provider'])
			->createQuery()
			->column();
		
		\App\Cache::save('Cases', $key, $providers);

		return $providers;
	}

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
			
			foreach ($providerFields as $providerField) {
				$queryGenerator->addNativeCondition([$providerField => $providers], false);
			}
		}
	}
}

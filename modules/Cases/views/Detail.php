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
 * Class Cases_Detail_View.
 *
 * @package View
 */
class Cases_Detail_View extends Vtiger_Detail_View
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

			$match = false;
			foreach ($providerFields as $providerField) {
				if (in_array($this->record->getRecord()->get($providerField), $providers)) {
					$match = true;
					break;
				}
			}

			if (!$match) {
				throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
			}
		}
	}

	public function showDetailViewByMode(App\Request $request)
	{
		switch ($request->getByType('requestMode', 1)) {
			case 'full':
				return $this->showModuleDetailView($request);
			case 'prelitigation':
			case 'litigation':
			case 'trial':
			case 'settlement':
				return $this->showModuleDetailView($request);
			default:
				return $this->showModuleBasicView($request);
		}
	}

	/**
	 * Function shows the entire detail for the record.
	 *
	 * @param \App\Request $request
	 *
	 * @return <type>
	 */
	public function showModuleDetailView(App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordModel = $this->record->getRecord();
		if (!$this->recordStructure) {
			$this->recordStructure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_DETAIL);
		}
		$structuredValues = $this->recordStructure->getStructure();

		$moduleModel = $recordModel->getModule();

		$viewer = $this->getViewer($request);
		$viewer->assign('VIEW', $request->getByType('view', 1));
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('RECORD_STRUCTURE', $structuredValues);
		$viewer->assign('VIEW_MODEL', $this->record);
		$requestMode = $request->getByType('requestMode', 1);
		$requestModeTabs = [
			'prelitigation' => ['Pre-Litigation', 'HO Law Firm', 'Appraisal', 'Cases-Same Claim Number'],
			'litigation' => ['Complaint', 'Plaintiff Discovery', 'Plaintiff Deposition', 'Defendant Discovery', 'Defendant Deposition', 'Mediation Arbitration', 'Plaintiff MSJ', 'Defendant MSJ', 'Appeal', 'PFS CRN 57.105', 'Defendant MTD'],
			'trial' => ['Trial', 'Witnesses'],
			'settlement' => ['Settlement Negotiations', 'Settlement', 'Mortgage'],
		];
		$allTabs = array_merge(...array_values($requestModeTabs));
		$rawBlocks = $moduleModel->getBlocks();
		$blocks = [];
		foreach ($rawBlocks as $blockKey => $block) {
			if (($requestMode === 'full' || empty($requestMode)) && !\in_array($blockKey, $allTabs)) {
				$blocks[$blockKey] = $block;
			} else if ($requestMode !== 'full' && !empty($requestMode) && array_key_exists($requestMode, $requestModeTabs) && \in_array($blockKey, $requestModeTabs[$requestMode])) {
				$blocks[$blockKey] = $block;
			}
		}
		$viewer->assign('BLOCK_LIST', $blocks);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('IS_AJAX_ENABLED', $this->isAjaxEnabled($recordModel));
		$viewer->assign('MODULE_TYPE', $moduleModel->getModuleType());
		$viewer->assign('IS_READ_ONLY', $request->getBoolean('isReadOnly') || $this->record->getRecord()->isReadOnly());
		if ($request->getBoolean('toWidget')) {
			return $viewer->view('Detail/Widget/BlockView.tpl', $moduleName, true);
		}
		$popupMessage = $recordModel->get('popup_message');
		$viewer->assign('DOTS_POPUP_MESSAGE', $popupMessage);
		$viewer->assign('DOTS_POPUP_MESSAGE_HASH', sha1($popupMessage));
		return $viewer->view('Detail/FullContents.tpl', $moduleName, true);
	}
}

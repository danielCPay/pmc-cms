<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

class Documents_Detail_View extends Vtiger_Detail_View
{
	/**
	 * {@inheritdoc}
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('showDocumentRelations');
	}

	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(App\Request $request) {
		parent::checkPermission($request);

		$userModel = \App\User::getCurrentUserModel();
		switch ($userModel->getRole()) {
    	case Cases_ListView_Model::PROVIDER_LITIGATION_ASSISTANCE_ROLE:
        $providerFields = Cases_MergeRecordsSpecial_View::getProviderFields();
        $providers = Cases_ListView_Model::getProviderLitigationAssists($userModel->getId());
        $caseId = $this->record->getRecord()->get('case');
        if (\App\Record::isExists($caseId, 'Cases')) {
            $case = \Vtiger_Record_Model::getInstanceById($caseId, 'Cases');

            $match = false;
            foreach ($providerFields as $providerField) {
                if (in_array($case->get($providerField), $providers)) {
                    $match = true;
                    break;
                }
            }
        }

        if (!$match) {
            throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
        }
        break;
			case Cases_ListView_Model::PRE_SUIT_ROLE:
				$caseId = $this->record->getRecord()->get('case');
				if (!\App\Record::isExists($caseId, 'Cases')) {
					throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
				}
				break;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function preProcess(App\Request $request, $display = true)
	{
		$fileIcon = \App\Layout\Icon::getIconByFileType($this->record->getRecord()->get('filetype'));

		$viewer = $this->getViewer($request);
		$viewer->assign('NO_SUMMARY', true);
		$viewer->assign('EXTENSION_ICON', $fileIcon);
		parent::preProcess($request);
	}

	/**
	 * {@inheritdoc}
	 */
	public function isAjaxEnabled($recordModel)
	{
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function showModuleBasicView(App\Request $request)
	{
		return $this->showModuleDetailView($request);
	}

	public function showDocumentRelations(App\Request $request)
	{
		$recordId = $request->getInteger('record');
		$moduleName = $request->getModule();

		$data = Documents_Record_Model::getReferenceModuleByDocId($recordId);
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORDID', $recordId);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('LIMIT', 0);
		$viewer->assign('DATA', $data);

		return $viewer->view('DetailViewDocumentRelations.tpl', $moduleName, true);
	}
}

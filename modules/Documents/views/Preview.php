<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * Contributor(s): DOT Systems sp. z o.o.
 * ********************************************************************************** */

class Documents_Preview_View extends \App\Controller\View\Page
{
	public const PREVIEWABLE_FORMATS = [
		'application/pdf', 
		'image/png', 
		'image/jpeg', 
		'image/gif', 
		'image/bmp', 
		'image/tiff',
		'text/plain',
		'text/html',
		'application/xml',
	];
	public const EDITABLE_FORMATS = [
		'application/msword',
		'application/rtf',
		'application/vnd.ms-excel',
		'application/vnd.ms-powerpoint',
		'application/vnd.oasis.opendocument.text',
		'application/vnd.oasis.opendocument.spreadsheet',
		'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
		'application/vnd.ms-word.document.macroEnabled.12',
		'application/vnd.ms-excel.sheet.macroEnabled.12',
		'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
		'application/vnd.openxmlformats-officedocument.presentationml.presentation',
	];

	/** @var Vtiger_DetailView_Model $record */
	protected $record;

  /**
	 * {@inheritdoc}
	 */
	protected function showBodyHeader()
	{
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function showFooter()
	{
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function showBreadCrumbLine()
	{
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(App\Request $request)
	{
		if ($request->isEmpty('record')) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$this->record = Vtiger_DetailView_Model::getInstance($request->getModule(), $request->getInteger('record'));
		if (!$this->record->getRecord()->isViewable()) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	public function process(App\Request $request)
	{
		/** @var Documents_Record_Model $record */
		$record = $this->record->getRecord();
    // if previewable then echo content inline
    $fileDetails = $record->getFileDetails();
    if ($fileDetails && \in_array($fileDetails['type'], self::PREVIEWABLE_FORMATS)) {
			header("location: {$record->getDownloadFileURL()}&show=true");
    } else {
			$viewer = $this->getViewer($request);
			$viewer->assign('RECORD_MODEL', $this->record->getRecord());
			$viewer->assign('IS_EDITABLE', $fileDetails && \in_array($fileDetails['type'], self::EDITABLE_FORMATS) && $this->record->getRecord()->isEditable());
			if ($fileDetails) {
				$editUrl = $record->getFileDAVURL();

				if ($editUrl) {
					$viewer->assign('EDIT_URL', $editUrl);
				}
			}
		  $viewer->view('Preview.tpl', $request->getModule());
    }
	}

	public function validateRequest(App\Request $request)
	{
		$request->validateReadAccess();
	}
}

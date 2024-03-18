<?php

/**
 * Mass download action class.
 *
 * @copyright DOT Systems sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Kamiński <mkaminski@dotsystems.pl>
 */
class Documents_MassDownload_Action extends Vtiger_Mass_Action
{
	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(App\Request $request)
	{
		$recordIds = self::getRecordsListFromRequest($request);
		foreach ($recordIds as $recordId) {
			if (!\App\Privilege::isPermitted($request->getModule(), 'DetailView', $recordId)){
				throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
			}
		}
	}

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public function process(App\Request $request)
	{
		$records = self::getRecordsListFromRequest($request);
		if (1 === \count($records)) {
			/** @var Documents_Record_Model $documentRecordModel */
			$documentRecordModel = Vtiger_Record_Model::getInstanceById($records[0]);
			$documentRecordModel->downloadFile();
			$documentRecordModel->updateDownloadCount();
		} else {
			Documents_Record_Model::downloadFiles($records);
		}
	}
}

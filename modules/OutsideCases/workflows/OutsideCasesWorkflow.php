<?php

/**
 * OutsideCasesWorkflow.
 *
 * @package   Workflow
 *
 * @copyright DOT Systems
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Kamiński <mkaminski@dotsystems.pl>
 */
class OutsideCasesWorkflow
{
	/**
	 * RECALCULATE_FROM_CLAIMS algorigthm
	 *
	 * @param \OutsideCases_Record_Model $recordModel
	 */
	public static function recalculateFromClaims(Vtiger_Record_Model $recordModel)
	{
		$id = $recordModel->getId();

		\App\Log::warning("OutsideCases::Workflows::recalculateFromClaims:$id");

		$recordModel->recalculateFromClaims();
	}

	/**
	 * RECALCULATE_FROM_COLLECTIONS algorigthm
	 *
	 * @param \OutsideCases_Record_Model $recordModel
	 */
	public static function recalculateFromCollections(Vtiger_Record_Model $recordModel)
	{
		$id = $recordModel->getId();

		\App\Log::warning("OutsideCases::Workflows::recalculateFromCollections:$id");

		$recordModel->recalculateFromClaims();
	}

	/**
	 * @param \OutsideCases_Record_Model $recordModel
	 */
	public static function recalculateFromCase(Vtiger_Record_Model $recordModel)
	{
		$id = $recordModel->getId();

		\App\Log::warning("OutsideCases::Workflows::recalculateFromCase:$id");

		$recordModel->recalculateFromCase();
	}

	/**
	 * RECALCULATE_ALL algorigthm
	 *
	 * @param \OutsideCases_Record_Model $recordModel
	 */
	public static function recalculateAll(Vtiger_Record_Model $recordModel)
	{
		$id = $recordModel->getId();

		\App\Log::warning("OutsideCases::Workflows::recalculateAll:$id");

		$recordModel->recalculateAll();
	}

	public static function setDateOfService(Vtiger_Record_Model $recordModel)
	{
		$id = $recordModel->getId();

		\App\Log::warning("OutsideCases::Workflows::setDateOfService:$id");

		$claims = VTWorkflowUtils::getAllRelatedRecords($recordModel, 'Claims');
		
		$dateOfService = null;
		foreach ($claims as $claimRow) {
			$claim = Vtiger_Record_Model::getInstanceById($claimRow['id'], 'Claims');
			$claimDateOfService = $claim->get('date_of_service');
			if (empty($dateOfService) || $claimDateOfService < $dateOfService) {
				$dateOfService = $claimDateOfService;
			}
		}

		$recordModel->set('date_of_service', $dateOfService);
		$recordModel->save();
	}

	public static function calculateDateOfService(Vtiger_Record_Model $recordModel)
	{
		$id = $recordModel->getId();

		\App\Log::warning("OutsideCases::Workflows::calculateDateOfService:$id");

		$changed = 0;

		date_default_timezone_set('US/Eastern');
		$end = date('Y-m-d');

		$dateOfService = $recordModel->get('date_of_service');

		if (!empty($dateOfService)) {
			$age = date_diff(date_create_from_format('Y-m-d', $dateOfService), date_create_from_format('Y-m-d', $end))->days;
			$recordModel->set('age', $age);
			$changed = 1;
		}

		$default_timezone = \App\Config::main('default_timezone');
		date_default_timezone_set($default_timezone);

		if ($changed != 0) {
			$recordModel->setHandlerExceptions(['disableHandlerClasses' => ['ModTracker_ModTrackerHandler_Handler']]);
			$recordModel->save();
		}
	}
}

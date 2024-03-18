<?php

/**
 * TexasCasesWorkflow.
 *
 * @package   Workflow
 *
 * @copyright DOT Systems
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Kamiński <mkaminski@dotsystems.pl>
 */
class TexasCasesWorkflow
{
	/**
	 * RECALCULATE_FROM_CLAIMS algorigthm
	 *
	 * @param \TexasCases_Record_Model $recordModel
	 */
	public static function recalculateFromClaims(Vtiger_Record_Model $recordModel)
	{
		$id = $recordModel->getId();

		\App\Log::warning("TexasCases::Workflows::recalculateFromClaims:$id");

		$recordModel->recalculateFromClaims();
	}

	/**
	 * RECALCULATE_FROM_COLLECTIONS algorigthm
	 *
	 * @param \TexasCases_Record_Model $recordModel
	 */
	public static function recalculateFromCollections(Vtiger_Record_Model $recordModel)
	{
		$id = $recordModel->getId();

		\App\Log::warning("TexasCases::Workflows::recalculateFromCollections:$id");

		$recordModel->recalculateFromCollections();
	}

	/**
	 * @param \TexasCases_Record_Model $recordModel
	 */
	public static function recalculateFromOthers(Vtiger_Record_Model $recordModel)
	{
		$id = $recordModel->getId();

		\App\Log::warning("TexasCases::Workflows::recalculateFromOthers:$id");

		$recordModel->recalculateFromOthers();
	}

	/**
	 * @param \TexasCases_Record_Model $recordModel
	 */
	public static function recalculateSettlementNegotiations(Vtiger_Record_Model $recordModel)
	{
		$id = $recordModel->getId();

		\App\Log::warning("TexasCases::Workflows::recalculateSettlementNegotiations:$id");

		$recordModel->recalculateSettlementNegotiations();
	}

	/**
	 * @param \TexasCases_Record_Model $recordModel
	 */
	public static function recalculateFromCase(Vtiger_Record_Model $recordModel)
	{
		$id = $recordModel->getId();

		\App\Log::warning("TexasCases::Workflows::recalculateFromCase:$id");

		$recordModel->recalculateFromCase();
	}

	/**
	 * @param \TexasCases_Record_Model $recordModel
	 */
	public static function recalculateAll(Vtiger_Record_Model $recordModel)
	{
		$id = $recordModel->getId();

		\App\Log::warning("TexasCases::Workflows::recalculateAll:$id");

		$recordModel->recalculateAll();
	}

	

	/**
	 * Update next hearing date
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 */
	public static function updateNextHearingDate(Vtiger_Record_Model $recordModel)
	{
		$id = $recordModel->getId();

		\App\Log::warning("TexasCases::Workflows::updateNextHearingDate:" . $id);

		if (!$recordModel->get('lock_automation')) {
			$next = null;

			$events = Vtiger_RelationListView_Model::getInstance($recordModel, "Calendar");
			$eventsRows = $events->getRelationQuery()->all();
			$eventsRecords = $events->getRecordsFromArray($eventsRows);

			foreach ($eventsRecords as $id => $event) {
				$event = Vtiger_Record_Model::getInstanceById($event->getId());

				if ($event->get('activitytype') === "Hearing") {
					$timeStart = strtotime($event->get('date_start') . " " . $event->get('time_start'));

					if ($next === null || $next > $timeStart) {
						$next = $timeStart;
					}
				}
			}

			if ($next !== null) {
				$nextDate = date('Y-m-d H:i:s', $next);

				\App\Log::trace("TexasCases::updateNextHearingDate:next_hearing_date = $nextDate");
				$recordModel->set('next_hearing_date', $nextDate);

				$recordModel->save();
			}
		}
	}

	/**
	 * Calculate status age
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 */
	public static function calculateStatusAge(Vtiger_Record_Model $recordModel)
	{
		$id = $recordModel->getId();

		\App\Log::warning("TexasCases::Workflows::calculateStatusAge:" . $id);

		// status age - jako różnica w dniach między aktualną datą (czasu EST) a datą Case.Status Date (czasu EST). 
		// Przy porównaniu zignorować godzinę (ale data powinna być właściwa wg czasu EST).

		// settl_negot_demand_age - na podstawie settl_negot_demand_last_date
		// settl_negot_offer_age - na podstawie settl_negot_offer_last_date
		
		$statusAge = NULL;
		$demandAge = NULL;
		$offerAge = NULL;
		$changed = 0;
			
		date_default_timezone_set('US/Eastern');
		$end = date('Y-m-d');

		if (!empty(($recordModel->get('status_date')))) {
			$start = date('Y-m-d', strtotime($recordModel->get('status_date')));
			$statusAge = date_diff(date_create_from_format('Y-m-d', $start), date_create_from_format('Y-m-d', $end))->days;
		}

		\App\Log::trace("TexasCases::Workflows::calculateStatusAge:status_age = $statusAge");
		if ($statusAge != $recordModel->get('status_age')) {
			$recordModel->set('status_age', $statusAge);
			$changed = 1;
		}

		if (!empty(($recordModel->get('settl_negot_demand_last_date')))) {
			$start = date('Y-m-d', strtotime($recordModel->get('settl_negot_demand_last_date')));
			$demandAge = date_diff(date_create_from_format('Y-m-d', $start), date_create_from_format('Y-m-d', $end))->days;
		}

		\App\Log::trace("TexasCases::Workflows::calculateStatusAge:settl_negot_demand_age = $demandAge");
		if ($demandAge != $recordModel->get('settl_negot_demand_age')) {
			$recordModel->set('settl_negot_demand_age', $demandAge);
			$changed = 1;
		}

		if (!empty(($recordModel->get('settl_negot_offer_last_date')))) {
			$start = date('Y-m-d', strtotime($recordModel->get('settl_negot_offer_last_date')));
			$offerAge = date_diff(date_create_from_format('Y-m-d', $start), date_create_from_format('Y-m-d', $end))->days;
		}

		\App\Log::trace("TexasCases::Workflows::calculateStatusAge:settl_negot_offer_age = $offerAge");
		if ($offerAge != $recordModel->get('settl_negot_offer_age')) {
			$recordModel->set('settl_negot_offer_age', $offerAge);
			$changed = 1;
		}

		$default_timezone = \App\Config::main('default_timezone');
		date_default_timezone_set($default_timezone);

		if ($changed != 0) {
			$recordModel->setHandlerExceptions(['disableHandlerClasses' => ['ModTracker_ModTrackerHandler_Handler']]);
			$recordModel->save();
		}
	}

	/**
	 * Create similar TexasCases
	 *
	 * @param \TexasCases_Record_Model $recordModel
	 */
	public static function findSimilarTexasCases(Vtiger_Record_Model $recordModel)
	{
		$id = $recordModel->getId();

		\App\Log::warning("TexasCases::Workflows::findSimilarTexasCases:$id");

		$recordModel->findSimilarTexasCases();
	}

	/**
	 * Create Case ID
	 *
	 * @param \TexasCases_Record_Model $recordModel
	 */
	public static function setNewCaseId(Vtiger_Record_Model $recordModel)
	{
		$id = $recordModel->getId();
		$typeOfClaim = $recordModel->get('type_of_claim');

		\App\Log::warning("TexasCases::Workflows::setNewCaseId:$id/$typeOfClaim");

		$hosClaimTypes = ['HO'];
		$lopClaimTypes = ['LOP/DTP', 'PA', 'Estimates'];

		if (\in_array($typeOfClaim, $lopClaimTypes)) {
			$newId = str_replace('TXC', 'TXL', $recordModel->getRecordNumber());
		} else if (\in_array($typeOfClaim, $hosClaimTypes)) {
			$year = date('y');
			$currentNumber = 
				(new \App\QueryGenerator('TexasCases'))
					->addCondition('case_id', "TXH$year-", 's')
					->createQuery()->max("cast(regexp_replace(case_id, '^TXH$year-(\\\\d+)$', '\\\\1') as integer)") ?? 0;
			$newId = "TXH$year-" . sprintf('%06d', $currentNumber + 1);
		} else {
			$newId = $recordModel->getRecordNumber();
		}

		if ($newId && $newId !== $recordModel->get('case_id')) {
			$recordModel->set('case_id', $newId);
			$recordModel->save();
		}
	}

	/**
	 * Assign TexasCases.attorney based on TexasCases.assigned_user_id
	 *
	 * @param \TexasCases_Record_Model $recordModel
	 */
	public static function setAttorneyByAssignedTo(Vtiger_Record_Model $recordModel)
	{
		$id = $recordModel->getId();
		$assignedUserId = $recordModel->get('assigned_user_id');

		\App\Log::warning("TexasCases::Workflows::setAttorneyByAssignedTo:$id/$assignedUserId");

		$userModel = \App\User::getUserModel($assignedUserId);
		$firstName = $userModel->getDetail('first_name');
		$lastName = $userModel->getDetail('last_name');
		$res = ["/\\b$firstName\\b/iu", "/\\b$lastName\\b/iu"];

		$attorneys = (new \App\QueryGenerator('Attorneys'))->setFields(['id', 'attorney_name'])->createQuery()->all();
		$matchedAttorney = null;
		foreach ($attorneys as $attorney) {
			foreach ($res as $re) {
				if (preg_match($re, $attorney['attorney_name']) !== 1) {
					continue 2;
				}
			}
			$matchedAttorney = $attorney['id'];
			break;
		}

		$recordModel->set('attorney', $matchedAttorney);
		$recordModel->save();
  }

	/**
	 * Revert to Previous Status.
	 *
	 * @param \TexasCases_Record_Model $recordModel
	 */
	public static function revertToPreviousStatus(Vtiger_Record_Model $recordModel)
	{
		$id = $recordModel->getId();
		$previousStatus = $recordModel->get('previous_status');
		$previousStage = $recordModel->get('previous_stage');
		\App\Log::warning("TexasCases::Workflows::revertToPreviousStatus:$id/$previousStatus/$previousStage");

		// if both Previous Status and Previous Stage are empty, do nothing
		if (empty($previousStatus) && empty($previousStage)) {
			return;
		} else { // otherwise
			// disable Workflow handler
			$handlerExceptions = $recordModel->getHandlerExceptions();
			if (!isset($handlerExceptions['disableHandlerClasses']) || !in_array('Vtiger_Workflow_Handler', $handlerExceptions['disableHandlerClasses'])) {
				$recordModel->setHandlerExceptions(['disableHandlerClasses' => ['Vtiger_Workflow_Handler']]);
			}

			// set stage to Previous Stage and status to Previous Status
			$recordModel->set('stage', $previousStage);
			$recordModel->set('status', $previousStatus);

			/*
				set substatus field (pre_litigation_status, complaint_status, plaintiff_discovery_status, plaintiff_deposition_status, 
				defendant_discovery_status, defendant_deposition_status, mediation_arbitration_status, plaintiff_msj_status, defendant_msj_status,
				trial_status, settlement_status, appeal_status or pfs_crn_57_105_status) for Previous Stage to Previous Status; if there is no applicable substatus field or value, do nothing
			*/
			switch($previousStage) {
				case 'Pre-Litigation':
					$recordModel->set('pre_litigation_status', $previousStatus);
					break;
				case 'Complaint':
					$recordModel->set('complaint_status', $previousStatus);
					break;
				case 'Plaintiff Discovery':
					$recordModel->set('plaintiff_discovery_status', $previousStatus);
					break;
				case 'Plaintiff Deposition':
					$recordModel->set('plaintiff_deposition_status', $previousStatus);
					break;
				case 'Defendant Discovery':
					$recordModel->set('defendant_discovery_status', $previousStatus);
					break;
				case 'Defendant Deposition':
					$recordModel->set('defendant_deposition_status', $previousStatus);
					break;
				case 'Mediation Arbitration':
					$recordModel->set('mediation_arbitration_status', $previousStatus);
					break;
				case 'Plaintiff MSJ':
					$recordModel->set('plaintiff_msj_status', $previousStatus);
					break;
				case 'Defendant MSJ':
					$recordModel->set('defendant_msj_status', $previousStatus);
					break;
				case 'Trial':
					$recordModel->set('trial_status', $previousStatus);
					break;
				case 'Settlement':
					$recordModel->set('settlement_status', $previousStatus);
					break;
				case 'Appeal':
					$recordModel->set('appeal_status', $previousStatus);
					break;
				case 'PFS CRN 57.105':
					$recordModel->set('pfs_crn_57_105_status', $previousStatus);
					break;
			}

			// set status_date to current date and time
			$recordModel->set('status_date', date('Y-m-d H:i:s'));

			// set Previous Stage and Previous Status to empty
			$recordModel->set('previous_stage', '');
			$recordModel->set('previous_status', '');

			// save record
			$recordModel->save();

			// reenable workflow handler
			$recordModel->setHandlerExceptions($handlerExceptions);
		}
  }
}

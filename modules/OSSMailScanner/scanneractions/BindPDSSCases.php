<?php

require_once 'modules/com_vtiger_workflow/include.php';

/**
 * Mail scanner action bind Bizon modules.
 *
 * @copyright DOT Systems Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Michał Kamiński <mkaminski@dotsystems.pl>
 */
class OSSMailScanner_BindPDSSCases_ScannerAction extends OSSMailScanner_PrefixScannerAction_Model
{
  /**
	 * Module name.
	 *
	 * @var string
	 */
	public $moduleName = 'Cases';

	/**
	 * Table name.
	 *
	 * @var string
	 */
	public $tableName = 'u_yf_cases';

	/**
	 * Table column.
	 *
	 * @var string
	 */
	public $tableColumn = 'case_id';

  public function process(OSSMail_Mail_Model $mail)
	{
		$this->mail = $mail;

    \App\Log::warning("OSSMailScanner::BindPDSSCases:". print_r([
      'uniqueId' => $this->mail->getUniqueId(), 
      'crmId' => $this->mail->getMailCrmId(), 
      'folder' => $this->mail->getFolder(), 
      'type' => $this->mail->getTypeEmail(), 
      'owner' => $this->mail->getAccountOwner(), 
      'to' => $this->mail->get('to_email'), 
      'from' => $this->mail->get('from_email'), 
      'subject' => $this->mail->get('subject'), 
      'date' => $this->mail->get('date')
    ], true));

    $caseIds = [];

		// find current matches
		$mailId = $this->mail->getMailCrmId();
		$recordId = 0;
		if ($mailId) {
			$query = (new \App\Db\Query())->select(['vtiger_ossmailview_relation.crmid'])
				->from('vtiger_ossmailview_relation')
				->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = vtiger_ossmailview_relation.crmid')
				->where(['ossmailviewid' => $mailId, 'setype' => $this->moduleName])
				->andWhere(['<>', 'vtiger_crmentity.deleted', 1])
				->orderBy(['modifiedtime' => \SORT_DESC]);
			$returnIds = $query->column();
			if (!empty($returnIds)) {
				$caseIds = $returnIds;
			} else {
				$cacheName = "OSSMailScanner:Claims";
				if (\App\Cache::has('OSSMailScanner', $cacheName)){
					$claims = \App\Cache::get('OSSMailScanner', $cacheName);
				} else {
					$claims = (new \App\QueryGenerator('Claims'))->setFields(['id', 'claim_id', 'claim_number', 'case'])->createQuery()->all();
					\App\Cache::save('OSSMailScanner', $cacheName);
				}

				$cacheName = "OSSMailScanner:Cases";
				if (\App\Cache::has('OSSMailScanner', $cacheName)){
					$cases = \App\Cache::get('OSSMailScanner', $cacheName);
				} else {
					$cases = (new \App\QueryGenerator('Cases'))->setFields(['id', 'case_id', 'case_number', 'claim_number'])->createQuery()->all();
					\App\Cache::save('OSSMailScanner', $cacheName);
				}

				\App\Log::warning("OSSMailScanner::BindPDSSCases:claims = " . count($claims) . "/cases = " . count($cases));

				$subject = $this->mail->get('subject');
				$subjectProcessed = preg_replace('/[^[:alnum:]]+/ui', '', $subject);
				$body = $this->mail->get('body');
				$bodyProcessed = preg_replace('/[^[:alnum:]]+/ui', '', $body);

				$matchByIdClaim = [];
				// $matchByNumberClaim = [];
				// $matchByNumberClaimCnt = 0;
				foreach ($claims as $claim) {
					$claimId = preg_replace('/[^[:alnum:]\/\\_-]+/ui', '', $claim['claim_id']);
					if (strlen($claimId) < 15) {
						$claimId = false;
					}
					// $claimNumber = preg_replace('/[^[:alnum:]\/\\_-]+/ui', '', $claim['claim_number']);
					// if (strlen($claimNumber) < 6) {
					// 	$claimNumber = false;
					// }
					$reId = "/\\b" . preg_quote($claimId, '/') . "\\b/iu";
					// $reNumber = "/\\b" . preg_quote($claimNumber, '/') . "\\b/iu";
					if ($claimId && (preg_match($reId, $subject) === 1 || preg_match($reId, $body) === 1)) {
						// matched by claim id
						if (!empty($claim['case'])) {
							$matchByIdClaim[] = $claim['case'];
						}
					}
					// if ($claimNumber && $matchByNumberClaimCnt < 11 && (preg_match($reNumber, $subject) === 1 || preg_match($reNumber, $body) === 1)) {
					// 	// matched by number (first 11)
					// 	if (!empty($claim['case'])) {
					// 		$matchByNumberClaim[] = $claim['case'];
					// 	}
					// 	$matchByNumberClaimCnt += 1;
					// }
				}
				\App\Log::warning("OSSMailScanner::BindPDSSCases:matched from Claims, " . count($matchByIdClaim) . " by id");
				// \App\Log::warning("OSSMailScanner::BindPDSSCases:matched from Claims, " . count($matchByIdClaim) . " by id and " . count($matchByNumberClaim) . " by number");
				array_push($caseIds, ...$matchByIdClaim);
				// if ($matchByNumberClaimCnt <= 10) {
				// 	array_push($caseIds, ...$matchByNumberClaim);
				// }

				$matchById = [];
				$matchByNumber = [];
				foreach ($cases as $case) {
					$caseId = preg_replace('/[^[:alnum:]\/\\_-]+/ui', '', $case['case_id']);
					if (strlen($caseId) < 11) {
						$caseId = false;
					}
					$caseNumber = preg_replace('/[^[:alnum:]]+/ui', '', $case['case_number']);
					if (strlen($caseNumber) < 7) {
						$caseNumber = false;
					}
					// $claimNumber = preg_replace('/[^[:alnum:]\/\\_-]+/ui', '', $case['claim_number']);
					// if (strlen($claimNumber) < 4) {
					// 	$claimNumber = false;
					// }
					$reId = "/\\b" . preg_quote($caseId, '/') . "\\b/iu";
					$reNumber = "/" . preg_quote($caseNumber, '/') . "/iu";
					// $reNumberClaim = "/\\b" . preg_quote($claimNumber, '/') . "\\b/iu";
					if ($caseId && (preg_match($reId, $subject) === 1 || preg_match($reId, $body) === 1)) {
						// matched by case id
						$matchById[] = $case['id'];
					}
					if (count($matchByNumber) < 11) {
						if ($caseNumber && (preg_match($reNumber, $subjectProcessed) === 1 || preg_match($reNumber, $bodyProcessed) === 1)) {
							// matched by number (first 11)
							$matchByNumber[] = $case['id'];
						} 
						// else if ($claimNumber && (preg_match($reNumberClaim, $subject) === 1 || preg_match($reNumberClaim, $body) === 1)) {
						// 	// matched by number (first 11)
						// 	$matchByNumber[] = $case['id'];
						// }
					}
				}
				\App\Log::warning("OSSMailScanner::BindPDSSCases:matched " . count($matchById) . " by id and " . count($matchByNumber) . " by number");
				array_push($caseIds, ...$matchById);
				if (count($matchByNumber) <= 10) {
					array_push($caseIds, ...$matchByNumber);
					$caseIds = array_unique($caseIds);
				}
				
				\App\Log::warning("OSSMailScanner::BindPDSSCases:matches = " . var_export($caseIds, true));

				// save matches in DB
				$caseIds = array_filter($caseIds, function ($id) { return \App\Record::isExists($id); } );
				(new OSSMailView_Relation_Model())->addRelation($mailId, $caseIds, $this->mail->get('date'));

				// process
				if (!empty($caseIds)) {
					$mail = Vtiger_Record_Model::getInstanceById($this->mail->getMailCrmId());
					$shareWith = explode(',', $mail->get('shownerid'));

					foreach ($caseIds as $matchId) {
						$match = Vtiger_Record_Model::getInstanceById($matchId);
						
						// get Assigned To and Case Manager, append to Share with
						$shareWith[] = $match->get('assigned_user_id');
						$shareWith[] = $match->get('case_manager');
					}
					
					if (!empty($shareWith)) {
						$mail->set('shownerid', array_unique(array_filter($shareWith)));
						$mail->save();
					}

					if($this->mail->getTypeEmail() != 'Sent') {
						$wfs = new VTWorkflowManager();

						$workflow = $wfs->retrieveByName('New e-mail received (Case)', 'Cases');
						
						foreach (array_unique($caseIds) as $caseId) {
							$recordModel = Vtiger_Record_Model::getInstanceById($caseId);

							\App\Log::info("New mail received for Case $caseId");

							$workflow->performTasks($recordModel);
						}
					}
				}

				// process Outside Cases
				$this->processOutsideCases();
			}
		}

    return $caseIds;
	}

	public function processOutsideCases() {
		$mailId = $this->mail->getMailCrmId();
		$subject = $this->mail->get('subject');
		$body = $this->mail->get('body');

		$cacheName = "OSSMailScanner:OutsideCases";
		if (\App\Cache::has('OSSMailScanner', $cacheName)){
			$outsideCases = \App\Cache::get('OSSMailScanner', $cacheName);
		} else {
			$outsideCases = (new \App\QueryGenerator('OutsideCases'))->setFields(['id', 'outside_case_id'])->createQuery()->all();
			\App\Cache::save('OSSMailScanner', $cacheName);
		}

		\App\Log::warning("OSSMailScanner::BindPDSSCases:outside = " . count($outsideCases));

		$caseIds = [];

		$matchById = [];
		foreach ($outsideCases as $case) {
			$caseId = preg_replace('/[^[:alnum:]\/\\_-]+/ui', '', $case['outside_case_id']);
			if (strlen($caseId) < 10) {
				$caseId = false;
			}
			$reId = "/\\b" . preg_quote($caseId, '/') . "\\b/iu";
			if ($caseId && (preg_match($reId, $subject) === 1 || preg_match($reId, $body) === 1)) {
				// matched by case id
				$matchById[] = $case['id'];
			}
		}
		\App\Log::warning("OSSMailScanner::BindPDSSCases:matched outside cases = " . count($matchById) . " by id");
		array_push($caseIds, ...$matchById);

		(new OSSMailView_Relation_Model())->addRelation($mailId, $caseIds, $this->mail->get('date'));

		if (!empty($caseIds)) {
			$mail = Vtiger_Record_Model::getInstanceById($this->mail->getMailCrmId());
			$shareWith = explode(',', $mail->get('shownerid'));

			foreach ($caseIds as $matchId) {
				$match = Vtiger_Record_Model::getInstanceById($matchId);
				
				// get Assigned To and Case Manager, append to Share with
				$shareWith[] = $match->get('assigned_user_id');
			}
			
			if (!empty($shareWith)) {
				$mail->set('shownerid', array_unique(array_filter($shareWith)));
				$mail->save();
			}

			// if($this->mail->getTypeEmail() != 'Sent') {
			// 	$wfs = new VTWorkflowManager();

			// 	$workflow = $wfs->retrieveByName('New e-mail received (Case)', 'Cases');
				
			// 	foreach (array_unique($caseIds) as $caseId) {
			// 		$recordModel = Vtiger_Record_Model::getInstanceById($caseId);

			// 		\App\Log::info("New mail received for Case $caseId");

			// 		$workflow->performTasks($recordModel);
			// 	}
			// }
		}

		return $caseIds;
	}
}

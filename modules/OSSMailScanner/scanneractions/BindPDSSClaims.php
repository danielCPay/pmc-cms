<?php

require_once 'modules/com_vtiger_workflow/include.php';

/**
 * Mail scanner action bind Bizon modules.
 *
 * @copyright DOT Systems Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Michał Kamiński <mkaminski@dotsystems.pl>
 */
class OSSMailScanner_BindPDSSClaims_ScannerAction extends OSSMailScanner_PrefixScannerAction_Model
{
  /**
	 * Module name.
	 *
	 * @var string
	 */
	public $moduleName = 'Claims';

	/**
	 * Table name.
	 *
	 * @var string
	 */
	public $tableName = 'u_yf_claims';

	/**
	 * Table column.
	 *
	 * @var string
	 */
	public $tableColumn = 'claim_id';

  public function process(OSSMail_Mail_Model $mail)
	{
		$this->mail = $mail;

    \App\Log::warning("OSSMailScanner::BindPDSSClaims:". print_r([
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

		$claimIds = [];

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
				$claimIds = $returnIds;
			} else {
				// $cases = (new \App\QueryGenerator('Cases'))->setFields(['id', 'case_id', 'case_number', 'claim_number'])->createQuery()->all();
				$cacheName = "OSSMailScanner:Claims";
				if (\App\Cache::has('OSSMailScanner', $cacheName)){
					$claims = \App\Cache::get('OSSMailScanner', $cacheName);
				} else {
					$claims = (new \App\QueryGenerator('Claims'))->setFields(['id', 'claim_id', 'claim_number', 'case'])->createQuery()->all();
					\App\Cache::save('OSSMailScanner', $cacheName);
				}

				\App\Log::warning("OSSMailScanner::BindPDSSClaims:claims = " . count($claims));

				$subject = $this->mail->get('subject');
				$body = $this->mail->get('body');

				$matchById = [];
				// $matchByNumber = [];
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
						$matchById[] = $claim['id'];
					}
					// if ($claimNumber && count($matchByNumber) < 11 && (preg_match($reNumber, $subject) === 1 || preg_match($reNumber, $body) === 1)) {
					// 	// matched by number (first 11)
					// 	$matchByNumber[] = $claim['id'];
					// }
				}
				\App\Log::warning("OSSMailScanner::BindPDSSClaims:matched " . count($matchById) . " by id");
				// \App\Log::warning("OSSMailScanner::BindPDSSClaims:matched " . count($matchById) . " by id and " . count($matchByNumber) . " by number");
				array_push($claimIds, ...$matchById);
				// if (count($matchByNumber) <= 10) {
				// 	array_push($claimIds, ...$matchByNumber);
				// 	$claimIds = array_unique($claimIds);
				// }
				
				\App\Log::warning("OSSMailScanner::BindPDSSClaims:matches = " . var_export($claimIds, true));

				// save matches in DB
				$claimIds = array_filter($claimIds, function ($id) { return \App\Record::isExists($id); } );
				(new OSSMailView_Relation_Model())->addRelation($mailId, $claimIds, $this->mail->get('date'));

				// process
				if (!empty($claimIds)) {
					$mail = Vtiger_Record_Model::getInstanceById($mailId);
					$shareWith = explode(',', $mail->get('shownerid'));
		
					foreach ($claimIds as $matchId) {
						$match = Vtiger_Record_Model::getInstanceById($matchId);
						
						// get Assigned To, append to Share with
						$shareWith[] = $match->get('assigned_user_id');
					}
					
					if (!empty($shareWith)) {
						$mail->set('shownerid', array_unique(array_filter($shareWith)));
						$mail->save();
					}
		
					if ($this->mail->getTypeEmail() != 'Sent') {
						$wfs = new VTWorkflowManager();
		
						$workflow = $wfs->retrieveByName('New e-mail received (Claim)', 'Claims');
						
						foreach (array_unique($claimIds) as $claimId) {
							$recordModel = Vtiger_Record_Model::getInstanceById($claimId);
		
							\App\Log::info("New mail received for Claim $claimId");
		
							$workflow->performTasks($recordModel);
						}
					}
				}
			}
		}


    return $claimIds;
	}
}

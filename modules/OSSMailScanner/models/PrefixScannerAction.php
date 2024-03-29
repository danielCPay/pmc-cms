<?php
/**
 * Base for action creating relations on the basis of prefix.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Base for action creating relations on the basis of prefix.
 */
abstract class OSSMailScanner_PrefixScannerAction_Model
{
	public $prefix;
	public $moduleName;
	public $mail;
	public $tableName;
	public $tableColumn;

	/**
	 * Process.
	 *
	 * @param OSSMail_Mail_Model $mail
	 */
	abstract public function process(OSSMail_Mail_Model $mail);

	/**
	 * Find and bind.
	 *
	 * @return bool|int|array
	 */
	public function findAndBind(bool $byRecordNumber = true)
	{
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
				$recordId = $returnIds;
			} else {
				$this->prefix = \App\Mail\RecordFinder::getRecordNumberFromString($this->mail->get('subject'), $this->moduleName, false, $byRecordNumber);
				if ($this->prefix) {
					$recordId = $this->add();
				} elseif (\Config\Modules\OSSMailScanner::$searchPrefixInBody && ($this->prefix = \App\Mail\RecordFinder::getRecordNumberFromString($this->mail->get('body'), $this->moduleName, true, $byRecordNumber))) {
					$recordId = $this->addByBody();
				}
			}
		}
		return $recordId;
	}

	/**
	 * Add.
	 *
	 * @return array
	 */
	protected function add()
	{
		$returnIds = [];
		$crmId = false;
		// 20220518 disable cache
		// if (\App\Cache::has('getRecordByPrefix', $this->prefix)) {
		// 	$crmId = \App\Cache::get('getRecordByPrefix', $this->prefix);
		// } else {
			$moduleObject = CRMEntity::getInstance($this->moduleName);
			$tableIndex = $moduleObject->tab_name_index[$this->tableName];
			$query = (new \App\Db\Query())
				->select([$tableIndex])
				->from($this->tableName)
				->innerJoin('vtiger_crmentity', "$this->tableName.$tableIndex = vtiger_crmentity.crmid")
				->where([$this->tableName . '.' . $this->tableColumn => $this->prefix]);
			$query = $query->andWhere(['vtiger_crmentity.deleted' => 0]);
			$query = $query->orderBy(['modifiedtime' => \SORT_DESC]);
			$crmId = $query->scalar();
			// if ($crmId) {
			// 	\App\Cache::save('getRecordByPrefix', $this->prefix, $crmId, \App\Cache::LONG);
			// }
		// }
		if ($crmId) {
			$status = (new OSSMailView_Relation_Model())->addRelation($this->mail->getMailCrmId(), $crmId, $this->mail->get('date'));
			if ($status) {
				$returnIds[] = $crmId;
			}
		}
		return $returnIds;
	}

	/**
	 * Add by body.
	 *
	 * @return array
	 */
	protected function addByBody()
	{
		$returnIds = [];
		if ($this->prefix && !empty($crmId = $this->findRecord())) {
			$status = (new OSSMailView_Relation_Model())->addRelation($this->mail->getMailCrmId(), $crmId, $this->mail->get('udate_formated'));
			if ($status) {
				$returnIds[] = $crmId;
			}
		}
		return $returnIds;
	}

	/**
	 * Find record by prefix.
	 *
	 * @return int|null
	 */
	public function findRecord()
	{
		$moduleObject = CRMEntity::getInstance($this->moduleName);
		$tableIndex = $moduleObject->tab_name_index[$this->tableName];
		$query = (new \App\Db\Query())
				->select([$tableIndex])
				->from($this->tableName)
				->innerJoin('vtiger_crmentity', "$this->tableName.$tableIndex = vtiger_crmentity.crmid")
				->where([$this->tableName . '.' . $this->tableColumn => $this->prefix]);
			if (!\in_array($this->moduleName, ['Claims', 'ClaimOpportunities'])) {
				$query = $query->andWhere(['vtiger_crmentity.deleted' => 0]);
			}
			$query = $query->orderBy(['modifiedtime' => \SORT_DESC]);
			$crmId = $query->scalar();

		return $crmId ?? false;
	}
}

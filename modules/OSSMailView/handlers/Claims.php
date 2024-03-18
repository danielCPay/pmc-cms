<?php

/**
 * Link handler for Claims
 *
 * @copyright DOT Systems Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Kamiński <mkaminski@dotsystems.pl>
 */
class OSSMailView_Claims_Handler
{
	private function refreshIsMatched($eventHandler) {
    $params = $eventHandler->getParams();
		if ($params['sourceModule'] !== 'OSSMailView' && $params['destinationModule'] !== 'OSSMailView') {
			return;
		}

		$mailId = $params['sourceModule'] === 'OSSMailView' ? $params['sourceRecordId'] : $params['destinationRecordId'];

    $query = (new \App\Db\Query())->select(['vtiger_ossmailview_relation.crmid'])
			->from('vtiger_ossmailview_relation')
			->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = vtiger_ossmailview_relation.crmid')
			->where(['ossmailviewid' => $mailId, 'setype' => ['ClaimOpportunities', 'Claims']])
			->andWhere(['<>', 'vtiger_crmentity.deleted', 1]);

		$count = $query->count();

		$recordModel = Vtiger_Record_Model::getInstanceById($mailId);
		$recordModel->set('is_matched', $count !== 0);
		$recordModel->save();
  }

	/**
	 * EntityAfterUnLink handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterUnLink(App\EventHandler $eventHandler)
	{
		$this->refreshIsMatched($eventHandler);
	}

	/**
	 * EntityAfterLink handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterLink(App\EventHandler $eventHandler)
	{
    $this->refreshIsMatched($eventHandler);
	}
}

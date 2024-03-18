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

class TexasCases_DetailView_Model extends Vtiger_DetailView_Model
{
	/**
	 * Function to get the detail view related links.
	 *
	 * @return <array> - list of links parameters
	 */
	public function getDetailViewRelatedLinks()
	{
		$recordModel = $this->getRecord();
		$relatedLinks = parent::getDetailViewRelatedLinks();
		$newRelatedLinks = [];
		foreach ($relatedLinks as $relatedLink) {
			$newRelatedLinks[] = $relatedLink;
			if ($relatedLink['linktype'] === 'DETAILVIEWTAB' && $relatedLink['linklabel'] === 'LBL_RECORD_DETAILS') {
				$newRelatedLinks[] = [
					'linktype' => 'DETAILVIEWTAB',
					'linklabel' => 'LBL_RECORD_DETAILS_PRELITIGATION',
					'linkKey' => 'LBL_RECORD_DETAILS_PRELITIGATION',
					'linkurl' => $recordModel->getDetailViewUrl() . '&mode=showDetailViewByMode&requestMode=prelitigation',
					'linkicon' => '',
					'related' => 'Details-Prelitigation',
				];
				$newRelatedLinks[] = [
					'linktype' => 'DETAILVIEWTAB',
					'linklabel' => 'LBL_RECORD_DETAILS_LITIGATION',
					'linkKey' => 'LBL_RECORD_DETAILS_LITIGATION',
					'linkurl' => $recordModel->getDetailViewUrl() . '&mode=showDetailViewByMode&requestMode=litigation',
					'linkicon' => '',
					'related' => 'Details-Litigation',
				];
				$newRelatedLinks[] = [
					'linktype' => 'DETAILVIEWTAB',
					'linklabel' => 'LBL_RECORD_DETAILS_TRIAL',
					'linkKey' => 'LBL_RECORD_DETAILS_TRIAL',
					'linkurl' => $recordModel->getDetailViewUrl() . '&mode=showDetailViewByMode&requestMode=trial',
					'linkicon' => '',
					'related' => 'Details-Trial',
				];
				$newRelatedLinks[] = [
					'linktype' => 'DETAILVIEWTAB',
					'linklabel' => 'LBL_RECORD_DETAILS_SETTLEMENT',
					'linkKey' => 'LBL_RECORD_DETAILS_SETTLEMENT',
					'linkurl' => $recordModel->getDetailViewUrl() . '&mode=showDetailViewByMode&requestMode=settlement',
					'linkicon' => '',
					'related' => 'Details-Settlement',
				];
			}
		}
		return $newRelatedLinks;
	}
}

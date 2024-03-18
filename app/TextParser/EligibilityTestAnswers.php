<?php

namespace App\TextParser;

/**
 * Print eligibility test answers for CO connected to this Agreement.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */
class EligibilityTestAnswers extends Base
{
	/** @var array Allowed modules */
	public $allowedModules = ['ClaimLSFundingAgreements', 'ClaimLegalServicesAgr', 'ClaimOpportunities', 'Claims'];

	/** @var string Class name */
	public $name = 'LBL_ELIGIBILITY_TEST_ANSWERS';

	/** @var mixed Parser type */
	public $type = 'pdf';

	/**
	 * Process.
	 *
	 * @return string
	 */
	public function process()
	{
		$html = '';
		// get claim opportunity
		/** @var $co ClaimOppotunities_Record_Model */
		$co = null;
		switch ($this->textParser->moduleName) {
			case 'Claims':
				$agreement = \Vtiger_Record_Model::getInstanceById('claim_ls_agreement');
				$co = \Vtiger_Record_Model::getInstanceById($agreement->get('claim_opportunity'));
				break;
			case 'ClaimLSFundingAgreements':
			case 'ClaimLegalServicesAgr':
				$co = \Vtiger_Record_Model::getInstanceById($this->textParser->recordModel->get('claim_opportunity'));
				break;
			default:
				$co = $this->textParser->recordModel;
				break;
		}
		$answers = \VTWorkflowUtils::getAllRelatedRecords($co, 'EligibilityTestAnswers');
		usort($answers, function ($a, $b) { return $a['eligibility_test_number'] - $b['eligibility_test_number']; });
		$module = \Vtiger_Module_Model::getInstance('EligibilityTestAnswers');
		$headerStyle = 'font-size:8px;padding:0px 4px;text-align:left;';
		$bodyStyle = 'font-size:8px;border:1px solid #ddd;padding:0px 4px;';
		$html .= '<table class="products-table-long-version" style="width:100%;font-size:8px;border-collapse:collapse;">
				<thead>
					<tr>';
		foreach (['question', 'answer'] as $fieldName) {
			$fieldModel = $module->getFieldByName($fieldName);
			$html .= "<th style=\"{$headerStyle}\">" . \App\Language::translate($fieldModel->get('label'), $module->getName(), 'pl-PL') . '</th>';
		}
		$html .= '</tr></thead>';
		
		$html .= '<tbody>';
		$counter = 1;
		foreach ($answers as $answer) {
			if (empty($answer['answer'])) {
				continue;
			}
			$html .= '<tr class="row-' . $counter++ . '">';
			$html .= "<td style=\"{$bodyStyle}\">" . $answer['question'] . '</td>';
			$html .= "<td style=\"{$bodyStyle}font-weight:bold;\">" . \App\Language::translate($answer['answer'], $module->getName(), 'pl-PL') . '</td>';
			$html .= '</tr>';
		}
		$html .= '</tbody></table>';
		return $html;
	}
}

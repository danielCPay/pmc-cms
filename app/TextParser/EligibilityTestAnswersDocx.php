<?php

namespace App\TextParser;

/**
 * Print eligibility test answers for CO connected to this Agreement.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */
class EligibilityTestAnswersDocx extends Base
{
	/** @var array Allowed modules */
	public $allowedModules = ['ClaimLSFundingAgreements', 'ClaimLegalServicesAgr', 'ClaimOpportunities', 'Claims'];

	/** @var string Class name */
	public $name = 'LBL_ELIGIBILITY_TEST_ANSWERS_DOCX';

	/** @var mixed Parser type */
	public $type = 'pdf';

	/**
	 * Process.
	 *
	 * @return string
	 */
	public function process()
	{
		\App\Log::warning("TextParser::EligibilityTestAnswersDocx");
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
		
		$output = '</w:t></w:r></w:p>';
		foreach ($answers as $answer) {
			if (empty($answer['answer'])) {
				continue;
			}
			$output .= '<w:p>
			<w:pPr>
				<w:spacing w:before="120"/>
				<w:jc w:val="both"/>
			</w:pPr>
			<w:r>
				<w:rPr>
					<w:rFonts w:ascii="Calibri" w:hAnsi="Calibri" w:cs="Calibri"/>
					<w:sz w:val="22"/>
				</w:rPr>
				<w:t xml:space="preserve">' . $answer['question'] . '</w:t>
			</w:r>
			<w:r>
				<w:rPr>
					<w:rFonts w:ascii="Calibri" w:hAnsi="Calibri" w:cs="Calibri"/>
					<w:sz w:val="22"/>
				</w:rPr>
				<w:t xml:space="preserve"> </w:t>
			</w:r>
			<w:r>
				<w:rPr>
					<w:rFonts w:ascii="Calibri" w:hAnsi="Calibri" w:cs="Calibri"/>
					<w:b/>
					<w:sz w:val="22"/>
				</w:rPr>
				<w:t>' . \App\Language::translate($answer['answer'], $module->getName(), 'pl-PL') . '</w:t>
			</w:r>
		</w:p>';
		}
		$output .= '<w:p><w:r><w:t>';
		return $output;
	}
}

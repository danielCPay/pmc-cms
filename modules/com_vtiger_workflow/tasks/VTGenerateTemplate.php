<?php

/**
 * Generate DocumentTemplate Task Class.
 *
 * @copyright DOT Systems Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Michał Kamiński <mkaminski@dotsystems.pl>
 */
class VTGenerateTemplate extends VTTask
{
	public $executeImmediately = true;

	/**
	 * Get field names.
	 *
	 * @return string[]
	 */
	public function getFieldNames()
	{
		return ['template', 'conditionString', 'stopOnError'];
	}

	/**
	 * Execute task.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function doTask($recordModel, $originalRecordModel = null)
	{
		if (!empty($this->template)) {
			if (!empty($this->conditionString)) {
				try {
					$textParser = \App\TextParser::getInstanceByModel($recordModel);
					if(!\App\Utils\Completions::processIfCondition(htmlspecialchars_decode($this->conditionString), $textParser)) {
						\App\Log::warning("Workflows::VTGenerateTemplate:invalid condition");
						return;
					}
				} finally {
					unset($textParser);
				}
			}

			try {
				$templateId = VTWorkflowUtils::getDocumentTemplateByNumber($this->template);
				/** @var DocumentTemplates_Record_Model $template */
				$template = Vtiger_Record_Model::getInstanceById($templateId);
				$template->generateDocument($recordModel);
			} catch (\Throwable $t) {
				\App\Log::warning("Workflows::VTGenerateTemplate:Error while generating document - " . $t->getMessage());
				\App\Log::error(var_export($t, true));
				$entry = \VTWorkflowUtils::createBatchErrorEntryRaw($this->summary, $this->workflowId, $recordModel->getModuleName(), "Document generation failed in template " . $template->getDisplayName() . " (" . $template->getId() . ")", $recordModel->getId());
				if ($this->stopOnError) {
					throw new \App\Exceptions\BatchErrorHandledNoRethrowWorkflowException("Document generation failed in template " . $template->getDisplayName() . " (" . $template->getId() . ")", 0, $t, $entry);
				}
				return false;
			}
		}
	}
}

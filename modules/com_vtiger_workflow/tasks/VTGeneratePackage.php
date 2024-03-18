<?php

/**
 * Generate DocumentPackage Task Class.
 *
 * @copyright DOT Systems Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Michał Kamiński <mkaminski@dotsystems.pl>
 */
class VTGeneratePackage extends VTTask
{
	public $executeImmediately = true;

	/**
	 * Get field names.
	 *
	 * @return string[]
	 */
	public function getFieldNames()
	{
		return ['package', 'conditionString', 'shouldSend', 'shouldDropbox', 'stopOnError'];
	}

	/**
	 * Execute task.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function doTask($recordModel, $originalRecordModel = null)
	{
		if (!empty($this->package)) {
			if (!empty($this->conditionString)) {
				try {
					$textParser = \App\TextParser::getInstanceByModel($recordModel);
					if(!\App\Utils\Completions::processIfCondition(htmlspecialchars_decode($this->conditionString), $textParser)) {
						\App\Log::warning("Workflows::VTGeneratePackage:invalid condition");
						return;
					}
				} finally {
					unset($textParser);
				}
			}

			try {
				$packageId = VTWorkflowUtils::getDocumentPackageByName($this->package);
				/** @var DocumentPackages_Record_Model $package */
				$package = Vtiger_Record_Model::getInstanceById($packageId);
				$id = $package->generate($recordModel);
				$package->send($recordModel, $id);
				$package->dropbox($recordModel, $id);
			} catch (\Throwable $t) {
				\App\Log::error("Workflows::VTGeneratePackage:Error while generating document package - " . $t->getMessage());
				\App\Log::error(var_export($t, true));
				$entry = \VTWorkflowUtils::createBatchErrorEntryRaw($this->summary, $this->workflowId, $recordModel->getModuleName(), "Document generation failed in package " . $package->getDisplayName() . " (" . $package->getId() . ") due to error: " . $t->getMessage(), $recordModel->getId());
				$entry->set('document_package', $packageId);
				$entry->save();
				if ($this->stopOnError) {
					throw new \App\Exceptions\BatchErrorHandledNoRethrowWorkflowException("Document generation failed in package " . $package->getDisplayName() . " (" . $package->getId() . ") due to error: " . $t->getMessage(), 0, $t, $entry);
				}
				return false;
			}
		}
	}
}

<?php

/**
 * Generate PDF Task Class.
 *
 * @copyright DOT Systems Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Michał Kamiński <mkaminski@dotsystems.pl>
 */
class VTGeneratePdf extends VTTask
{
	/** @var bool Generating PDF is slow, so do it through the queue. */
	public $executeImmediately = true;

	/**
	 * Get field names.
	 *
	 * @return string[]
	 */
	public function getFieldNames()
	{
		return ['pdfTemplate'];
	}

	/**
	 * Execute task.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function doTask($recordModel, $originalRecordModel = null)
	{
		if (!empty($this->pdfTemplate)) {
			VTWorkflowUtils::generateDocument($recordModel, $this->pdfTemplate);
		}
	}
}

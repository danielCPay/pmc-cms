<?php

/**
 * InsuredsWorkflow.
 *
 * @package   Workflow
 *
 * @copyright DOT Systems
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Kamiński <mkaminski@dotsystems.pl>
 */
class InsuredsWorkflow
{
  /**
	 * Find county
	 *
	 * @param \Insureds_Record_Model $recordModel
	 */
	public static function findCounty(Vtiger_Record_Model $recordModel)
	{
		$id = $recordModel->getId();

		\App\Log::warning("Insureds::Workflows::findCounty:" . $id);
    
    $recordModel->findCounty();
	}
}

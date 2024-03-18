<?php
/**
 * Workflow action - allows to call workflows by API.
 *
 * @package Api
 *
 * @copyright DOT Systems Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Kamiński <mkaminski@dotsystems.pl>
 */

namespace Api\Portal\BaseModule;

/**
 * Workflow class.
 */
class Workflow extends \Api\Core\BaseAction
{
	/** @var string[] Allowed request methods */
	public $allowedMethod = ['PUT'];
	/**
	 * Record model.
	 *
	 * @var \Vtiger_Record_Model
	 */
	protected $recordModel;

	/**
	 * Check permission to method.
	 *
	 * @throws \Api\Core\Exception
	 *
	 * @return bool
	 */
	public function checkPermission()
	{
		$result = parent::checkPermission();
		$moduleName = $this->controller->request->getModule();
		if (!\Api\Portal\Privilege::isPermitted($moduleName, 'WorkflowTrigger')) {
			throw new \Api\Core\Exception("No permissions for action {$moduleName}:WorkflowTrigger", 405);
		}
		$this->recordModel = \Vtiger_Record_Model::getInstanceById($this->controller->request->getInteger('record'), $moduleName);
		if (!$this->recordModel->isViewable()) {
			throw new \Api\Core\Exception('No permissions to view record', 403);
		}
		return $result;
	}

	/**
	 * PUT method.
	 *
	 * @return array
	 */
	public function put()
	{
		$workflows = $this->controller->request->getArray('workflows', 'String');
		$moduleName = $this->controller->request->getModule();

		require_once 'modules/com_vtiger_workflow/include.php';
		$wfs = new \VTWorkflowManager();

		\App\Log::warning("API::Workflow::" . print_r(['record' => $this->recordModel->getId(), 'workflows' => $workflows], true));
		foreach ($workflows as $workflowName) {
			$workflow = $wfs->retrieveByName($workflowName, $moduleName);
			if (!$workflow || !$workflow->evaluate($this->recordModel)) {
				\App\Log::warning("API::Workflow::skip " . var_export(['!workflow' => !$workflow, 'evaluate' => $workflow->evaluate($this->recordModel)], true));
				continue;
			}
			$workflow->performTasks($this->recordModel);
		}

		$return = ['id' => $this->recordModel->getId()];
		return $return;
	}
}

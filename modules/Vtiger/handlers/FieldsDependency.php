<?php
/**
 * Base fields dependency handler file.
 *
 * @package		Handler
 *
 * @copyright	YetiForce Sp. z o.o
 * @license		YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author		Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Base fields dependency handler class.
 */
class Vtiger_FieldsDependency_Handler
{
	/**
	 * EditViewChangeValue handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function editViewChangeValue(App\EventHandler $eventHandler)
	{
		$return = [];
		$recordModel = $eventHandler->getRecordModel();
		$fieldsDependency = \App\FieldsDependency::getByRecordModel(\App\Request::_getByType('fromView'), $recordModel);
		if ($fieldsDependency['show']['frontend']) {
			$return['showFields'] = $fieldsDependency['show']['frontend'];
		}
		if ($fieldsDependency['hide']['frontend']) {
			$return['hideFields'] = $fieldsDependency['hide']['frontend'];
		}
		return $return;
	}

	/**
	 * Function to get the record model based on the request parameters.
	 *
	 * @param \App\EventHandler $eventHandler
	 * @param \App\Request $request
	 *
	 * @return Vtiger_Record_Model or Module specific Record Model instance
	 */
	private function getRecordModelFromRequest(App\EventHandler $eventHandler, App\Request $request)
	{
		if ('SaveAjax' === $request->getByType('action') && !$request->isEmpty('record')) {
			$recordModel = $eventHandler->getRecordModel() ? $eventHandler->getRecordModel() : Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $request->getModule());
			$fieldModel = $recordModel->getModule()->getFieldByName($request->getByType('field', 2));
			if ($fieldModel && $fieldModel->isEditable()) {
				$fieldModel->getUITypeModel()->setValueFromRequest($request, $recordModel, 'value');
			}
		}
		return $recordModel ?? $eventHandler->getRecordModel();
	}

	/**
	 * EditViewPreSave handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function editViewPreSave(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		$response = ['result' => true];
		$fieldsDependency = \App\FieldsDependency::getByRecordModel(\App\Request::_getByType('fromView'), $recordModel);
		if ($fieldsDependency['show']['mandatory']) {
			$mandatoryFields = [];
			foreach ($fieldsDependency['show']['mandatory'] as $fieldName) {
				if ('' === $recordModel->get($fieldName) || ($recordModel->getField($fieldName)->isReferenceField() && $recordModel->get($fieldName) === 0)) {
					$mandatoryFields[] = $recordModel->getField($fieldName)->getFullLabelTranslation();
				}
			}
			if ($mandatoryFields) {
				$response = [
					'result' => false,
					'hoverField' => reset($fieldsDependency['show']['mandatory']),
					'message' => \App\Language::translate('LBL_NOT_FILLED_MANDATORY_FIELDS') . ': <br /> - ' . implode('<br /> - ', $mandatoryFields)
				];
			}
		}
		if ($response['result'] === true) {
			// validate after changes from edit
			$recordModel = $this->getRecordModelFromRequest($eventHandler, \App\Request::init());
			$response = ['result' => true];
			$fieldsDependency = \App\FieldsDependency::getByRecordModel(\App\Request::_getByType('fromView') ?: (\App\Request::_getByType('action') === 'SaveAjax' ? 'Edit' : ''), $recordModel);
			if ($fieldsDependency['show']['mandatory']) {
				$mandatoryFields = [];
				foreach ($fieldsDependency['show']['mandatory'] as $fieldName) {
					if ('' === $recordModel->get($fieldName) || ($recordModel->getField($fieldName)->isReferenceField() && $recordModel->get($fieldName) === 0)) {
						$mandatoryFields[] = $recordModel->getField($fieldName)->getFullLabelTranslation();
					}
				}
				if ($mandatoryFields) {
					$response = [
						'result' => false,
						'hoverField' => reset($fieldsDependency['show']['mandatory']),
						'message' => \App\Language::translate('LBL_NOT_FILLED_MANDATORY_FIELDS_FULL_EDIT') . ': <br /> - ' . implode('<br /> - ', $mandatoryFields)
					];
				}
			}
		}
		return $response;
	}

	/**
	 * Get variables for the current event.
	 *
	 * @param string $name
	 * @param array  $params
	 * @param string $moduleName
	 *
	 * @return array|null
	 */
	public function vars(string $name, array $params, string $moduleName): ?array
	{
		if (\App\EventHandler::EDIT_VIEW_CHANGE_VALUE === $name) {
			[$recordModel,$view] = $params;
			if (empty($recordModel)) {
				$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
			}
			return \App\FieldsDependency::getByRecordModel($view, $recordModel)['conditionsFields'];
		}
		return null;
	}
}

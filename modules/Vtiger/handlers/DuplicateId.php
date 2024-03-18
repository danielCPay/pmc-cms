<?php
/**
 * Duplicate id handler.
 *
 * @package Handler
 *
 * @copyright DOT Systems sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Michał Kamiński <mkaminski@dotsystems.pl>
 */
/**
 * Vtiger_DuplicateId_Handler class.
 */
class Vtiger_DuplicateId_Handler
{
	/** @var string $fieldName */
	protected $fieldName = null;

	/** @var string[] $ignoreValues */
	protected $ignoreValues = [];

	/**
	 * EditViewPreSave handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function editViewPreSave(App\EventHandler $eventHandler)
	{
		$response = ['result' => true];
		
		if (!empty($this->fieldName)) {
			$recordModel = $eventHandler->getRecordModel();
			$fieldModel = $recordModel->getModule()->getFieldByName($this->fieldName);
			if ($fieldModel->isViewable() && ($value = $recordModel->get($this->fieldName)) && !\in_array($value, $this->ignoreValues)) {
				$queryGenerator = new \App\QueryGenerator($recordModel->getModuleName());
				$queryGenerator->setFields(['id'])->permissions = false;
				$queryGenerator->addCondition($fieldModel->getName(), $value, 'e');
				if ($recordModel->getId()) {
					$queryGenerator->addCondition('id', $recordModel->getId(), 'n');
				}
				if ($queryGenerator->createQuery()->exists()) {
					$response = [
						'result' => false,
						'hoverField' => $this->fieldName,
						'message' => App\Language::translateArgs('LBL_DUPLICATE_FIELD_VALUE', $recordModel->getModuleName(), $fieldModel->getFullLabelTranslation())
					];
				}
			}
		}
		return $response;
	}
}

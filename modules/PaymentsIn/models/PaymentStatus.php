<?php
/**
 * The file contains: Class to change of payment status on related records.
 *
 * @package Model
 *
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Adach <a.adach@yetiforce.com>
 */

/**
 * Change of payment status on related records.
 */
abstract class PaymentsIn_PaymentStatus_Model
{
	/**
	 * Module name.
	 *
	 * @var string
	 */
	protected static $moduleName;

	/**
	 * Field payment status name.
	 *
	 * @var string
	 */
	protected static $fieldPaymentStatusName;

	/**
	 * Field payment sum name.
	 *
	 * @var string
	 */
	protected static $fieldPaymentSumName;

	/**
	 * Related record ID name.
	 *
	 * @var string
	 */
	protected static $relatedRecordIdName;

	/**
	 * Allowable underpayment.
	 * 
	 * @var float
	 */
	protected static $allowableUnderpayment = 0;

	/**
	 * Update if possible.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return void
	 */
	public static function updateIfPossible(Vtiger_Record_Model $recordModel, bool $isDelete = false)
	{
		\App\Log::warning(static::class . "::updateIfPossible:" . $recordModel->getId());
		if (static::canUpdatePaymentStatus($recordModel, $isDelete)) {
			if (!empty($recordModel->get(static::$relatedRecordIdName))) {
				(new \App\BatchMethod(['method' => static::class . '::updatePaymentStatus', 'params' => [$recordModel->get(static::$relatedRecordIdName)]]))->save();
			}
			if (!empty($recordModel->getPreviousValue(static::$relatedRecordIdName))) {
				(new \App\BatchMethod(['method' => static::class . '::updatePaymentStatus', 'params' => [$recordModel->getPreviousValue(static::$relatedRecordIdName)]]))->save();
			}
		}
	}

	/**
	 * Update payment status.
	 *
	 * @param int $recordId
	 *
	 * @return void
	 */
	public static function updatePaymentStatus(int $recordId)
	{
		\App\Log::warning(static::class . "::updatePaymentStatus:" . $recordId);
		$changes = false;
		$recordModel = \Vtiger_Record_Model::getInstanceById($recordId, static::$moduleName);
		if(!empty(static::$fieldPaymentStatusName)){
			$statusFieldModel = $recordModel->getField(static::$fieldPaymentStatusName);
			if ($statusFieldModel && $statusFieldModel->isActiveField() && !empty($recordModel->get(
				static::$fieldPaymentStatusName))) {
				$recordModel->set(
					static::$fieldPaymentStatusName,
					static::calculatePaymentStatus((float) $recordModel->get('sum_gross'), static::getSumOfPaymentsByRecordId($recordId, static::$moduleName))
				);
				$changes = true;
			}
		}
		if(!empty(static::$fieldPaymentSumName)){
			$sumFieldModel = $recordModel->getField(static::$fieldPaymentSumName);
			if ($sumFieldModel && $sumFieldModel->isActiveField()) {
				$recordModel->set(
					static::$fieldPaymentSumName,
					static::getSumOfPaymentsByRecordId($recordId, static::$moduleName)
				);
				$changes = true;
			}
		}
		if ($changes) {
			$recordModel->save();
		}
	}

	/**
	 * Get the sum of all payments by record ID.
	 *
	 * @param int    $recordId
	 * @param string $moduleName
	 *
	 * @return float
	 */
	public static function getSumOfPaymentsByRecordId(int $recordId, string $moduleName): float
	{
		$cacheNamespace = "getSumOfPaymentsByRecordId.{$moduleName}";
		if (\App\Cache::staticHas($cacheNamespace, $recordId)) {
			$sumOfPayments = (float) \App\Cache::staticGet($cacheNamespace, $recordId);
		} else {
			$relationModel = Vtiger_Relation_Model::getInstance(
				Vtiger_Module_Model::getInstance($moduleName),
				Vtiger_Module_Model::getInstance('PaymentsIn')
			);
			$relationModel->set('parentRecord', Vtiger_Record_Model::getInstanceById($recordId, $moduleName));
			$queryGenerator = $relationModel->getQuery();
			$queryGenerator->addNativeCondition(['vtiger_paymentsin.paymentsin_status' => 'PLL_PAID']);
			$sumOfPayments = (float) $queryGenerator->createQuery()
				->sum('vtiger_paymentsin.paymentsvalue');
			\App\Cache::staticSave($cacheNamespace, $recordId, $sumOfPayments);
		}
		return $sumOfPayments;
	}

	/**
	 * Calculate payment status.
	 *
	 * @param float $sumOfGross
	 * @param float $sumOfPayments
	 *
	 * @return string
	 */
	protected static function calculatePaymentStatus(float $sumOfGross, float $sumOfPayments): string
	{
		if (\App\Validator::floatIsEqual($sumOfGross, $sumOfPayments, 2) || (!\App\Validator::floatIsEqual(static::$allowableUnderpayment, 0.0, 2) && $sumOfGross - $sumOfPayments <= static::$allowableUnderpayment)) {
			$paymentStatus = 'PLL_PAID';
		} elseif (\App\Validator::floatIsEqual(0.0, $sumOfPayments, 2)) {
			$paymentStatus = 'PLL_NOT_PAID';
		} elseif ($sumOfGross > $sumOfPayments) {
			$paymentStatus = 'PLL_UNDERPAID';
		} else {
			$paymentStatus = 'PLL_OVERPAID';
		}

		\App\Log::warning(static::class . "::calculatePaymentStatus:$paymentStatus");

		return $paymentStatus;
	}

	/**
	 * Checking if you can update the payment status.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return bool
	 */
	protected static function canUpdatePaymentStatus(Vtiger_Record_Model $recordModel, bool $isDelete = false): bool
	{
		\App\Log::warning(static::class . "::canUpdatePaymentStatus:" . var_export(['relatedRecordIdName' => static::$relatedRecordIdName, 'isEmpty' => $recordModel->isEmpty(static::$relatedRecordIdName), 'isDelete' => $isDelete, 'isNew' => $recordModel->isNew(), 'previousStatus' => $recordModel->getPreviousValue('paymentsin_status'), 'previousId' => $recordModel->getPreviousValue(static::$relatedRecordIdName)], true));

		$returnValue = (!$recordModel->isEmpty(static::$relatedRecordIdName) || !empty($recordModel->getPreviousValue(static::$relatedRecordIdName))) && ($isDelete || $recordModel->isNew() || false !== $recordModel->getPreviousValue('paymentsin_status') || false !== $recordModel->getPreviousValue(static::$relatedRecordIdName) || false !== $recordModel->getPreviousValue('paymentsvalue'));
		if ($returnValue) {
			$fieldModel = \Vtiger_Module_Model::getInstance(static::$moduleName)->getFieldByName(static::$fieldPaymentStatusName);
			$returnValue = $fieldModel && $fieldModel->isActiveField();
		}

		\App\Log::warning(static::class . "::canUpdatePaymentStatus:$returnValue");

		return $returnValue;
	}
}

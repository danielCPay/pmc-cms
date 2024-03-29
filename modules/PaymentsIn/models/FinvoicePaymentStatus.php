<?php
/**
 * The file contains: Class to change the payment status of a sales invoice.
 *
 * @package Model
 *
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Adach <a.adach@yetiforce.com>
 */

/**
 * Class to change the payment status of a sales invoice.
 */
class PaymentsIn_FinvoicePaymentStatus_Model extends PaymentsIn_PaymentStatus_Model
{
	/**
	 * {@inheritdoc}
	 */
	protected static $moduleName = 'FInvoice';

	/**
	 * {@inheritdoc}
	 */
	protected static $fieldPaymentStatusName = 'payment_status';

	/**
	 * {@inheritdoc}
	 */
	protected static $fieldPaymentSumName = 'payment_sum';

	/**
	 * {@inheritdoc}
	 */
	protected static $relatedRecordIdName = 'finvoiceid';

	/**
	 * {@inheritdoc}
	 */
	public static function updatePaymentStatus(int $recordId)
	{
		$getProviderByName = function($name) {
			return (new \App\QueryGenerator('Providers'))->addCondition('provider_name', $name, 'e')->createQuery()->scalar();
		};

		$recordModel = \Vtiger_Record_Model::getInstanceById($recordId, static::$moduleName);
		
		$fromId = $getProviderByName('LWB');
		$toId = $getProviderByName('RePlan');

		// underpayment allowed only for invoices from LWB to RePlan
		if($recordModel->get('seller') == $fromId && $recordModel->get('buyer') == $toId) {
			static::$allowableUnderpayment = 400;
		} else {
			static::$allowableUnderpayment = 0;
		}

		parent::updatePaymentStatus($recordId);
	}
}

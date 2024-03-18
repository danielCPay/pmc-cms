<?php

/**
 * Mail scanner action bind Customers.
 *
 * @copyright DOT Systems Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Michał Kamiński <mkaminski@dotsystems.pl>
 */
class OSSMailScanner_BindCustomers_ScannerAction extends OSSMailScanner_EmailScannerAction_Model
{
	public function process(OSSMail_Mail_Model $mail, $moduleName = 'Customers')
	{
		return parent::process($mail, 'Customers');
	}
}

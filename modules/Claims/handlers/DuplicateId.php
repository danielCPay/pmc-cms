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
 * Claims_DuplicateId_Handler class.
 */
class Claims_DuplicateId_Handler extends Vtiger_DuplicateId_Handler
{
	protected $fieldName = 'claim_id';
	protected $ignoreValues = ['(New)'];
}

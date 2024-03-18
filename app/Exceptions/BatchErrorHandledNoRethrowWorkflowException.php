<?php

namespace App\Exceptions;

/**
 * WorkflowException represents a workflow error.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Kamiński <mkaminski@dotsystems.pl>
 */
class BatchErrorHandledNoRethrowWorkflowException extends NoRethrowWorkflowException { 
  /** @var Vtiger_Record_Model $batchError */
  public $batchError;

  public function __construct($message = "", $code = 0, \Throwable $previous = null, \Vtiger_Record_Model $entry = null) {
    parent::__construct($message, $code, $previous);

    $this->batchError = $entry;
  }
}

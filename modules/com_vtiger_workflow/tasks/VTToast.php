<?php
/**
 * Toast Task Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Kamiński <mkaminski@dotsystems.pl>
 */

class VTToast extends VTTask
{
	public $executeImmediately = true;

	public function getFieldNames()
	{
		return ['message', 'title', 'level', 'sticky'];
	}

	/**
	 * Execute task.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function doTask($recordModel, $originalRecordModel = null)
	{
		$textParser = \App\TextParser::getInstanceByModel($recordModel);
		$message = $textParser->setContent($this->message)->parse()->getContent();
		$title = $textParser->setContent($this->title)->parse()->getContent();
		unset($textParser);

		\App\Toasts::addToast(\App\User::getCurrentUserId(), $message, $this->level . ( $this->sticky ? 'Sticky' : '' ), $title);
	}
}

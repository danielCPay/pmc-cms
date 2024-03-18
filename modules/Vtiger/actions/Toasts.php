<?php

/**
 * Toast actions.
 *
 * @copyright 	YetiForce Sp. z o.o
 * @license 	YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author   	Michał Kamiński <mkaminski@dotsystems.pl>
 */

use App\ToastManager;

/**
 * Vtiger_Toasts_Action class.
 */
class Vtiger_Toasts_Action extends \App\Controller\Action
{
	use \App\Controller\ExposeMethod;

	private const SESSION_KEY = 'lastToastId';

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		return true;
	}

	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getToasts');
		$this->exposeMethod('markToastsAsDisplayed');
	}

	public function getToasts(\App\Request $request)
	{
		$user = \App\User::getCurrentUserId();
		if ($request->getRaw('lastId') !== '') {
			$lastId = $request->getInteger('lastId', null);
		}

		$toasts = \App\Toasts::getOldestUndisplayedToasts($user, -1, $lastId) ?: [];

		// If there are more than 5 toasts, create artificial toast with 
		// error about too many messages. Return that toast (in first position) + 4 more. 
		// Mark all toasts, also those not returned as displayed. Return $lastId as maximum id
		// of all toasts, also not displayed.
		if (count($toasts) > 5) {
			$toastIds = array_column($toasts, 'id');
			$lastId = max($toastIds);
			$toasts = array_merge(
				[
					[
						'id' => -1,
						'added' => date('Y-m-d H:i:s'),
						'owner' => $user,
						'level' => 'error',
						'title' => \App\Language::translate('LBL_TOO_MANY_TOASTS_TITLE'),
						'message' => \App\Language::translateArgs('LBL_TOO_MANY_TOASTS_MESSAGE', '_Base', count($toastIds) - 4),
					],
				],
				array_slice($toasts, 0, 4)
			);
			\App\Toasts::markToastsAsDisplayed($toastIds);
		} else if (count($toasts)) {
			$lastId = max(array_column($toasts, 'id'));
		} else if (empty($lastId)) {
			$lastId = \App\Toasts::getGreatestId($user);
		}

		$response = new Vtiger_Response();
		$response->setResult(['toasts' => $toasts, 'lastId' => $lastId]);
		$response->emit();
	}

	public function markToastsAsDisplayed(App\Request $request)
	{
		$ids = $request->getArray('ids', 'Integer');
		
		$num = \App\Toasts::markToastsAsDisplayed($ids);

		$response = new Vtiger_Response();
		$response->setResult($num);
		$response->emit();
	}
}

<?php

/**
 * LawFirmsWorkflow.
 *
 * @package   Workflow
 *
 * @copyright DOT Systems
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Kamiński <mkaminski@dotsystems.pl>
 */
class LawFirmsWorkflow
{
  /**
	 * Call Portal API to create activtation link
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 */
	public static function createActivationLink(Vtiger_Record_Model $recordModel) {
		$id = $recordModel->getId();
		\App\Log::warning("LawFirms::Workflows::createActivationLink:" . $id);

		$url = \App\Config::api('LAW_FIRM_PORTAL_API_URL') . 'createActivationLink';
		$body = ['json' => ['id' => $id]];

		// TODO remove verify => false
		$options = array_merge(\App\RequestHttp::getOptions(), ['verify' => false]);
		$client = (new \GuzzleHttp\Client($options));
		$request = $client->request('POST', $url, $body);
		if (200 !== $request->getStatusCode()) {
			throw new \App\Exceptions\AppException('Error with connection |' . $request->getReasonPhrase());
		}
	}

  /**
	 * Call Portal API to create reset password link
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 */
	public static function createResetPasswordLink(Vtiger_Record_Model $recordModel) {
		$id = $recordModel->getId();
		\App\Log::warning("LawFirms::Workflows::createResetPasswordLink:" . $id);

		$url = \App\Config::api('LAW_FIRM_PORTAL_API_URL') . 'resetPasswordLink';
		$body = ['json' => ['id' => $id]];

    // TODO remove verify => false
		$options = array_merge(\App\RequestHttp::getOptions(), ['verify' => false]);
    $client = (new \GuzzleHttp\Client($options));
		$request = $client->request('POST', $url, $body);
		if (200 !== $request->getStatusCode()) {
			throw new \App\Exceptions\AppException('Error with connection |' . $request->getReasonPhrase());
		}
	}

	/**
	 * Create onetime password
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 */
	public static function createOnetimePassword(Vtiger_Record_Model $recordModel) {
		$id = $recordModel->getId();
		\App\Log::warning("LawFirms::Workflows::createOnetimePassword:" . $id);

		// generate random onetime password
		$onetimePassword = \App\Encryption::generatePassword(8);

		// save onetime password in database
		$recordModel->set('onetime_password', $onetimePassword);
		$recordModel->save();
	}
}

<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */
require_once 'include/ConfigUtils.php';
require_once 'include/utils/CommonUtils.php';
require_once 'include/fields/DateTimeField.php';
require_once 'include/fields/DateTimeRange.php';
require_once 'include/fields/CurrencyField.php';
require_once 'include/CRMEntity.php';
include_once 'modules/Vtiger/CRMEntity.php';
require_once 'include/runtime/Cache.php';
require_once 'modules/Vtiger/helpers/Util.php';
require_once 'modules/PickList/DependentPickListUtils.php';
require_once 'modules/Users/Users.php';
require_once 'include/Webservices/Utils.php';
require_once 'include/Loader.php';
Vtiger_Loader::includeOnce('include.runtime.EntryPoint');
App\Cache::init();
App\Debuger::init();
App\Db::$connectCache = App\Config::performance('ENABLE_CACHING_DB_CONNECTION');
App\Log::$logToProfile = Yii::$logToProfile = App\Config::debug('LOG_TO_PROFILE');
App\Log::$logToConsole = App\Config::debug('DISPLAY_LOGS_IN_CONSOLE');
App\Log::$logToFile = App\Config::debug('LOG_TO_FILE');

class Vtiger_WebUI extends Vtiger_EntryPoint
{
	/**
	 * User privileges model instance.
	 *
	 * @var Users_Privileges_Model
	 */
	protected $userPrivilegesModel;

	/**
	 * Function to check if the User has logged in.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\Unauthorized
	 */
	protected function checkLogin(App\Request $request)
	{
		if (!$this->hasLogin()) {
			if ($request->isAjax()) {
				throw new \App\Exceptions\Unauthorized('LBL_LOGIN_IS_REQUIRED', 401);
			}
			header('location: index.php');
			return true;
		}
		return false;
	}

	/**
	 * Function to get the instance of the logged in User.
	 *
	 * @return Users object
	 */
	public function getLogin()
	{
		$user = parent::getLogin();
		if (!$user && App\Session::has('authenticated_user_id')) {
			// \App\Log::warning("APP::login:No user but has authenticated_user_id in session");
			$userId = App\Session::get('authenticated_user_id');
			if ($userId && App\Config::main('application_unique_key') === App\Session::get('app_unique_key') && \App\User::isExists($userId)) {
				// \App\Log::warning("APP::login:Found user ($userId)");
				\App\User::setCurrentUserId($userId);
				$this->setLogin();
			} else {
				\App\Log::warning("APP::login problem:" . var_export(['userId' => $userId, 'appKey1' => App\Config::main('application_unique_key'), 'appKey2' => App\Session::get('app_unique_key'), 'exists' => \App\User::isExists($userId)], true));
			}
		} else {
			// \App\Log::warning("APP::login:Has user or no user in session");
		}

		return $user;
	}

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 *
	 * @throws Exception
	 * @throws \App\Exceptions\AppException
	 */
	public function process(App\Request $request)
	{
		if (\Config\Security::$forceHttpsRedirection && !\App\RequestUtil::isHttps()) {
			header("location: https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]", true, 301);
		}
		if (\Config\Security::$forceUrlRedirection) {
			$requestUrl = (\App\RequestUtil::isHttps() ? 'https' : 'http') . '://' . $request->getServer('HTTP_HOST') . $request->getServer('REQUEST_URI');
			if (0 !== stripos($requestUrl, App\Config::main('site_URL'))) {
				header('location: ' . App\Config::main('site_URL'), true, 301);
			}
		}
		try {
			$moduleName = $request->getModule();
			$qualifiedModuleName = $request->getModule(false);
			$view = $request->getByType('view', 2);
			$mode = $request->getByType('mode', 2);
			$action = $request->getByType('action', 2);
			$relatedModule = $request->getRaw('relatedModule');
			
			// log all requests, except following
			$ignoreRequests = [
				[ 'module' => 'Notification', 'view' => 'Reminders'],
				[ 'module' => 'Notification', 'action' => 'Toasts'],
				[ 'module' => 'Calendar', 'view' => 'Reminders'],
				[ 'module' => 'ModTracker', 'action' => 'ChangesReviewedOn'],
				[ 'module' => 'ModTracker', 'action' => 'getRelatedListPageCount'],
				[ 'relatedModule' => 'ModTracker', 'mode' => 'getRelatedListPageCount'],
				[ 'module' => 'Chat' ],
				[ 'view' => 'Pagination' ],
				[ 'view' => 'RecordPopover' ],
				[ 'view' => 'Detail', 'mode' => 'showRelatedRecords'],
				[ 'view' => 'Detail', 'mode' => 'showRelatedList'],
				[ 'view' => 'Detail', 'mode' => 'showDetailViewByMode'],
			];
			$shouldLog = true;
			foreach($ignoreRequests as $filter) {
				foreach($filter as $field => $value) {
					switch($field) {
						case 'module':
							if ($moduleName !== $value) {
								break 2;
							}
							break;
						case 'relatedModule':
							if ($relatedModule !== $value) {
								break 2;
							}
							break;
						case 'view':
							if ($view !== $value) {
								break 2;
							}
							break;
						case 'mode':
							if ($mode !== $value) {
								break 2;
							}
							break;
						case 'action':
							if ($action !== $value) {
								break 2;
							}
							break;
					}
					$shouldLog = false;
					break 2;
				}
			}
			if ($shouldLog) {
				$start = hrtime(true);
				\App\Log::warning("REQUEST");
			}

			App\Session::init();
			// common utils api called, depend on this variable right now
			$this->getLogin();
			App\Debuger::initConsole();
			$hasLogin = $this->hasLogin();

			$response = false;
			if (!$hasLogin && 'GET' === $_SERVER['REQUEST_METHOD'] && ($returnUrl = $request->getServer('QUERY_STRING')) && !\App\Session::has('return_params') && $returnUrl != 'module=Users&amp;view=Login') {
				\App\Log::warning("No login, preparing redirect to $returnUrl");

				//Take the url that user would like to redirect after they have successfully logged in.
				\App\Session::set('return_params', str_replace('&amp;', '&', $returnUrl));
			}
			if (empty($moduleName)) {
				if ($hasLogin) {
					$defaultModule = App\Config::main('default_module');
					if (!empty($defaultModule) && 'Home' !== $defaultModule && \App\Privilege::isPermitted($defaultModule)) {
						$moduleName = $defaultModule;
						$qualifiedModuleName = $defaultModule;
						if (empty($view = Vtiger_Module_Model::getInstance($moduleName)->getDefaultViewName())) {
							$view = 'List';
						}
					} else {
						$qualifiedModuleName = $moduleName = 'Home';
						$view = 'DashBoard';
					}
				} else {
					$qualifiedModuleName = $moduleName = 'Users';
					$view = 'Login';
				}
				$request->set('module', $moduleName);
				$request->set('view', $view);
			}
			if (!empty($action)) {
				$componentType = 'Action';
				$componentName = $action;
				\App\Config::setJsEnv('action', $action);
			} else {
				$componentType = 'View';
				if (empty($view)) {
					$view = 'Index';
				}
				$componentName = $view;
				\App\Config::setJsEnv('view', $view);
			}
			if ('Login' === $view && 'Users' === $moduleName) {
				if (!\App\Session::has('CSP_TOKEN')) {
					\App\Session::set('CSP_TOKEN', hash('sha256', \App\Encryption::generatePassword(10)));
				}
				if ($hasLogin) {
					header('location: index.php');
					return false;
				}
			}
			\App\Process::$processName = $componentName;
			\App\Process::$processType = $componentType;
			\App\Config::setJsEnv('module', $moduleName);
			\App\Config::setJsEnv('mode', $request->getMode());
			if ($qualifiedModuleName && 0 === stripos($qualifiedModuleName, 'Settings') && empty(\App\User::getCurrentUserId())) {
				header('location: ' . App\Config::main('site_URL'), true);
			}

			if ($view === 'Detail' && $request->getRaw('recordNumber') && empty($request->getRaw('record'))) {
				$url = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

				$recordId = \App\Record::getIdByRecordNumber($request->getRaw('recordNumber'), $moduleName);
				if (!empty($recordId)) {
					$url = str_replace('recordNumber=' . $request->getRaw('recordNumber'), "record=$recordId", $url);
					header("location: $url", true, 301);
					return false;
				}
			}

			if ('AppComponents' === $moduleName) {
				$handlerClass = "App\\Controller\\Components\\{$componentType}\\{$componentName}";
			} else {
				$handlerClass = Vtiger_Loader::getComponentClassName($componentType, $componentName, $qualifiedModuleName);
			}
			$handler = new $handlerClass();
			if (!$handler) {
				\App\Log::error("HandlerClass: $handlerClass", 'Loader');
				throw new \App\Exceptions\AppException('LBL_HANDLER_NOT_FOUND', 405);
			}
			if ($handler->csrfActive) {
				$handler->validateRequest($request);
			}
			if ($handler->loginRequired() && $this->checkLogin($request)) {
				return true;
			}
			if ($handler->isSessionExtend($request)) {
				\App\Session::set('last_activity', \App\Process::$startTime);
			}
			if ('ModComments' === $moduleName && 'List' === $view) {
				header('location: index.php?module=Home&view=DashBoard');
			}
			$skipList = ['Users', 'Home', 'CustomView', 'Import', 'Export', 'Install', 'ModTracker', 'AppComponents'];
			if ($handler->loginRequired() && !\in_array($moduleName, $skipList) && false === stripos($qualifiedModuleName, 'Settings')) {
				$this->triggerCheckPermission($handler, $request);
			} elseif (0 === stripos($qualifiedModuleName, 'Settings') || \in_array($moduleName, $skipList) || !$handler->loginRequired()) {
				$handler->checkPermission($request);
			}
			$this->triggerPreProcess($handler, $request);
			$response = $handler->process($request);
			$this->triggerPostProcess($handler, $request);
		} catch (Throwable $e) {
			\App\Log::error($e->getMessage() . PHP_EOL . $e->__toString());
			$messageHeader = 'LBL_ERROR';
			if ($e instanceof \App\Exceptions\NoPermittedToRecord || $e instanceof WebServiceException) {
				$messageHeader = 'LBL_PERMISSION_DENIED';
			} elseif ($e instanceof \App\Exceptions\Security) {
				$messageHeader = 'LBL_BAD_REQUEST';
			} elseif ($e instanceof \yii\db\Exception) {
				$messageHeader = 'LBL_ERROR';
			}
			\vtlib\Functions::throwNewException($e, false, $messageHeader);
			if (!($request->isAjax() && $request->isJSON())) {
				if (App\Config::debug('DISPLAY_EXCEPTION_BACKTRACE')) {
					echo '<pre class="my-5 mx-auto card p-3 u-w-fit shadow js-exception-backtrace">' . App\Purifier::encodeHtml(str_replace(ROOT_DIRECTORY . DIRECTORY_SEPARATOR, '', $e->__toString())) . '</pre>';
					$response = false;
				}
				if (App\Config::debug('DISPLAY_EXCEPTION_LOGS')) {
					echo '<pre class="my-5 mx-auto card p-3 u-w-fit shadow js-exception-logs">' . App\Purifier::encodeHtml(str_replace(ROOT_DIRECTORY . DIRECTORY_SEPARATOR, '', \App\Log::getlastLogs())) . '</pre>';
					$response = false;
				}
			}
			if ('test' === App\Config::main('systemMode')) {
				file_put_contents('cache/logs/request.log', print_r($request->getAll(), true));
				if (\function_exists('apache_request_headers')) {
					file_put_contents('cache/logs/request.log', print_r(apache_request_headers(), true));
				}
				throw $e;
			}
		}
		if (\is_object($response)) {
			$response->emit();
		}
		if ($shouldLog) {
			$duration = (hrtime(true) - $start) / 1000000000;
			\App\Log::warning("REQUEST FINISHED - $duration s" . ($duration > 1 ? " (SLOW)" : ""));
		}
	}

	/**
	 * Trigger check permission.
	 *
	 * @param \App\Controller\Base $handler
	 * @param \App\Request         $request
	 *
	 * @throws \App\Exceptions\AppException
	 * @throws \App\Exceptions\NoPermitted
	 *
	 * @return bool
	 */
	protected function triggerCheckPermission(App\Controller\Base $handler, App\Request $request)
	{
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		if (empty($moduleModel)) {
			\App\Log::error('HandlerModule: ' . $moduleName, 'Loader');
			throw new \App\Exceptions\AppException('ERR_MODULE_DOES_NOT_EXIST||' . $moduleName, 405);
		}
		$this->userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if ($this->userPrivilegesModel->hasModulePermission($moduleName)) {
			$handler->checkPermission($request);
			return true;
		}
		\App\Log::error("No permissions to the module: $moduleName", 'NoPermitted');
		throw new \App\Exceptions\NoPermitted('ERR_NOT_ACCESSIBLE', 403);
	}

	/**
	 * Trigger pre process.
	 *
	 * @param \App\Controller\Base $handler
	 * @param \App\Request         $request
	 *
	 * @return bool
	 */
	protected function triggerPreProcess(App\Controller\Base $handler, App\Request $request)
	{
		$handler->sendHeaders();
		if ($request->isAjax()) {
			$handler->preProcessAjax($request);
			return true;
		}
		$handler->preProcess($request);
	}

	/**
	 * Trigger post process.
	 *
	 * @param \App\Controller\Base $handler
	 * @param \App\Request         $request
	 *
	 * @return bool
	 */
	protected function triggerPostProcess(App\Controller\Base $handler, App\Request $request)
	{
		if ($request->isAjax()) {
			$handler->postProcessAjax($request);
			return true;
		}
		$handler->postProcess($request);
	}

	/**
	 * Content Security Policy token.
	 *
	 * @return void
	 */
	public function cspInitToken(): void
	{
		if (!App\Session::has('CSP_TOKEN') || App\Session::get('CSP_TOKEN_TIME') < time()) {
			App\Session::set('CSP_TOKEN', \base64_encode(\random_bytes(16)));
			App\Session::set('CSP_TOKEN_TIME', strtotime('+' . \Config\Security::$cspHeaderTokenTime));
		}
	}
}

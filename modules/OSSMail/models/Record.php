<?php

/**
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMail_Record_Model extends Vtiger_Record_Model
{
	/**
	 * Return accounts array.
	 *
	 * @param int|bool $user
	 * @param bool     $onlyMy
	 * @param bool     $password
	 *
	 * @return array
	 */
	public static function getAccountsList($user = false, $onlyMy = false, $password = false)
	{
		$users = [];
		$query = (new \App\Db\Query())->from('roundcube_users');
		if ($user) {
			$query->where(['user_id' => $user]);
		}
		if ($onlyMy) {
			$userModel = \App\User::getCurrentUserModel();
			$crmUsers = $userModel->getGroups();
			$crmUsers[] = $userModel->getId();
			$query->andWhere(['crm_user_id' => $crmUsers]);
		}
		if ($password) {
			$query->andWhere(['<>', 'password', '']);
		}
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$row['actions'] = empty($row['actions']) ? [] : explode(',', $row['actions']);
			$users[] = $row;
		}
		$dataReader->close();

		return $users;
	}

	/**
	 * Returns Roundcube configuration.
	 *
	 * @return array
	 */
	public static function loadRoundcubeConfig()
	{
		$configMail = \App\Config::module('OSSMail');
		if (!\defined('RCMAIL_VERSION') && file_exists(RCUBE_INSTALL_PATH . '/program/include/iniset.php')) {
			// read rcube version from iniset
			$iniset = file_get_contents(RCUBE_INSTALL_PATH . '/program/include/iniset.php');
			if (preg_match('/define\(.RCMAIL_VERSION.,\s*.([0-9.]+[a-z-]*)?/', $iniset, $matches)) {
				$rcubeVersion = str_replace('-git', '.999', $matches[1]);
				\define('RCMAIL_VERSION', $rcubeVersion);
				\define('RCUBE_VERSION', $rcubeVersion);
			} else {
				throw new \App\Exceptions\AppException('Unable to find a Roundcube version');
			}
		}
		include 'public_html/modules/OSSMail/roundcube/config/defaults.inc.php';
		return $configMail + $config;
	}

	/**
	 * Imap connection cache.
	 *
	 * @var array
	 */
	protected static $imapConnectCache = [];

	/**
	 * $imapConnectMailbox.
	 *
	 * @var string
	 */
	public static $imapConnectMailbox = '';

	public static function isOauth() {
		return !empty(\App\Config::module('OSSMail')['oauth_provider']);
	}

	/**
	 * Return imap connection resource.
	 *
	 * @param string $user
	 * @param string $password
	 * @param string $host
	 * @param string $folder     Character encoding UTF7-IMAP
	 * @param bool   $dieOnError
	 * @param array  $config
	 *
	 * @return resource|\Webklex\PHPIMAP\Folder
	 */
	public static function imapConnect($user, $password, $host = false, $folder = 'INBOX', $dieOnError = true, $config = false)
	{
		\App\Log::info("Entering OSSMail_Record_Model::imapConnect($user , $password , $folder) method ...");
		if (!$config) {
			$config = self::loadRoundcubeConfig();
		}
		$cacheName = $user . $host . $folder;
		if (isset(self::$imapConnectCache[$cacheName])) {
			return self::$imapConnectCache[$cacheName];
		}
		if (self::isOauth()) {
			\App\Log::beginProfile(__METHOD__ . '|oauth_token', 'Mail|IMAP');
			// get access token
			$client = new \GuzzleHttp\Client([
				'timeout' => 10.0,
				'verify' => true,
			]);

			try {
				$response = $client->post($config['oauth_token_uri'], [
					'form_params' => [
						'client_id'     => $config['oauth_client_id'],
						'client_secret' => $config['oauth_client_secret'],
						'refresh_token' => $password,
						'grant_type'    => 'refresh_token',
					],
				]);
			} catch (\Throwable $t) {
				\App\Log::error("Mailer Error: OAUTH2 connect error - " . print_r($t, true));
				
				// if error is refresh token failure, add notification for users with access to this mailbox to login interactively
				$excMessage = $t->__toString();
				if (stripos($excMessage, 'invalid_grant') !== false) {
					// find users with access to account/find management users
					$users = \App\Db::getInstance()->createCommand("SELECT 
						al.crmuser_id, u.language
					FROM 
						roundcube_users ru
						JOIN roundcube_users_autologin al ON ( al.rcuser_id = ru.user_id )
						JOIN vtiger_users u ON ( u.id = al.crmuser_id )
						JOIN vtiger_user2role u2r ON ( u2r.userid = u.id )
						JOIN vtiger_role r ON ( r.roleid = u2r.roleid )
					WHERE
						ru.username = :email
						AND ( r.roleid = 'H2' OR r.rolename = 'DOTS' )", ['email' => $user])->queryAll();

					// loop users, fetch language, send notification translated into proper language
					foreach ($users as $userData) {
						[ 'crmuser_id' => $userId, 'language' => $language ] = $userData;
						\VTWorkflowUtils::createNotificationRaw(
							[$userId], 
							\App\Language::translate('title', 'OSSMail', $language),
							sprintf(\App\Language::translate('description', 'OSSMail', $language), $user),
							'PLL_USERS'
						);
					}
				}
				
				return false;
			}

			['access_token' => $accessToken] = \GuzzleHttp\Utils::jsonDecode($response->getBody(), true);
			\App\Log::endProfile(__METHOD__ . '|oauth_token', 'Mail|IMAP');

			$webklexConfig = [ 
				'default' => false, 
				'accounts' => [
					'default' => [
						'host'           => $host ?: $config['default_host'],
						'port'           => 993,
						'encryption'     => 'ssl',
						'validate_cert'  => true,
						'protocol'       => 'imap',
						'username'       => $user,
						'password'       => $accessToken,
						'authentication' => 'oauth',
				 ]
				],
			];

			\App\Log::beginProfile(__METHOD__ . '|imap_open', 'Mail|IMAP');
			$cm = new \Webklex\PHPIMAP\ClientManager($webklexConfig);
			$client = $cm->account('default');
			$client->connect();
			\App\Log::endProfile(__METHOD__ . '|imap_open', 'Mail|IMAP');
			\App\Log::beginProfile(__METHOD__ . '|imap_folder', 'Mail|IMAP');
			$mbox = $client->getFolder($folder);
			\App\Log::endProfile(__METHOD__ . '|imap_folder', 'Mail|IMAP');

			self::$imapConnectCache[$cacheName] = $mbox;
			register_shutdown_function(function () use ($client) {
				\App\Log::beginProfile(__METHOD__ . '|imap_close', 'Mail|IMAP');
				$client->disconnect();
				\App\Log::endProfile(__METHOD__ . '|imap_close', 'Mail|IMAP');
			});
		} else {
			if (!$host) {
				$host = key($config['default_host']);
			}
			$parseHost = parse_url($host);
			$validatecert = '';
			if (!empty($parseHost['host'])) {
				$host = $parseHost['host'];
				$sslMode = (isset($parseHost['scheme']) && \in_array($parseHost['scheme'], ['ssl', 'imaps', 'tls'])) ? $parseHost['scheme'] : null;
				if (!empty($parseHost['port'])) {
					$port = $parseHost['port'];
				} elseif ($sslMode && 'tls' !== $sslMode && (!$config['default_port'] || 143 == $config['default_port'])) {
					$port = 993;
				}
			} else {
				if (993 == $config['default_port']) {
					$sslMode = 'ssl';
				} else {
					$sslMode = 'tls';
				}
			}
			if (empty($port)) {
				$port = $config['default_port'];
			}
			if (!$config['validate_cert'] && $config['imap_open_add_connection_type']) {
				$validatecert = '/novalidate-cert';
			}
			if ($config['imap_open_add_connection_type']) {
				$sslMode = '/' . $sslMode;
			} else {
				$sslMode = '';
			}
			imap_timeout(IMAP_OPENTIMEOUT, 5);
			$maxRetries = $options = 0;
			if (isset($config['imap_max_retries'])) {
				$maxRetries = $config['imap_max_retries'];
			}
			$params = [];
			if (isset($config['imap_params'])) {
				$params = $config['imap_params'];
			}
			if (strpos($user, '\\') !== false) {
				[$mainUser, $aliasUser] = explode('\\', $user);
				$aliasConfig = "/authuser=$mainUser/user=$aliasUser";
				$user = $aliasUser;
			} else if (strpos($user, '/') !== false) {
				[$mainUser, $aliasUser] = explode('/', $user);
				$aliasConfig = "/authuser=$mainUser/user=$aliasUser";
				$user = $aliasUser;
			} else {
				$aliasConfig = '';
			}

			static::$imapConnectMailbox = "{{$host}:{$port}/imap{$sslMode}{$validatecert}{$aliasConfig}}{$folder}";
			\App\Log::trace('imap_open((' . static::$imapConnectMailbox . ", $user , $password. $options, $maxRetries, " . var_export($params, true) . ', ' . $aliasConfig . ') method ...');
			\App\Log::beginProfile(__METHOD__ . '|imap_open', 'Mail|IMAP');
			$mbox = imap_open(static::$imapConnectMailbox, $user, $password, $options, $maxRetries, $params);
			\App\Log::endProfile(__METHOD__ . '|imap_open', 'Mail|IMAP');
			self::$imapConnectCache[$cacheName] = $mbox;
			if ($mbox) {
				\App\Log::trace('Exit OSSMail_Record_Model::imapConnect() method ...');
				register_shutdown_function(function () use ($mbox) {
					\App\Log::beginProfile(__METHOD__ . '|imap_close', 'Mail|IMAP');
					imap_close($mbox);
					\App\Log::endProfile(__METHOD__ . '|imap_close', 'Mail|IMAP');
				});
			} else {
				\App\Log::error('Error OSSMail_Record_Model::imapConnect(' . static::$imapConnectMailbox . '): ' . imap_last_error());
				if ($dieOnError) {
					throw new \App\Exceptions\AppException('IMAP_ERROR' . ': ' . imap_last_error());
				}
			}
		}
		return $mbox;
	}

	/**
	 * Update mailbox mesages info for users.
	 *
	 * @param array $users
	 *
	 * @return bool
	 */
	public static function updateMailBoxmsgInfo($users): bool
	{
		\App\Log::trace(__METHOD__ . ' - Start');
		$dbCommand = \App\Db::getInstance()->createCommand();
		if (0 == \count($users)) {
			return false;
		}
		$sUsers = implode(',', $users);
		$query = (new \App\Db\Query())->from('yetiforce_mail_quantities')->where(['userid' => $sUsers, 'status' => 1]);
		if ($query->count()) {
			return false;
		}
		$dbCommand->update('yetiforce_mail_quantities', ['status' => 1], ['userid' => $sUsers])->execute();
		foreach ($users as $user) {
			$account = self::getMailAccountDetail($user);
			if (false !== $account) {
				$result = (new \App\Db\Query())->from('yetiforce_mail_quantities')->where(['userid' => $user])->count();
				$mbox = self::imapConnect($account['username'], \App\Encryption::getInstance()->decrypt($account['password']), $account['mail_host'], 'INBOX', false);
				if ($mbox) {
					\App\Log::beginProfile(__METHOD__ . '|imap_status', 'Mail|IMAP');
					if (self::isOauth()) {
						$unseen = $mbox->examine()['recent'];
					} else {
						$info = imap_status($mbox, static::$imapConnectMailbox, SA_UNSEEN);
						$unseen = $info->unseen;
					}
					\App\Log::endProfile(__METHOD__ . '|imap_status', 'Mail|IMAP');
					if ($result > 0) {
						$dbCommand->update('yetiforce_mail_quantities', ['num' => $unseen, 'status' => 0], ['userid' => $user])->execute();
					} else {
						$dbCommand->insert('yetiforce_mail_quantities', ['num' => $unseen, 'userid' => $user])->execute();
					}
				}
			}
		}
		\App\Log::trace(__METHOD__ . ' - End');
		return true;
	}

	/**
	 * Return users messages count.
	 *
	 * @param array $users
	 *
	 * @return array
	 */
	public static function getMailBoxmsgInfo($users): array
	{
		$query = (new \App\Db\Query())->select(['userid', 'num'])->from('yetiforce_mail_quantities')->where(['userid' => $users]);
		return $query->createCommand()->queryAllByGroup(0);
	}

	/**
	 * @param resource|\Webklex\PHPIMAP\Folder $mbox
	 * @param int      $id
	 * @param int      $msgno
	 * @param bool     $fullMode
	 *
	 * @return bool|\OSSMail_Mail_Model
	 */
	public static function getMail($mbox, $id, $msgno = false, bool $fullMode = true)
	{
		if (self::isOauth()) {
			/** @var \Webklex\PHPIMAP\Query\WhereQuery $query */
			$query = $mbox->query();
			$query->setFetchBody($fullMode);
			$query->setFetchFlags($fullMode);
			/** @var \Webklex\PHPIMAP\Message $message */
			$message = $id ? $query->getMessageByUid($id) : $query->all()->limit(1, $msgno)->get()[0];
			/** @var \Webklex\PHPIMAP\Header $header */
			$header = $message->getHeader();

			$mail = new OSSMail_Mail_Model();
			$mail->set('header', $header);
			$mail->set('id', $message->uid);
			$mail->set('Msgno', $message->msgn);
			$mail->set('message_id', $message->message_id ? \App\Purifier::purifyByType($message->message_id->toString(), 'MailId') : '');
			$mail->set('to_email', \App\Purifier::purify($mail->getEmail('to')));
			$mail->set('from_email', \App\Purifier::purify($mail->getEmail('from')));
			$mail->set('reply_toaddress', \App\Purifier::purify($mail->getEmail('reply_to')));
			$mail->set('cc_email', \App\Purifier::purify($mail->getEmail('cc')));
			$mail->set('bcc_email', \App\Purifier::purify($mail->getEmail('bcc')));
			$mail->set('subject', $header->subject !== null ? \App\TextParser::textTruncate(\App\Purifier::purify($header->subject->toString()), 65535, false) : '');
			$mail->set('date', $header->date->toString());

			if ($fullMode) {
				$structure = [
					'body' => ($message->getTextBody() ?: $message->getHtmlBody()) ?: '',
					'attachment' => $message->hasAttachments() 
						? array_reduce(
								$message->getAttachments()->all(), 
								function ($aggr, $attachment) {
									$id = trim($attachment->id, ' <>') ?: random_int(0, PHP_INT_MAX) . random_int(0, PHP_INT_MAX);
									$aggr[$id] = [
										'filename' => $attachment->getName(),
										'attachment' => $attachment->getContent()
									];
									return $aggr;
								}, [])
					: [],
				];
				
				$mail->set('body', $structure['body']);
				$mail->set('attachments', $structure['attachment']);
				$mail->set('clean', $header->raw . $message->getRawBody());
			}
		} else {
			if (!$msgno) {
				\App\Log::beginProfile(__METHOD__ . '|imap_msgno', 'Mail|IMAP');
				$msgno = imap_msgno($mbox, $id);
				\App\Log::endProfile(__METHOD__ . '|imap_msgno', 'Mail|IMAP');
			}
			if (!$id) {
				\App\Log::beginProfile(__METHOD__ . '|imap_uid', 'Mail|IMAP');
				$id = imap_uid($mbox, $msgno);
				\App\Log::endProfile(__METHOD__ . '|imap_uid', 'Mail|IMAP');
			}
			if (!$msgno) {
				return false;
			}
			\App\Log::beginProfile(__METHOD__ . '|imap_headerinfo', 'Mail|IMAP');
			$header = imap_headerinfo($mbox, $msgno);
			\App\Log::endProfile(__METHOD__ . '|imap_headerinfo', 'Mail|IMAP');
			$messageId = '';
			if (property_exists($header, 'message_id')) {
				$messageId = $header->message_id;
			}
			$mail = new OSSMail_Mail_Model();
			$mail->set('header', $header);
			$mail->set('id', $id);
			$mail->set('Msgno', $header->Msgno);
			$mail->set('message_id', $messageId ? \App\Purifier::purifyByType($messageId, 'MailId') : '');
			$mail->set('to_email', \App\Purifier::purify($mail->getEmail('to')));
			$mail->set('from_email', \App\Purifier::purify($mail->getEmail('from')));
			$mail->set('reply_toaddress', \App\Purifier::purify($mail->getEmail('reply_to')));
			$mail->set('cc_email', \App\Purifier::purify($mail->getEmail('cc')));
			$mail->set('bcc_email', \App\Purifier::purify($mail->getEmail('bcc')));
			$mail->set('subject', isset($header->subject) ? \App\TextParser::textTruncate(\App\Purifier::purify(self::decodeText($header->subject)), 65535, false) : '');
			$mail->set('date', date('Y-m-d H:i:s', $header->udate));
			if ($fullMode) {
				$structure = self::getBodyAttach($mbox, $id, $msgno);
				$mail->set('body', $structure['body']);
				$mail->set('attachments', $structure['attachment']);

				$clean = '';
				\App\Log::beginProfile(__METHOD__ . '|imap_fetch_overview', 'Mail|IMAP');
				$msgs = imap_fetch_overview($mbox, $msgno);
				\App\Log::endProfile(__METHOD__ . '|imap_fetch_overview', 'Mail|IMAP');

				foreach ($msgs as $msg) {
					\App\Log::beginProfile(__METHOD__ . '|imap_fetchheader', 'Mail|IMAP');
					$clean .= imap_fetchheader($mbox, $msg->msgno);
					\App\Log::endProfile(__METHOD__ . '|imap_fetchheader', 'Mail|IMAP');
				}
				$mail->set('clean', $clean);
			}
		}
		return $mail;
	}

	/**
	 * Users cache.
	 *
	 * @var array
	 */
	protected static $usersCache = [];

	/**
	 * Return user account detal.
	 *
	 * @param int $userid
	 *
	 * @return array
	 */
	public static function getMailAccountDetail($userid)
	{
		if (isset(self::$usersCache[$userid])) {
			return self::$usersCache[$userid];
		}
		$user = (new \App\Db\Query())->from('roundcube_users')->where(['user_id' => $userid])->one();
		self::$usersCache[$userid] = $user;
		return $user;
	}

	/**
	 * Convert text encoding.
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	public static function decodeText($text)
	{
		$data = imap_mime_header_decode($text);
		$text = '';
		foreach ($data as &$row) {
			$charset = ('default' == $row->charset) ? 'ASCII' : $row->charset;
			if (\function_exists('mb_convert_encoding') && \in_array($charset, mb_list_encodings())) {
				$text .= mb_convert_encoding($row->text, 'utf-8', $charset);
			} else {
				$text .= iconv($charset, 'UTF-8', $row->text);
			}
		}
		return $text;
	}

	/**
	 * Return full name.
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	public static function getFullName($text)
	{
		$return = '';
		foreach ($text as $row) {
			if ('' != $return) {
				$return .= ',';
			}
			if ('' == $row->personal) {
				$return .= $row->mailbox . '@' . $row->host;
			} else {
				$return .= self::decodeText($row->personal) . ' - ' . $row->mailbox . '@' . $row->host;
			}
		}
		return $return;
	}

	/**
	 * Return body and attachments.
	 *
	 * @param resource $mbox
	 * @param int      $id
	 * @param int      $msgno
	 *
	 * @return array
	 */
	public static function getBodyAttach($mbox, $id, $msgno)
	{
		\App\Log::beginProfile(__METHOD__ . '|imap_fetchstructure', 'Mail|IMAP');
		$struct = imap_fetchstructure($mbox, $id, FT_UID);
		\App\Log::endProfile(__METHOD__ . '|imap_fetchstructure', 'Mail|IMAP');
		$mail = ['id' => $id];
		if (empty($struct->parts)) {
			$mail = self::initMailPart($mbox, $mail, $struct, 0);
		} else {
			foreach ($struct->parts as $partNum => $partStructure) {
				$mail = self::initMailPart($mbox, $mail, $partStructure, $partNum + 1);
			}
		}
		$body = '';
		$body = (!empty($mail['textPlain'])) ? $mail['textPlain'] : $body;
		$body = (!empty($mail['textHtml'])) ? $mail['textHtml'] : $body;
		$attachment = (isset($mail['attachments'])) ? $mail['attachments'] : [];

		return [
			'body' => $body,
			'attachment' => $attachment,
		];
	}

	/**
	 * Init mail part.
	 *
	 * @param resource $mbox
	 * @param array    $mail
	 * @param object   $partStructure
	 * @param int      $partNum
	 *
	 * @return array
	 */
	protected static function initMailPart($mbox, $mail, $partStructure, $partNum)
	{
		if ($partNum) {
			\App\Log::beginProfile(__METHOD__ . '|imap_fetchbody', 'Mail|IMAP');
			$data = imap_fetchbody($mbox, $mail['id'], $partNum, FT_UID | FT_PEEK);
			\App\Log::endProfile(__METHOD__ . '|imap_fetchbody', 'Mail|IMAP');
		} else {
			\App\Log::beginProfile(__METHOD__ . '|imap_body', 'Mail|IMAP');
			$data = imap_body($mbox, $mail['id'], FT_UID | FT_PEEK);
			\App\Log::endProfile(__METHOD__ . '|imap_body', 'Mail|IMAP');
		}
		if (1 == $partStructure->encoding) {
			$data = imap_utf8($data);
		} elseif (2 == $partStructure->encoding) {
			$data = imap_binary($data);
		} elseif (3 == $partStructure->encoding) {
			$data = imap_base64($data);
		} elseif (4 == $partStructure->encoding) {
			$data = imap_qprint($data);
		}
		$params = [];
		if (!empty($partStructure->parameters)) {
			foreach ($partStructure->parameters as $param) {
				$params[strtolower($param->attribute)] = $param->value;
			}
		}
		if (!empty($partStructure->dparameters)) {
			foreach ($partStructure->dparameters as $param) {
				$paramName = strtolower(preg_match('~^(.*?)\*~', $param->attribute, $matches) ? $matches[1] : $param->attribute);
				if (isset($params[$paramName])) {
					$params[$paramName] .= $param->value;
				} else {
					$params[$paramName] = $param->value;
				}
			}
		}
		if (!empty($params['charset']) && 'utf-8' !== strtolower($params['charset'])) {
			if (\function_exists('mb_convert_encoding') && \in_array($params['charset'], mb_list_encodings())) {
				$encodedData = mb_convert_encoding($data, 'UTF-8', $params['charset']);
			} else {
				$encodedData = iconv($params['charset'], 'UTF-8', $data);
			}
			if ($encodedData) {
				$data = $encodedData;
			}
		}
		$attachmentId = $partStructure->ifid ? trim($partStructure->id, ' <>') : '';
  $attachmentId = $attachmentId ?: (isset($params['filename']) || isset($params['name']) ? random_int(0, PHP_INT_MAX) . random_int(0, PHP_INT_MAX) : null);
		if ($attachmentId) {
			if (empty($params['filename']) && empty($params['name'])) {
				$fileName = $attachmentId . '.' . strtolower($partStructure->subtype);
			} else {
				$fileName = !empty($params['filename']) ? $params['filename'] : $params['name'];
				$fileName = self::decodeText($fileName);
				$fileName = self::decodeRFC2231($fileName);
			}
			$mail['attachments'][$attachmentId]['filename'] = $fileName;
			$mail['attachments'][$attachmentId]['attachment'] = $data;
		} elseif (0 == $partStructure->type && $data) {
			if (preg_match('/^([a-zA-Z0-9]{76} )+[a-zA-Z0-9]{76}$/', $data) && base64_decode($data, true)) {
				$data = base64_decode($data);
			}
			if ('plain' == strtolower($partStructure->subtype)) {
				$uuDecode = self::uuDecode($data);
				if (isset($uuDecode['attachments'])) {
					$mail['attachments'] = $uuDecode['attachments'];
				}
				if (!isset($mail['textPlain'])) {
					$mail['textPlain'] = '';
				}
				$mail['textPlain'] .= $uuDecode['text'];
			} else {
				if (!isset($mail['textHtml'])) {
					$mail['textHtml'] = '';
				}
				$mail['textHtml'] .= $data;
			}
		} elseif (2 == $partStructure->type && $data) {
			if (!isset($mail['textPlain'])) {
				$mail['textPlain'] = '';
			}
			$mail['textPlain'] .= trim($data);
		}
		if (!empty($partStructure->parts)) {
			foreach ($partStructure->parts as $subPartNum => $subPartStructure) {
				if (2 == $partStructure->type && 'RFC822' == $partStructure->subtype) {
					$mail = self::initMailPart($mbox, $mail, $subPartStructure, $partNum);
				} else {
					$mail = self::initMailPart($mbox, $mail, $subPartStructure, $partNum . '.' . ($subPartNum + 1));
				}
			}
		}
		return $mail;
	}

	/**
	 * Decode string.
	 *
	 * @param string $input
	 *
	 * @return array
	 */
	protected static function uuDecode($input)
	{
		$attachments = [];
		$uu_regexp_begin = '/begin [0-7]{3,4} ([^\r\n]+)\r?\n/s';
		$uu_regexp_end = '/`\r?\nend((\r?\n)|($))/s';

		while (preg_match($uu_regexp_begin, $input, $matches, PREG_OFFSET_CAPTURE)) {
			$startpos = $matches[0][1];
			if (!preg_match($uu_regexp_end, $input, $m, PREG_OFFSET_CAPTURE, $startpos)) {
				break;
			}

			$endpos = $m[0][1];
			$begin_len = \strlen($matches[0][0]);
			$end_len = \strlen($m[0][0]);

			// extract attachment body
			$filebody = substr($input, $startpos + $begin_len, $endpos - $startpos - $begin_len - 1);
			$filebody = str_replace("\r\n", "\n", $filebody);

			// remove attachment body from the message body
			$input = substr_replace($input, '', $startpos, $endpos + $end_len - $startpos);

			// add attachments to the structure
			$attachments[] = [
				'filename' => trim($matches[1][0]),
				'attachment' => convert_uudecode($filebody),
			];
		}
		return ['attachments' => $attachments, 'text' => $input];
	}

	/**
	 * Check if url is encoded.
	 *
	 * @param string $string
	 *
	 * @return bool
	 */
	public static function isUrlEncoded($string)
	{
		$string = str_replace('%20', '+', $string);
		$decoded = urldecode($string);

		return $decoded != $string && urlencode($decoded) == $string;
	}

	/**
	 * decode RFC2231 formatted string.
	 *
	 * @param string $string
	 * @param string $charset
	 *
	 * @return string
	 */
	protected static function decodeRFC2231($string, $charset = 'utf-8')
	{
		if (preg_match("/^(.*?)'.*?'(.*?)$/", $string, $matches)) {
			$encoding = $matches[1];
			$data = $matches[2];
			if (self::isUrlEncoded($data)) {
				$string = iconv(strtoupper($encoding), $charset, urldecode($data));
			}
		}
		return $string;
	}

	/**
	 * Return user folders.
	 *
	 * @param int $user
	 *
	 * @return array
	 */
	public static function getFolders($user)
	{
		$account = self::getAccountsList($user);
		$account = reset($account);
		$folders = false;
		$mbox = self::imapConnect($account['username'], \App\Encryption::getInstance()->decrypt($account['password']), $account['mail_host'], 'INBOX', false);
		if ($mbox) {
			if (self::isOauth()) {
				$folders = [];
				$client = $mbox->getClient();
				$rawFolders = $client->getFolders(false);
				foreach ($rawFolders as $folder) {
					$folders[$folder->path] = $folder->path;
				}
			} else {
				$folders = [];
				$ref = '{' . $account['mail_host'] . '}';
				$list = imap_list($mbox, $ref, '*');
				foreach ($list as $mailboxname) {
					$name = str_replace($ref, '', $mailboxname);
					$name = \App\Utils::convertCharacterEncoding($name, 'UTF7-IMAP', 'UTF-8');
					$folders[$name] = $name;
				}
			}
		}
		return $folders;
	}

	/**
	 * Return site URL.
	 *
	 * @return string
	 */
	public static function getSiteUrl()
	{
		$site_URL = App\Config::main('site_URL');
		if ('/' != substr($site_URL, -1)) {
			$site_URL = $site_URL . '/';
		}
		return $site_URL;
	}

	/**
	 * Fetch mails from IMAP.
	 *
	 * @param int $user
	 *
	 * @return array
	 */
	public static function getMailsFromIMAP($user = false)
	{
		$account = self::getAccountsList($user, true);
		$mails = [];
		$mailLimit = 5;
		if ($account) {
			$imap = self::imapConnect($account[0]['username'], \App\Encryption::getInstance()->decrypt($account[0]['password']), $account[0]['mail_host']);
			\App\Log::beginProfile(__METHOD__ . '|imap_num_msg', 'Mail|IMAP');
			if (self::isOauth()) {
				$numMessages = $imap->examine()['exists'];
			} else {
				$numMessages = imap_num_msg($imap);
			}
			
			\App\Log::endProfile(__METHOD__ . '|imap_num_msg', 'Mail|IMAP');
			if ($numMessages < $mailLimit) {
				$mailLimit = $numMessages;
			}
			
			for ($i = $numMessages; $i > ($numMessages - $mailLimit); --$i) {
				$mail = self::getMail($imap, false, $i);
				$mails[] = $mail;
			}
		}
		return $mails;
	}

	/**
	 * Get mail account detail by hash ID.
	 *
	 * @param string $hash
	 *
	 * @return bool|array
	 */
	public static function getAccountByHash($hash)
	{
		if (preg_match('/^[_a-zA-Z0-9.,]+$/', $hash)) {
			$result = (new \App\Db\Query())
				->from('roundcube_users')
				->where(['like', 'preferences', "%:\"$hash\";%", false])
				->one();
			if ($result) {
				return $result;
			}
		}
		return false;
	}

	/**
	 * Get mail account detail by user name
	 *
	 * @param string $userName
	 *
	 * @return bool|array
	 */
	public static function getAccountByUserName($userName)
	{
		$result = (new \App\Db\Query())
			->from('roundcube_users')
			->where(['username' => $userName])
			->one();
		if ($result) {
			return $result;
		}
		return false;
	}
}

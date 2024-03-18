<?php

namespace App\Session;

/**
 * Base Session Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */
class File extends Base
{
	/** {@inheritdoc} */
	public static function clean()
	{
		$time = microtime(true);
		$lifeTime = \Config\Security::$maxLifetimeSession;
		$exclusion = ['.htaccess', 'index.html', 'sess_' . session_id()];
		foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(\App\Session::SESSION_PATH, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST) as $item) {
			if ($item->isFile() && !\in_array($item->getBasename(), $exclusion)) {
				$sessionData = static::unserialize(static::getFileContents($item->getPathname()));
				if (!empty($sessionData['last_activity']) && ($time - $sessionData['last_activity']) < $lifeTime) {
					continue;
				}
				$sessionId = session_id();
				$path = $item->getPathname();
				$lastActivity = $sessionData['last_activity'];
				$duration = $time - $lastActivity;
				\App\Log::warning("Session::File::clean:$sessionId/$path/$lastActivity/$time/$duration");
				unlink($item->getPathname());
				if (!empty($sessionData['baseUserId']) || !empty($sessionData['authenticated_user_id'])) {
					$userId = empty($sessionData['baseUserId']) ? $sessionData['authenticated_user_id'] : $sessionData['baseUserId'];
					$userName = \App\User::getUserModel($userId)->getDetail('user_name');
					if (!empty($userName)) {
						yield $userId => $userName;
					}
				}
			}
		}
	}

	/** {@inheritdoc} */
	public static function cleanAll(): int
	{
		$exclusion = ['.htaccess', 'index.html', 'sess_' . session_id()];
		$i = 0;
		foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(\App\Session::SESSION_PATH, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST) as $item) {
			if ($item->isFile() && !\in_array($item->getBasename(), $exclusion)) {
				\App\Log::warning("Session::File::cleanAll:removing" . $item->getBasename());
				unlink($item->getPathname());
				++$i;
			}
		}
		return $i;
	}

	/** {@inheritdoc} */
	public function getById(string $sessionId): array
	{
		$sessionFilePath = \App\Session::SESSION_PATH . \DIRECTORY_SEPARATOR . 'sess_' . $sessionId;
		$sessionData = [];
		
		try {
			if (!file_exists("$sessionFilePath")) {
				\App\Log::warning("Session::File::getById:$sessionId is missing");
			} else if ($content = static::getFileContents($sessionFilePath)) {
				$sessionData = self::unserialize($content);
			}
		} catch (\Exception $e) {
			\App\Log::error(var_export($e, true));
			throw $e;
		}

		return $sessionData;
	}

	/**
	 * Deserialize session data from string, entry function.
	 *
	 * @param string $session
	 *
	 * @throws \App\Exceptions\IllegalValue
	 * @throws \App\Exceptions\NotAllowedMethod
	 *
	 * @return array
	 *
	 * @example http://php.net/manual/en/function.session-decode.php#108037
	 */
	public static function unserialize(string $session)
	{
		$method = ini_get('session.serialize_handler');
		switch ($method) {
			case 'php':
				return self::unserializePhp($session);
				break;
			case 'php_binary':
				return self::unserializePhpBinary($session);
				break;
			default:
				throw new \App\Exceptions\NotAllowedMethod('Unsupported session.serialize_handler: ' . $method . '. Supported: php, php_binary');
		}
	}

	/**
	 * Deserialize session data from string php handler method.
	 *
	 * @param string $session
	 *
	 * @throws \App\Exceptions\IllegalValue
	 *
	 * @return array
	 */
	private static function unserializePhp(string $session)
	{
		$return = [];
		$offset = 0;
		while ($offset < \strlen($session)) {
			if (!strstr(substr($session, $offset), '|')) {
				throw new \App\Exceptions\IllegalValue('invalid data, remaining: ' . substr($session, $offset));
			}
			$pos = strpos($session, '|', $offset);
			$num = $pos - $offset;
			$varName = substr($session, $offset, $num);
			$offset += $num + 1;
			$data = unserialize(substr($session, $offset), ['allowed_classes' => false]);
			$return[$varName] = $data;
			$offset += \strlen(serialize($data));
		}
		return $return;
	}

	/**
	 * Deserialize session data from string php_binary handler method.
	 *
	 * @param string $session
	 *
	 * @return array
	 */
	private static function unserializePhpBinary(string $session)
	{
		$return = [];
		$offset = 0;
		while ($offset < \strlen($session)) {
			$num = \ord($session[$offset]);
			++$offset;
			$varName = substr($session, $offset, $num);
			$offset += $num;
			$data = unserialize(substr($session, $offset), ['allowed_classes' => false]);
			$return[$varName] = $data;
			$offset += \strlen(serialize($data));
		}
		return $return;
	}

	private $savePath;

	/** {@inheritdoc} */
	public function open($savePath, $sessionName): bool
	{
		$this->savePath = $savePath;
		if (!is_dir($this->savePath)) {
				mkdir($this->savePath, 0777);
		}

		return true;
	}

	/** {@inheritdoc} */
	public function close(): bool
	{
		return true;
	}

	public function read($id): string
	{
		if (!file_exists("$this->savePath/sess_$id")) {
			\App\Log::warning("Session::File::read:$id is missing");
			return "";
		}
		return (string)@static::getFileContents("$this->savePath/sess_$id");
	}

	/** {@inheritdoc} */
	public function write($id, $data): bool
	{
		$repetitions = 0;
		$writeResult = false;
		while(!$writeResult && $repetitions++ < 3) {
			$writeResult = file_put_contents("$this->savePath/sess_$id", $data, LOCK_EX);
		}
		return $writeResult === false ? false : true;
	}

	/** {@inheritdoc} */
	public function destroy($id): bool
	{
		\App\Log::warning("Session::File::destroy:$id");

		$file = "$this->savePath/sess_$id";
		if (file_exists($file)) {
				unlink($file);
		}

		return true;
	}

	/** {@inheritdoc} */
	public function gc($maxlifetime): bool
	{
		\App\Log::warning("Session::File::gc:$maxlifetime");

		foreach (glob("$this->savePath/sess_*") as $file) {
			if (file_exists($file) && filemtime($file) + $maxlifetime < time()) {
				\App\Log::warning("Session::File::gc:removing $file");
				unlink($file);
			}
		}

		return true;
	}

	private static function getFileContents($file) {
		$myfile = fopen($file,'rt');
		try {
			flock($myfile, LOCK_SH);
			return file_get_contents($file);
		} finally {
			fclose($myfile);
		}
	}
}

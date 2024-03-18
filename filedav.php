<?php
/**
 * SabreDav init file for file serving.
 *
 *
 * @copyright DOT Systems Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Kamiński <mkaminski@dotsystems.pl>
 */

require __DIR__ . '/include/ConfigUtils.php';
require __DIR__ . '/include/main/WebUI.php';

if (!\in_array('dav', \App\Config::api('enabledServices', []))) {
	$apiLog = new \App\Exceptions\NoPermittedToApi();
	$apiLog->stop('Dav - Service is not active');
	return;
}

const REALM = 'DotsDAV';

class DotsAuthBackendBearer extends \Sabre\DAV\Auth\Backend\AbstractBearer {
	private $token;
	private $jwtKey;
	private $jwt;

	public function __construct($token) {
		$this->token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.$token";

		$this->jwtKey = \App\Config::api('FILE_DAV_KEY');
		$this->jwt = new \Ahc\Jwt\JWT($this->jwtKey);
	}

	public function check(\Sabre\HTTP\RequestInterface $request, \Sabre\HTTP\ResponseInterface $response) {
		$bearerToken = $this->token;
		if (!$bearerToken) {
			return [false, "No 'Authorization: Bearer' header found. Either the client didn't send one, or the server is mis-configured"];
		}
		
		$data = $this->validateBearerToken($bearerToken);
		if (!$data) {
			return [false, 'Bearer token was incorrect'];
		}
		if ($data['exp'] < time()) {
			return [false, 'Bearer token has expired'];
		}
		if (!strpos("Documents/{$data['pth']}", $request->getPath()) === 0) {
			return [false, 'Bearer token is not valid for this path'];
		}

		return [true, strval($data['usr'])];
	}

	/**
	 * Validates a bearer token
	 *
	 * This method should return the principal uri associated with the bearer token,
	 * or false if the bearer token was incorrect.
	 *
	 * @param string $bearerToken
	 * @return string|false
	 */
	public function validateBearerToken($bearerToken) {
		try {
			$decoded = $this->jwt->decode($bearerToken);
			return $decoded;
		} catch (\Ahc\Jwt\JWTException $e) {
			\App\Log::warning( "validateBearerToken( $bearerToken ) -> ERROR: " . $e->getMessage() );
			return false;
		}
		
		return false;
	}
}

class DotsLog extends \Sabre\DAV\ServerPlugin {
	/**
	 * Reference to server object.
	 *
	 * @var Sabre\DAV\Server
	 */
	protected $server;

	/**
	 * Response body.
	 *
	 * @var string
	 */
	protected $response;

	/**
	 * Request body.
	 *
	 * @var string
	 */
	protected $request;

	const LOG_FILE = 'cache/logs/dav.log';

	/**
	 * Initializes selected functions.
	 *
	 * @param \Sabre\DAV\Server $server
	 */
	public function initialize(\Sabre\DAV\Server $server)
	{
		$this->server = $server;
		$this->server->on('beforeMethod:*', [$this, 'beforeMethod'], 5);
		$this->server->on('exception', [$this, 'exception']);
		$this->server->on('afterResponse', [$this, 'afterResponse']);
	}

	/**
	 * Force user authentication.
	 *
	 * @param \Sabre\HTTP\RequestInterface  $request
	 * @param \Sabre\HTTP\ResponseInterface $response
	 *
	 * @return bool
	 */
	public function beforeMethod(\Sabre\HTTP\RequestInterface $request, \Sabre\HTTP\ResponseInterface $response)
	{
		file_put_contents(self::LOG_FILE, '============ ' . date('Y-m-d H:i:s') . ' ====== Request ======' . PHP_EOL, FILE_APPEND);
		$content = $request->getMethod() . ' ' . $request->getUrl() . ' HTTP/' . $request->getHTTPVersion() . "\r\n";
		foreach ($request->getHeaders() as $key => $values) {
			foreach ($values as $value) {
				$content .= $key . ': ' . $value . PHP_EOL;
			}
		}
		file_put_contents(self::LOG_FILE, $content . PHP_EOL, FILE_APPEND);
		return true;
	}

	/**
	 * Places a list of headers.
	 *
	 * @param \Sabre\HTTP\RequestInterface  $request
	 * @param \Sabre\HTTP\ResponseInterface $response
	 *
	 * @return bool
	 */
	public function afterResponse(\Sabre\HTTP\RequestInterface $request, \Sabre\HTTP\ResponseInterface $response)
	{
		file_put_contents(self::LOG_FILE, '============ ' . date('Y-m-d H:i:s') . ' ====== Response ======' . PHP_EOL, FILE_APPEND);
		$content = 'HTTP/' . $response->getHttpVersion() . ' ' . $response->getStatus() . ' ' . $response->getStatusText() . PHP_EOL;
		foreach ($response->getHeaders() as $key => $values) {
			foreach ($values as $value) {
				$content .= $key . ': ' . $value . PHP_EOL;
			}
		}
		file_put_contents(self::LOG_FILE, $content . PHP_EOL, FILE_APPEND);
		return true;
	}

	/**
	 * This function will cause the "exception" event
	 * to occur as soon as the error document is returned.
	 *
	 * @param \Throwable $e
	 *
	 * @return bool
	 */
	public function exception(\Throwable $e)
	{
		$error = 'exception: ' . \get_class($e) . PHP_EOL;
		$error .= 'message: ' . $e->getMessage() . PHP_EOL;
		$error .= 'file: ' . $e->getFile() . PHP_EOL;
		$error .= 'line: ' . $e->getLine() . PHP_EOL;
		$error .= 'stacktrace: ' . PHP_EOL . $e->getTraceAsString() . PHP_EOL;
		file_put_contents(self::LOG_FILE, '============ ' . date('Y-m-d H:i:s') . ' ====== Error exception ======'
			. PHP_EOL . $error . PHP_EOL, FILE_APPEND);
		return true;
	}

	/**
	 * Returns a plugin name.
	 *
	 * Using this name other plugins will be able to access other plugins
	 * using \Sabre\DAV\Server::getPlugin
	 *
	 * @return string
	 */
	public function getPluginName()
	{
		return 'Dots DAV debug';
	}

	/**
	 * Returns a bunch of meta-data about the plugin.
	 *
	 * Providing this information is optional, and is mainly displayed by the
	 * Browser plugin.
	 *
	 * The description key in the returned array may contain html and will not
	 * be sanitized.
	 *
	 * @return array
	 */
	public function getPluginInfo()
	{
		return [
			'name' => $this->getPluginName(),
			'description' => 'Logs requests and exceptions.',
		];
	}

	/**
	 * Mapping PHP errors to exceptions.
	 *
	 * While this is not strictly needed, it makes a lot of sense to do so. If an
	 * E_NOTICE or anything appears in your code, this allows SabreDAV to intercept
	 * the issue and send a proper response back to the client (HTTP/1.1 500).
	 *
	 * @param int    $errno
	 * @param string $errstr
	 * @param string $errfile
	 * @param int    $errline
	 * @param array  $errcontext
	 *
	 * @throws \ErrorException
	 */
	public static function exceptionErrorHandler($errno, $errstr, $errfile, $errline, $errcontext)
	{
		throw new \App\Exceptions\AppException($errstr, 0, new \ErrorException($errstr, 0, $errno, $errfile, $errline));
	}
}

set_error_handler(['DotsLog', 'exceptionErrorHandler']);
$enableFileDAV = \App\Config::api('enableFileDAV');
$enableBrowser = \App\Config::api('enableBrowser');

// Database
$dbConfig = \App\Config::db('base');
$pdo = new PDO($dbConfig['dsn'] . ';charset=' . $dbConfig['charset'], $dbConfig['username'], $dbConfig['password']);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// get token from url
$url = \substr($_SERVER['REQUEST_URI'], \strlen($_SERVER['SCRIPT_NAME']));
$parts = explode('/', $url);
$token = $parts[1]; 


// Backends
$authBackend = new DotsAuthBackendBearer($token);
$authBackend->setRealm(REALM);

if ($enableFileDAV) {
	$nodes[] = new \Sabre\DAV\FS\Directory('storage/Documents');
}

// The object tree needs in turn to be passed to the server class
$server = new \App\Integrations\Dav\Server($nodes);

$server->setBaseUri($_SERVER['SCRIPT_NAME'] . '/' . $token);
$server->debugExceptions = \App\Config::debug('DAV_DEBUG_EXCEPTIONS');

if ($enableFileDAV) {
	// add handler to remove extensions from files
	$server->on('beforeMethod', function (\Sabre\HTTP\RequestInterface $request, \Sabre\HTTP\ResponseInterface $response) {
		$path = $request->getUrl();
		$ext = pathinfo($path, PATHINFO_EXTENSION);
		if ($ext) {
			$path = substr($path, 0, -\strlen($ext) - 1);
			$request->setUrl($path);
		}
	}, 10);

	// add handler to detect modified files and save modifications in history
	$server->on('afterMethod:PUT', function(\Sabre\HTTP\RequestInterface $request, \Sabre\HTTP\ResponseInterface$response) use ($pdo, $server) {
		$validStatuses = [200, 204];

		// check if request finished successfully
		$status = $response->getStatus();
		if (\in_array($status, $validStatuses)) {
			// get file id
			$path = $request->getPath();
			$fileId = basename($path);

			// convert file id into document id
			$stmt = $pdo->prepare('SELECT crmid FROM vtiger_seattachmentsrel WHERE attachmentsid = ?');
			$stmt->execute([$fileId]);

			$documentId = $stmt->fetchColumn() ?: null;

			// check if document exists
			if (\App\Record::isExists($documentId, 'Documents')) {
				// get document instance
				/** @var \Documents_Record_Model $document */
				$document = \Vtiger_Record_Model::getInstanceById($documentId, 'Documents');

				// get user id (current principal)
				/** @var $authPlugin \Sabre\DAV\Auth\Plugin */
        $authPlugin = $server->getPlugin('auth');
        
        $userId = $authPlugin->getCurrentPrincipal();

				// store current executing user id for later
				$currentUserId = \App\User::getCurrentUserId();
				try {
					// use user id of user making change to file
					\App\User::setCurrentUserId($userId);

					// update document fields and save
					require_once 'modules/ModTracker/ModTracker.php';
					$document->ext['modificationType'] = \ModTracker::$FILEDAV_CHANGED;
					$document->setFieldValue('filesize', filesize("storage/$path"));
					$document->setFieldValue('filedownloadcount', 0);
					$document->save();
				} finally {
					// restore executing user id
					\App\User::setCurrentUserId($currentUserId);
				}
			}
		}

    return;
	});
}

// Plugins
// replace
$server->addPlugin(new Sabre\DAV\Auth\Plugin($authBackend));
if ($enableBrowser) {
	$server->addPlugin(new Sabre\DAV\Browser\Plugin());
}
if (\App\Config::debug('DAV_DEBUG_PLUGIN')) {
	$server->addPlugin(new DotsLog());
}
if ($enableFileDAV) {
	$lockBackend = new \Sabre\DAV\Locks\Backend\File('cache/locksdb');
	$server->addPlugin(new \Sabre\DAV\Locks\Plugin($lockBackend));
	$server->addPlugin(new \Sabre\DAV\TemporaryFileFilterPlugin('cache/dav'));
}

// Starts the DAV Server.
$server->start();

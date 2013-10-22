<?php
namespace Scion\Authentication\Adapter\HybridAuth;

/**
 * self class
 * self class provide a simple way to authenticate users via OpenID and OAuth.
 * Generally, self is the only class you should instantiate and use throughout your application.
 */
class Auth {
	public static $version = "2.1.2";

	public static $config = array();

	public static $store = null;

	public static $error = null;

	public static $logger = null;

	// --------------------------------------------------------------------

	/**
	 * Try to start a new session of none then initialize self
	 * self constructor will require either a valid config array or
	 * a path for a configuration file as parameter. To know more please
	 * refer to the Configuration section:
	 * http://hybridauth.sourceforge.net/userguide/Configuration.html
	 */
	function __construct($config) {
		self::initialize($config);
	}

	// --------------------------------------------------------------------

	/**
	 * Try to initialize self with given $config hash or file
	 */
	public static function initialize($config) {
		if (! is_array($config) && ! file_exists($config)) {
			throw new \Exception("Hybriauth config does not exist on the given path.", 1);
		}

		if (! is_array($config)) {
			$config = include $config;
		}

		// build some need'd paths
		$config["path_base"]      = realpath(dirname(__FILE__)) . "/";
		$config["path_libraries"] = $config["path_base"] . "thirdparty/";
		$config["path_resources"] = $config["path_base"] . "resources/";
		$config["path_providers"] = $config["path_base"] . "Providers/";

		// reset debug mode
		if (! isset($config["debug_mode"])) {
			$config["debug_mode"] = false;
			$config["debug_file"] = null;
		}

		// hash given config
		self::$config = $config;

		// instace of log mng
		self::$logger = new Logger();

		// instace of errors mng
		self::$error = new Error();

		// start session storage mng
		self::$store = new Storage();

		Logger::info("Enter self::initialize()");
		Logger::info("self::initialize(). PHP version: " . PHP_VERSION);
		Logger::info("self::initialize(). self version: " . self::$version);
		Logger::info("self::initialize(). self called from: " . self::getCurrentUrl());

		// PHP Curl extension [http://www.php.net/manual/en/intro.curl.php]
		if (! function_exists('curl_init')) {
			Logger::error('Hybridauth Library needs the CURL PHP extension.');
			throw new \Exception('Hybridauth Library needs the CURL PHP extension.');
		}

		// PHP JSON extension [http://php.net/manual/en/book.json.php]
		if (! function_exists('json_decode')) {
			Logger::error('Hybridauth Library needs the JSON PHP extension.');
			throw new \Exception('Hybridauth Library needs the JSON PHP extension.');
		}

		// session.name
		if (session_name() != "PHPSESSID") {
			Logger::info('PHP session.name diff from default PHPSESSID. http://php.net/manual/en/session.configuration.php#ini.session.name.');
		}

		// safe_mode is on
		if (ini_get('safe_mode')) {
			Logger::info('PHP safe_mode is on. http://php.net/safe-mode.');
		}

		// open basedir is on
		if (ini_get('open_basedir')) {
			Logger::info('PHP open_basedir is on. http://php.net/open-basedir.');
		}

		Logger::debug("self initialize. dump used config: ", serialize($config));
		Logger::debug("self initialize. dump current session: ", self::storage()->getSessionData());
		Logger::info("self initialize: check if any error is stored on the endpoint...");

		if (Error::hasError()) {
			$m = Error::getErrorMessage();
			$c = Error::getErrorCode();
			$p = Error::getErrorPrevious();

			Logger::error("self initialize: A stored Error found, Throw an new Exception and delete it from the store: Error#$c, '$m'");

			Error::clearError();

			// try to provide the previous if any
			// Exception::getPrevious (PHP 5 >= 5.3.0) http://php.net/manual/en/exception.getprevious.php
			if (version_compare(PHP_VERSION, '5.3.0', '>=') && ($p instanceof Exception)) {
				throw new \Exception($m, $c, $p);
			}
			else {
				throw new \Exception($m, $c);
			}
		}

		Logger::info("self initialize: no error found. initialization succeed.");

		// Endof initialize 
	}

	// --------------------------------------------------------------------

	/**
	 * Hybrid storage system accessor
	 * Users sessions are stored using HybridAuth storage system ( HybridAuth 2.0 handle PHP Session only) and can be acessed directly by
	 * self::storage()->get($key) to retrieves the data for the given key, or calling
	 * self::storage()->set($key, $value) to store the key => $value set.
	 */
	public static function storage() {
		return self::$store;
	}

	// --------------------------------------------------------------------

	/**
	 * Get hybridauth session data.
	 */
	function getSessionData() {
		return self::storage()->getSessionData();
	}

	// --------------------------------------------------------------------

	/**
	 * restore hybridauth session data.
	 */
	function restoreSessionData($sessiondata = null) {
		self::storage()->restoreSessionData($sessiondata);
	}

	// --------------------------------------------------------------------

	/**
	 * Try to authenticate the user with a given provider.
	 * If the user is already connected we just return and instance of provider adapter,
	 * ELSE, try to authenticate and authorize the user with the provider.
	 * $params is generally an array with required info in order for this provider and HybridAuth to work,
	 *  like :
	 *          hauth_return_to: URL to call back after authentication is done
	 *        openid_identifier: The OpenID identity provider identifier
	 *           google_service: can be "Users" for Google user accounts service or "Apps" for Google hosted Apps
	 */
	public static function authenticate($providerId, $params = null) {
		Logger::info("Enter self::authenticate( $providerId )");

		// if user not connected to $providerId then try setup a new adapter and start the login process for this provider
		if (! self::storage()->get("hauth_session.$providerId.is_logged_in")) {
			Logger::info("self::authenticate( $providerId ), User not connected to the provider. Try to authenticate..");

			$provider_adapter = self::setup($providerId, $params);

			$provider_adapter->login();
		}

		// else, then return the adapter instance for the given provider
		else {
			Logger::info("self::authenticate( $providerId ), User is already connected to this provider. Return the adapter instance.");

			return self::getAdapter($providerId);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Return the adapter instance for an authenticated provider
	 */
	public static function getAdapter($providerId = null) {
		Logger::info("Enter self::getAdapter( $providerId )");

		return self::setup($providerId);
	}

	// --------------------------------------------------------------------

	/**
	 * Setup an adapter for a given provider
	 */
	public static function setup($providerId, $params = null) {
		Logger::debug("Enter self::setup( $providerId )", $params);

		if (! $params) {
			$params = self::storage()->get("hauth_session.$providerId.id_provider_params");

			Logger::debug("self::setup( $providerId ), no params given. Trying to get the sotred for this provider.", $params);
		}

		if (! $params) {
			$params = ARRAY();

			Logger::info("self::setup( $providerId ), no stored params found for this provider. Initialize a new one for new session");
		}

		if (! isset($params["hauth_return_to"])) {
			$params["hauth_return_to"] = self::getCurrentUrl();
		}

		Logger::debug("self::setup( $providerId ). HybridAuth Callback URL set to: ", $params["hauth_return_to"]);

		# instantiate a new IDProvider Adapter
		$provider = new ProviderAdapter();

		$provider->factory($providerId, $params);

		return $provider;
	}

	// --------------------------------------------------------------------

	/**
	 * Check if the current user is connected to a given provider
	 */
	public static function isConnectedWith($providerId) {
		return (bool)self::storage()->get("hauth_session.{$providerId}.is_logged_in");
	}

	// --------------------------------------------------------------------

	/**
	 * Return array listing all authenticated providers
	 */
	public static function getConnectedProviders() {
		$idps = array();

		foreach (self::$config["providers"] as $idpid => $params) {
			if (self::isConnectedWith($idpid)) {
				$idps[] = $idpid;
			}
		}

		return $idps;
	}

	// --------------------------------------------------------------------

	/**
	 * Return array listing all enabled providers as well as a flag if you are connected.
	 */
	public static function getProviders() {
		$idps = array();

		foreach (self::$config["providers"] as $idpid => $params) {
			if ($params['enabled']) {
				$idps[$idpid] = array('connected' => false);

				if (self::isConnectedWith($idpid)) {
					$idps[$idpid]['connected'] = true;
				}
			}
		}

		return $idps;
	}

	// --------------------------------------------------------------------

	/**
	 * A generic function to logout all connected provider at once
	 */
	public static function logoutAllProviders() {
		$idps = self::getConnectedProviders();

		foreach ($idps as $idp) {
			$adapter = self::getAdapter($idp);

			$adapter->logout();
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Utility function, redirect to a given URL with php header or using javascript location.href
	 */
	public static function redirect($url, $mode = "PHP") {
		Logger::info("Enter self::redirect( $url, $mode )");

		if ($mode == "PHP") {
			header("Location: $url");
		}
		elseif ($mode == "JS") {
			echo '<html>';
			echo '<head>';
			echo '<script type="text/javascript">';
			echo 'function redirect(){ window.top.location.href="' . $url . '"; }';
			echo '</script>';
			echo '</head>';
			echo '<body onload="redirect()">';
			echo 'Redirecting, please wait...';
			echo '</body>';
			echo '</html>';
		}

		die();
	}

	// --------------------------------------------------------------------

	/**
	 * Utility function, return the current url. TRUE to get $_SERVER['REQUEST_URI'], FALSE for $_SERVER['PHP_SELF']
	 */
	public static function getCurrentUrl($request_uri = true) {
		if (
			isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1)
			|| isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'
		) {
			$protocol = 'https://';
		}
		else {
			$protocol = 'http://';
		}

		$url = $protocol . $_SERVER['HTTP_HOST'];

		// use port if non default
		if (isset($_SERVER['SERVER_PORT']) && strpos($url, ':' . $_SERVER['SERVER_PORT']) === false) {
			$url .= ($protocol === 'http://' && $_SERVER['SERVER_PORT'] != 80 && ! isset($_SERVER['HTTP_X_FORWARDED_PROTO']))
			|| ($protocol === 'https://' && $_SERVER['SERVER_PORT'] != 443 && ! isset($_SERVER['HTTP_X_FORWARDED_PROTO']))
				? ':' . $_SERVER['SERVER_PORT']
				: '';
		}

		if ($request_uri) {
			$url .= $_SERVER['REQUEST_URI'];
		}
		else {
			$url .= $_SERVER['PHP_SELF'];
		}

		// return current url
		return $url;
	}
}

<?php
namespace Scion\Authentication\Adapter\HybridAuth;

/*!
* HybridAuth
* http://hybridauth.sourceforge.net | http://github.com/hybridauth/hybridauth
* (c) 2009-2012, HybridAuth authors | http://hybridauth.sourceforge.net/licenses.html
*/
use Scion\Loader\RouteLoader;

/**
 * self class
 * self class provides a simple way to handle the OpenID and OAuth endpoint.
 */
class Endpoint {
	public static $request = null;
	public static $initDone = false;

	/**
	 * Process the current request
	 * $request - The current request parameters. Leave as NULL to default to use $_REQUEST.
	 */
	public static function process($request = null) {
		// Setup request variable
		self::$request = $request;

		if (is_null(self::$request)) {
			// Fix a strange behavior when some provider call back ha endpoint
			// with /index.php?hauth.done={provider}?{args}...
			// >here we need to recreate the $_REQUEST
			if (strrpos($_SERVER["QUERY_STRING"], '?')) {
				$_SERVER["QUERY_STRING"] = str_replace("?", "&", $_SERVER["QUERY_STRING"]);

				parse_str($_SERVER["QUERY_STRING"], $_REQUEST);
			}

			self::$request = $_REQUEST;
		}

		// If openid_policy requested, we return our policy document
		if (isset(self::$request["get"]) && self::$request["get"] == "openid_policy") {
			self::processOpenidPolicy();
		}

		// If openid_xrds requested, we return our XRDS document
		if (isset(self::$request["get"]) && self::$request["get"] == "openid_xrds") {
			self::processOpenidXRDS();
		}

		$route = RouteLoader::getRouter()->getMatchedRoute();

		// If we get a hauth.start
		if ($route->getName() == 'hauth_start') {
			self::processAuthStart();
		}
		// Else if hauth.done
		elseif ($route->getName() == 'hauth_done') {
			self::processAuthDone();
		}
		// Else we advertise our XRDS document, something supposed to be done from the Realm URL page
		else {
			self::processOpenidRealm();
		}
	}

	/**
	 * Process OpenID policy request
	 */
	public static function processOpenidPolicy() {
		$output = file_get_contents(dirname(__FILE__) . "/resources/openid_policy.html");
		print $output;
		die();
	}

	/**
	 * Process OpenID XRDS request
	 */
	public static function processOpenidXRDS() {
		header("Content-Type: application/xrds+xml");

		$output = str_replace("{RETURN_TO_URL}", str_replace(array("<", ">", "\"", "'", "&"), array("&lt;", "&gt;",
																									"&quot;", "&apos;",
																									"&amp;"
																							  ), Auth::getCurrentUrl(false)), file_get_contents(dirname(__FILE__) . "/resources/openid_xrds.xml"));
		print $output;
		die();
	}

	/**
	 * Process OpenID realm request
	 */
	public static function processOpenidRealm() {
		$output = str_replace("{X_XRDS_LOCATION}", htmlentities(Auth::getCurrentUrl(false), ENT_QUOTES, 'UTF-8') . "?get=openid_xrds&v=" . Auth::$version, file_get_contents(dirname(__FILE__) . "/resources/openid_realm.html"));
		print $output;
		die();
	}

	/**
	 * define:endpoint step 3.
	 */
	public static function processAuthStart() {
		self::authInit();

		$provider_id = trim(strip_tags(RouteLoader::getRouter()->getParam('provider')));

		# check if page accessed directly
		if (!Auth::storage()->get("hauth_session.$provider_id.hauth_endpoint")) {
			Logger::error("Endpoint: hauth_endpoint parameter is not defined on hauth_start, halt login process!");

			header("HTTP/1.0 404 Not Found");
			die("You cannot access this page directly.");
		}

		# define:hybrid.endpoint.php step 2.
		$hauth = Auth::setup($provider_id);

		# if REQUESTED hauth_idprovider is wrong, session not created, etc.
		if (!$hauth) {
			Logger::error("Endpoint: Invalid parameter on hauth_start!");

			header("HTTP/1.0 404 Not Found");
			die("Invalid parameter! Please return to the login page and try again.");
		}

		try {
			Logger::info("Endpoint: call adapter [{$provider_id}] loginBegin()");

			$hauth->adapter->loginBegin();
		}
		catch (\Exception $e) {
			Logger::error("Exception:" . $e->getMessage(), $e);
			Error::setError($e->getMessage(), $e->getCode(), $e->getTraceAsString(), $e->getPrevious());

			$hauth->returnToCallbackUrl();
		}

		die();
	}

	/**
	 * define:endpoint step 3.1 and 3.2
	 */
	public static function processAuthDone() {
		self::authInit();

		$provider_id = trim(strip_tags(RouteLoader::getRouter()->getParam('provider')));

		$hauth = Auth::setup($provider_id);

		if (!$hauth) {
			Logger::error("Endpoint: Invalid parameter on hauth_done!");

			$hauth->adapter->setUserUnconnected();

			header("HTTP/1.0 404 Not Found");
			die("Invalid parameter! Please return to the login page and try again.");
		}

		try {
			Logger::info("Endpoint: call adapter [{$provider_id}] loginFinish() ");

			$hauth->adapter->loginFinish();
		}
		catch (\Exception $e) {
			Logger::error("Exception:" . $e->getMessage(), $e);
			Error::setError($e->getMessage(), $e->getCode(), $e->getTraceAsString(), $e->getPrevious());

			$hauth->adapter->setUserUnconnected();
		}

		Logger::info("Endpoint: job done. retrun to callback url.");

		$hauth->returnToCallbackUrl();
		die();
	}

	public static function authInit() {
		if (!self::$initDone) {
			self::$initDone = true;

			# Init Auth
			try {
				$storage = new Storage();

				// Check if Auth session already exist
				if (!$storage->config("CONFIG")) {
					header("HTTP/1.0 404 Not Found");
					die("You cannot access this page directly.");
				}

				Auth::initialize($storage->config("CONFIG"));
			}
			catch (\Exception $e) {
				Logger::error("Endpoint: Error while trying to init Auth");

				header("HTTP/1.0 404 Not Found");
				die("Oophs. Error!");
			}
		}
	}
}

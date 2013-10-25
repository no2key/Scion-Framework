<?php
namespace Scion\Authentication\Adapter\HybridAuth\thirdparty\Viadeo;

// == THE VIADEO API CLASS ====================================================
//
// You can create an instance in two ways :
//
//      // Empty instance, needs authentication
//      $VD = new ViadeoAPI();
//
//           or
//
//      // With an access token, authentication is done
//      $AT = "abcdef42ghijkl42mnopqr42stuvwxyz";
//      $VD = new ViadeoAPI($AT);
//
// You can also specify some options :
//
//      $VD->init(array(
//        'client_id'        =>    'CLIENTID',
//        'client_secret'    =>    'CLIENTSECRE'
//      ));
//
//      $VD->setOption('store', true);
//
// Available options are :
//
//      - client_id     (OAuth 2.0 - mandatory for authentication)
//      - client_secret (OAuth 2.0 - mandatory for authentication)
//      - access_token  (other way to specify access_token)
//      - store (bool)  (enable/disable access_token storing into cookie)
//
// You can also specify cURL options to be used during connections :
//
//      $VD->setCurlOption(CURLOPT_TIMEOUT, 10);
//
// OAuth 2.0 Connection management :
//
//      $VD->isAuthenticated();                  // True if access_token is set
//
//      $VD->disconnect();                       // if access_token storage is activated
//                                               // delete the cookie
//
//      $VD->(set/get)AccessToken($AT);          // The Viadeo API Acccess Token
//
//      $VD->(set/get)AuthorizationCode($AC);    // The OAuth2.0 step 1 code)
//                                               // Try to get from $_REQUEST if not set
//
//      $VD->(set/get)RedirectURI('http://...'); // Defaulted to current script URI
//
// OAuth 2.0 - step 1 :
//
//      $VD->getAuthorizationURL();      // Return the URL for user redirection
//      $VD->getAuthorizationURLPopup(); // Same thing but with popup layout
//      $VD-authorize();                 // Helper, redirects user to the getAuthorizationURL()
//                                       // Send header('Location')
//
// OAuth 2.0 - step 2 :
//
//      $VD->setAccessTokenFromCode();   // Use the step 1 code (getAuthorizationCode())
//                                       // in order to fill-in the access token from cURL
//
// OAuth helper :
//
//      $VD->OAuth_auto();               // Automatically runs all the OAuth 2.0 workflow
//                                       // on main page
//      ex :
//
//      // insert here $VD initialization, setting client_id and client_secret
//      try { $VD->OAuth_auto(); } catch (ViadeoException $e)  {
//          echo "An error occured during Viadeo API authentication: $e";
//      }
//      // insert here API calls, ex: $me = $VD->get('me')->execute();
//
// Execute a ViadeoRequest :
//
//      $res = $VD->execute($req);
//
// ============================================================================

class ViadeoAPI {

	private $authorization_code; // OAuth 2.0 - The authorization code
	private $redirect_uri; // OAuth 2.0 - The redirection URI
	private $access_token; // OAuth 2.0 - The Access Token for API calls
	private $config; // The Viadeo API configuration

	// -- Static URIs ---------------------------------------------------------
	public static $api_base = "https://api.viadeo.com";
	public static $authorize_url = "https://secure.viadeo.com/oauth-provider/authorize2";
	public static $token_url = "https://secure.viadeo.com/oauth-provider/access_token2";

	// -- Default CURL options ------------------------------------------------
	private $curl_opts = array(
		CURLOPT_CONNECTTIMEOUT => 10,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_HEADER         => true,
		CURLOPT_TIMEOUT        => 60,
		CURLOPT_USERAGENT      => "viadeo-api-php-sdk-agent", // FIXME: add version
		CURLOPT_HTTPHEADER     => array("Accept: application/json; charset=UTF-8")
	);

	// == Initialization / Configuration ======================================
	// ========================================================================
	function __construct($access_token = null) {
		$this->setAccessToken($access_token);
	}

	public function init($config) {
		$this->config = $config;

		return $this;
	}

	public function setOption($name, $value) {
		$this->config[$name] = $value;
	}

	private function getConfigKey($key, $mandatory = false) {
		if (isset($this->config[$key])) {
			return $this->config[$key];
		}
		else if ($mandatory) {
			throw new ViadeoInvalidConfigurationException(
				"Configuration key '" . $key . "' is missing");
		}
		else {
			return null;
		}
	}

	private function getCookieName() {
		$suffix = $this->getConfigKey('client_id');
		if ($suffix == null) {
			$suffix = "default";
		}

		return "vds_" . $suffix;
	}

	public function setCurlOption($key, $value) {
		$this->curl_opts[$key] = $value;
	}

	// == OAuth2 Authentication layer =========================================
	// ========================================================================

	// -- Access Token mgt ----------------------------------------------------
	public function isAuthenticated() {
		return ($this->getAccessToken() != null);
	}

	public function disconnect() {
		$this->access_token = null;
		if ($this->getConfigKey('store') === true) {
			setcookie($this->getCookieName(), "", time() - 3600);
			unset($_COOKIE[$this->getCookieName()]);
		}

		return $this;
	}

	public function setAccessToken($access_token) {
		$this->access_token = $access_token;
		if ($this->getConfigKey('store') === true) {
			setrawcookie($this->getCookieName(),
				'"access_token=' . $access_token . '"', time() + 3600);
		}

		return $this;
	}

	public function getAccessToken() {
		$token = null;

		if (isset($this->access_token)) {
			$token = $this->access_token;

		}
		else if ($this->getConfigKey('access_token') != null) {
			$this->access_token = $this->getConfigKey('access_token');
			$token              = $this->access_token;

		}
		else if ($this->getConfigKey('store') === true) {
			if (isset($_COOKIE[$this->getCookieName()])) {
				$cookVal = $_COOKIE[$this->getCookieName()];
				parse_str(str_replace('"', '', $cookVal), $cookArr);
				if (isset($cookArr['access_token'])) {
					$this->access_token = $cookArr['access_token'];
					$token              = $this->access_token;
				}
			}
		}

		return $token;
	}

	// -- Authorization code --------------------------------------------------
	public function setAuthorizationCode($authorization) {
		$this->authorization_code = $authorization;

		return $this;
	}

	public function getAuthorizationCode() {
		$code = null;

		if (isset($this->authorization_code)) {
			$code = $this->authorization_code;

		}
		else if (isset($_REQUEST["code"])) {
			$this->authorization_code = $_REQUEST["code"];
			$code                     = $this->authorization_code;

		}
		else if (isset($_REQUEST["error"])) {
			throw new ViadeoOAuth2Exception($_REQUEST["error"]);
		}

		return $code;
	}

	// -- redirect uri --------------------------------------------------------
	public function setRedirectURI($redirect_uri) {
		$this->redirect_uri = $redirect_uri;

		return $this;
	}

	public function getRedirectURI() {
		if (isset($this->redirect_uri)) {
			return $this->redirect_uri;
		}
		else {
			return ViadeoHelper::getCurrentURL();
		}
	}

	// -- OAuth2.0 step 1 -- get authorization code ---------------------------
	public function getAuthorizationURL($extras = array()) {
		$params = array_merge(array(
								   'response_type' => 'code',
								   'client_id'     => self::getConfigKey('client_id', true),
								   'redirect_uri'  => self::getRedirectURI()
							  ), $extras);
		$url    = self::$authorize_url . "?" . http_build_query($params, null, '&');

		return $url;
	}

	public function getAuthorizationURLPopup($extras = array()) {
		$extras['display'] = 'popup';

		return $this->getAuthorizationURL($extras);
	}

	public function authorize($extras = array()) {
		header("Location: " . self::getAuthorizationURL($extras));
	}

	// -- OAuth2.0 step 2 -- exchange code with access_token ------------------
	public function setAccessTokenFromCode($extras = array()) {
		$curl_opts = $this->curl_opts;
		$params    = array_merge(array(
									  'grant_type'    => 'authorization_code',
									  'client_id'     => $this->getConfigKey('client_id', true),
									  'client_secret' => $this->getConfigKey('client_secret', true),
									  'redirect_uri'  => $this->getRedirectURI(),
									  'code'          => $this->getAuthorizationCode()
								 ), $extras);

		$curl_opts[CURLOPT_URL]        = self::$token_url;
		$curl_opts[CURLOPT_POSTFIELDS] = http_build_query($params, null, '&');

		$ch = curl_init(self::$token_url);
		curl_setopt_array($ch, $curl_opts);

		// mod:btw:dont yell at me
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		$result = curl_exec($ch);

		if ($result === false) {
			throw new ViadeoConnectionException(curl_error($ch));
		}

		list($headers, $body) = explode("\r\n\r\n", $result);
		$result = json_decode($body);

		$ex = null;
		try {
			if (isset($result->error)) {
				throw new ViadeoOAuth2Exception($result->error);
			}
			else if (isset($result->access_token)) {
				$this->setAccessToken($result->access_token);
			}
			else {
				throw new ViadeoOAuth2Exception("No token returned !");
			}
		}
		catch (ViadeoException $e) {
			$ex = $e;
		}
		curl_close($ch);

		if ($ex) {
			throw $ex;
		}

		return $this;
	}

	// -- OAuth2.0 - Automation -----------------------------------------------
	public function OAuth_auto() {
		if ($this->isAuthenticated()) {
			return;
		}
		else if ($this->getAuthorizationCode() != null) {
			$this->setAccessTokenFromCode();
		}
		else {
			$this->authorize();
		}
	}

	// == Request management ==================================================
	// ========================================================================

	public function id($id) {
		$d = preg_replace('/^\/*(.*)\/*?$/', '$1', $id);

		return new ViadeoRequest($this, '/' . $id);
	}

	public function get($path) {
		return new ViadeoRequest($this, $path);
	}

	public function post($path) {
		return new ViadeoRequest($this, $path, "POST");
	}

	public function put($path) {
		return new ViadeoRequest($this, $path, "PUT");
	}

	public function del($path) {
		return new ViadeoRequest($this, $path, "DELETE");
	}

	// ------------------------------------------------------------------------

	public function execute(ViadeoRequest $request) {
		if (! $this->isAuthenticated()) {
			throw new ViadeoAuthenticationException("No access token is defined");
		}

		$curl_opts = $this->curl_opts;

		$curl_opts[CURLOPT_HTTPHEADER] = array('Authorization: Bearer ' . $this->getAccessToken());

		$headers = array('Authorization: Bearer ' . $this->getAccessToken());
		if ($request->getMethod() != "GET") {
			# post method dynamically overriden by Tianji adaptation scripts
			$post_method                      = "application/x-www-form-urlencoded; charset=UTF-8";
			$headers[]                        = 'Content-Type: ' . $post_method;
			$json                             = (strpos($post_method, 'json') == false) ? false : true;
			$curl_opts[CURLOPT_POSTFIELDS]    = $request->getParams(array(), $json);
			$curl_opts[CURLOPT_CUSTOMREQUEST] = $request->getMethod();

			$url = $request->getPath();
		}
		else {
			$url = $request->getFullPath();
		}
		$curl_opts[CURLOPT_HTTPHEADER] = $headers;
		$curl_opts[CURLOPT_URL]        = $url;

		$ch = curl_init($url);
		curl_setopt_array($ch, $curl_opts);

		// mod:btw:dont yell at me
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		$result = curl_exec($ch);

		if ($result === false) {
			throw new ViadeoConnectionException(curl_error($ch));
		}

		list($headers, $body) = explode("\r\n\r\n", $result);
		$result = json_decode($body);

		$ex = null;
		if (isset($result->error)) {
			curl_close($ch);
			throw new ViadeoAPIException($result->error->type . " - " . $result->error->message[0]);
		}
		curl_close($ch);

		return isset($result->id) ? new ViadeoGraphObject($this, $result) : $result;
	}

	public function object($data) {
		return new ViadeoGraphObject($this, $data);
	}
}
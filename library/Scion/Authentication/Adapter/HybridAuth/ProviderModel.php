<?php
namespace Scion\Authentication\Adapter\HybridAuth;
	/*!
	* HybridAuth
	* http://hybridauth.sourceforge.net | http://github.com/hybridauth/hybridauth
	* (c) 2009-2012, HybridAuth authors | http://hybridauth.sourceforge.net/licenses.html
	*/

/**
 * Provider_Model provide a common interface for supported IDps on HybridAuth.
 *
 * Basically, each provider adapter has to define at least 4 methods:
 *   Providers_{provider_name}::initialize()
 *   Providers_{provider_name}::loginBegin()
 *   Providers_{provider_name}::loginFinish()
 *   Providers_{provider_name}::getUserProfile()
 *
 * HybridAuth also come with three others models
 *   Class Provider_Model_OpenID for providers that uses the OpenID 1 and 2 protocol.
 *   Class Provider_Model_OAuth1 for providers that uses the OAuth 1 protocol.
 *   Class Provider_Model_OAuth2 for providers that uses the OAuth 2 protocol.
 */
abstract class ProviderModel {
	/* IDp ID (or unique name) */
	public $providerId = null;

	/* specific provider adapter config */
	public $config = null;

	/* provider extra parameters */
	public $params = null;

	/* Endpoint URL for that provider */
	public $endpoint = null;

	/* User obj, represents the current loggedin user */
	public $user = null;

	/* the provider api client (optional) */
	public $api = null;

	/**
	 * common providers adapter constructor
	 */
	function __construct($providerId, $config, $params = null) {
		# init the IDp adapter parameters, get them from the cache if possible
		if (!$params) {
			$this->params = Auth::storage()->get("hauth_session.$providerId.id_provider_params");
		}
		else {
			$this->params = $params;
		}

		// idp id
		$this->providerId = $providerId;

		// set HybridAuth endpoint for this provider
		$this->endpoint = Auth::storage()->get("hauth_session.$providerId.hauth_endpoint");

		// idp config
		$this->config = $config;

		// new user instance
		$this->user             = new User();
		$this->user->providerId = $providerId;

		// initialize the current provider adapter
		$this->initialize();

		Logger::debug("Provider_Model::__construct( $providerId ) initialized. dump current adapter instance: ", serialize($this));
	}

	// --------------------------------------------------------------------

	/**
	 * IDp wrappers initializer
	 *
	 * The main job of wrappers initializer is to performs (depend on the IDp api client it self):
	 *     - include some libs nedded by this provider,
	 *     - check IDp key and secret,
	 *     - set some needed parameters (stored in $this->params) by this IDp api client
	 *     - create and setup an instance of the IDp api client on $this->api
	 */
	abstract protected function initialize();

	// --------------------------------------------------------------------

	/**
	 * begin login
	 */
	abstract protected function loginBegin();

	// --------------------------------------------------------------------

	/**
	 * finish login
	 */
	abstract protected function loginFinish();

	// --------------------------------------------------------------------

	/**
	 * generic logout, just erase current provider adapter stored data to let Auth all forget about it
	 */
	function logout() {
		Logger::info("Enter [{$this->providerId}]::logout()");

		$this->clearTokens();

		return true;
	}

	// --------------------------------------------------------------------

	/**
	 * grab the user profile from the IDp api client
	 */
	function getUserProfile() {
		Logger::error("HybridAuth do not provide users contacts list for {$this->providerId} yet.");

		throw new \Exception("Provider does not support this feature (userProfile).", 8);
	}

	// --------------------------------------------------------------------

	/**
	 * load the current logged in user contacts list from the IDp api client
	 */
	function getUserContacts() {
		Logger::error("HybridAuth do not provide users contacts list for {$this->providerId} yet.");

		throw new \Exception("Provider does not support this feature (userContacts).", 8);
	}

	// --------------------------------------------------------------------

	/**
	 * return the user activity stream
	 */
	function getUserActivity($stream) {
		Logger::error("HybridAuth do not provide user's activity stream for {$this->providerId} yet.");

		throw new \Exception("Provider does not support this feature (userActivity).", 8);
	}

	// --------------------------------------------------------------------

	/**
	 * return the user activity stream
	 */
	function setUserStatus($status) {
		Logger::error("HybridAuth do not provide user's activity stream for {$this->providerId} yet.");

		throw new \Exception("Provider does not support this feature (userStatus).", 8);
	}

	// --------------------------------------------------------------------

	/**
	 * return true if the user is connected to the current provider
	 */
	public function isUserConnected() {
		return (bool)Auth::storage()->get("hauth_session.{$this->providerId}.is_logged_in");
	}

	// --------------------------------------------------------------------

	/**
	 * set user to connected
	 */
	public function setUserConnected() {
		Logger::info("Enter [{$this->providerId}]::setUserConnected()");

		Auth::storage()->set("hauth_session.{$this->providerId}.is_logged_in", 1);
	}

	// --------------------------------------------------------------------

	/**
	 * set user to unconnected
	 */
	public function setUserUnconnected() {
		Logger::info("Enter [{$this->providerId}]::setUserUnconnected()");

		Auth::storage()->set("hauth_session.{$this->providerId}.is_logged_in", 0);
	}

	// --------------------------------------------------------------------

	/**
	 * get or set a token
	 */
	public function token($token, $value = null) {
		if ($value === null) {
			return Auth::storage()->get("hauth_session.{$this->providerId}.token.$token");
		}
		else {
			Auth::storage()->set("hauth_session.{$this->providerId}.token.$token", $value);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * delete a stored token
	 */
	public function deleteToken($token) {
		Auth::storage()->delete("hauth_session.{$this->providerId}.token.$token");
	}

	// --------------------------------------------------------------------

	/**
	 * clear all existen tokens for this provider
	 */
	public function clearTokens() {
		Auth::storage()->deleteMatch("hauth_session.{$this->providerId}.");
	}
}

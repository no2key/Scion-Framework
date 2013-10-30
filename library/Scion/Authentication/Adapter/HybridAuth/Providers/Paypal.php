<?php
namespace Scion\Authentication\Adapter\HybridAuth\Providers;

use Scion\Authentication\Adapter\HybridAuth\Auth;
use Scion\Authentication\Adapter\HybridAuth\ProviderModelOAuth2;

class Paypal extends ProviderModelOAuth2 {

	private $_scope = 'openid';

	/**
	 * IDp wrappers initializer
	 */
	function initialize() {
		parent::initialize();

		// Provider api end-points
		$this->api->api_base_url  = 'https://api.paypal.com';
		$this->api->authorize_url = 'https://www.paypal.com/webapps/auth/protocol/openidconnect/v1/authorize';
		$this->api->token_url     = 'https://api.paypal.com/v1/identity/openidconnect/tokenservice';

		// for access_token need to POST data instead of using GET
		$this->api->access_token_method = 'POST';
	}

	/**
	 * begin login step
	 */
	function loginBegin() {
		$parameters = ['scope' => $this->_scope];

		Auth::redirect($this->api->authorizeUrl($parameters));
	}

	/**
	 * load the user profile from the IDp api client
	 */
	function getUserProfile() {
		// refresh tokens if needed
		//$this->refreshToken();

		// ask api for user infos
		//$response = $this->api->get('https://www.paypal.com/webapps/auth/protocol/openidconnect/v1/userinfo', ['schema' => 'openid', 'access_token' => $this->api->access_token]);
		//var_dump($this->api->access_token);
	}
} 
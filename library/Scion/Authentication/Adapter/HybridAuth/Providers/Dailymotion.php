<?php
namespace Scion\Authentication\Adapter\HybridAuth\Providers;

use Scion\Authentication\Adapter\HybridAuth\Auth;
use Scion\Authentication\Adapter\HybridAuth\ProviderModelOAuth2;

class Dailymotion extends ProviderModelOAuth2 {

	// Scopes, white space separator
	private $_scope = 'read write email userinfo';

	// see http://www.dailymotion.com/doc/api/obj-user.html
	private $_fields = 'address,avatar_120_url,birthday,city,id,email,country,description,gender,language,first_name,last_name,phone,screenname,username';

	/**
	 * IDp wrappers initializer
	 */
	function initialize() {
		parent::initialize();

		// Provider apis end-points
		$this->api->api_base_url  = 'https://api.dailymotion.com/';
		$this->api->authorize_url = 'https://www.dailymotion.com/oauth/authorize';
		$this->api->token_url     = 'https://api.dailymotion.com/oauth/token';
	}

	/**
	 * begin login step
	 */
	function loginBegin() {
		$parameters = ['scope' => $this->_scope];
		$optionals  = ['scope'];

		foreach ($optionals as $parameter) {
			if (isset($this->config[$parameter]) && !empty($this->config[$parameter])) {
				$parameters[$parameter] = $this->config[$parameter];
			}
		}

		Auth::redirect($this->api->authorizeUrl($parameters));
	}

	/**
	 * load the user profile from the IDp api client
	 */
	function getUserProfile() {
		// refresh tokens if needed
		$this->refreshToken();

		// parameters
		$parameters = ['access_token' => $this->api->access_token, 'fields' => $this->_fields];
		$optionals  = ['fields'];

		foreach ($optionals as $parameter) {
			if (isset($this->config[$parameter]) && !empty($this->config[$parameter])) {
				$parameters[$parameter] = $this->config[$parameter];
			}
		}

		// ask api for user infos
		$response = $this->api->get('https://api.dailymotion.com/me', $parameters);

		$this->user->profile->identifier  = (property_exists($response, 'id')) ? $response->id : '';
		$this->user->profile->displayName = (property_exists($response, 'screenname')) ? $response->screenname : '';
		$this->user->profile->address     = (property_exists($response, 'address')) ? $response->address : '';
		$this->user->profile->email       = (property_exists($response, 'email')) ? $response->email : '';
		$this->user->profile->city        = (property_exists($response, 'city')) ? $response->city : '';
		$this->user->profile->birthDay    = (property_exists($response, 'birthday')) ? $response->birthday : '';
		$this->user->profile->country     = (property_exists($response, 'country')) ? $response->country : '';
		$this->user->profile->description = (property_exists($response, 'description')) ? $response->description : '';
		$this->user->profile->gender      = (property_exists($response, 'gender')) ? $response->gender : '';
		$this->user->profile->language    = (property_exists($response, 'language')) ? $response->language : '';
		$this->user->profile->firstName   = (property_exists($response, 'first_name')) ? $response->first_name : '';
		$this->user->profile->lastName    = (property_exists($response, 'last_name')) ? $response->last_name : '';
		$this->user->profile->phone       = (property_exists($response, 'phone')) ? $response->phone : '';
		$this->user->profile->photoURL    = (property_exists($response, 'avatar_120_url')) ? $response->avatar_120_url : '';
		$this->user->profile->profileURL  = (property_exists($response, 'username')) ? 'http://www.dailymotion.com/' . $response->username : '';

		return $this->user->profile;
	}
}
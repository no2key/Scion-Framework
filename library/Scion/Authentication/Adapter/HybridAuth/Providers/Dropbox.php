<?php
namespace Scion\Authentication\Adapter\HybridAuth\Providers;

use Scion\Authentication\Adapter\HybridAuth\Auth;
use Scion\Authentication\Adapter\HybridAuth\ProviderModelOAuth2;
use Scion\Math\Rand;

class Dropbox extends ProviderModelOAuth2 {

	/**
	 * IDp wrappers initializer
	 */
	function initialize() {
		parent::initialize();

		// Provider api end-points
		$this->api->api_base_url  = 'https://www.dropbox.com';
		$this->api->authorize_url = 'https://www.dropbox.com/1/oauth2/authorize';
		$this->api->token_url     = 'https://api.dropbox.com/1/oauth2/token';
	}

	/**
	 * begin login step
	 */
	function loginBegin() {
		$parameters = ['state' => Rand::secureRandomBytes(16), 'response_type' => 'token'];

		Auth::redirect($this->api->authorizeUrl($parameters));
	}

} 
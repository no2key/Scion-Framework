<?php
namespace Scion\Authentication\Adapter\HybridAuth\Providers;

use Scion\Authentication\Adapter\HybridAuth\ProviderModelOAuth2;

class PixelPin extends ProviderModelOAuth2 {
	/**
	 * IDp wrappers initializer
	 */
	function initialize() {
		parent::initialize();

		// Provider apis end-points
		$this->api->api_base_url  = 'https://ws3.pixelpin.co.uk/index.php/api/';
		$this->api->authorize_url = 'https://login.pixelpin.co.uk/OAuth2/FLogin.aspx';
		$this->api->token_url     = 'https://ws3.pixelpin.co.uk/index.php/api/token';

		$this->api->sign_token_name = 'oauth_token';
	}

	/**
	 * load the user profile from the IDp api client
	 */
	function getUserProfile() {
		$data = $this->api->api('userdata', 'POST');

		if (!isset($data->id)) {
			throw new \Exception('User profile request failed! '.$this->providerId.' returned an invalid response.', 6);
		}

		$this->user->profile->identifier    = $data->id;
		$this->user->profile->firstName     = $data->firstName;
		$this->user->profile->displayName   = $data->firstName;
		$this->user->profile->email         = $data->email;
		$this->user->profile->emailVerified = $data->email;

		return $this->user->profile;
	}
} 
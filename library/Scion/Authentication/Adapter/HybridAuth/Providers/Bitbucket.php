<?php
namespace Scion\Authentication\Adapter\HybridAuth\Providers;

use Scion\Authentication\Adapter\HybridAuth\ProviderModelOAuth1;

class Bitbucket extends ProviderModelOAuth1 {
	/**
	 * IDp wrappers initializer
	 */
	function initialize() {
		parent::initialize();

		// provider api end-points
		$this->api->api_base_url      = 'https://bitbucket.org/';
		$this->api->authorize_url     = 'https://bitbucket.org/!api/1.0/oauth/authenticate';
		$this->api->request_token_url = 'https://bitbucket.org/!api/1.0/oauth/request_token';
		$this->api->access_token_url  = 'https://bitbucket.org/!api/1.0/oauth/access_token';
	}

	/**
	 * load the user profile from the IDp api client
	 */
	function getUserProfile() {
		$response = $this->api->get('https://bitbucket.org/api/1.0/user');

		if (!isset($response->user)) {
			throw new \Exception('User profile request failed! {$this->providerId} returned an invalid response.', 6);
		}

		$this->user->profile->identifier    = (property_exists($response->user, 'resource_uri')) ? $response->user->resource_uri : '';
		$this->user->profile->firstName     = (property_exists($response->user, 'first_name')) ? $response->user->first_name : '';
		$this->user->profile->lastName      = (property_exists($response->user, 'last_name')) ? $response->user->last_name : '';
		$this->user->profile->displayName   = (property_exists($response->user, 'display_name')) ? $response->user->display_name : '';
		$this->user->profile->photoURL      = (property_exists($response->user, 'avatar')) ? $response->user->avatar : '';
		$this->user->profile->profileURL    = 'https://bitbucket.org/' . $response->user->username;

		return $this->user->profile;
	}
} 
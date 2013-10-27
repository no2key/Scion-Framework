<?php
namespace Scion\Authentication\Adapter\HybridAuth\Providers;

use Scion\Authentication\Adapter\HybridAuth\Auth;
use Scion\Authentication\Adapter\HybridAuth\ProviderModel;
use Scion\Authentication\Adapter\HybridAuth\thirdparty\Draugiem\DraugiemApi;

class Draugiem extends ProviderModel {
	public $user_id;

	/**
	 * IDp wrappers initializer
	 */
	function initialize() {

		if (!$this->config['keys']['key'] || !$this->config['keys']['secret']) {
			throw new \Exception('Your application key and secret are required in order to connect to ' . $this->providerId . '.', 4);
		}

		// include supplied api wrapper
		require_once Auth::$config['path_libraries'] . 'Draugiem/DraugiemApi.php';

		//Create Draugiem.lv API object
		$this->api = new DraugiemApi($this->config['keys']['key'], $this->config['keys']['secret']);

		//Try to authenticate user
		$session = $this->api->getSession();

		//Authentication successful
		if ($session) {
			//Get user info
			$user = $this->api->getUserData();

			$this->user_id = $user['uid'];
		}

	}

	/**
	 * begin login step
	 */
	function loginBegin() {

		Auth::redirect($this->api->getLoginUrl($this->endpoint));

	}

	/**
	 * finish login step
	 */
	function loginFinish() {

		if (!$_REQUEST['dr_auth_code']) {
			throw new \Exception('Authentication failed! ' . $this->providerId . ' returned an invalid Token and Verifier.', 5);
		}

		$this->token('access_token', $_REQUEST['dr_auth_code']);

		// set user as logged in
		$this->setUserConnected();

		Auth::storage()->set("hauth_session.{$this->providerId}.user", $this->user);

	}

	/**
	 * load the user profile from the IDp api client
	 */
	function getUserProfile() {

		//Get user info
		$response = $this->api->getUserData();

		if (!$response) {
			throw new \Exception('User profile request failed! ' . $this->providerId . ' api returned an invalid response.', 6);
		}

		list($year, $month, $day) = explode("-", $response['birthday']);

		$this->user->profile->identifier  = @ $response['uid'];
		$this->user->profile->displayName = $response['name'] . ' ' . $response['surname'];
		$this->user->profile->firstName   = @ $response['name'];
		$this->user->profile->lastName    = @ $response['surname'];
		$this->user->profile->age         = @ $response['age'];
		$this->user->profile->birthDay    = @ $day;
		$this->user->profile->birthMonth  = @ $month;
		$this->user->profile->birthYear   = @ $year;
		$this->user->profile->address     = @ $response['place'];
		$this->user->profile->profileURL  = @ 'http://www.draugiem.lv/user/' . $response['uid'];
		$this->user->profile->photoURL    = @ $response['img'];
		$this->user->profile->webSiteURL  = @ '';
		switch ($response['sex']) {
			case 'M':
				$this->user->profile->gender = 'male';
				break;
			case 'F':
				$this->user->profile->gender = 'female';
				break;
		}

		return $this->user->profile;
	}

} 
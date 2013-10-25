<?php
namespace Scion\Authentication\Adapter\HybridAuth\Providers;

use Scion\Authentication\Adapter\HybridAuth\Auth;
use Scion\Authentication\Adapter\HybridAuth\ProviderModelOpenID;

class Steam extends ProviderModelOpenID {
	public $openidIdentifier = "http://steamcommunity.com/openid";

	/**
	 * finish login step
	 */
	function loginFinish() {
		parent::loginFinish();

		$uid = str_replace("http://steamcommunity.com/openid/id/", "", $this->user->profile->identifier);

		if ($uid) {
			$data = @ file_get_contents("http://steamcommunity.com/profiles/$uid/?xml=1");

			$data = @ new \SimpleXMLElement($data);

			if (! is_object($data)) {
				return false;
			}

			$this->user->profile->displayName = (string)$data->{'steamID'};
			$this->user->profile->photoURL    = (string)$data->{'avatarMedium'};
			$this->user->profile->description = (string)$data->{'summary'};

			$realname = (string)$data->{'realname'};

			if ($realname) {
				$this->user->profile->displayName = $realname;
			}

			$customURL = (string)$data->{'customURL'};

			if ($customURL) {
				$this->user->profile->profileURL = "http://steamcommunity.com/id/$customURL/";
			}

			// restore the user profile
			Auth::storage()->set("hauth_session.{$this->providerId}.user", $this->user);
		}
	}
} 
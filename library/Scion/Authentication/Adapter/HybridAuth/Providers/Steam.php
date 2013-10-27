<?php
namespace Scion\Authentication\Adapter\HybridAuth\Providers;

use Scion\Authentication\Adapter\HybridAuth\Auth;
use Scion\Authentication\Adapter\HybridAuth\ProviderModelOpenID;

/**
 * Class Steam
 * @package Scion\Authentication\Adapter\HybridAuth\Providers
 */
class Steam extends ProviderModelOpenID {
	public $openidIdentifier = "http://steamcommunity.com/openid";

	/**
	 * finish login step
	 */
	function loginFinish() {
		parent::loginFinish();

		// Get SteamID
		$uid                             = str_replace("http://steamcommunity.com/openid/id/", "", $this->user->profile->identifier);
		$this->user->profile->identifier = $uid;

		$link     = file_get_contents('http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=' . $this->config['keys']['key'] . '&steamids=' . $uid . '&format=json');
		$response = json_decode($link)->response->players[0];

		$this->user->profile->displayName = (property_exists($response, 'realname')) ? $response->realname : '';
		$this->user->profile->profileURL  = (property_exists($response, 'profileurl')) ? $response->profileurl : '';
		$this->user->profile->photoURL    = (property_exists($response, 'avatarfull')) ? $response->avatarfull : '';
		$this->user->profile->language    = (property_exists($response, 'loccountrycode')) ? $response->loccountrycode : "";

		// restore the user profile
		Auth::storage()->set("hauth_session.{$this->providerId}.user", $this->user);
	}

} 
<?php
namespace Scion\Authentication\Adapter\HybridAuth\Providers;

use Scion\Authentication\Adapter\HybridAuth\Auth;
use Scion\Authentication\Adapter\HybridAuth\ProviderModelOpenID;

/**
 * Latch provider adapter based on OpenID protocol
 *
 * http://hybridauth.sourceforge.net/userguide/IDProvider_info_Latch.html
 */
class Latch extends ProviderModelOpenID {
	var $openidIdentifier = "http://auth.latch-app.com/OpenIdServer/user.jsp";

	/**
	 * finish login step
	 */
	function loginFinish() {
		parent::loginFinish();

		$this->user->profile->identifier    = $this->user->profile->email;
		$this->user->profile->emailVerified = $this->user->profile->email;

		// restore the user profile
		Auth::storage()->set("hauth_session.{$this->providerId}.user", $this->user);
	}
} 
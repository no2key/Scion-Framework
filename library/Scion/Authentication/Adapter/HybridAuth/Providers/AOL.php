<?php
namespace Scion\Authentication\Adapter\HybridAuth\Providers;

use Scion\Authentication\Adapter\HybridAuth\ProviderModelOpenID;

/**
 * AOL provider adapter based on OpenID protocol
 *
 * http://hybridauth.sourceforge.net/userguide/IDProvider_info_AOL.html
 */
class AOL extends ProviderModelOpenID {
	var $openidIdentifier = "http://openid.aol.com/";
}

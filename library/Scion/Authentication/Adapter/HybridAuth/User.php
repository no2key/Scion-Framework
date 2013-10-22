<?php
namespace Scion\Authentication\Adapter\HybridAuth;
	/*!
	* HybridAuth
	* http://hybridauth.sourceforge.net | http://github.com/hybridauth/hybridauth
	* (c) 2009-2012, HybridAuth authors | http://hybridauth.sourceforge.net/licenses.html
	*/

/**
 * The Hybrid_User class represents the current loggedin user
 */
class User {
	/* The ID (name) of the connected provider */
	public $providerId = null;

	/* timestamp connection to the provider */
	public $timestamp = null;

	/* user profile, containts the list of fields available in the normalized user profile structure used by HybridAuth. */
	public $profile = null;

	/**
	 * inisialize the user object,
	 */
	function __construct() {
		$this->timestamp = time();

		$this->profile = new UserProfile();
	}
}

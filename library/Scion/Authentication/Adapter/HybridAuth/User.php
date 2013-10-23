<?php
namespace Scion\Authentication\Adapter\HybridAuth;

/**
 * The User class represents the current loggedin user
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

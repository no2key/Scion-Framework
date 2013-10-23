<?php
namespace Scion\Authentication\Adapter\HybridAuth;

/**
 * UserContact
 *
 * used to provider the connected user contacts list on a standardized structure across supported social apis.
 *
 * http://hybridauth.sourceforge.net/userguide/Profile_Data_User_Contacts.html
 */
class UserContact {
	/* The Unique contact user ID */
	public $identifier = null;

	/* User website, blog, web page */
	public $webSiteURL = null;

	/* URL link to profile page on the IDp web site */
	public $profileURL = null;

	/* URL link to user photo or avatar */
	public $photoURL = null;

	/* User dispalyName provided by the IDp or a concatenation of first and last name */
	public $displayName = null;

	/* A short about_me */
	public $description = null;

	/* User email. Not all of IDp garant access to the user email */
	public $email = null;
}

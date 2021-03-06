<?php
namespace Scion\Authentication\Adapter\HybridAuth;

/**
 * UserActivity
 *
 * used to provider the connected user activity stream on a standardized structure across supported social apis.
 *
 * http://hybridauth.sourceforge.net/userguide/Profile_Data_User_Activity.html
 */
class UserActivity {
	/* activity id on the provider side, usually given as integer */
	public $id = null;

	/* activity date of creation */
	public $date = null;

	/* activity content as a string */
	public $text = null;

	/* user who created the activity */
	public $user = null;

	public function __construct() {
		$this->user = new \stdClass();

		// typically, we should have a few information about the user who created the event from social apis
		$this->user->identifier  = null;
		$this->user->displayName = null;
		$this->user->profileURL  = null;
		$this->user->photoURL    = null;
	}
}

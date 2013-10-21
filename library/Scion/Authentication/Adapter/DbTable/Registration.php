<?php
namespace Scion\Authentication\Adapter\DbTable;

use Scion\Authentication\Adapter\DbTable;
use Scion\Crypt\Hash;
use Scion\Crypt\Key\Derivation\Pbkdf2;

class Registration {

	private $_dbh;
	private $_attempt;
	private $_user;

	public function __construct($dbh, Attempt $attempt, User $user, Log $log) {
		$this->_dbh     = $dbh;
		$this->_attempt = $attempt;
		$this->_user    = $user;
		$this->_log     = $log;
	}

	/**
	 * Creates a new user, adds them to database
	 * @param string $email
	 * @param string $username
	 * @param string $password (MUST be already twice hashed with SHA1 : Ideally client side with JS)
	 * @return array $return
	 */
	public function register($email, $username, $password) {
		$return = array();

		if ($this->_attempt->isBlocked()) {
			$return['code'] = 0;

			return $return;
		}

		if (strlen($email) == 0 || strlen($email) > 100 || strlen($email) < 3 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($username) == 0 || strlen($username) > 30 || strlen($username) < 3 || strlen($password) != 40) {
			$return['code'] = 1;
			$this->_attempt->add();
		}
		else {
			$password = Pbkdf2::create(Hash::ALGO_SHA512, base64_encode(str_rot13(hash(Hash::ALGO_SHA512, str_rot13(DbTable::SALT_1 . $password . DbTable::SALT_2)))), DbTable::SALT_3);

			if (!$this->_user->isEmailTaken($email)) {
				if (!$this->_user->isUsernameTaken($username)) {
					$uid = $this->_user->addUser($email, $username, $password);

					$this->_log->addNew($uid, "REGISTER_SUCCESS", "Account created successfully, activation email sent.");

					$return['code']  = 4;
					$return['email'] = $email;

				}
				else {
					$this->_attempt->add();

					$this->_log->addNew("", "REGISTER_FAIL_USERNAME", "User attempted to register new account with the username : {$username} -> Username already in use");

					$return['code'] = 3;
				}
			}
			else {
				$this->_attempt->add();

				$this->_log->addNew("", "REGISTER_FAIL_EMAIL", "User attempted to register new account with the email : {$email} -> Email already in use");

				$return['code'] = 2;
			}
		}

		return $return;
	}

}
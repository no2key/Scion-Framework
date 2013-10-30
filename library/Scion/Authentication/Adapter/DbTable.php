<?php
namespace Scion\Authentication\Adapter;

use Scion\Authentication\Adapter\DbTable\Activation;
use Scion\Authentication\Adapter\DbTable\Attempt;
use Scion\Authentication\Adapter\DbTable\Log;
use Scion\Authentication\Adapter\DbTable\Registration;
use Scion\Authentication\Adapter\DbTable\Session;
use Scion\Authentication\Adapter\DbTable\User;
use Scion\Crypt\Hash;
use Scion\Crypt\Key\Derivation\Pbkdf2;
use Scion\Db\Pdo;
use Scion\Db\Provider\AbstractProvider;
use Scion\Http\Client;
use Scion\Mvc\Singleton;

class DbTable implements AdapterInterface {
	use Singleton;

	const SITE_KEY = 'dk;l189654è(tyhj§!dfgdfàzgq_f4fá.';
	const SALT_1   = 'us_1dUDN4N-53/dkf7Sd?vbc_due1d?df!feg';
	const SALT_2   = 'Yu23ds09*d?u8SDv6sd?usi$_YSdsa24fd+83';
	const SALT_3   = '63fds.dfhsAdyISs_?&jdUsydbv92bf54ggvc';

	private $_dbh;
	private $_attempts;
	private $_log;
	private $_session;
	private $_user;
	private $_activation;
	private $_registration;

	protected function __construct($dbh) {
		if (!$dbh instanceof AbstractProvider) {
			throw new \Exception('$provider must be a instance of a valid provider (mysql, sqlite, ...)');
		}
		$this->_dbh          = $dbh;
		$this->_attempts     = new Attempt($dbh);
		$this->_log          = new Log($dbh);
		$this->_session      = new Session($dbh);
		$this->_activation   = new Activation($dbh);
		$this->_user         = new User($dbh, $this->_activation);
	}

	/**
	 * Return true is user is logged in, otherwise return false;
	 * @param $hash
	 * @return bool
	 */
	public function isLoggedIn($hash) {
		return $this->_checkSession($hash);
	}

	/**
	 * Login from a provider (e.g. google, facebook, twitter, ...)
	 * @param $provider
	 * @param $username
	 * @param $password
	 * @return array
	 */
	public function loginProvider($provider, $username, $password) {
		$return = [];

		if ($this->_attempts->isBlocked()) {
			$return['code'] = 0;

			return $return;
		}

		if (strlen($username) == 0 || strlen($username) > 30 || strlen($username) < 3) {
			$return['code'] = 1;
			$this->_attempts->add();

			return $return;
		}
		else {
			if ($userdata = $this->_user->getUserData($username)) {
				if ($password === $userdata['password']) {
					if ($userdata['isactive'] == 1) {

						$sessiondata = $this->_session->add($userdata['uid'], "+1 hour");

						$return['code']         = 4;
						$return['session_hash'] = $sessiondata['hash'];
						$return['expire']       = $sessiondata['expire'];

						$this->_log->addNew($userdata['uid'], "LOGIN_SUCCESS", "User logged in. Session hash : " . $sessiondata['hash']);
						$this->_log->addNew($userdata['uid'], "LOGIN_SUCCESS_FROM_PROVIDER", "Logged in from : " . $provider);

						return $return;
					}
					else {
						$this->_attempts->add();

						$this->_log->addNew($userdata['uid'], "LOGIN_FAIL_NONACTIVE", "Account inactive");

						$return['code'] = 3;

						return $return;
					}
				}
				else {
					$this->_attempts->add();

					$this->_log->addNew($userdata['uid'], "LOGIN_FAIL_PASSWORD", "Password incorrect");

					$return['code'] = 2;

					return $return;
				}
			}
			else {
				$this->_attempts->add();

				$this->_log->addNew(null, "LOGIN_FAIL_USERNAME", "Attempted login with the username : {$username} -> Username doesn't exist in DB");

				$return['code'] = 2;

				return $return;
			}
		}
	}

	/**
	 * Logs a user in
	 * @param string $username
	 * @param string $password (MUST be already twice hashed with SHA1 : Ideally client side with JS)
	 * @param bool   $rememberme
	 * @return array
	 */
	public function login($username, $password, $rememberme = false) {
		$return = [];

		if ($this->_attempts->isBlocked()) {
			$return['code'] = 0;

			return $return;
		}

		if (strlen($username) == 0 || strlen($username) > 30 || strlen($username) < 3 || strlen($password) == 0 || strlen($password) != 40) {
			$return['code'] = 1;
			$this->_attempts->add();

			return $return;
		}
		else {
			$plainpass = $password;
			$password  = Pbkdf2::create(Hash::ALGO_SHA512, base64_encode(str_rot13(hash(Hash::ALGO_SHA512, str_rot13(self::SALT_1 . $password . self::SALT_2)))), self::SALT_3);

			if ($userdata = $this->_user->getUserData($username)) {
				if ($password === $userdata['password']) {
					if ($userdata['isactive'] == 1) {

						if ($rememberme === true) {
							$sessiondata = $this->_session->add($userdata['uid'], "+1 month");
						}
						else if ($rememberme === false) {
							$sessiondata = $this->_session->add($userdata['uid'], "+1 hour");
						}
						else {
							$return['code'] = 1;
							$this->_attempts->add();

							return $return;
						}

						$return['code']         = 4;
						$return['session_hash'] = $sessiondata['hash'];
						$return['expire']       = $sessiondata['expire'];

						$this->_log->addNew($userdata['uid'], "LOGIN_SUCCESS", "User logged in. Session hash : " . $sessiondata['hash']);

						return $return;
					}
					else {
						$this->_attempts->add();

						$this->_log->addNew($userdata['uid'], "LOGIN_FAIL_NONACTIVE", "Account inactive");

						$return['code'] = 3;

						return $return;
					}
				}
				else {
					$this->_attempts->add();

					$this->_log->addNew($userdata['uid'], "LOGIN_FAIL_PASSWORD", "Password incorrect : {$plainpass}");

					$return['code'] = 2;

					return $return;
				}
			}
			else {
				$this->_attempts->add();

				$this->_log->addNew(null, "LOGIN_FAIL_USERNAME", "Attempted login with the username : {$username} -> Username doesn't exist in DB");

				$return['code'] = 2;

				return $return;
			}
		}
	}

	/**
	 * Logs out the session, identified by hash
	 * @param $hash
	 * @return bool
	 */
	public function logout($hash) {
		/*if (strlen($hash) != 40) {
			return false;
		}*/

		$return = $this->_session->deleteFromHash($hash);

		if ($return) {
			$this->_log->addNew($return['uid'], 'LOGOUT_SUCCESSFULLY', 'User logged out successfully');
			unset($_COOKIE['auth_session']);
			setcookie('auth_session', $hash, time() - 3600, '/', '', false, true);
			return true;
		}

		return false;
	}

	/**
	 * Get User object
	 * @return User
	 */
	public function getUser() {
		return $this->_user;
	}

	public function register($email, $username, $password) {
		$this->_registration = new Registration($this->_dbh, $this->_attempts, $this->_user, $this->_log);
		$this->_registration->register($email, $username, $password);
	}

	/**
	 * Function to check if a session is valid
	 * @param $hash
	 * @return bool
	 */
	private function _checkSession($hash) {
		if ($this->_attempts->isBlocked()) {
			return false;
		}

		if (strlen($hash) != 40) {
			setcookie('auth_session', $hash, time() - 3600, '/', '', false, true);

			return false;
		}

		$row = $this->_dbh->from('sessions')->select(null)->select('id, uid, expiredate, ip, agent, cookie_crc')->where('hash = ?', $hash)->execute()->fetch(Pdo::FETCH_ASSOC);
		if (!$row) {
			setcookie('auth_session', $hash, time() - 3600, '/', '', false, true);
			$this->_log->addNew($row['uid'], "CHECKSESSION_FAIL_NOEXIST", "Hash ({$hash}) doesn't exist in DB -> Cookie deleted");
		}
		else {
			$sid        = $row['id'];
			$uid        = $row['uid'];
			$expiredate = $row['expiredate'];
			$db_ip      = $row['ip'];
			$db_agent   = $row['agent'];
			$db_cookie  = $row['cookie_crc'];

			if ((new Client)->getIp() != $db_ip) {
				if ($_SERVER['HTTP_USER_AGENT'] != $db_agent) {
					$this->_session->deleteFromUid($uid);
					setcookie('auth_session', $hash, time() - 3600, '/', '', false, true);
					$this->_log->addNew($uid, "CHECKSESSION_FAIL_DIFF", "IP and User Agent Different ( DB : {$db_ip} / Current : " . (new Client())->getIp() . " ) -> UID sessions deleted, cookie deleted");
				}
				else {
					$expiredate  = strtotime($expiredate);
					$currentdate = strtotime(date("Y-m-d H:i:s"));

					if ($currentdate > $expiredate) {
						$this->_session->deleteFromUid($uid);
						setcookie('auth_session', $hash, time() - 3600, '/', '', false, true);
						$this->_log->addNew($uid, "CHECKSESSION_FAIL_EXPIRE", "Session expired ( Expire date : {$row['expiredate']} ) -> UID sessions deleted, cookie deleted");
					}
					else {
						return $this->_session->updateIp($sid);
					}
				}
			}
			else {
				$expiredate  = strtotime($expiredate);
				$currentdate = strtotime(date("Y-m-d H:i:s"));

				if ($currentdate > $expiredate) {
					$this->_session->deleteFromUid($uid);
					setcookie('auth_session', $hash, time() - 3600, '/', '', false, true);
					$this->_log->addNew($uid, "AUTH_CHECKSESSION_FAIL_EXPIRE", "Session expired ( Expire date : {$row['expiredate']} ) -> UID sessions deleted, cookie deleted");
				}
				else {
					$cookie_crc = sha1($hash . self::SITE_KEY);
					if ($db_cookie == $cookie_crc) {
						return true;
					}
					else {
						$this->_log->addNew($uid, "AUTH_COOKIE_FAIL_BADCRC", "Cookie Integrity failed");
					}
				}
			}
		}

		return false;
	}

}
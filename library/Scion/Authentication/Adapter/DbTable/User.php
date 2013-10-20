<?php
namespace Scion\Authentication\Adapter\DbTable;

use Scion\Db\Pdo;
use Scion\Math\Rand;
use Scion\Stdlib\DateTime;

class User {

	private $_dbh;
	private $_activation;

	public function __construct($dbh, Activation $activation) {
		$this->_dbh = $dbh;
		$this->_activation = $activation;
	}

	/**
	 * Gets user data for a given username and returns an array
	 * @param string $username
	 * @return array|bool
	 */
	public function getUserData($username) {
		$data = $this->_dbh->from('users')->select(null)->select('id, password, email, salt, lang, isactive, reg_date')->where('username = ?', $username)->execute()->fetch(Pdo::FETCH_ASSOC);

		if ($data) {
			$data['username'] = $username;
			$data['uid']      = $data['id'];

			return $data;
		}

		return false;
	}

	/**
	 * Returns username based on session hash
	 * @param string $hash
	 * @return string $username
	 */
	public function getUsername($hash) {
		$row = $this->_dbh->from('sessions')->select(null)->select('uid')->where('hash = ?', $hash)->execute()->fetch(Pdo::FETCH_ASSOC);

		if ($row) {
			$row = $this->_dbh->from('users')->select(null)->select('username')->where('id = ?', $row['uid'])->execute()->fetch(Pdo::FETCH_ASSOC);
			if ($row) {
				return $row['username'];
			}
		}
		return false;
	}

	/**
	 * Checks if an email is already in use
	 * @param string $email
	 * @return bool
	 */
	public function isEmailTaken($email) {
		$query = $this->_dbh->from('users')->where('email = ?', $email)->execute();

		if ($query->rowCount() == 0) {
			return false;
		}

		return true;
	}

	/**
	* Checks if a username is already in use
	* @param string $username
	* @return bool
	*/
	public function isUsernameTaken($username) {
		$query = $this->_dbh->from('users')->where('username = ?', $username)->execute();

		if ($query->rowCount() == 0) {
			return false;
		}

		return true;
	}

	/**
	* Adds a new user to database
	* @param string $email
	* @param string $username
	* @param string $password
	* @return int $uid
	*/
	public function addUser($email, $username, $password) {
		$username = htmlentities($username);
		$email    = htmlentities($email);

		$salt = Rand::getBytes(20);

		$lang = 'en';

		$this->_dbh->insertInto('users', ['username' => $username, 'password' => $password, 'email' => $email, 'salt' => $salt, 'lang' => $lang, 'reg_date' => (new DateTime())->now(DateTime::MYSQL_DATETIME)])->execute();
		$user = $this->getUserData($username);

		$this->_activation->add($user['id'], $email);

		return $user['id'];
	}

	/**
	* Changes a user's password
	* @param int $uid
	* @param string $currpass
	* @param string $newpass
	* @return array $return
	*/
	/*public function changePassword($uid, $currpass, $newpass) {
		$return = array();

		if ($this->isBlocked()) {
			$return['code'] = 0;

			return $return;
		}
		else {
			if (strlen($currpass) != 40) {
				$return['code'] = 1;
				$this->addAttempt();

				return $return;
			}
			elseif (strlen($newpass) != 40) {
				$return['code'] = 1;
				$this->addAttempt();

				return $return;
			}
			else {
				$currpass = $this->getHash($currpass);
				$newpass  = $this->getHash($newpass);

				$query = $this->dbh->prepare("SELECT password FROM " . $this->config->table_users . " WHERE id = ?");
				$query->execute(array($uid));
				$row = $query->fetch(\PDO::FETCH_ASSOC);

				if (!$row) {
					$this->addAttempt();

					$this->addNewLog($uid, "CHANGEPASS_FAIL_UID", "User attempted to change password for the UID : {$uid} -> UID doesn't exist !");

					$return['code'] = 2;

					return $return;
				}
				else {
					if ($currpass != $newpass) {
						if ($currpass == $row['password']) {
							$query = $this->dbh->prepare("UPDATE " . $this->config->table_users . " SET password = ? WHERE id = ?");
							$query->execute(array($newpass, $uid));

							$this->addNewLog($uid, "CHANGEPASS_SUCCESS", "User changed the password for the UID : {$uid}");

							$return['code'] = 5;

							return $return;
						}
						else {
							$this->addAttempt();

							$this->addNewLog($uid, "CHANGEPASS_FAIL_PASSWRONG", "User attempted to change password for the UID : {$uid} -> Current password incorrect !");

							$return['code'] = 4;

							return $return;
						}
					}
					else {
						$this->addAttempt();

						$this->addNewLog($uid, "CHANGEPASS_FAIL_PASSMATCH", "User attempted to change password for the UID : {$uid} -> New password matches current password !");

						$return['code'] = 3;

						return $return;
					}
				}
			}
		}
	}*/

	/**
	* Gets a user's email address by UID
	* @param int $uid
	* @return string $email
	*/
	/*public function getEmail($uid) {
		$query = $this->dbh->prepare("SELECT email FROM " . $this->config->table_users . " WHERE id = ?");
		$query->execute(array($uid));
		$row = $query->fetch(\PDO::FETCH_ASSOC);

		if ($row) {
			return $row['email'];
		}

		return false;
	}*/

	/**
	* Changes a user's email
	* @param int $uid
	* @param string $currpass
	* @param string $newpass
	* @return array $return
	*/
	/*public function changeEmail($uid, $email, $password) {
		$return = array();

		if ($this->isBlocked()) {
			$return['code'] = 0;

			return $return;
		}
		else {
			if (strlen($email) == 0) {
				$return['code'] = 1;
				$this->addAttempt();

				return $return;
			}
			elseif (strlen($email) > 100) {
				$return['code'] = 1;
				$this->addAttempt();

				return $return;
			}
			elseif (strlen($email) < 3) {
				$return['code'] = 1;
				$this->addAttempt();

				return $return;
			}
			elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$return['code'] = 1;
				$this->addAttempt();

				return $return;
			}
			elseif (strlen($password) != 40) {
				$return['code'] = 1;
				$this->addAttempt();

				return $return;
			}
			else {
				$password = $this->getHash($password);

				$query = $this->dbh->prepare("SELECT password, email FROM " . $this->config->table_users . " WHERE id = ?");
				$query->execute(array($uid));
				$row = $query->fetch(\PDO::FETCH_ASSOC);

				if (!$row) {
					$this->addAttempt();

					$this->addNewLog($uid, "CHANGEEMAIL_FAIL_UID", "User attempted to change email for the UID : {$uid} -> UID doesn't exist !");

					$return['code'] = 2;

					return $return;
				}
				else {
					if ($password == $row['password']) {
						if ($email == $row['email']) {
							$this->addAttempt();

							$this->addNewLog($uid, "CHANGEEMAIL_FAIL_EMAILMATCH", "User attempted to change email for the UID : {$uid} -> New Email address matches current email !");

							$return['code'] = 4;

							return $return;
						}
						else {
							$query = $this->dbh->prepare("UPDATE " . $this->config->table_users . " SET email = ? WHERE id = ?");
							$row   = $query->execute(array($email, $uid));

							if (!$row) {
								return false;
							}

							$this->addNewLog($uid, "CHANGEEMAIL_SUCCESS", "User changed email address for UID : {$uid}");

							$return['code'] = 5;

							return $return;
						}
					}
					else {
						$this->addAttempt();

						$this->addNewLog($uid, "CHANGEEMAIL_FAIL_PASS", "User attempted to change email for the UID : {$uid} -> Password is incorrect !");

						$return['code'] = 3;

						return $return;
					}
				}
			}
		}
	}*/
}
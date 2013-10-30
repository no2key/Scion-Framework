<?php
namespace Scion\Authentication\Adapter\DbTable;

use Scion\Authentication\Adapter\DbTable;
use Scion\Crypt\Hash;
use Scion\Crypt\Key\Derivation\Pbkdf2;
use Scion\Db\Pdo;
use Scion\Math\Rand;

class Reset {

	private $_dbh;
	private $_attempt;
	private $_log;

	public function __construct($dbh, Attempt $attempt) {
		$this->_dbh     = $dbh;
		$this->_attempt = $attempt;
		$this->_log     = new Log($dbh);
	}

	/**
	 * Creates a reset entry and sends email to user
	 * @param int    $uid
	 * @param string $email
	 * @return boolean
	 */
	private function addReset($uid, $email) {
		$resetkey = Rand::getBytes(20);

		$query = $this->_dbh->from('resets')->select(null)->select('expiredate')->where('uid = ?', $uid)->execute();
		$row   = $query->fetch(\PDO::FETCH_ASSOC);

		if (!$row) {
			$expiredate = date("Y-m-d H:i:s", strtotime("+1 day"));

			$return = $this->_dbh->insertInto('resets', ['uid'       => $uid,
														'resetkey'   => $resetkey,
														'expiredate' => $expiredate
														])->execute();

			/*if ($return) {
				$emailTemplate = new Localization\Handler(array('base_url' => $this->config->base_url,
																'key'      => $resetkey
														  ), $this->config->lang);
				$emailTemplate = $emailTemplate->getLocale();
				$emailTemplate = $emailTemplate->getResetEmail();

				@mail($email, $emailTemplate['subject'], $emailTemplate['body'], $emailTemplate['head']);
			}*/

			return $return;
		}
		else {
			$expiredate  = strtotime($row['expiredate']);
			$currentdate = strtotime(date("Y-m-d H:i:s"));

			if ($currentdate < $expiredate) {
				return false;
			}
			else {
				$this->deleteUserResets($uid);
			}
			$expiredate = date("Y-m-d H:i:s", strtotime("+1 day"));

			$return = $this->_dbh->insertInto('resets', ['uid'       => $uid,
														'resetkey'   => $resetkey,
														'expiredate' => $expiredate
														])->execute();

			/*if ($return) {
				$emailTemplate = new Localization\Handler(array('base_url' => $this->config->base_url,
																'key'      => $resetkey
														  ), $this->config->lang);
				$emailTemplate = $emailTemplate->getLocale();
				$emailTemplate = $emailTemplate->getResetEmail();

				@mail($email, $emailTemplate['subject'], $emailTemplate['body'], $emailTemplate['head']);
			}*/

			return $return;
		}
	}

	/**
	 * Deletes all reset entries for a user
	 * @param int $uid
	 * @return boolean
	 */
	private function deleteUserResets($uid) {
		return $this->_dbh->deleteFrom('resets')->where('uid = ?', $uid)->execute();
	}

	/**
	 * Checks if a reset key is valid
	 * @param string $key
	 * @return array $return
	 */
	public function isResetValid($key) {
		$return = array();

		if ($this->_attempt->isBlocked()) {
			$return['code'] = 0;

			return $return;
		}
		else {
			if (strlen($key) > 20) {
				$return['code'] = 1;
				$this->_attempt->add();

				return $return;
			}
			elseif (strlen($key) < 20) {
				$return['code'] = 1;
				$this->_attempt->add();

				return $return;
			}
			else {
				$query = $this->_dbh->from('resets')->select(null)->select('uid,expiredate')->where('resetkey = ?', $key)->execute();
				$row   = $query->fetch(Pdo::FETCH_ASSOC);

				if (!$row) {
					$this->_attempt->add();

					$return['code'] = 2;

					return $return;
				}
				else {
					$expiredate  = strtotime($row['expiredate']);
					$currentdate = strtotime(date("Y-m-d H:i:s"));

					if ($currentdate > $expiredate) {
						$this->_attempt->add();

						$this->deleteUserResets($row['uid']);

						$return['code'] = 3;

						return $return;
					}
					else {
						$return['code'] = 4;
						$return['uid']  = $row['uid'];

						return $return;
					}
				}
			}
		}
	}

	/**
	 * After verifying key validity, changes user's password
	 * @param string $key
	 * @param string $password (Must be already twice hashed with SHA1 : Ideally client side with JS)
	 * @return array $return
	 */
	public function resetPass($key, $password) {
		$return = array();

		if ($this->_attempt->isBlocked()) {
			$return['code'] = 0;

			return $return;
		}
		else {
			if (strlen($password) != 40) {
				$return['code'] = 1;
				$this->_attempt->add();

				return $return;
			}

			$data = $this->isResetValid($key);

			if ($data['code'] = 4) {
				$password = Pbkdf2::create(Hash::ALGO_SHA512, base64_encode(str_rot13(hash(Hash::ALGO_SHA512, str_rot13(DbTable::SALT_1 . $password . DbTable::SALT_2)))), DbTable::SALT_3);

				$query = $this->_dbh->from('users')->select(null)->select('password')->where('id = ?', $data['uid'])->execute();
				$row   = $query->fetch(Pdo::FETCH_ASSOC);

				if (!$row) {
					$this->_attempt->add();

					$this->deleteUserResets($data['uid']);

					$this->_log->addNew($data['uid'], "RESETPASS_FAIL_UID", "User attempted to reset password with key : {$key} -> User doesn't exist !");

					$return['code'] = 3;

					return $return;
				}
				else {
					if ($row['password'] == $password) {
						$this->_attempt->add();

						$this->_log->addNew($data['uid'], "RESETPASS_FAIL_SAMEPASS", "User attempted to reset password with key : {$key} -> New password matches previous password !");

						$this->deleteUserResets($data['uid']);

						$return['code'] = 4;

						return $return;
					}
					else {
						$return = $this->_dbh->update('users')->set(['password' => $password])->where('id', $data['uid'])->execute();

						if (!$return) {
							return false;
						}

						$this->_log->addNew($data['uid'], "RESETPASS_SUCCESS", "User attempted to reset password with key : {$key} -> Password changed, reset keys deleted !");

						$this->deleteUserResets($data['uid']);

						$return['code'] = 5;

						return $return;
					}
				}
			}
			else {
				$this->_log->addNew($data['uid'], "RESETPASS_FAIL_KEY", "User attempted to reset password with key : {$key} -> Key is invalid / incorrect / expired !");

				$return['code'] = 2;

				return $return;
			}
		}
	}

	/**
	 * Creates a reset key for an email address and sends email
	 * @param string $email
	 * @return array $return
	 */
	public function requestReset($email) {
		$return = array();

		if ($this->_attempt->isBlocked()) {
			$return['code'] = 0;

			return $return;
		}
		else {
			if (strlen($email) == 0) {
				$return['code'] = 1;
				$this->_attempt->add();

				return $return;
			}
			elseif (strlen($email) > 100) {
				$return['code'] = 1;
				$this->_attempt->add();

				return $return;
			}
			elseif (strlen($email) < 3) {
				$return['code'] = 1;
				$this->_attempt->add();

				return $return;
			}
			elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$return['code'] = 1;
				$this->_attempt->add();

				return $return;
			}
			else {
				$query = $this->_dbh->from('users')->select(null)->select('id')->where('email = ?', $email)->execute();
				$row = $query->fetch(Pdo::FETCH_ASSOC);

				if (!$row) {
					$this->_attempt->add();

					$this->_log->addNew("", "REQUESTRESET_FAIL_EMAIL", "User attempted to reset the password for the email : {$email} -> Email doesn't exist in DB");

					$return['code'] = 2;

					return $return;
				}
				else {
					if ($this->addReset($row['id'], $email)) {
						$this->_log->addNew($row['id'], "REQUESTRESET_SUCCESS", "A reset request was sent to the email : {$email}");

						$return['code']  = 4;
						$return['email'] = $email;

						return $return;
					}
					else {
						$this->_attempt->add();

						$this->_log->addNew($row['id'], "REQUESTRESET_FAIL_EXIST", "User attempted to reset the password for the email : {$email} -> A reset request already exists.");

						$return['code'] = 3;

						return $return;
					}
				}
			}
		}
	}
}
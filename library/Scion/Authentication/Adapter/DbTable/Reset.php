<?php
namespace Scion\Authentication\Adapter\DbTable;

class Reset {


	/**
	* Creates a reset entry and sends email to user
	* @param int $uid
	* @param string $email
	* @return boolean
	*/
	private function addReset($uid, $email) {
		$resetkey = $this->getRandomKey(20);

		$query = $this->dbh->prepare("SELECT expiredate FROM " . $this->config->table_resets . " WHERE uid = ?");
		$query->execute(array($uid));
		$row = $query->fetch(\PDO::FETCH_ASSOC);

		if (!$row) {
			$expiredate = date("Y-m-d H:i:s", strtotime("+1 day"));

			$query  = $this->dbh->prepare("INSERT INTO " . $this->config->table_resets . " (uid, resetkey, expiredate) VALUES (?, ?, ?)");
			$return = $query->execute(array($uid, $resetkey, $expiredate));

			if ($return) {
				$emailTemplate = new Localization\Handler(array('base_url' => $this->config->base_url,
																'key'      => $resetkey
														  ), $this->config->lang);
				$emailTemplate = $emailTemplate->getLocale();
				$emailTemplate = $emailTemplate->getResetEmail();

				@mail($email, $emailTemplate['subject'], $emailTemplate['body'], $emailTemplate['head']);
			}

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

			$query  = $this->dbh->prepare("INSERT INTO " . $this->config->table_resets . " (uid, resetkey, expiredate) VALUES (?, ?, ?)");
			$return = $query->execute(array($uid, $resetkey, $expiredate));

			if ($return) {
				$emailTemplate = new Localization\Handler(array('base_url' => $this->config->base_url,
																'key'      => $resetkey
														  ), $this->config->lang);
				$emailTemplate = $emailTemplate->getLocale();
				$emailTemplate = $emailTemplate->getResetEmail();

				@mail($email, $emailTemplate['subject'], $emailTemplate['body'], $emailTemplate['head']);
			}

			return $return;
		}
	}

	/**
	* Deletes all reset entries for a user
	* @param int $uid
	* @return boolean
	*/
	private function deleteUserResets($uid) {
		$query  = $this->dbh->prepare("DELETE FROM " . $this->config->table_resets . " WHERE uid = ?");
		$return = $query->execute(array($uid));

		return $return;
	}

	/**
	* Checks if a reset key is valid
	* @param string $key
	* @return array $return
	*/
	public function isResetValid($key) {
		$return = array();

		if ($this->isBlocked()) {
			$return['code'] = 0;

			return $return;
		}
		else {
			if (strlen($key) > 20) {
				$return['code'] = 1;
				$this->addAttempt();

				return $return;
			}
			elseif (strlen($key) < 20) {
				$return['code'] = 1;
				$this->addAttempt();

				return $return;
			}
			else {
				$query = $this->dbh->prepare("SELECT uid, expiredate FROM " . $this->config->table_resets . " WHERE resetkey = ?");
				$query->execute(array($key));
				$row = $query->fetch(\PDO::FETCH_ASSOC);

				if (!$row) {
					$this->addAttempt();

					$return['code'] = 2;

					return $return;
				}
				else {
					$expiredate  = strtotime($row['expiredate']);
					$currentdate = strtotime(date("Y-m-d H:i:s"));

					if ($currentdate > $expiredate) {
						$this->addAttempt();

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

		if ($this->isBlocked()) {
			$return['code'] = 0;

			return $return;
		}
		else {
			if (strlen($password) != 40) {
				$return['code'] = 1;
				$this->addAttempt();

				return $return;
			}

			$data = $this->isResetValid($key);

			if ($data['code'] = 4) {
				$password = $this->getHash($password);

				$query = $this->dbh->prepare("SELECT password FROM " . $this->config->table_users . " WHERE id = ?");
				$query->execute(array($data['uid']));
				$row = $query->fetch(\PDO::FETCH_ASSOC);

				if (!$row) {
					$this->addAttempt();

					$this->deleteUserResets($data['uid']);

					$this->addNewLog($data['uid'], "RESETPASS_FAIL_UID", "User attempted to reset password with key : {$key} -> User doesn't exist !");

					$return['code'] = 3;

					return $return;
				}
				else {
					if ($row['password'] == $password) {
						$this->addAttempt();

						$this->addNewLog($data['uid'], "RESETPASS_FAIL_SAMEPASS", "User attempted to reset password with key : {$key} -> New password matches previous password !");

						$this->deleteUserResets($data['uid']);

						$return['code'] = 4;

						return $return;
					}
					else {
						$query  = $this->dbh->prepare("UPDATE " . $this->config->table_users . " SET password = ? WHERE id = ?");
						$return = $query->execute(array($password, $data['uid']));

						if (!$return) {
							return false;
						}

						$this->addNewLog($data['uid'], "RESETPASS_SUCCESS", "User attempted to reset password with key : {$key} -> Password changed, reset keys deleted !");

						$this->deleteUserResets($data['uid']);

						$return['code'] = 5;

						return $return;
					}
				}
			}
			else {
				$this->addNewLog($data['uid'], "RESETPASS_FAIL_KEY", "User attempted to reset password with key : {$key} -> Key is invalid / incorrect / expired !");

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
			else {
				$query = $this->dbh->prepare("SELECT id FROM " . $this->config->table_users . " WHERE email = ?");
				$query->execute(array($email));
				$row = $query->fetch(\PDO::FETCH_ASSOC);

				if (!$row) {
					$this->addAttempt();

					$this->addNewLog("", "REQUESTRESET_FAIL_EMAIL", "User attempted to reset the password for the email : {$email} -> Email doesn't exist in DB");

					$return['code'] = 2;

					return $return;
				}
				else {
					if ($this->addReset($row['id'], $email)) {
						$this->addNewLog($row['id'], "REQUESTRESET_SUCCESS", "A reset request was sent to the email : {$email}");

						$return['code']  = 4;
						$return['email'] = $email;

						return $return;
					}
					else {
						$this->addAttempt();

						$this->addNewLog($row['id'], "REQUESTRESET_FAIL_EXIST", "User attempted to reset the password for the email : {$email} -> A reset request already exists.");

						$return['code'] = 3;

						return $return;
					}
				}
			}
		}
	}
}
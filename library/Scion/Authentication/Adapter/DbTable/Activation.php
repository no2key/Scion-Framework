<?php
namespace Scion\Authentication\Adapter\DbTable;

use Scion\Db\Pdo;
use Scion\Math\Rand;

class Activation {

	private $_dbh;

	public function __construct($dbh) {
		$this->_dbh = $dbh;
	}

	/**
	* Activates a user's account
	* @param string $activekey
	* @return array $return
	*/
	/*public function activate($activekey) {
		$return = array();

		if ($this->isBlocked()) {
			$return['code'] = 0;

			return $return;
		}
		else {
			if (strlen($activekey) > 20) {
				$return['code'] = 1;
				$this->addAttempt();

				return $return;
			}
			elseif (strlen($activekey) < 20) {
				$return['code'] = 1;
				$this->addAttempt();

				return $return;
			}
			else {
				$query = $this->dbh->prepare("SELECT uid, expiredate FROM " . $this->config->table_activations . " WHERE activekey = ?");
				$query->execute(array($activekey));
				$row = $query->fetch(\PDO::FETCH_ASSOC);

				if (!$row) {
					$this->addAttempt();

					$this->addNewLog("", "ACTIVATE_FAIL_ACTIVEKEY", "User attempted to activate an account with the key : {$activekey} -> Activekey not found in database");

					$return['code'] = 2;

					return $return;
				}
				else {
					if (!$this->isUserActivated($row['uid'])) {
						$expiredate  = strtotime($row['expiredate']);
						$currentdate = strtotime(date("Y-m-d H:i:s"));

						if ($currentdate < $expiredate) {
							$isactive = 1;

							$query = $this->dbh->prepare("UPDATE " . $this->config->table_users . " SET isactive = ? WHERE id = ?");
							$query->execute(array($isactive, $row['uid']));

							$this->deleteUserActivations($row['uid']);

							$this->addNewLog($row['uid'], "ACTIVATE_SUCCESS", "Account activated -> Isactive : 1");

							$return['code'] = 5;

							return $return;
						}
						else {
							$this->addAttempt();

							$this->addNewLog($row['uid'], "ACTIVATE_FAIL_EXPIRED", "User attempted to activate account with key : {$activekey} -> Key expired");

							$this->deleteUserActivations($row['uid']);

							$return['code'] = 4;

							return $return;
						}
					}
					else {
						$this->addAttempt();

						$this->deleteUserActivations($row['uid']);

						$this->addNewLog($row['uid'], "ACTIVATE_FAIL_ALREADYACTIVE", "User attempted to activate an account with the key : {$activekey} -> Account already active. Set activekey : 0");

						$return['code'] = 3;

						return $return;
					}
				}
			}
		}
	}*/

	/**
	* Creates an activation entry and sends email to user
	* @param int $uid
	* @param string $email
	* @return boolean
	*/
	public function add($uid, $email) {
		$activekey = Rand::getBytes(20);

		if ($this->_isUserActivated($uid)) {
			return false;
		}
		else {
			$row        = $this->_dbh->from('activations')->select(null)->select('expiredate')->where('uid', $uid)->execute()->fetch(Pdo::FETCH_ASSOC);
			$expiredate = $row['expiredate'];

			if (count($expiredate) > 0) {
				$expiredate  = strtotime($expiredate);
				$currentdate = strtotime(date("Y-m-d H:i:s"));

				if ($currentdate < $expiredate) {
					return false;
				}
				else {
					$this->_deleteUserActivations($uid);
				}
			}

			$expiredate = date("Y-m-d H:i:s", strtotime("+1 day"));

			$return = $this->_dbh->insertInto('activations', ['uid' => $uid, 'activekey' => $activekey, 'expiredate' => $expiredate])->execute();

			if ($return) {
				//Initialize Handler which loads language
				/*$emailTemplate = new Localization\Handler(array('base_url' => $this->config->base_url,
																'key'      => $activekey
														  ), $this->config->lang);
				//Get the language template
				$emailTemplate = $emailTemplate->getLocale();
				//Get array with body, head, and subject
				$emailTemplate = $emailTemplate->getActivationEmail();

				@mail($email, $emailTemplate['subject'], $emailTemplate['body'], $emailTemplate['head']);*/
			}

			return $return;
		}
	}

	/**
	* Deletes all activation entries for a user
	* @param int $uid
	* @return boolean
	*/
	private function _deleteUserActivations($uid) {
		return $this->_dbh->deleteFrom('activations')->where('uid', $uid)->execute();
	}

	/**
	* Checks if a user account is activated based on uid
	* @param int $uid
	* @return boolean
	*/
	private function _isUserActivated($uid) {
		$row = $this->_dbh->from('users')->select(null)->select('isactive')->where('id', $uid)->execute()->fetch(Pdo::FETCH_ASSOC);

		if (!$row || $row['isactive'] == 0) {
			return false;
		}

		return true;
	}

	/**
	* Recreates activation email for a given email and sends
	* @param string $email
	* @return array $return
	*/
	/*public function resendActivation($email) {
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

					$this->addNewLog("", "RESENDACTIVATION_FAIL_EMAIL", "User attempted to resend activation email for the email : {$email} -> Email doesn't exist in DB !");

					$return['code'] = 2;

					return $return;
				}
				else {
					if ($this->isUserActivated($row['uid'])) {
						$this->addAttempt();

						$this->addNewLog($row['uid'], "RESENDACTIVATION_FAIL_ACTIVATED", "User attempted to resend activation email for the email : {$email} -> Account is already activated !");

						$return['code'] = 3;

						return $return;
					}
					else {
						if ($this->addActivation($row['uid'], $email)) {
							$this->addNewLog($row['uid'], "RESENDACTIVATION_SUCCESS", "Activation email was resent to the email : {$email}");

							$return['code'] = 5;

							return $return;
						}
						else {
							$this->addAttempt();

							$this->addNewLog($row['uid'], "RESENDACTIVATION_FAIL_EXIST", "User attempted to resend activation email for the email : {$email} -> Activation request already exists. 24 hour expire wait required !");

							$return['code'] = 4;

							return $return;
						}
					}
				}
			}
		}
	}*/

}
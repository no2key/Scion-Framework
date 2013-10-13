<?php
namespace Scion\Authentication\Adapter\DbTable;

use Scion\Db\Pdo;
use Scion\Http\Client;

class Attempt {

	private $_dbh;

	public function __construct($dbh) {
		$this->_dbh = $dbh;
	}

	/**
	 * Informs if a user is locked out
	 * @return boolean
	 */
	public function isBlocked() {
		$row = $this->_dbh->from('attempts')->select(null)->select('count, expiredate')->where('ip = ?', (new Client())->getIp())->execute()->fetch(Pdo::FETCH_ASSOC);

		if ($row) {
			$expireDate  = strtotime($row['expiredate']);
			$currentDate = strtotime(date("Y-m-d H:i:s"));

			if ($row['count'] == 5) {
				if ($currentDate < $expireDate) {
					return true;
				}
				$this->_deleteAttempts();
			}
			else {
				if ($currentDate > $expireDate) {
					$this->_deleteAttempts();
				}
			}
		}

		return false;
	}

	/**
	 * Adds an attempt to database for given IP
	 * @return bool
	 */
	public function add() {
		$row = $this->_dbh->from('attempts')->select(null)->select('count')->where('ip = ?', (new Client)->getIp())->execute()->fetch(Pdo::FETCH_ASSOC);

		$attempt_expiredate = date("Y-m-d H:i:s", strtotime("+30 minutes"));

		if (!$row) {
			$attempt_count      = 1;
			$return = $this->_dbh->insertInto('attempts', ['ip' => (new Client())->getIp(), 'count' => $attempt_count, 'expiredate' => $attempt_expiredate])->execute();
		}
		else {
			$attempt_count      = $row['count'] + 1;
			$return = $this->_dbh->update('attempts')->set('count', $attempt_count)->set('expiredate', $attempt_expiredate)->where('ip', (new Client)->getIp())->execute();
		}
		return $return;
	}

	/**
	 * Deletes all attempts for a given IP from database
	 * @return boolean
	 */
	private function _deleteAttempts() {
		return $this->_dbh->deleteFrom('attempts')->where('ip', (new Client())->getIp())->execute();
	}

}
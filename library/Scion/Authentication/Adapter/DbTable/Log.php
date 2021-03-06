<?php
namespace Scion\Authentication\Adapter\DbTable;

use Scion\Http\RemoteAddress;

class Log {

	private $_dbh;

	public function __construct($dbh) {
		$this->_dbh = $dbh;
	}

	/**
	* Function to add data to log table
	* @param string $uid
	* @param string $action
	* @param string $info
	* @return bool
	*/
	public function addNew($uid = null, $action, $info) {
		if (strlen($action) == 0) {
			return false;
		}
		else if (strlen($action) > 100) {
			return false;
		}
		else if (strlen($info) == 0) {
			return false;
		}
		else if (strlen($info) > 1000) {
			return false;
		}
		else {
			return $this->_dbh->insertInto('log', ['uid' => $uid, 'action' => $action, 'info' => $info, 'ip' => (new RemoteAddress())->getIpAddress()])->execute();
		}
	}
}
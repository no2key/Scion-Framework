<?php
namespace Scion\Authentication\Adapter\DbTable;

use Scion\Http\Client;

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
	public function addNew($uid = 'UNKNOWN', $action, $info) {
		if (strlen($action) == 0 || strlen($action) > 100 || strlen($info) == 0 || strlen($info) > 1000) {
			return false;
		}
		else {
			return $this->_dbh->insertInto('log', ['username' => $uid, 'action' => $action, 'info' => $info, 'ip' => (new Client())->getIp()]);
		}
	}
}
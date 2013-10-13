<?php
namespace Scion\Authentication;

use Scion\Authentication\Adapter\DbTable;
use Scion\Mvc\Singleton;

class AuthenticationService {
	use Singleton;

	protected function __construct() {

	}

	public function getDbTable($dbh) {
		return DbTable::getinstance($dbh);
	}


}
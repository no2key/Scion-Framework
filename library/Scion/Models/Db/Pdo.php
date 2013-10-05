<?php
namespace Scion\Models\Db;

class Pdo extends \PDO {

	/**
	 * PDO class constructor
	 * @param       $dsn
	 * @param null  $user
	 * @param null  $password
	 * @param array $options
	 */
	public function __construct($dsn, $user = null, $password = null, array $options = null) {
		parent::__construct($dsn, $user, $password, $options);
		$this->setAttribute(parent::ATTR_ERRMODE, parent::ERRMODE_EXCEPTION);
		// Configure PDO to really prepare statements and to not emulate them
		$this->setAttribute(parent::ATTR_EMULATE_PREPARES, false); //Prevent SQL Injections
	}
}
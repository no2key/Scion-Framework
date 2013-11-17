<?php
namespace Scion\Mvc;

use Scion\Authentication\Auth;
use Scion\Authentication\AuthenticationService;
use Scion\Db\Database;

trait Model {

	/**
	 * Get SQL provider (mysql, sqlite, ...) object
	 * @param string $instance
	 * @return \Scion\Db\Sql
	 */
	final public function getSql($instance = 'default') {
		return Database::initSql($instance);
	}

	/**
	 * Get NoSQL object
	 * @param string $instance
	 * @return \Scion\Db\NoSql
	 */
	final public function getNoSql($instance = 'default') {
		return Database::initNoSql($instance);
	}

	/**
	 * Get an AuthenticationService object
	 * @return \Scion\Authentication\AuthenticationService
	 */
	final public function getAuth() {
		return AuthenticationService::getInstance();
	}
}
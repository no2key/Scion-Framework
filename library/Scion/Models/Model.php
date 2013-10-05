<?php
namespace Scion\Models;

use Scion\Models\Db\Database;

trait Model {

	/**
	 * Get Database object
	 * @param string $instance
	 * @return \Scion\Models\Db\Sql
	 */
	final public function getSql($instance = 'default') {
		return Database::initSql($instance);
	}

	final public function getNoSql($instance = 'default') {
		return Database::initNoSql($instance);
	}
}
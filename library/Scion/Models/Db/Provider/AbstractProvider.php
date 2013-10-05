<?php
namespace Scion\Models\Db\Provider;

use Scion\Models\Db\Configuration;
use Scion\Models\Db\Pdo;
use Scion\Models\Db\Query\Delete;
use Scion\Models\Db\Query\Insert;
use Scion\Models\Db\Query\Select;
use Scion\Models\Db\Query\Structure;
use Scion\Models\Db\Query\Update;

abstract class AbstractProvider {

	public $debug;

	protected $_parameters;
	protected $_pdo;
	private $_instanceConfiguration;

	/**
	 * Constructor
	 * @param Configuration $configuration
	 */
	public function __construct(Configuration $configuration) {
		$this->_instanceConfiguration = $configuration;
		$this->_parameters = $configuration->getParameters();
		$this->_processPdo();
		$this->structure = new Structure();
	}

	/**
	 * Build driver DSN
	 * @return mixed
	 */
	abstract public function getDsn();


	/**
	 * Get a configuration container object
	 * @return null|Configuration
	 */
	public function getConfiguration() {
		return $this->_instanceConfiguration;
	}

	/**
	 * Create SELECT query from $table
	 * @param string $table  db table name
	 * @param integer $id  return one row by primary key
	 * @return $this
	 */
	public function from($table, $id = null) {
		$query = new Select($this, $table);
		if ($id) {
			$tableTable = $query->getFromTable();
			$tableAlias = $query->getFromAlias();
			$primary = $this->structure->getPrimaryKey($tableTable);
			$query = $query->where("$tableAlias.$primary = ?", $id);
		}
		return $query;
	}

	/**
	 * Create INSERT INTO query
	 * @param string $table
	 * @param array $values  you can add one or multi rows array @see docs
	 * @return Insert
	 */
	public function insertInto($table, $values = array()) {
		$query = new Insert($this, $table, $values);
		return $query;
	}

	/**
	 * Create UPDATE query
	 * @param string $table
	 * @param array|string $set
	 * @param string $where
	 * @param string $whereParams one or more params for where
	 * @return Update
	 */
	public function update($table, $set = array(), $where = '', $whereParams = '') {
		$query = new Update($this, $table, $set, $where);
		$query->set($set);
		$args = func_get_args();
		if (count($args) > 2) {
			array_shift($args);
			array_shift($args);
			if (is_null($args)) {
				$args = array();
			}
			$query = call_user_func_array(array($query, 'where'), $args);
		}
		return $query;
	}

	/**
	 * Create DELETE query
	 * @param string $tables
	 * @param string $where
	 * @param string $whereParams one or more params for where
	 * @return Delete
	 */
	public function delete($tables, $where = '', $whereParams = '') {
		$query = new Delete($this, $tables);
		$args = func_get_args();
		if (count($args) > 1) {
			array_shift($args);
			if (is_null($args)) {
				$args = array();
			}
			$query = call_user_func_array(array($query, 'where'), $args);
		}
		return $query;
	}

	/**
	 * Create DELETE FROM query
	 * @param string $table
	 * @param string $where
	 * @param string $whereParams one or more params for where
	 * @return mixed
	 */
	public function deleteFrom($table, $where = '', $whereParams = '') {
		$args = func_get_args();
		return call_user_func_array(array($this, 'delete'), $args);

	}

	/**
	 * Get a Pdo object
	 * @return Pdo
	 */
	public function getPdo() {
		return $this->_pdo;
	}

	/**
	 * Get a Structure object
	 * @return Structure
	 */
	public function getStructure() {
		return $this->structure;
	}

	/**
	 * Check each values passed to Pdo constructor
	 * @throws Exception
	 */
	private function _processPdo() {
		$dsn = $this->getDsn();
		if ($dsn == '' || !is_string($dsn)) {
			throw new Exception('DSN error! DSN must be a string and not empty!');
		}

		$username = null;
		if (property_exists($this->_parameters, 'username')) {
			$username = $this->_parameters->username;
		}

		$password = null;
		if (property_exists($this->_parameters, 'password')) {
			$password = $this->_parameters->password;
		}

		$arrayOptions = null;
		if (property_exists($this->_parameters, 'options')) {
			$options = $this->_parameters->options;
			foreach ($options as $key => $value) {
				$arrayOptions[constant('Pdo::'.$key)] = $value;
			}
		}

		$this->_pdo = new Pdo($dsn, $username, $password, $arrayOptions);
	}
}
<?php
namespace Scion\Models\Db;

class Configuration {

	private $_parameters = null;
	private $_driverMap = [
		'4d'       => '_4D',
		'cubrid'   => 'Cubrid',
		'dblib'    => 'DBlib',
		'firebird' => 'Firebird',
		'ibm'      => 'IBMDB2',
		'informix' => 'Informix',
		'mariadb'  => 'MySql',
		'mssql'    => 'MsSql',
		'mysql'    => 'MySql',
		'oci'      => 'Oracle',
		'odbc'     => 'Odbc',
		'pgsql'    => 'PgSql',
		'sqlite'   => 'Sqlite',
		'sqlsrv'   => 'SqlSrv',
		'sybase'   => 'Sybase'
	];

	/**
	 * Construct a new configuration container
	 * @param \stdClass $parameters
	 */
	public function __construct(\stdClass $parameters) {
		$this->_parameters = $parameters;
	}

	/**
	 * Return the name of the driver class
	 * @return mixed
	 */
	public function getDriverClass() {
		return $this->_driverMap[$this->_parameters->dsn->driver];
	}

	/**
	 * Check property exists in \stdClass
	 * @param $key
	 * @return bool
	 */
	public function exists($key) {
		return property_exists($this->_parameters, $key);
	}

	/**
	 * Get a value from the container
	 * @param $name
	 * @return mixed
	 */
	public function getParameter($name) {
		return $this->_parameters->$name;
	}

	/**
	 * Store a value to the container
	 * @param $name
	 * @param $value
	 */
	public function setParameter($name, $value) {
		$this->_parameters->$name = $value;
	}

	/**
	 * Get all values from the container
	 * @return null|\stdClass
	 */
	public function getParameters() {
		return $this->_parameters;
	}
}
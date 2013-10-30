<?php
namespace Scion\Mvc;

trait Singleton {

	protected static $instance = null;

	/**
	 * private construct, generally defined by using class
	 *
	 * Don not create a private constructor or the autoloader
	 * can't instanciate the class and return an error
	 */
	//private function __construct() {}

	/**
	 * Used to retrieve singleton instances.
	 * if the singleton instance already exists returns it,
	 * otherwise creates an instance and returns it
	 * @access public
	 * @static
	 */
	public static function getInstance() {
		if (self::$instance === null) {
			if (func_num_args() > 0) {
				$reflectionClass = new \ReflectionClass(__CLASS__);
				$reflectionMethod = new \ReflectionMethod(__CLASS__, '__construct');
				$reflectionMethod->setAccessible(true);
				self::$instance = $reflectionClass->newInstanceWithoutConstructor();
				$reflectionMethod->invokeArgs(self::$instance, func_get_args());
			}
			else {
				self::$instance = new self();
			}
		}

		return self::$instance;
	}

	/**
	 * Prevent cloning of the object: issues an E_USER_ERROR if this is attempted
	 * @access public
	 */
	public function __clone() {
		trigger_error('Cloning ' . __CLASS__ . ' is not allowed.', E_USER_ERROR);
	}

	/**
	 * Prevent unserializing object: issues an E_USER_ERROR if this is attempted
	 * @access public
	 */
	public function __sleep() {
		strigger_error('Unerializing ' . __CLASS__ . ' is not allowed.', E_USER_ERROR);
	}

	/**
	 * Prevent unserializing object: issues an E_USER_ERROR if this is attempted
	 * @access public
	 */
	public function __wakeup() {
		trigger_error('Unserializing ' . __CLASS__ . ' is not allowed.', E_USER_ERROR);
	}
}
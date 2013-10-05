<?php
namespace Scion\Models;

trait Singleton {
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
		static $_instance = null;
		$class = __CLASS__;

		return $_instance ? : $_instance = new $class();
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
		trigger_error('Unserializing ' . __CLASS__ . ' is not allowed.', E_USER_ERROR);
	}

	/**
	 * Prevent unserializing object: issues an E_USER_ERROR if this is attempted
	 * @access public
	 */
	public function __wakeup() {
		trigger_error('Unserializing ' . __CLASS__ . ' is not allowed.', E_USER_ERROR);
	}
}
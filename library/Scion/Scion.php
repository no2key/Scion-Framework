<?php
/**
 * Scion - a PHP5.5+ framework
 *
 * @author David Sanchez <david38.sanchez@gmail.com>
 * @Copyright 2013 David Sanchez
 * @version 1.0
 * @package Scion
 */
namespace Scion;

use Scion\Models\Loader\Autoloader;

define('SCION_DIR', __DIR__ . DIRECTORY_SEPARATOR);

class Scion {

	/**
	 * @const float Version number
	 */
	const VERSION = 1.0;

	/**
	 * @const string Minimum PHP Version supported by Scion
	 */
	const MINIMUM_PHP_VERSION = '5.5';

	/**
	 * Constructor
	 */
	public function __construct() {
		// Check PHP version greater than 5.5
		if ($this->_checkPhpVersion() === false) {
			die('You need PHP 5.5 minimum.<br>Current version: ' . PHP_VERSION);
		}
	}

	/**
	 * Register Scion's PSR-0 autoloader
	 * @param string|array $registerAutoload
	 */
	public static function registerAutoloader($registerAutoload = '') {
		require __DIR__ . '/Models/Loader/Autoloader.php';

		$autoload = new Autoloader('library/Scion/Config/scionAutoload.json');
		if (!empty($registerAutoload)) {
			$autoload->registerFromJson($registerAutoload);
		}
		$autoload->register();
	}

	/**
	 * Test version of PHP is greater than 5.5
	 * @return bool
	 */
	private function _checkPhpVersion() {
		return version_compare(PHP_VERSION, self::MINIMUM_PHP_VERSION) >= 0;
	}
}
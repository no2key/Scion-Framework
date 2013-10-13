<?php
/**
 * Scion - a PHP5.5+ framework
 *
 * @author    David Sanchez <david38.sanchez@gmail.com>
 * @Copyright 2013 David Sanchez
 * @version   1.0
 * @package   Scion
 */
namespace Scion;

use Scion\Routing\Route;
use Scion\Db\Database;
use Scion\File\Json;
use Scion\Loader\Autoloader;
use Scion\Loader\RouteLoader;
use Scion\Views\TemplateEngine;

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

	private static $_jsonConfiguration;

	/**
	 * Used to call private static methods below
	 * @param string $name
	 * @param mixed $arguments
	 */
	public static function __callStatic($name, $arguments) {
		// Check PHP version greater than 5.5
		if (self::_checkPhpVersion() === false) {
			die('You need PHP ' . self::MINIMUM_PHP_VERSION . ' minimum.<br>Current version: ' . PHP_VERSION);
		}

		if (empty($arguments)) {
			self::$name();
		}
		else {
			$reflectionMethod = new \ReflectionMethod(__CLASS__, $name);
			$reflectionMethod->setAccessible(true);
			$reflectionMethod->invokeArgs(null, $arguments);
		}
	}

	/**
	 * Set json configuration
	 * @param $jsonUrl
	 */
	public static function setConfiguration($jsonUrl) {
		self::$_jsonConfiguration = json_decode(file_get_contents($jsonUrl));
	}

	/**
	 * Get json configuration
	 * @return mixed
	 */
	public static function getJsonConfiguration() {
		return self::$_jsonConfiguration;
	}

	/**
	 * Initialize autoloader system
	 * @param null|string $jsonUrl
	 */
	protected static function initAutoloader($jsonUrl = null) {
		require __DIR__ . DIRECTORY_SEPARATOR . 'Loader/AutoLoader.php';
		$autoload = new AutoLoader(__DIR__ . DIRECTORY_SEPARATOR . 'Resources/autoload.json');

		// Get autoload json file from configuration.json
		if (isset(self::$_jsonConfiguration->configuration->framework->autoloader)) {

			$autoload->registerFromJson(self::$_jsonConfiguration->configuration->framework->autoloader);
		}

		// Get json configuration passed in parameter
		if ($jsonUrl !== null) {
			$autoloaderConfiguration = json_decode(file_get_contents($jsonUrl));
			if (isset($autoloaderConfiguration->configuration->framework->autoloader)) {
				$autoload->registerFromJson($autoloaderConfiguration->configuration->framework->autoloader);
			}
		}
		$autoload->register();
	}

	/**
	 * Initialize routing system
	 * @param null|string $jsonUrl
	 */
	protected static function initRouter($jsonUrl = null) {
		if (isset(self::$_jsonConfiguration->configuration->framework->router)) {
			RouteLoader::registerRoutes(self::$_jsonConfiguration->configuration->framework->router);
		}

		if ($jsonUrl !== null) {
			RouteLoader::registerRoutes($jsonUrl);
		}

		RouteLoader::processRoutes();
	}

	/**
	 * Test version of PHP is greater than 5.5
	 *
	 * @return bool
	 */
	private static function _checkPhpVersion() {
		return version_compare(PHP_VERSION, self::MINIMUM_PHP_VERSION) >= 0;
	}
}
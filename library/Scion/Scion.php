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

	private $_jsonConfiguration;

	/**
	 * Constructor
	 */
	public function __construct($jsonUrl) {
		// Check PHP version greater than 5.5
		if ($this->_checkPhpVersion() === false) {
			die('You need PHP 5.5 minimum.<br>Current version: ' . PHP_VERSION);
		}

		// Load content from json file
		$this->_jsonConfiguration = json_decode(file_get_contents($jsonUrl));

		// Init Autoloader
		$this->_initAutoloader();

		// Init Database
		$this->_initDatabase();

		// Init TemplateEngine
		$this->_initTemplateEngine();

		// Init Router always at the end
		$this->_initRouter();
	}

	/**
	 * Test version of PHP is greater than 5.5
	 *
	 * @return bool
	 */
	private function _checkPhpVersion() {
		return version_compare(PHP_VERSION, self::MINIMUM_PHP_VERSION) >= 0;
	}

	/**
	 * Initialize autoloader system
	 */
	private function _initAutoloader() {
		require __DIR__ . '/Loader/AutoLoader.php';
		$autoload = new AutoLoader('library/Scion/Resources/autoload.json');
		if (isset($this->_jsonConfiguration->configuration->framework->autoloader)) {
			$autoload->registerFromJson($this->_jsonConfiguration->configuration->framework->autoloader);
		}
		$autoload->register();
	}

	/**
	 * Initialize routing system
	 */
	private function _initRouter() {
		if (isset($this->_jsonConfiguration->configuration->framework->router)) {
			RouteLoader::registerRoutes($this->_jsonConfiguration->configuration->framework->router);
		}
	}

	/**
	 * Initialize database system
	 */
	private function _initDatabase() {
		if (isset($this->_jsonConfiguration->configuration->framework->database)) {
			Database::init($this->_jsonConfiguration->configuration->framework->database);
		}
	}

	/**
	 * Initialize template engine
	 */
	private function _initTemplateEngine() {
		if (isset($this->_jsonConfiguration->configuration->framework->template)) {
			TemplateEngine::getInstance();
			TemplateEngine::init($this->_jsonConfiguration->configuration->framework->template);
		}
	}
}
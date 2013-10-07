<?php
namespace Scion\Views;

use Dwoo\Core;
use Dwoo\ITemplate;
use Dwoo\Template\File;
use Scion\File\Json;

class TemplateEngine extends Core {

	/**
	 * Stores a Core instance
	 * @var
	 */
	protected static $instance;

	/**
	 * Singleton accessor, use it to access this object if you need to call specific Dwoo functions
	 * @return TemplateEngine
	 */
	public static function getInstance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initialize default values to dwoo
	 * @param $configFile
	 */
	public static function init($configFile) {
		$template = Json::decode(file_get_contents($configFile))->dwoo;

		if (property_exists($template, 'cache')) {
			self::$instance->setCacheDir($template->cache);
		}

		if (property_exists($template, 'compiled')) {
			self::$instance->setCompileDir($template->compiled);
		}

		if (property_exists($template, 'view')) {
			self::$instance->setTemplateDir($template->view);
		}

		// Register custom plugins directory
		//self::$instance->getLoader()->addDirectory(SCION_DIR . 'Views' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR);
	}
}
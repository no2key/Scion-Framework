<?php
namespace Scion\Controllers;

use Scion\Models\Loader\RouteLoader;
use Scion\Views\TemplateEngine;

trait Controller {

	/**
	 * Get class name using controller trait
	 * @return string
	 */
	final public function getClassName() {
		return __CLASS__;
	}

	/**
	 * Get Router object
	 * @return \Scion\Controllers\Routing\Router
	 */
	final public function getRouter() {
		return RouteLoader::getRouter();
	}

	/**
	 * Get TemplateEngine object
	 */
	final public function getTempate() {
		return TemplateEngine::getInstance();
	}
}
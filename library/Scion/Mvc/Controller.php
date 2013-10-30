<?php
namespace Scion\Mvc;

use Scion\Form\Form;
use Scion\Http\Request;
use Scion\Loader\RouteLoader;
use Scion\Mvc\Controller\Plugin\Redirect;
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
	 * @return \Scion\Routing\Router
	 */
	final public function getRouter() {
		return RouteLoader::getRouter();
	}

	/**
	 * Get TemplateEngine object
	 */
	final public function getTemplate() {
		return TemplateEngine::getInstance();
	}

	/**
	 * Get Form object
	 * @param $name
	 * @return Form
	 */
	final public function createFormBuilder($name = null) {
		return new Form($name);
	}

	/**
	 * Get Redirect object
	 * @return Redirect
	 */
	final public function redirect() {
		return new Redirect();
	}

	/**
	 * Get Request object
	 * @return Request
	 */
	final public function getRequest() {
		return new Request();

	}
}
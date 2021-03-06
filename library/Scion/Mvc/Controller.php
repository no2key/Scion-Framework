<?php
namespace Scion\Mvc;

use Scion\Form\Form;
use Scion\Http\Headers;
use Scion\Http\Request;
use Scion\Loader\RouteLoader;
use Scion\Mvc\Controller\Plugin\Redirect;
use Scion\Views\TemplateEngine;

trait Controller {
	use ModelController;

	/**
	 * Call service and return object
	 * @param string $service
	 * @param array  $parameters
	 * @return mixed
	 */
	protected function __getService($service, $parameters = []) {
		switch ($service) {
			/**
			 * Get class name using this controller trait
			 * @return string
			 */
			case 'class':
				return __CLASS__;
				break;

			/**
			 * Get Router object
			 * @return \Scion\Routing\Router
			 */
			case 'router':
				return RouteLoader::getRouter();
				break;

			/**
			 * Get TemplateEngine object
			 * @return \Scion\Views\TemplateEngine
			 */
			case 'template':
				return TemplateEngine::getInstance();
				break;

			/**
			 * Get Form object
			 * @return \Scion\Form\Form
			 */
			case 'form':
				if (isset($parameters[1])) {
					return new Form($parameters[1]);
				}

				return new Form();
				break;

			/**
			 * Get Request object
			 * @return \Scion\Http\Request
			 */
			case 'request':
				return new Request();
				break;

			/**
			 * Get Headers object
			 * @return \Scion\Http\Headers
			 */
			case 'headers':
				return Headers::getinstance();
				break;

			default:
				break;
		}
	}
}
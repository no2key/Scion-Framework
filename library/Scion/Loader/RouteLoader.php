<?php
namespace Scion\Loader;

use Scion\Routing\Http\Controller;
use Scion\Routing\Http\Format;
use Scion\Routing\Route;
use Scion\Routing\Router;
use Scion\File\Json;
use Scion\Http\Request;

class RouteLoader {

	private static $_router;

	/**
	 * Register all routes from Json file
	 * @param $routingFilePath
	 * @throws Exception
	 */
	public static function registerRoutes($routingFilePath) {
		self::$_router = new Router();

		$routes = Json::decode(file_get_contents($routingFilePath));

		foreach ($routes->routes as $route => $values) {
			self::$_router->addRoute(new Route($route, $values));
		}

		try {
			// Find valid route
			if (self::$_router->match()) {

				// Call controller
				$controller = self::$_router->getMatchedRoute()->_controller;
				// Check controller
				if ($controller instanceof Controller) {
					// Call specific controller, need to check the format bellow
					$controller->callController(self::$_router->getMatchedRoute()->_format);

					// Check format
					if (self::$_router->getMatchedRoute()->_format !== null) {
						if (!(new Format($controller, self::$_router->getMatchedRoute()->_format))->validFormat()) {
							throw new \Exception('Format specified no matching');
						}
					}

					/* @TODO Need to change that when Dwoo will be implemented */
					echo self::$_router->getMatchedRoute()->_controller->_beginContent;
					echo self::$_router->getMatchedRoute()->_controller->_methodContent;
					echo self::$_router->getMatchedRoute()->_controller->_endContent;
				}
			}
			else {
				echo 'There is no match for this route!!!<br />';
			}

		}
		catch (\Exception $e) {
			echo 'Error: ' . $e->getMessage() . '<br />';
		}

		// Clear all routes
		self::$_router->clear();
	}

	/**
	 * Get an object from the class Router
	 * @return mixed
	 */
	public static function getRouter() {
		return self::$_router;
	}
}
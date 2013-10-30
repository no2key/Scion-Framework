<?php
namespace Scion\Loader;

use Scion\File\FileIterator;
use Scion\Routing\Http\Controller;
use Scion\Routing\Http\Format;
use Scion\Routing\Route;
use Scion\Routing\RouteChild;
use Scion\Routing\Router;
use Scion\File\Json;
use Scion\Http\Request;

class RouteLoader {

	public static $_router;
	private static $_routes = [];

	/**
	 * Register all routes from Json file
	 * @param $routingFilePath
	 * @throws Exception
	 */
	public static function registerRoutes($routingFilePath) {
		self::$_routes = Json::decode(file_get_contents($routingFilePath));
	}

	/**
	 * Process routing, check match and load controllers
	 */
	public static function processRoutes() {
		self::$_router = new Router();

		if (self::$_routes != null) {
			$funcRoutes = function($routes, $prefix = null) use (&$funcRoutes) {
				foreach ($routes as $route => $values) {
					// Add a route to the Router
					if ($prefix !== null) {
						$values->pattern = str_replace('//', '/', $prefix . $values->pattern);
					}
					self::$_router->addRoute(new Route($route, $values));

					// Add a child route
					if (property_exists($values, 'children')) {
						if (is_string($values->children)) {
							if (!empty($values->children) && file_exists(dirname($_SERVER["SCRIPT_FILENAME"]) . $values->children)) {
								$childRoutes = Json::decode(file_get_contents(dirname($_SERVER["SCRIPT_FILENAME"]) . $values->children));
								$funcRoutes($childRoutes->routes, $values->pattern);
							}
						}
						else if (is_object($values->children)) {
							$funcRoutes($values->children, $values->pattern);
						}
					}
				}
			};
			$funcRoutes(self::$_routes->routes);

			//var_dump(self::$_router->getHashedRoutes());

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
	}

	/**
	 * Get an object from the class Router
	 * @return mixed
	 */
	public static function getRouter() {
		return self::$_router;
	}
}
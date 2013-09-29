<?php
namespace Scion\Models\Loader;

use Scion\Controllers\Routing\Http\Controller;
use Scion\Controllers\Routing\Http\Format;
use Scion\Controllers\Routing\Route;
use Scion\Controllers\Routing\Router;
use Scion\Models\File\Json;
use Scion\Models\Http\Request;

class RouteLoader {

	private static $_router;

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
					$controller->callController();

					// Check format
					if (self::$_router->getMatchedRoute()->_format !== null) {
						if (!(new Format($controller, self::$_router->getMatchedRoute()->_format))->validFormat()) {
							throw new Exception('Format specified no matching');
						}
					}

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

	public static function getRouter() {
		return self::$_router;
	}
}
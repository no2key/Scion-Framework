<?php
namespace Scion\Routing;
use Scion\Http\Request;

class Router {

	protected $hashedRoutes = [];
	protected $routes = [];
	protected $matchedRoute = null;
	private $_request;

	public static $DEBUG = 0;

	/**
	 * Router constructor, create a Request object
	 */
	public function __construct() {
		$this->_request = new Request();
	}

	/**
	 * Add route to router
	 * @param Route $newRoute reference to route object
	 */
	public function addRoute(Route &$newRoute) {
		$this->hashedRoutes[$newRoute->getName()] = $newRoute;
		$this->routes[]                           = $newRoute;
	}

	/**
	 * Add routes to router
	 * @param array $newRoutes array if Routes
	 */
	public function addRoutes(array $newRoutes) {
		foreach ($newRoutes as &$nr) {
			$this->hashedRoutes[$nr->getName()] = $nr;
			$this->routes[]                     = $nr;
		}
	}

	/**
	 * Try to find match for url
	 * also sets $this->matchedRoute which can be later retrieved
	 * @return boolean true if route is matched
	 */
	public function match() {
		$url                = $this->_request->getPath();
		$this->matchedRoute = null;
		foreach ($this->routes as &$route) {
			if ($route->match($url)) {
				$this->matchedRoute = $route;

				return true;
			}
		}

		return false;
	}

	/**
	 * Generates url depending on routeName and parameters
	 * @param string $routeName
	 * @param mixed  $params - can be (key,value) array or string of url parameters
	 * @throws \Exception if route with $routeName does not exist
	 * @return string generated url
	 */
	public function generate($routeName, $params = []) {
		if (!array_key_exists($routeName, $this->hashedRoutes)) {
			throw new \Exception(sprintf("Route with name %s does not exist", $routeName));
		}
		//if $params is provided as string make array from it
		//this is very bad(although can be easier for developer). 
		//Preparing $params as array IS THE BEST CHOICE!!!
		if (!is_array($params)) {
			$params = $this->stringToRouteParams($params);
		}
		$route = $this->hashedRoutes[$routeName];

		if ($this->_request->isModeRewriteActive() === false) {
			$this->_request->getPath();
			return $this->_request->getBaseIndex() . $route->generate($params);
		}

		return $this->_request->getRelativeUrlRoot() . $route->generate($params);
	}

	/**
	 * Converts string to route params array
	 * @param string $v - string contains params key1=val1&key2=val2...
	 * @return array
	 */
	public function stringToRouteParams($v) {
		$keyValuePairs = explode('&', $v);
		$params        = [];
		foreach ($keyValuePairs as $kvPair) {
			$kv = explode('=', $kvPair, 2);
			if (count($kv) == 2) {
				$params[$kv[0]] = $kv[1];
			}
		}

		return $params;
	}

	/**
	 * Check if route with name $name exists
	 * @param $name
	 * @return bool
	 */
	public function isRouteExists($name) {
		return array_key_exists($name, $this->hashedRoutes);
	}

	/**
	 * Check if there is matched route object
	 * @return bool
	 */
	public function gotMatchedRoute() {
		return $this->matchedRoute instanceof Route;
	}

	/**
	 * Proxy for getMatchedParameters of matched route. Route must be matched before calling this!
	 * @param boolean $withoutDefaults
	 * if true default matched parameters are removed from matched parameters
	 */
	public function getParameters($withoutDefaults = false) {
		return $this->matchedRoute->getMatchedParameters($withoutDefaults);
	}

	/**
	 * Set a new route param
	 * @param $name
	 * @param $value
	 * @return mixed
	 */
	public function setParam($name, $value) {
		return $this->matchedRoute->setMatchedParam($name, $value);
	}

	/**
	 * Return a matched param
	 * @param $name
	 * @return mixed
	 */
	public function getParam($name) {
		return $this->matchedRoute->getMatchedParam($name);
	}

	/**
	 * Remove a matched param
	 * @param $name
	 * @return mixed
	 */
	public function removeParam($name) {
		return $this->matchedRoute->removeMatchedParam($name);
	}

	/**
	 * Returns matched route
	 * @return Route or null
	 */
	public function getMatchedRoute() {
		return $this->matchedRoute;
	}

	/**
	 * Return hashed route
	 * @return array
	 */
	public function getHashedRoutes() {
		return $this->hashedRoutes;
	}

	/**
	 * clears all routes
	 */
	public function clear() {
		$this->hashedRoutes = [];
		$this->routes       = [];
		$this->matchedRoute = null;
	}

}
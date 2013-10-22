<?php
namespace Scion\Routing;

use Scion\Routing\Http\Constraint;
use Scion\Routing\Http\Controller;
use Scion\Routing\Http\Method;
use Scion\Routing\Http\Pattern;
use Scion\Routing\Http\Scheme;
use Scion\Mvc\Magic;

class Route {
	use Magic;

	const ANY_KEY     = '__any';
	const ANY_PATTERN = '(?P<__any>.*)';

	private $_regex = null;
	private $_createPattern;
	private $_base = null;
	private $_name = '';
	private $_constraints = [];
	private $_defaultMatchedParameters = [];
	private $_matchedParameters;
	private $_allowAdditionalParameters;
	private $_scheme = null;
	private $_controller = null;
	private $_method = null;
	private $_format = null;

	/**
	 * Name of parameters must contains only letters and numbers!
	 * @param string    $name  name of route - something descriptive
	 * @param \stdClass $json
	 * @throws \Exception
	 */
	public function __construct($name, \stdClass $json) {
		$this->_name = $name;


		if (!property_exists($json, 'pattern')) {
			throw new \Exception('Pattern property must be specified !');
		}

		// Add pattern
		$pattern                          = new Pattern($json->pattern);
		$this->_allowAdditionalParameters = $pattern->_additionalParameters;
		$this->_createPattern             = $pattern->_pattern;

		// Add options
		if (property_exists($json, 'options')) {
			// Literal
			if (property_exists($json->options, 'defaults')) {
				$this->_defaultMatchedParameters = $json->options->defaults;
			}

			// Constraints
			if (property_exists($json->options, 'constraints')) {
				foreach ($json->options->constraints as $constraintName => $constraintValue) {
					$this->_constraints[$constraintName] = new Constraint($constraintValue);
				}
			}

			// Regex
			if (property_exists($json->options, 'regex')) {
				$this->_regex = $json->options->regex;
			}

			// Scheme
			if (property_exists($json->options, 'scheme')) {
				$this->_scheme = $json->options->scheme;
			}

			// Controller + Method + Format
			if (property_exists($json->options, 'controller')) {
				$this->_controller = new Controller($json->options->controller);

				// Method
				if (property_exists($json->options, 'method')) {
					$this->_method = $json->options->method;
				}

				// Output Format
				if (property_exists($json->options, 'format')) {
					$this->_format = $json->options->format;
				}
			}
		}

		// Base
		if (property_exists($json, 'base')) {
			$this->_base = $json->base;
		}

	}

	/**
	 * Check if this route can match provided url
	 * @param $url string - Url to match
	 * @return bool true if this route can match provided $url param
	 */
	public function match($url) {
		//if we have static base compare with base first(faster comparison)
		if (!$this->_base) {
			$this->extractBase();
		}

		if (strpos($url, $this->_base) !== 0) {
			return false;
		}

		//extract parameters if they are not yet extracted(or passed)
		$this->extractParameters();

		//compile match pattern if not specified
		if (!$this->_regex) {
			$this->compileMatchPattern();
		}

		if (preg_match($this->_regex, $url, $tMatchedParams)) {
			// Check Regex
			$this->_matchedParameters = (array)$this->_defaultMatchedParameters;
			if (isset($tMatchedParams[self::ANY_KEY])) {
				$this->parseAny($tMatchedParams[self::ANY_KEY]);
				unset($tMatchedParams[self::ANY_KEY]);
			}
			foreach ($tMatchedParams as $key => $value) {
				//skip no named matches
				if (is_int($key)) {
					continue;
				}
				$this->_matchedParameters[urldecode($key)] = urldecode($value);
			}

			// Check scheme
			if ($this->_scheme !== null) {
				return (new Scheme($this->_scheme))->isValid();
			}

			// Check method
			if ($this->_method !== null) {
				return (new Method($this->_method))->isValidMethod();
			}

			return true;
		}

		return false;
	}

	/**
	 * Parse any value
	 * @param $value
	 */
	protected function parseAny($value) {
		//module, action and matched parameters
		//are allowed to be specified through __any parameter
		$notAllowed = array_keys($this->_matchedParameters);
		//remove all characters after ?(and including) if ? exists	
		//this can produce errors if there is multiple ?, but that url is not valid, so we dont want to bother with it(same goes to & without ? in url)
		$startOfGetPos = strrpos($value, '?');
		if ($startOfGetPos !== false) {
			$value = substr($value, 0, $startOfGetPos);
		}
		$array = explode('/', trim($value, '/'));
		$i     = 0;
		while ($i + 1 < count($array)) {
			$key = urldecode($array[$i]);
			//skip if key is 0, null, '', or in not allowed parameter names
			if ($key && !in_array($key, $notAllowed)) {
				$this->_matchedParameters[$key] = urldecode($array[$i + 1]);
			}
			$i += 2;
		}
	}

	/**
	 * Generates url
	 * Iterates through parameters and replaces :parameter_name in createPattern with specified value
	 * if value is same as default parameter
	 * @param $params array of (key, value) pairs. Replace route parameters
	 * @throws \Exception
	 * @return string generated url
	 */
	public function generate($params) {
		//extract parameters from createPattern if not specified
		$this->extractParameters();

		//put default parameters not specified in $params into $params
		$mgParams = array_merge((array)$this->_defaultMatchedParameters, $params);

		//this is code from symfony its better to check missing parameters immediately
		if (($diff = array_diff_key($this->_constraints, $mgParams))) {
			throw new \Exception(sprintf("Route with name %s, pattern %s has missing parameters (%s)", $this->_name, $this->_createPattern, implode(', ', array_keys($diff))));
		}

		//
		$tokens      = [];
		$replaces    = [];
		$url         = $this->_createPattern;
		$hasDefaults = false;

		//!IMPORTANT: order of parameters match order of parameters in $createPattern
		foreach ($this->_constraints as $key => $constraintObject) {
			//throw exception if key is not in params
			//if ( !array_key_exists($key, $mgParams) ) throw new icException("$key is not specified for route $this->_name");
			$value = $mgParams[$key];

			// if parameter is last one and exactly the same value as value in defaultMatchedParameters
			// than just clear this parameter from generated url
			if (/*empty($this->_defaultMatchedParameters) || */!property_exists($this->_defaultMatchedParameters, $key) || $this->_defaultMatchedParameters->$key != $value) {
				//if there is route parameter object for this parameter, parse its value with routeParameterObject otherwise just do urlencode
				if (!$hasDefaults) {
					$url = str_replace(':' . $key, !($constraintObject instanceof Constraint) ? urlencode($value) : $constraintObject->$value, $url);
				}
				else {
					$tokens[]    = ':' . $key;
					$replaces[]  = !($constraintObject instanceof Constraint) ? urlencode($value) : $constraintObject->$value;
					$url         = str_replace($tokens, $replaces, $url);
					$tokens      = [];
					$replaces    = [];
					$hasDefaults = false;
				}
			}
			else {
				$tokens[]    = ':' . $key;
				$replaces[]  = $value;
				$hasDefaults = true;
			}
		}

		//add additional at the end if route allows them
		if ($this->_allowAdditionalParameters) {
			$params = array_diff_key($params, array_merge($this->_constraints, (array)$this->_defaultMatchedParameters));
			foreach ($params as $key => $value) {
				$url .= '/' . urlencode($key) . '/' . urlencode($value);
			}
		}

		//fix last parameter
		if ($hasDefaults) {
			if (!$this->_allowAdditionalParameters || !count($params)) {
				//substring to position of first default parameter
				$url = substr($url, 0, strpos($url, $tokens[0]));

				return rtrim($url, '/');
			}
			else {
				return str_replace($tokens, $replaces, $url);
			}
		}

		return $url;
	}

	/**
	 * Returns matched parameters (or matched parameters without default matches)
	 * @param  boolean $withoutDefaults
	 * @return array key, value 
	 */
	public function getMatchedParameters($withoutDefaults = false) {
		if (!$withoutDefaults) {
			return $this->_matchedParameters;
		}
		$tMatArray = array_merge($this->_matchedParameters, []);
		//remove all paremeters which are default
		foreach ($this->_defaultMatchedParameters as $key => $value) {
			if ($tMatArray[$key] == $value) {
				unset($tMatArray[$key]);
			}
		}

		return $tMatArray;
	}

	/**
	 * Return matched param for the specific name
	 * @param $name
	 * @return null
	 */
	public function getMatchedParam($name) {
		return array_key_exists($name, $this->_matchedParameters) ? $this->_matchedParameters[$name] : null;
	}

	/**
	 * Set a matched param
	 * @param $name
	 * @param $value
	 * @return void
	 */
	public function setMatchedParam($name, $value) {
		$this->_matchedParameters[$name] = $value;
	}

	/**
	 * Remove a specific matched param in the array
	 * @param $name
	 */
	public function removeMatchedParam($name) {
		unset($this->_matchedParameters[$name]);
	}

	/**
	 *  Get the name of the current route
	 * @return string
	 */
	public function getName() {
		return $this->_name;
	}

	/**
	 * Extract parameters from $this->_createPattern
	 * if this->parameters is not specified
	 */
	private function extractParameters() {
		if (is_array($this->_constraints)) {
			return;
		}
		preg_match_all('/\:([A-Za-z0-9_]+)/', $this->_createPattern, $matches);
		$this->_constraints = array_flip($matches[1]);
	}

	/**
	 * Extract unmutable base from $this->_createPattern
	 */
	private function extractBase() {
		$pos        = strpos($this->_createPattern, ':');
		$this->_base = $pos !== false ? substr($this->_createPattern, 0, $pos) : $this->_createPattern;
	}

	/**
	 * Compiles find(match) regular expression pattern from create pattern string
	 */
	private function compileMatchPattern() {
		$defaultReplaces = [];
		$hasDefaults     = false;

		$findPattern = '';
		$parts       = explode('/', trim($this->_createPattern, '/'));
		foreach ($parts as $part) {
			//in one part of url we can have more than one key /:id-:name
			preg_match_all('/\:([A-Za-z0-9_]+)/', $part, $matches);
			$matches = $matches[1];

			if (count($matches)) {
				$tokens      = [];
				$replaces    = [];
				$isInDefault = true;
				foreach ($matches as $key) {
					$patternPart = isset($this->_constraints[$key]) && $this->_constraints[$key] instanceof Constraint ? $this->_constraints[$key]->getPattern() : '[^/]+';
					$replaces[]  = '(?P<' . $key . '>' . $patternPart . ')';
					$tokens[]    = ':' . $key;
					$isInDefault = $isInDefault && array_key_exists($key, $this->_defaultMatchedParameters);
				}

				$part = str_replace($tokens, $replaces, $part);

				if (!$isInDefault) {
					if (!$hasDefaults) {
						$findPattern .= '/' . $part;
					}
					else {
						$findPattern .= '/' . join('/', $defaultReplaces) . '/' . $part;
						$defaultReplaces = [];
						$hasDefaults     = false;
					}
				}
				else {
					$defaultReplaces[] = $part;
					$hasDefaults       = true;
				}
			}
			else if ($hasDefaults) {
				$defaultReplaces[] = $part;
			}
			else {
				$findPattern .= '/' . $part;
			}

		}
		//adds sufix
		if (!$hasDefaults) {
			if ($this->_allowAdditionalParameters) {
				$findPattern .= self::ANY_PATTERN;
			}
			else {
				$findPattern .= '/?';
			}
		}
		else {
			$patternSuffix = $this->_allowAdditionalParameters ? self::ANY_PATTERN : '/?';
			$i             = count($defaultReplaces) - 1;
			while ($i >= 0) {
				$patternSuffix = '(?:/' . $defaultReplaces[$i] . $patternSuffix . ')?';
				$i--;
			}

			$findPattern .= $patternSuffix . '/?';

		}

		$this->_regex = '#^' . $findPattern . '$#';
	}

}
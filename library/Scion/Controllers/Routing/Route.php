<?php
namespace Scion\Controllers\Routing;

use Scion\Controllers\Routing\Http\Constraint;
use Scion\Controllers\Routing\Http\Controller;
use Scion\Controllers\Routing\Http\Format;
use Scion\Controllers\Routing\Http\Literal;
use Scion\Controllers\Routing\Http\Method;
use Scion\Controllers\Routing\Http\Pattern;
use Scion\Controllers\Routing\Http\Regex;
use Scion\Controllers\Routing\Http\Scheme;

class Route {
	const ANY_KEY     = '__any';
	const ANY_PATTERN = '(?P<__any>.*)';


	protected $base;
	/*
	 * route name must be descriptive, because it is using for creating urls...
	 */
	protected $name;

	protected $allowAdditionalParameters;

	private $_matchedParameters;
	private $_defaultMatchedParameters = [];
	private $_constraints = [];
	private $_scheme = null;
	private $_regex = null;
	private $_method = null;
	private $_format = null;
	private $_controller = null;
	private $_createPattern;

	/**
	 * Name of parameters must contains only letters and numbers!
	 * @param string    $name  name of route - something descriptive
	 * @param \stdClass $json
	 */
	public function __construct($name, \stdClass $json) {
		$this->name = $name;

		// Add pattern
		if (property_exists($json, 'pattern')) {
			$pattern                         = new Pattern($json->pattern);
			$this->allowAdditionalParameters = $pattern->_additionalParameters;
			$this->_createPattern             = $pattern->_pattern;
		}

		// Add options
		if (property_exists($json, 'options')) {

			// Literal
			if (property_exists($json->options, 'defaults')) {
				$this->_defaultMatchedParameters = (new Literal($json->options->defaults))->_options;
			}

			// Controller + Method + Format
			if (property_exists($json->options, 'controller')) {
				$this->_controller = new Controller($json->options->controller);

				// Method
				if (property_exists($json->options, 'method')) {
					$this->_method = new Method($this->_controller, $json->options->method);
				}

				// Output Format
				if (property_exists($json->options, 'format')) {
					$this->_format = new Format($this->_controller, $json->options->format);
				}
			}

			// Constraints
			if (property_exists($json->options, 'constraints')) {
				foreach ($json->options->constraints as $constraintName => $constraintValue) {
					$this->_constraints[$constraintName] = new Constraint($constraintValue);
				}
			}

			// Regex
			if (property_exists($json->options, 'regex')) {
				$this->_regex = new Regex($json->options->regex);
			}

			// Scheme
			if (property_exists($json->options, 'scheme')) {
				$this->_scheme = new Scheme($json->options->scheme);
			}

		}

		// base
		$this->base = null;
	}

	/**
	 * Check if this route can match provided url
	 * @param string $url Url to match
	 * @return bool (true if this route can match provided $url param)
	 */
	public function match($url) {
		// if we have static base compare with base first(faster comparison)
		if (!$this->base) {
			$this->extractBase();
		}

		if (strpos($url, $this->base) !== 0) {
			return false;
		}

		// Check scheme
		if ($this->_scheme instanceof Scheme) {
			if ($this->_scheme->isValid() === false) {
				return false;
			}
		}

		// Check format
		if ($this->_controller instanceof Controller && $this->_format instanceof Format) {
			if (!$this->_format->validFormat()) {
				return false;
			}
		}

		// Check method
		if ($this->_method instanceof Method) {
			if (!$this->_method->isValidMethod()) {
				return false;
			}
		}

		// Compile match pattern if not specified
		if (!$this->_regex instanceof Regex) {
			$this->compileMatchPattern();
		}

		// Check Regex
		if (preg_match($this->_regex, $url, $tMatchedParams)) {
			$this->_matchedParameters = $this->_defaultMatchedParameters;

			if (isset($tMatchedParams[self::ANY_KEY])) {
				$this->parseAny($tMatchedParams[self::ANY_KEY]);
				unset($tMatchedParams[self::ANY_KEY]);
			}
			foreach ($tMatchedParams as $key => $value) {

				// Convert $value to each type is not a string
				if (filter_var($value, FILTER_VALIDATE_INT)) {
					$value = (int)$value;
				}
				else if (filter_var($value, FILTER_VALIDATE_FLOAT)) {
					$value = (float)$value;
				}
				else if (filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
					$value = (bool)$value;
				}

				//skip no named matches
				if (is_int($key)) {
					continue;
				}
				$this->_matchedParameters[urldecode($key)] = urldecode($value);
			}

			return true;
		}

		return false;
	}

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
	 * @return string generated url
	 * @throws \Exception
	 */
	public function generate($params) {
		//put default parameters not specified in $params into $params
		$mgParams = array_merge($this->_defaultMatchedParameters, $params);

		//this is code from Symfony its better to check missing parameters immediately
		if (($diff = array_diff_key($this->_constraints, $mgParams))) {
			throw new \Exception(sprintf("Route with name %s, pattern %s has missing parameters (%s)", $this->name, $this->_createPattern, implode(', ', array_keys($diff))));
		}

		//
		$tokens      = array();
		$replaces    = array();
		$url         = $this->_createPattern;
		$hasDefaults = false;

		//!IMPORTANT: order of parameters match order of parameters in $_createPattern
		foreach ($this->_constraints as $key => $constraintObject) {
			//throw exception if key is not in params
			//if ( !array_key_exists($key, $mgParams) ) throw new \Exception("$key is not specified for route $this->name");
			$value = $mgParams[$key];

			// if parameter is last one and exactly the same value as value in defaultMatchedParameters
			// than just clear this parameter from generated url
			if (!isset($this->_defaultMatchedParameters[$key]) || $this->_defaultMatchedParameters[$key] != $value) {
				//if there is route parameter object for this parameter, parse its value with routeParameterObject otherwise just do urlencode
				if (!$hasDefaults) {
					$url = str_replace(':' . $key, !($constraintObject instanceof Constraint) ? urlencode($value) : $constraintObject->$value, $url);

				}
				else {
					$tokens[]    = ':' . $key;
					$replaces[]  = !($constraintObject instanceof Constraint) ? urlencode($value) : $constraintObject->$value;
					$url         = str_replace($tokens, $replaces, $url);
					$tokens      = array();
					$replaces    = array();
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
		if ($this->allowAdditionalParameters) {
			$params = array_diff_key($params, array_merge($this->_constraints, $this->_defaultMatchedParameters));

			foreach ($params as $key => $value) {
				$url .= '/' . urlencode($key) . '/' . urlencode($value);
			}
		}

		//fix last parameter
		if ($hasDefaults) {
			if (!$this->allowAdditionalParameters || !count($params)) {
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
	 * @return array key, value
	 */
	public function getMatchedParameters() {
		$tMatArray['defaults'] = $this->_matchedParameters;

		// Return controller parameter
		if ($this->_controller instanceof Controller) {
			$tMatArray['controller'] = (string)$this->_controller;
		}

		// Return format parameter
		if ($this->_format instanceof Format) {
			$tMatArray['format'] = (string)$this->_format;
		}

		// Return method parameter
		if ($this->_method instanceof Method) {
			$tMatArray['method'] = (string)$this->_method;
		}

		// Return scheme parameter
		if ($this->_scheme instanceof Scheme) {
			$tMatArray['scheme'] = (string)$this->_scheme;
		}

		// Return regex parameter
		if ($this->_regex instanceof Regex) {
			$tMatArray['regex'] = (string)$this->_regex;
		}

		// Return constraints parameter
		if (!empty($this->_constraints)) {
			foreach ($this->_constraints as $key => $value) {
				$tMatArray['constraints'][$key] = (string)$value;
			}
		}

		return $tMatArray;
	}

	public function getMatchedParam($name) {
		return array_key_exists($name, $this->_matchedParameters) ? $this->_matchedParameters[$name] : null;
	}

	public function setMatchedParam($name, $value) {
		$this->_matchedParameters[$name] = $value;
	}

	public function removeMatchedParam($name) {
		unset($this->_matchedParameters[$name]);
	}

	public function getName() {
		return $this->name;
	}

	/**
	 * extract unmutable base from $this->_createPattern
	 */
	private function extractBase() {
		$pos        = strpos($this->_createPattern, ':');
		$this->base = $pos !== false ? substr($this->_createPattern, 0, $pos) : $this->_createPattern;
	}

	/**
	 * compiles find(match) regular expression pattern from create pattern string
	 */
	private function compileMatchPattern() {
		$defaultReplaces = array();
		$hasDefaults     = false;

		$findPattern = '';
		$parts       = explode('/', trim($this->_createPattern, '/'));
		foreach ($parts as $part) {
			//in one part of url we can have more than one key /:id-:name
			preg_match_all('/\:([A-Za-z0-9_]+)/', $part, $matches);
			$matches = $matches[1];

			if (count($matches)) {
				$tokens      = array();
				$replaces    = array();
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
						$defaultReplaces = array();
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
			if ($this->allowAdditionalParameters) {
				$findPattern .= self::ANY_PATTERN;
			}
			else {
				$findPattern .= '/?';
			}
		}
		else {
			$patternSuffix = $this->allowAdditionalParameters ? self::ANY_PATTERN : '/?';
			$i             = count($defaultReplaces) - 1;
			while ($i >= 0) {
				$patternSuffix = '(?:/' . $defaultReplaces[$i] . $patternSuffix . ')?';
				$i--;
			}

			$findPattern .= $patternSuffix . '/?';

		}

		//if ( Router::$DEBUG ) echo htmlspecialchars('#^'.$findPattern.'$#').'<br />';
		$this->_regex = new Regex('#^' . $findPattern . '$#');
	}

}
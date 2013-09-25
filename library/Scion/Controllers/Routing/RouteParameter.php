<?php
namespace Scion\Controllers\Routing;

class RouteParameter {
	const ANY   = 0;
	const INT   = 1;
	const FLOAT = 2; //value will be float
	const REG_REPLACE = 4; //
	const REG_MATCH = 5;
	const IN        = 6;
	const NOT_IN    = 7;
	/**
	 * @brief: Type of parameter
	 *
	 * @var pick one of above const values
	 */
	protected $type;
	/**
	 * @brief: Additional parameter
	 *
	 * @var mixed. Additonal parameters, depends on type
	 */
	protected $additional;

	/**
	 * @param mixed $values
	 */
	public function __construct($values) {
		if (is_array($values)) {
			$this->type = $this->_replaceConstantsFromString(array_keys($values)[0]);
			//default for reg replace
			if ($this->type == self::REG_REPLACE && is_string(array_values($values)[0])) {
				$this->additional = array(array_values($values)[0], '-');

				return;
			}
			$this->additional = array_values($values)[0];
		}
		else {
			$this->type = $this->_replaceConstantsFromString($values);
		}
	}

	/**
	 * Convert string constant name to the constant result
	 * @param $string
	 * @return int
	 */
	private function _replaceConstantsFromString($string) {
		$search  = ['ANY', 'INT', 'FLOAT', 'REG_REPLACE', 'REG_MATCH', 'IN', 'NOT_IN'];
		$replace = [self::ANY, self::INT, self::FLOAT, self::REG_REPLACE, self::REG_MATCH, self::IN, self::NOT_IN];

		return (int)str_replace($search, $replace, $string);
	}

	/**
	 * Get parameter's type
	 * @return mixed
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Parse value depending on type of parameter
	 * @params mixed $value value to parse
	 * @throws \Exception for IN_ARRAY and REG_MATCH
	 * @return parsed value
	 */
	public function parseValue($value) {
		switch ($this->type) {
			case self::INT:
				return intval($value);
			case self::FLOAT:
				return doubleval($value);
			case self::REG_REPLACE:
				//third parameter can be set to avoid strtolower
				if (!isset($this->additional[2]) || !$this->additional[2]) {
					$value = strtolower($value);
				}
				$value = preg_replace('/[^' . $this->additional[0] . ']/', $this->additional[1], $value);
				$value = preg_replace('/' . $this->additional[1] . '+/', $this->additional[1], $value);

				return trim($value, $this->additional[1]);
			case self::REG_MATCH:
				if (preg_match('/^' . $this->additional . '$/', $value)) {
					//if matches returns value otherwise throw exception
					return $value;
				}
				throw new \Exception("Parameter $value is not match $this->additional");
			case self::IN:
				if (!in_array($value, $this->additional)) {
					$valuesOk = join(', ', $this->additional);
					throw new \Exception("Parameter $value is not match one of [$valuesOk]");
				}

				return $value;
			case self::NOT_IN:
				if (in_array($value, $this->additional)) {
					$valuesOk = join(', ', $this->additional);
					throw new \Exception("Parameter $value must not be in [$valuesOk]");
				}

				return $value;
			case self::ANY:
				return $value;
		}

	}

	/**
	 * Retrieves pattern for regular expressions. Depends on type
	 * @return: string value (pattern part)
	 */
	public function getPattern() {
		switch ($this->type) {
			case self::INT:
				return '[0-9]+';
			case self::FLOAT:
				return '-?[0-9]+|[0-9]+\.[0-9]+';
			case self::REG_REPLACE:
				return '[' . $this->additional[0] . ']+';
			case self::REG_MATCH:
				return $this->additional;
			case self::IN:
				return join('|', $this->additional);
			case self::NOT_IN:
				return '(?!' . join('|', $this->additional) . ')[^\/]+';
			case self::ANY:
				return '[^\/]+';
		}
	}

	/*
	 *TODO: is this code really neccessary?
	protected static $defaultIntParameter = null;
	protected static $defaultFloatParameter = null;
	protected static $defaultReplaceParameter = null;

	public static function getInt()
	{
		if ( self::$defaultIntParameter == null ) self::$defaultIntParameter = new self(self::INT);
		return self::$defaultIntParameter;
	}
	public static function getFloat()
	{
		if ( self::$defaultFloatParameter == null ) self::$defaultFloatParameter = new self(self::FLOAT);
		return self::$defaultFloatParameter;
	}
	public static function getReplace()
	{
		if ( self::$defaultReplaceParameter == null ) self::$defaultReplaceParameter = new self(self::REG_REPLACE, array('a-zA-Z0-9_\-', '-') );
		return self::$defaultReplaceParameter;
	}
	*/
}
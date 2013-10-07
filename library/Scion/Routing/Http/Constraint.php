<?php
namespace Scion\Routing\Http;

use Scion\Mvc\Magic;

class Constraint {
	use Magic;

	const NULL        = -1;
	const ANY         = 0;
	const INT         = 1;
	const FLOAT       = 2;
	const REG_REPLACE = 4;
	const REG_MATCH   = 5;
	const IN          = 6;
	const NOT_IN      = 7;

	/**
	 * Type of parameter from above constant
	 * @var int
	 */
	private $_type;

	/**
	 * Additional parameter
	 * @var  mixed. Additional parameters, depends on type
	 */
	private $_additional;

	/**
	 * Constructor
	 * @param mixed $value
	 */
	public function __construct($value) {
		if ($value instanceof \stdClass) {
			$this->_type = constant('self::'.array_keys((array)$value)[0]);
			//default for reg replace
			if ($this->_type == self::REG_REPLACE && is_string(array_values((array)$value)[0])) {
				$this->_additional = [array_values((array)$value)[0], '-'];

				return;
			}
			$this->_additional = array_values((array)$value)[0];
		}
		else if (is_string($value)) {
			$this->_type = constant('self::'.$value);
		}
		else if (is_null($value)) {
			$this->_type = self::NULL;
			$this->_additional = null;
		}
	}

	public function __toString() {
		if (is_array($this->_additional)) {
			return (string)$this->_type . '::'.$this->_additional[0];
		}
		return (string)$this->_type . '::'.$this->_additional;
	}

	/**
	 * Get value
	 * @param $value
	 * @return float|int|string
	 * @throws \Exception
	 */
	public function __get($value) {
		switch ($this->_type) {
			case self::INT:
				return intval($value);

			case self::FLOAT:
				return doubleval($value);

			case self::REG_REPLACE:
				//third parameter can be set to avoid strtolower
				if (!isset($this->_additional[2]) || !$this->_additional[2]) {
					$value = strtolower($value);
				}
				$value = preg_replace('/[^' . $this->_additional[0] . ']/', $this->_additional[1], $value);
				$value = preg_replace('/' . $this->_additional[1] . '+/', $this->_additional[1], $value);

				return trim($value, $this->_additional[1]);

			case self::REG_MATCH:
				if (preg_match('/^' . $this->_additional . '$/', $value)) {
					//if matches returns value otherwise throw exception
					return $value;
				}
				throw new \Exception("Parameter $value is not match $this->_additional");

			case self::IN:
				if (!in_array($value, $this->_additional)) {
					$valuesOk = join(', ', $this->_additional);
					throw new \Exception("Parameter $value is not match one of [$valuesOk]");
				}

				return $value;

			case self::NOT_IN:
				if (in_array($value, $this->_additional)) {
					$valuesOk = join(', ', $this->_additional);
					throw new \Exception("Parameter $value must not be in [$valuesOk]");
				}

				return $value;

			case self::ANY:
				return $value;
		}
	}

	/**
	 * Get pattern
	 * @return mixed|string
	 */
	public function getPattern() {
		switch ($this->_type) {
			case self::INT:
				return '[0-9]+';

			case self::FLOAT:
				return '-?[0-9]+|[0-9]+\.[0-9]+';

			case self::REG_REPLACE:
				return '[' . $this->_additional[0] . ']+';

			case self::REG_MATCH:
				return $this->_additional;

			case self::IN:
				return join('|', $this->_additional);

			case self::NOT_IN:
				return '(?!' . join('|', $this->_additional) . ')[^\/]+';

			case self::ANY:
				return '[^\/]+';
		}
	}
}
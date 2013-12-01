<?php
namespace Scion\Routing\Http;

use Scion\Mvc\GetterSetter;

class Pattern {
	use GetterSetter;

	private $_pattern;
	private $_additionalParameters;

	/**
	 * Pattern constructor
	 * @param $pattern
	 */
	public function __construct($pattern) {
		$this->_pattern = $pattern;

		$cpLen = strlen($this->_pattern);
		// if create pattern has * on end of string than it means that additional parameters are allowed
		if ($this->_pattern[$cpLen - 1] == '*') {
			$this->wildcard($cpLen);
		}
		else {
			$this->_additionalParameters = false;
		}
	}

	/**
	 * Manage wildcard (*) pattern
	 * @param $cpLen
	 */
	public function wildcard($cpLen) {
		$this->_additionalParameters = true;
		$this->_pattern = substr($this->_pattern, 0, $cpLen > 2 && $this->_pattern[$cpLen - 2] == '/' ? -1 : -1);
	}
}
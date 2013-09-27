<?php
namespace Scion\Controllers\Routing\Http;

use Scion\Models\Magic;

class Pattern {
	use Magic;

	private $_pattern;
	private $_additionalParameters;

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

	public function wildcard($cpLen) {
		$this->_additionalParameters = true;
		$this->_pattern = substr($this->_pattern, 0, $cpLen > 2 && $this->_pattern[$cpLen - 2] == '/' ? -2 : -1);
	}
}
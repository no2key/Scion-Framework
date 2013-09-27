<?php
namespace Scion\Controllers\Routing\Http;

use Scion\Models\Magic;

class Regex {
	use Magic;

	private $_regex;

	public function __construct($regex) {
		$this->_regex = $regex;
	}

	public function __toString() {
		return $this->_regex;
	}
}
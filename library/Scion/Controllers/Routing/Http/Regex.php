<?php
namespace Scion\Controllers\Routing\Http;

class Regex {
	private $_regex;

	public function __construct($regex) {
		$this->_regex = $regex;
	}
}
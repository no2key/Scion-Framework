<?php
namespace Scion\Routing\Http;

use Scion\Mvc\Magic;
use Scion\Uri\Http;

class Scheme {
	use Magic;

	private $_scheme;
	private $_isSecure;

	/**
	 * Constructor
	 * @param $scheme
	 */
	public function __construct($scheme) {
		$this->_scheme  = $scheme;
		$this->_isSecure = Http::getScheme() == Http::SCHEME_HTTPS;
	}

	/**
	 * toString, return the sheme of the current route
	 * @return mixed
	 */
	public function __toString() {
		return $this->_scheme;
	}

	/**
	 * Check url is valid
	 * @return bool
	 */
	public function isValid() {
		if ($this->_scheme == Http::SCHEME_HTTPS && $this->_isSecure || $this->_scheme == Http::SCHEME_HTTP && !$this->_isSecure) {
			return true;
		}
		return false;
	}

}
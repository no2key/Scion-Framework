<?php
namespace Scion\Routing\Http;

use Scion\Mvc\Magic;

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
		$this->_isSecure = (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) == 'on' || $_SERVER['HTTPS'] == 1)) || (isset($_SERVER['HTTP_SSL_HTTPS']) && (strtolower($_SERVER['HTTP_SSL_HTTPS']) == 'on' || $_SERVER['HTTP_SSL_HTTPS'] == 1)) || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https');
	}

	public function __toString() {
		return $this->_scheme;
	}

	/**
	 * Check url is valid
	 * @return bool
	 */
	public function isValid() {
		if ($this->_scheme == 'https' && $this->_isSecure || $this->_scheme == 'http' && !$this->_isSecure) {
			return true;
		}
		return false;
	}

}
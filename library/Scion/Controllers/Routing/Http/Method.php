<?php
namespace Scion\Controllers\Routing\Http;

use Scion\Models\Http\Request;

class Method {

	private $_method;
	private $_requestMethod;

	/**
	 * Constructor
	 * @param $method
	 */
	public function __construct($method) {
		$this->_method        = $method;
		$this->_requestMethod = (new Request())->getMethod();
	}

	public function __toString() {
		return $this->_method;
	}

	/**
	 * Check specified method is equal to server method
	 *
	 * @return bool
	 */
	public function isValidMethod() {
		if ($this->_method == Request::METHOD_GET || $this->_method == Request::METHOD_POST) {
			return $this->_method == $this->_requestMethod;
		}
		else if ($this->_method == Request::METHOD_REQUEST) {
			return true;
		}

		return false;
	}

}
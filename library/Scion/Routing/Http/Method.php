<?php
namespace Scion\Routing\Http;

use Scion\Http\Request;

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

	/**
	 * toString, get the method used to the route
	 * @return mixed
	 */
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
<?php
namespace Scion\Controllers\Routing\Http;

class Method {

	const METHOD_GET     = 'GET';
	const METHOD_POST    = 'POST';
	const METHOD_REQUEST = 'REQUEST';

	private $_method;
	private $_serverMethod;

	/**
	 * Constructor
	 *
	 * @param Controller $controller
	 * @param            $method
	 */
	public function __construct(Controller $controller, $method) {
		$this->_method       = $method;
		$this->_serverMethod = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
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
		if ($this->_method == self::METHOD_GET || $this->_method == self::METHOD_POST) {
			return $this->_method == $this->_serverMethod;
		}
		else if ($this->_method == self::METHOD_REQUEST) {
			return true;
		}

		return false;
	}

}
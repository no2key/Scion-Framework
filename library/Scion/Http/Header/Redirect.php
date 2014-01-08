<?php
namespace Scion\Http\Header;

use Scion\Http\Headers;

class Redirect {

	private $_headers;

	/**
	 * Redirect constructor
	 * @param Headers $headers
	 */
	public function __construct(Headers $headers) {
		$this->_headers = $headers;
	}

	/**
	 * Creates a redirect response so that it conforms to the rules defined for a redirect status code.
	 * @param string $routeUrl
	 * @param int $status
	 */
	public function to($routeUrl, $status = 302) {
		$this->_headers->setStatus($status);
		$this->_headers->setLocation($routeUrl);
		$this->_headers->sendHeader();
	}

} 
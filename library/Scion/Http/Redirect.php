<?php
namespace Scion\Http;

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
	 * Redirect to a specific location
	 * @param string $routeUrl
	 * @param int $status
	 */
	public function to($routeUrl, $status = 302) {
		$this->_header->setStatus($status);
		$this->_header->setLocation($routeUrl);
		$this->_header->sendHeader();
	}

} 
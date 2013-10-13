<?php
namespace Scion\Http;

use Scion\Mvc\Singleton;

class Header {
	use Singleton;

	protected $statuscodes = array(
		100 => 'Continue',
		101 => 'Switching Protocols',
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		307 => 'Temporary Redirect',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported'
	);

	/**
	 * Flag which indicated wether the header has already been sent.
	 *
	 * @access private
	 */
	protected $sent = false;

	/**
	 * Internal header buffer.
	 *
	 * Saves all header strings.
	 *
	 * @access protected
	 */
	protected $buffer = array();

	protected $cookiesBuffer = array();

	/**
	 * Singleton-pattern constructor set protected to deny direct access.
	 */
	protected function __construct() {
		$this->sent = headers_sent();
	}

	public function __destruct() {
		return $this->sendHeader();
	}

	public function sendHeader() {
		if (! $this->sent & ! headers_sent()) {
			foreach ($this->buffer as $part) {
				if (strlen($part[1]) > 0) {
					header($part[0] . ': ' . $part[1]);
				}
				else {
					header($part[0]);
				}
			}
			$this->sendCookies();

			return true;
		}
		$this->sent = true;

		return false;
	}

	public function sendCookies() {
		$return = true;
		foreach ($this->cookiesBuffer as $cookie) {
			$return &= setcookie($cookie['name'], $cookie['value'], $cookie['expire']);
		}

		return $return;
	}

	public function addCookie($name, $value, $expire) {
		$this->cookiesBuffer[] = array(
			'name'   => $name,
			'value'  => $value,
			'expire' => $expire
		);

		return $this;
	}

	protected function add($type, $value = '') {
		$this->sent = headers_sent();
		if (! $this->sent) {
			$type[0]        = strtoupper($type[0]);
			$this->buffer[] = array($type, $value);
		}
		else {
			throw new \Exception('Cannot add a buffer. Header already sent.');
		}

		return $this;
	}

	public function setLocation($location) {
		$this->add('Location', $location);
		return $this;
	}

	public function contentType($media, $charset = '') {
		$this->add('Content-Type', $media . (empty($charset) ? '' : '; charset=' . $charset));
		return $this;
	}

	public function contentDisposition($filename, $disposition = 'inline') {
		$this->add('Content-Disposition', $disposition . '; filename="' . $filename . '"');
		return $this;
	}

	public function status($statuscode) {
		$this->add('HTTP/1.1 ' . $statuscode . ' ' . $this->statuscodes[$statuscode]);
		return $this;
	}

	public function lastModified($date) {
		$this->add('Last-Modified', $date);
		return $this;
	}

	public function etag($etag) {
		$this->add('Etag', $etag);
		return $this;
	}
}
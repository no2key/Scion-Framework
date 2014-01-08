<?php
namespace Scion\Http;

use Scion\Http\Header\Browser;
use Scion\Http\Header\Cookie;
use Scion\Http\Header\Platform;
use Scion\Http\Header\Redirect;
use Scion\Http\Header\UserAgent;
use Scion\Mvc\Singleton;

/**
 * This class is used to compose and send HTTP response headers.
 * It can add headers to the current HTTP request response for several
 * purposes, such as redirecting the request to another URL, setting
 * the content type or content disposition for downloads.
 * It can also set the response status and automatically determine the
 * textual description by looking up a list of known response codes.
 * Once the list of headers is fully defined, the class issues
 * the response header output commands.
 * @link    http://www.phpclasses.org/package/5407-PHP-Compose-and-send-HTTP-response-headers.html
 * @package Scion\Http
 */
class Headers {
	use Singleton;

	/**
	 * All HTTP status codes
	 * @var array
	 */
	protected $statuscodes = [100 => 'Continue',
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
	];

	/**
	 * Flag which indicated wether the header has already been sent.
	 * @access private
	 */
	protected $sent = false;

	/**
	 * Internal header buffer.
	 * Saves all header strings.
	 * @access protected
	 */
	protected $buffer = [];

	/**
	 * Http headers values
	 * @var array
	 */
	protected $httpHeaders = [];

	/**
	 * Singleton-pattern constructor set protected to deny direct access.
	 * Only save HTTP headers.
	 * In PHP land, that means only _SERVER vars that start with HTTP_.
	 */
	protected function __construct() {
		foreach ($_SERVER as $key => $value) {
			if (substr($key, 0, 5) == 'HTTP_') {
				$this->httpHeaders[$key] = $value;
			}
		}

		$this->sent = headers_sent();
	}

	/**
	 * Desctructor
	 */
	public function __destruct() {
		return $this->sendHeader();
	}

	/**
	 * Send headers
	 * @return bool
	 */
	public function sendHeader() {
		if (!$this->sent & !headers_sent()) {
			foreach ($this->buffer as $part) {
				if (strlen($part[1]) > 0) {
					header($part[0] . ': ' . $part[1]);
				}
				else {
					header($part[0]);
				}
			}

			return true;
		}
		$this->sent = true;

		return false;
	}

	/**
	 * Add new element
	 * @param string $type
	 * @param string $value
	 * @return $this
	 * @throws \Exception
	 */
	protected function add($type, $value = '') {
		$this->sent = headers_sent();
		if (!$this->sent) {
			$type[0]        = strtoupper($type[0]);
			$this->buffer[] = [$type,
							   $value
			];
		}
		else {
			throw new \Exception('Cannot add a buffer. Header already sent.');
		}

		return $this;
	}

	/**
	 * Add new location to the buffer
	 * @param $location
	 * @return $this
	 */
	public function setLocation($location) {
		$this->add('Location', $location);

		return $this;
	}

	/**
	 * Add new content type tho the buffer
	 * @param string $media
	 * @param string $charset
	 * @return $this
	 */
	public function contentType($media, $charset = '') {
		$this->add('Content-Type', $media . (empty($charset) ? '' : '; charset=' . $charset));

		return $this;
	}

	/**
	 * Add new content disposition to the buffer
	 * @param string $filename
	 * @param string $disposition
	 * @return $this
	 */
	public function contentDisposition($filename, $disposition = 'inline') {
		$this->add('Content-Disposition', $disposition . '; filename="' . $filename . '"');

		return $this;
	}

	/**
	 * Add new status code to the buffer
	 * @param $statuscode
	 * @return $this*
	 */
	public function setStatus($statuscode) {
		$this->add('HTTP/1.1 ' . $statuscode . ' ' . $this->statuscodes[$statuscode]);

		return $this;
	}

	/**
	 * Add last modified date to the buffer
	 * @param $date
	 * @return $this
	 */
	public function lastModified($date) {
		$this->add('Last-Modified', $date);

		return $this;
	}

	/**
	 * Add entity tag to the buffer
	 * @see http://en.wikipedia.org/wiki/HTTP_ETag
	 * @param $etag
	 * @return $this
	 */
	public function setEtag($etag) {
		$this->add('Etag', $etag);

		return $this;
	}

	/**
	 * Get an array of languages accepted by the browser ordered by descending quality.
	 * @return mixed
	 */
	public function getLanguages() {
		$acceptLanguages = $this->httpHeaders['HTTP_ACCEPT_LANGUAGE'];
		$tmpLng          = [];
		$languages       = [];

		/**
		 * Break up string into pieces (languages and q factors).
		 */
		preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $acceptLanguages, $lang_parse);
		if (count($lang_parse[1])) {
			/**
			 * Create a list like "en" => 0.8.
			 */
			$tmpLng = array_combine($lang_parse[1], $lang_parse[4]);
			/**
			 * Set default to 1 for any without q factor.
			 */
			foreach ($tmpLng as $lang => $factor) {
				if ($factor === '') {
					$tmpLng[$lang] = 1;
				}
			}
			/**
			 * Sort list based on value.
			 */
			arsort($tmpLng, SORT_NUMERIC);
		}

		/**
		 * Extract most important (first) with - like fr-FR or en-US.
		 * Explode on - and make second variable UPPERCASE.
		 */
		foreach ($tmpLng as $lang => $factor) {
			if (stristr($lang, '-')) {
				list($lng, $country) = explode('-', $lang);
				$languages[$lng . '-' . strtoupper($country)] = $factor;
			}
		}

		return $languages;
	}

	/**
	 * Get the list of accepted charsets ordered by descending quality.
	 */
	public function getEncoding() {
		$encodings = $this->httpHeaders['HTTP_ACCEPT_ENCODING'];

		return explode(',', $encodings);
	}

	/**
	 * Get Http headers values
	 */
	public function getHttpHeaders() {
		return $this->httpHeaders;
	}

	/**
	 * Set a cookie
	 * @param Cookie $cookie
	 * @return int
	 */
	public function setCookie(Cookie $cookie) {
		if (setcookie($cookie->name, $cookie->value, $cookie->expire, $cookie->path, $cookie->domain, $cookie->secure, $cookie->httpOnly)) {
			return Cookie::ERROR_NONE;
		}
		return Cookie::ERROR_SET_COOKIE;
	}

	/**
	 * Removes a cookie
	 * @param string $name
	 */
	public function removeCookie($name) {
		if (isset($_COOKIE[$name])) {
			unset($_COOKIE[$name]);
			setcookie($name, null, -1, '/');
		}
	}

	/**
	 * Get a sub-service of Headers
	 * @param $service
	 * @return mixed
	 */
	public function get($service) {
		switch ($service) {

			/**
			 * Get a UserAgent object
			 */
			case 'userAgent':
				return new UserAgent($this);
				break;

			/**
			 * Get a Browser object
			 */
			case 'browser':
				return Browser::getInstance($this);
				break;

			/**
			 * Get a Platform object
			 */
			case 'platform':
				return Platform::getInstance($this);
				break;

			/**
			 * Get a Redirect object
			 */
			case 'redirect':
				return new Redirect($this);
				break;

			default:
				break;
		}
	}
}
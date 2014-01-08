<?php
namespace Scion\Http\Header;

use Scion\Mvc\GetterSetter;
use Scion\Uri\Http;

class Cookie {
	use GetterSetter;

	/**
	 * Helpers
	 */
	const SESSION = 0;
	const EXPIRE  = - 3600;
	const HOUR    = 3600;
	const DAY     = 86400;
	const WEEK    = 604800;
	const MONTH   = 2592000;
	const YEAR    = 31536000;

	/**
	 * Errors
	 */
	const ERROR_SET_COOKIE   = - 2;
	const ERROR_NONE         = 1;

	/**
	 * Properties
	 * @var
	 */
	protected $name;
	protected $value;
	protected $expire;
	protected $path;
	protected $domain;
	protected $secure;
	protected $httpOnly;

	/**
	 * Constructor
	 * @param string $name
	 * @param string $value
	 * @param int    $expire
	 * @param string $path
	 * @param null   $domain
	 * @param bool   $secure
	 * @param bool   $httpOnly
	 * @throws \InvalidArgumentException
	 */
	public function __construct($name, $value = '', $expire = self::SESSION, $path = '/', $domain = null, $secure = false, $httpOnly = true) {

		// from PHP source code
		if (preg_match("/[=,; \t\r\n\013\014]/", $name)) {
			throw new \InvalidArgumentException(sprintf('The cookie name "%s" contains invalid characters.', $name));
		}

		if (empty($name)) {
			throw new \InvalidArgumentException('The cookie name cannot be empty.');
		}

		// convert expiration time to a Unix timestamp
		if ($expire instanceof \DateTime) {
			$expire = $expire->format('U');
		}
		elseif (is_numeric($expire)) {
			$expire = time() + $expire;
		}
		else {
			$expire = strtotime($expire);

			if (false === $expire || -1 === $expire) {
				throw new \InvalidArgumentException('The cookie expiration time is not valid.');
			}
		}

		$this->name     = $name;
		$this->value    = $value;
		$this->path     = $path;
		$this->expire   = $expire;
		$this->domain   = $domain;
		$this->secure   = $secure === null ? Http::getScheme() == Http::SCHEME_HTTPS : (bool)$secure;
		$this->httpOnly = $httpOnly;
	}

	/**
	 * Returns the cookie as a string.
	 * @return string The cookie
	 */
	public function __toString() {
		$str = urlencode($this->name) . '=';

		if ('' === (string)$this->value) {
			$str .= 'deleted; expires=' . gmdate("D, d-M-Y H:i:s T", time() - 31536001);
		}
		else {
			$str .= urlencode($this->value);

			if ($this->expire !== 0) {
				$str .= '; expires=' . gmdate("D, d-M-Y H:i:s T", $this->expire);
			}
		}

		if ('/' !== $this->path) {
			$str .= '; path=' . $this->path;
		}

		if (null !== $this->domain) {
			$str .= '; domain=' . $this->domain;
		}

		if (true === $this->secure) {
			$str .= '; secure';
		}

		if (true === $this->httpOnly) {
			$str .= '; httponly';
		}

		return $str;
	}
} 
<?php
namespace Scion\Uri;

class Http extends Uri {

	const SCHEME_HTTP = 'http';
	const SCHEME_HTTPS = 'https';

	protected static $schemes = [self::SCHEME_HTTP, self::SCHEME_HTTPS];
	protected static $defaultPorts = [self::SCHEME_HTTP => 80, self::SCHEME_HTTPS => 443];
	protected static $scheme;

	/**
	 * Get scheme
	 * @return string
	 */
	public static function getScheme() {
		if (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) == 'on' || $_SERVER['HTTPS'] == 1)
		|| (isset($_SERVER['HTTP_SSL_HTTPS']) && (strtolower($_SERVER['HTTP_SSL_HTTPS']) == 'on' || $_SERVER['HTTP_SSL_HTTPS'] == 1))  ||
		(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https')) {
			return self::$scheme = self::SCHEME_HTTPS;
		}

		return self::SCHEME_HTTP;
	}

	/**
	 * Check localhost
	 * @return bool
	 */
	public static function isLocalhost() {
		return $_SERVER['HTTP_HOST'] == '127.0.0.1' || !preg_match('/(\.[\d\w]+)+/', $_SERVER['HTTP_HOST']);
	}

}
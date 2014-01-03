<?php
namespace Scion\Http;

class Server {

	const SERVER_SOFTWARE_APACHE              = 'Apache';
	const SERVER_SOFTWARE_IIS                 = 'IIS';
	const SERVER_SOFTWARE_EXPRESSIONDEVSERVER = 'ExpressionDevServer';
	const SERVER_SOFTWARE_LIGHTTPD            = 'lighttpd';
	const SERVER_SOFTWARE_LITESPEED           = 'LiteSpeed';
	const SERVER_SOFTWARE_NGINX               = 'nginx';

	private $_serverName = '';
	private $_serverVersion = 0.0;

	/**
	 * Constructor, detect server software
	 */
	public function __construct() {
		$this->_detectServerSoftware();
	}

	/**
	 * Get the name of the server software
	 * @return string
	 */
	public function getServerSoftwareName() {
		return $this->_serverName;
	}

	/**
	 * Get the version of the server software
	 * @return float
	 */
	public function getServerSoftwareVersion() {
		return $this->_serverVersion;
	}

	/**
	 * Get the information (name, version) of the server software
	 * @return string
	 */
	public function getServerSoftware() {
		return $_SERVER['SERVER_SOFTWARE'];
	}

	/**
	 * Detect the server software used
	 */
	private function _detectServerSoftware() {
		if (strpos($_SERVER['SERVER_SOFTWARE'], self::SERVER_SOFTWARE_APACHE) !== false) {
			$this->_serverName    = self::SERVER_SOFTWARE_APACHE;
			$this->_serverVersion = explode(' ', explode("/", $_SERVER['SERVER_SOFTWARE'])[1])[0];
		}
		else if (strpos($_SERVER['SERVER_SOFTWARE'], self::SERVER_SOFTWARE_IIS) !== false) {
			$this->_serverName = self::SERVER_SOFTWARE_IIS;
		}
		else if (strpos($_SERVER['SERVER_SOFTWARE'], self::SERVER_SOFTWARE_EXPRESSIONDEVSERVER) !== false) {
			$this->_serverName = self::SERVER_SOFTWARE_EXPRESSIONDEVSERVER;
		}
		else if (strpos($_SERVER['SERVER_SOFTWARE'], self::SERVER_SOFTWARE_LIGHTTPD) !== false) {
			$this->_serverName = self::SERVER_SOFTWARE_LIGHTTPD;
		}
		else if (strpos($_SERVER['SERVER_SOFTWARE'], self::SERVER_SOFTWARE_LITESPEED) !== false) {
			$this->_serverName = self::SERVER_SOFTWARE_LITESPEED;
		}
		else if (strpos($_SERVER['SERVER_SOFTWARE'], self::SERVER_SOFTWARE_NGINX) !== false) {
			$this->_serverName = self::SERVER_SOFTWARE_NGINX;
		}
	}
}
<?php
namespace Scion\Models\Http;

class Server {

	const SERVER_SOFTWARE_APACHE              = 'Apache';
	const SERVER_SOFTWARE_IIS                 = 'IIS';
	const SERVER_SOFTWARE_EXPRESSIONDEVSERVER = 'ExpressionDevServer';
	const SERVER_SOFTWARE_LIGHTTPD            = 'lighttpd';
	const SERVER_SOFTWARE_LITESPEED           = 'LiteSpeed';
	const SERVER_SOFTWARE_NGINX               = 'nginx';

	private $_serverSoftware = '';

	public function __construct() {
		$this->_detectServerSoftware();
	}

	/**
	 * Get the name of the server software
	 * @return string
	 */
	public function getServerSoftwareName() {
		return $this->_serverSoftware;
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
			$this->_serverSoftware = self::SERVER_SOFTWARE_APACHE;
		}
		else if (strpos($_SERVER['SERVER_SOFTWARE'], self::SERVER_SOFTWARE_IIS) !== false) {
			$this->_serverSoftware = self::SERVER_SOFTWARE_IIS;
		}
		else if (strpos($_SERVER['SERVER_SOFTWARE'], self::SERVER_SOFTWARE_EXPRESSIONDEVSERVER) !== false) {
			$this->_serverSoftware = self::SERVER_SOFTWARE_EXPRESSIONDEVSERVER;
		}
		else if (strpos($_SERVER['SERVER_SOFTWARE'], self::SERVER_SOFTWARE_LIGHTTPD) !== false) {
			$this->_serverSoftware = self::SERVER_SOFTWARE_LIGHTTPD;
		}
		else if (strpos($_SERVER['SERVER_SOFTWARE'], self::SERVER_SOFTWARE_LITESPEED) !== false) {
			$this->_serverSoftware = self::SERVER_SOFTWARE_LITESPEED;
		}
		else if (strpos($_SERVER['SERVER_SOFTWARE'], self::SERVER_SOFTWARE_NGINX) !== false) {
			$this->_serverSoftware = self::SERVER_SOFTWARE_NGINX;
		}
	}
}
<?php
namespace Scion\Models\Http;

use Scion\Models\Registry\WindowsRegistry;

class Request {

	const METHOD_REQUEST = 'REQUEST';
	const METHOD_GET     = 'GET';
	const METHOD_POST    = 'POST';

	private $_relativeUrlRoot = null;
	private $_requestMethod = self::METHOD_GET;
	private $_pathUrlPrefix = '';
	private $_isModeRewriteActive = false;
	private $_baseIndex;

	/**
	 * Constructor
	 */
	public function __construct() {
		// Set request method
		if (isset($_SERVER['REQUEST_METHOD'])) {
			$this->_requestMethod = $_SERVER['REQUEST_METHOD'];
		}

		// Check url rewriting is enabled
		$this->_checkUrlRewriting();
	}

	/**
	 * Get request method
	 * @return mixed
	 */
	public function getMethod() {
		return $this->_requestMethod;
	}

	/**
	 * Set request method
	 * @param $method
	 */
	public function setMethod($method) {
		$this->_requestMethod = $method;
	}

	/**
	 * Return the relative url root
	 * @return string
	 */
	public function getRelativeUrlRoot() {
		if (is_null($this->_relativeUrlRoot)) {
			$this->_relativeUrlRoot = $this->findRelativeUrlRoot();
		}

		return $this->_relativeUrlRoot;
	}

	/**
	 * Calculates relative url root
	 * @return string
	 */
	public function findRelativeUrlRoot() {
		$scriptName = !empty($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : $_SERVER['ORIG_SCRIPT_NAME'];

		return rtrim(dirname($scriptName), '/\\');
	}

	/**
	 * See if the client is using absolute uri
	 * @param string $url
	 * @return bool true, if is absolute uri otherwise false
	 */
	public function isAbsUrl($url) {
		return strpos($url, 'http') === 0;
	}

	/**
	 * Check if rewrite mode is enabled
	 * @return bool
	 */
	public function isModeRewriteActive() {
		return $this->_isModeRewriteActive;
	}

	public function getBaseIndex() {
		return $this->_baseIndex;
	}

	/**
	 * Get Path/Url without prefix and relative root
	 * @return string
	 */
	public function getPath() {
		if (!isset($_SERVER['REQUEST_URI'])) {
			return '';
		}
		$url = $_SERVER['REQUEST_URI'];

		if ($this->isAbsUrl($url)) {
			$str = $this->getUrlPrefix() . $this->getRelativeUrlRoot() . $this->_pathUrlPrefix;
		}
		else {
			$str = $this->getRelativeUrlRoot() . $this->_pathUrlPrefix;
		}
		//remove prefix
		$url = substr($url, strlen($str));

		// url rewriting not enabled
		if ($this->_isModeRewriteActive === false) {
			// save name of php base file (index.php)
			preg_match('#^/[^/]+\.php#', $url, $matches);
			if (isset($matches[0])) {
				$this->_baseIndex = $matches[0];
			}
			else {
				$this->_baseIndex = '/index.php';
			}

			// remove index.php to the url
			$url = preg_replace('#^/[^/]+\.php#', '', $url);
		}

		//remove fragment if exists
		$fragPos = strpos($url, '#');
		if ($fragPos !== false) {
			$url = substr($url, 0, $fragPos - 1);
		}

		//if url is empty return /
		return $url ? $url : '/';
	}

	/**
	 * Check URL Rewriting is enable in your web server
	 */
	private function _checkUrlRewriting() {
		$server = new Server();

		if ($server->getServerSoftwareName() == Server::SERVER_SOFTWARE_APACHE) {
			if (function_exists('apache_get_modules')) {
				$this->_isModeRewriteActive = in_array('mod_rewrite', apache_get_modules());
			}
			else {
				ob_start();
				phpinfo(INFO_MODULES);
				$contents = ob_get_contents();
				ob_end_clean();
				$this->_isModeRewriteActive = (strpos($contents, 'mod_rewrite') !== false);
			}
		}
		else if ($server->getServerSoftwareName() == Server::SERVER_SOFTWARE_IIS) {
			return (boolean)(new WindowsRegistry())->keyExists('HKEY_LOCAL_MACHINE\\SOFTWARE\\Microsoft\\IIS Extensions\\URL Rewrite');
		}
		else if ($server->getServerSoftwareName() == Server::SERVER_SOFTWARE_LITESPEED) {

		}
		else if ($server->getServerSoftwareName() == Server::SERVER_SOFTWARE_NGINX) {

		}
	}
}
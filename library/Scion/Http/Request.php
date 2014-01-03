<?php
namespace Scion\Http;

use Scion\Registry\WindowsRegistry;
use Scion\Security\Filter;
use Scion\Uri\Http;

class Request {

	const METHOD_REQUEST = 'REQUEST';
	const METHOD_GET     = 'GET';
	const METHOD_POST    = 'POST';

	private $_fixedFilesArray = [];
	private $_relativeUrlRoot = null;
	private $_requestMethod = self::METHOD_GET;
	private $_pathUrlPrefix = '';
	private $_isModeRewriteActive = false;
	private $_baseIndex;
	private $_urlPrefix;
	private $_secureUrlPrefix;
	private $_normalUrlPrefix;

	/**
	 * Constructor
	 * Get the requested method
	 * Check url rewriting
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
	 * @return void
	 */
	public function setMethod($method) {
		$this->_requestMethod = $method;
	}

	/**
	 * Set a relative url root
	 * @param $val
	 * @return void
	 */
	public function setRelativeUrlRoot($val) {
		$this->_relativeUrlRoot = $val;
	}

	/**
	 * Return the relative url root
	 * @return null|string
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

	/**
	 * Get base index php file (e.g. index.php, base.php)
	 * @return mixed
	 */
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
	 * Get url prefix
	 * @return mixed
	 */
	public function getUrlPrefix() {
		if (is_null($this->_urlPrefix)) {
			$this->_urlPrefix = $this->getDynamicUrlPrefix(Http::getScheme() == Http::SCHEME_HTTPS);
		}

		return $this->_urlPrefix;
	}

	/**
	 * Return the dynamic url prefix
	 * @param bool $isSecure
	 * @return string
	 */
	public function getDynamicUrlPrefix($isSecure = false) {
		if (!$isSecure) {
			//if not secure and already defined - return it
			if ($this->_normalUrlPrefix) {
				return $this->_normalUrlPrefix;
			}
		}
		//if its secure and already defined - return it
		else if ($this->_secureUrlPrefix) {
			return $this->_secureUrlPrefix;
		}

		if (!$isSecure) {
			$prefix       = 'http://';
			$standardPort = '80';
		}
		else {
			$prefix       = 'https://';
			$standardPort = '443';
		}
		$hostParam = explode(':', $_SERVER['HTTP_HOST']);
		if (count($hostParam) == 1) {
			$hostParam[] = isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : $standardPort;
		}
		if ($hostParam[1] == $standardPort) {
			unset($hostParam[1]);
		}

		if (!$isSecure) {
			$this->_normalUrlPrefix = $prefix . join(':', $hostParam);

			return $this->_normalUrlPrefix;
		}

		$this->_secureUrlPrefix = $prefix . join(':', $hostParam);

		return $this->_secureUrlPrefix;

	}

	/**
	 * Get var param from the request
	 * @param      $name
	 * @param null $defaultValue
	 * @param null $method
	 * @return null
	 */
	public function getVar($name, $defaultValue = null, $method = null) {
		$rv = $this->getValue($name, $method);

		return !is_null($rv) ? $rv : $defaultValue;
	}

	/**
	 * Get int param from the request
	 * @param      $name
	 * @param int  $defaultValue
	 * @param null $method
	 * @return int
	 */
	public function getInt($name, $defaultValue = 0, $method = null) {
		$rv = $this->getValue($name, $method);

		return !is_null($rv) ? intval($rv) : $defaultValue;
	}

	/**
	 * Get float param from the request
	 * @param      $name
	 * @param int  $defaultValue
	 * @param null $method
	 * @return float|int
	 */
	public function getFloat($name, $defaultValue = 0, $method = null) {
		$rv = $this->getValue($name, $method);

		return !is_null($rv) ? doubleval($rv) : $defaultValue;
	}

	/**
	 * Get string param from the request
	 * @param      $name
	 * @param null $defaultValue
	 * @param null $method
	 * @return mixed|null
	 */
	public function getString($name, $defaultValue = null, $method = null) {
		$value = $this->getValue($name, $method);
		if (is_null($value)) {
			return $defaultValue;
		}

		return Filter::clean($value);
	}

	/**
	 * Get word param from the request
	 * @param        $name
	 * @param string $defaultValue
	 * @param string $pattern
	 * @param null   $method
	 * @return null|string
	 */
	public function getWord($name, $defaultValue = '', $pattern = '/^[A-Za-z0-9]*$/', $method = null) {
		$value = $this->getValue($name, $method);
		if (is_null($value)) {
			return $defaultValue;
		}
		if (preg_match($pattern, $value)) {
			return $value;
		}

		return $defaultValue;
	}

	/**
	 * Get array param from the request
	 * @param      $name
	 * @param bool $defaultValue
	 * @param null $method
	 * @return array|bool
	 */
	public function getArray($name, $defaultValue = false, $method = null) {
		$value = $this->getValue($name, $method);

		return !is_null($value) && is_array($value) ? $value : $defaultValue;
	}

	/**
	 * Get file param from the request
	 * @param $key
	 * @return array
	 */
	public function getFiles($key) {
		if (!$this->_fixedFilesArray && count($_FILES)) {
			foreach ($_FILES as $key => $val) {
				$tmp = array();
				foreach ($val as $pFieldName => $pFieldValue) {
					if (is_array($pFieldValue)) {
						foreach ($pFieldValue as $fieldName => $fieldValue) {
							if (!isset($tmp[$fieldName])) {
								$tmp[$fieldName] = array();
							}
							$tmp[$fieldName][$pFieldName] = $fieldValue;
						}
					}
					else {
						$tmp[$pFieldName] = $pFieldValue;
					}
				}
				$this->_fixedFilesArray[$key] = $tmp;
			}
		}

		return isset($this->_fixedFilesArray[$key]) ? $this->_fixedFilesArray[$key] : array();
	}

	/**
	 * Get unspecified value param from the request
	 * @param      $name
	 * @param null $method
	 * @return null
	 */
	public function getValue($name, $method = null) {
		if ($method == null) {
			//if we didnt specified method try both $_POST AND $_GET
			//but first try from requested method
			$method = $this->_requestMethod == self::METHOD_GET ? $_GET : $_POST;
			if (isset($method[$name])) {
				return $method[$name];
			}
			$secondMethod = $method == self::METHOD_GET ? $_POST : $_GET;
			if (isset($secondMethod[$name])) {
				return $secondMethod[$name];
			}

			return null;
		}
		$method = $method == self::METHOD_GET ? $_GET : $_POST;
		if (isset($method[$name])) {
			return $method[$name];
		}

		//not defined
		return null;
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

		return false;
	}
}
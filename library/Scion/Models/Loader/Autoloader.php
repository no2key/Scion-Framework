<?php
namespace Scion\Models\Loader;

class Autoloader {

	private $_fileExtension = '.php';
	private $_namespaces = [];
	private $_namespaceSeparator = '\\';

	/**
	 * Constructor
	 */
	public function __construct() {

	}

	/**
	 * Magic method getter
	 * @param $name
	 * @return mixed
	 */
	public function __get($name) {
		return $this->$name;
	}

	/**
	 * Magic method setter
	 *
	 * @param $name
	 * @param $value
	 */
	public function __set($name, $value) {
		$this->$name = $value;
	}

	/**
	 * Installs this class loader on the SPL autoload stack.
	 */
	public function register() {
		spl_autoload_register([__CLASS__, 'loadClass']);
	}

	/**
	 * Uninstalls this class loader from the SPL autoloader stack.
	 */
	public function unregister() {
		spl_autoload_unregister([__CLASS__, 'loadClass']);
	}

	/**
	 * Scion PSR-0 autoloader
	 */
	public function loadClass($className) {
		foreach ($this->_namespaces as $namespace => $includePath) {
			if (0 === strpos($className, $namespace)) {
				$trimmedClass = substr($className, strlen($namespace));
				$filename     = $this->_transformClassNameToFilename($trimmedClass, $includePath);
				if (file_exists($filename) && !class_exists($className)) {
					require $filename;
				}
			}
		}
	}

	/**
	 * @see https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md
	 * @param string $className
	 * @param string $directory
	 * @return string
	 */
	private function _transformClassNameToFilename($className, $directory) {
		$className = ltrim($className, $this->_namespaceSeparator);
		$fileName  = '';
		if ($lastNsPos = strrpos($className, $this->_namespaceSeparator)) {
			$namespace = substr($className, 0, $lastNsPos);
			$className = substr($className, $lastNsPos + 1);
			$fileName  = str_replace($this->_namespaceSeparator, DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
		}
		$fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . $this->_fileExtension;

		// Add / at the end
		$directory = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

		return $directory . $fileName;
	}
}
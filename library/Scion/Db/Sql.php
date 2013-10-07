<?php
namespace Scion\Db;

class Sql {

	private $_instanceConfiguration = null;
	private $_instanceDriver = [];

	/**
	 * Constructor
	 * @param \stdClass $parameters
	 */
	public function __construct(\stdClass $parameters) {
		$this->_instanceConfiguration = new Configuration($parameters);
	}

	/**
	 * Get a specific driver object
	 * @return object
	 * @throws \Exception
	 */
	public function getManager() {
		$classNamespace = __NAMESPACE__ . '\Provider\\' . $this->_instanceConfiguration->getDriverClass();
		if (!array_key_exists($classNamespace, $this->_instanceDriver)) {
			try {
				$reflectionClass = new \ReflectionClass($classNamespace);
				return $this->_instanceDriver[$classNamespace] = $reflectionClass->newInstance($this->_instanceConfiguration);
			}
			catch (\ReflectionException $e) {
				throw new \Exception($e->getMessage());
			}
		}
		return $this->_instanceDriver[$classNamespace];
	}

}
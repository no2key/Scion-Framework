<?php
namespace Scion\Controllers\Routing\Http;

class Literal {

	private $_options = [];
	private $_methodContent = '';

	/**
	 * @param array $options
	 */
	public function __construct(array $options) {
		$this->_options = $options;

		$this->_loadController();
	}

	/**
	 * @param $name
	 */
	public function __get($name) {
		if (property_exists($this, $name) && (strpos($name,"pri_") !== 0) ) {
			return $this->$name;
		}
		else {

		}
	}

	/**
	 * @throws \Exception
	 */
	private function _loadController() {
		$controller = str_replace (':', '\\', $this->_options['controller']);
		$lastSpacePosition = strrpos($controller, '\\');
		$className = substr($controller, 0, $lastSpacePosition);
		$methodName = substr($controller, strrpos($controller, '\\') + 1);

		$controllerClass = new \ReflectionClass($className);
		$instance = $controllerClass->newInstance();

		if ($controllerClass->hasMethod($methodName.'Action')) {
			$this->_methodContent = (new \ReflectionMethod($instance, $methodName.'Action'))->invoke($instance);
		}
		else {
			throw new \Exception('Method '.$className.'\\'.$methodName. 'Action() not found!!!');
		}
	}
}
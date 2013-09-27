<?php
namespace Scion\Controllers\Routing\Http;

use Scion\Models\Magic;

class Controller {
	use Magic;

	private $_calledController;
	private $_calledClass;
	private $_calledMethod;
	private $_methodContent;

	/**
	 * Constructor
	 * @param $controllerName
	 */
	public function __construct($controllerName) {
		$this->_calledController = $controllerName;

		$this->_getControllerInfos();
	}

	public function __toString() {
		return $this->_calledController;
	}

	/**
	 * Retrieve name of called class & method
	 * @throws \Exception
	 */
	private function _getControllerInfos() {
		$controller          = str_replace(':', '\\', $this->_calledController);
		$lastSpacePosition   = strrpos($controller, '\\');
		$this->_calledClass  = substr($controller, 0, $lastSpacePosition);
		$this->_calledMethod = substr($controller, strrpos($controller, '\\') + 1) . 'Action';

		$controllerClass = new \ReflectionClass($this->_calledClass);
		$instance        = $controllerClass->newInstance();

		if ($controllerClass->hasMethod($this->_calledMethod)) {
			$this->_methodContent = (new \ReflectionMethod($instance, $this->_calledMethod))->invoke($instance);
		}
		else {
			throw new \Exception('Method ' . $this->_calledClass . '\\' . $this->_calledMethod . '() not found!!!');
		}
	}
}
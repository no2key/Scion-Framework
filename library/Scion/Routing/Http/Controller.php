<?php
namespace Scion\Routing\Http;

use Scion\Mvc\GetterSetter;

class Controller {
	use GetterSetter;

	private $_calledController;
	private $_calledClass;
	private $_calledMethod;
	private $_beginContent;
	private $_methodContent;
	private $_endContent;
	private $_traitNames = [];

	/**
	 * Constructor
	 * @param $controllerName
	 */
	public function __construct($controllerName) {
		$this->_calledController = $controllerName;

		$this->_getControllerInfos();
	}

	/**
	 * Get controller name
	 * @return mixed
	 */
	public function __toString() {
		return $this->_calledController;
	}

	/**
	 * Call specified controller
	 * @throws \Exception
	 */
	public function callController($format) {
		// Create a ReflectionClass
		$controllerClass = new \ReflectionClass($this->_calledClass);

		// Check current controller class use specific Trait.
		// Check also if the parent controller class use specific Trait
		if (!in_array('Scion\Mvc\Controller', $this->_traitNames)) {
			throw new \Exception('A controller must use the next valid Trait: "Scion\Mvc\Controller"');
		}

		// Check if the controller class use the Model trait
		if (in_array('Scion\Mvc\Model', $this->_traitNames)) {
			throw new \Exception('A controller cannot use the "Scion\Mvc\Model" trait');
		}

		// Create instance of the controller
		$instance = $controllerClass->newInstance();

		// call begin() method if exist
		if ($controllerClass->hasMethod('begin')) {
			$this->_beginContent = (new \ReflectionMethod($instance, 'begin'))->invoke($instance);
		}

		// Check and save content of the called method if exists, otherwise throw an Exception
		if ($controllerClass->hasMethod($this->_calledMethod)) {
			$this->_methodContent = (new \ReflectionMethod($instance, $this->_calledMethod))->invoke($instance);

			if ($format != null || (is_string($format) && $format != 'void')) {
				//Check method return something, can't be null
				if ($this->_methodContent === null) {
					throw new \Exception('A called controller need to return something not null');
				}
			}

		}
		else {
			throw new \Exception('Method ' . $this->_calledClass . '\\' . $this->_calledMethod . '() not found!!!');
		}

		// call end() method if exist
		if ($controllerClass->hasMethod('end')) {
			$this->_endContent = (new \ReflectionMethod($instance, 'end'))->invoke($instance);
		}
	}

	/**
	 * Retrieve name of called class & method
	 * @throws \Exception
	 */
	private function _getControllerInfos() {
		/**
		 * Replace ":" by "\"
		 * Get class name
		 * Get method name, suffix with "Action"
		 * Get trait names from current and parents classes
		 */
		$controller          = str_replace(':', '\\', $this->_calledController);
		$lastSpacePosition   = strrpos($controller, '\\');
		$this->_calledClass  = substr($controller, 0, $lastSpacePosition);
		$this->_calledMethod = substr($controller, strrpos($controller, '\\') + 1) . 'Action';
		$this->_getTraitNames(new \ReflectionClass($this->_calledClass));
	}

	/**
	 * Get all trait names from the current and parents classes
	 * @param $class
	 * @return array
	 */
	private function _getTraitNames($class) {
		if ($class->getParentClass() != false) {
			$this->_getTraitNames($class->getParentClass());
		}

		$this->_traitNames = array_merge($this->_traitNames, $class->getTraitNames());
	}
}
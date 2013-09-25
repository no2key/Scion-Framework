<?php
namespace HelloWorld;

class ControllerHelloWorld {

	public function indexAction() {
		return 'hello';
	}

	public function jsonAction() {
		return json_encode('json');
	}

	public function photoAction() {

	}
}
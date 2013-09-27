<?php
namespace HelloWorld;

class ControllerHelloWorld {

	public function indexAction() {

		return 'hello index';
	}

	public function jsonAction() {
		return json_encode(['json' => 256]);
	}

	public function photoAction() {

	}

	public function homeAction() {

	}
}
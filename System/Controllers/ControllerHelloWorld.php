<?php
namespace HelloWorld;

use Scion\Controllers\Controller;

class ControllerHelloWorld {
	use Controller;

	public function begin() {
		return 'Hello';
	}

	public function indexAction() {

		return 'index action method';
	}

	public function jsonAction() {
		return json_encode(['json' => 256]);
	}

	public function photoAction() {
		return 'World';
	}

	public function homeAction() {
		return '<a href="/scion'.$this->getRouter()->generate('json').'">Home</a>';
	}

	public function end() {
		return '!';
	}
}
<?php
namespace Scion\Form\Element;

use Scion\Form\Element;

class Button extends Element {

	public function render() {
		return '<button' . $this->getAttributes() . '>' . $this->filter($this->value) . '</button>';
	}

}
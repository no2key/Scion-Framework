<?php
namespace Scion\Form\Element;

use Scion\Form\Element;

abstract class Input extends Element {

	public function render() {
		return '<input' . $this->getAttributes() . $this->getValue() . '>';
	}
}
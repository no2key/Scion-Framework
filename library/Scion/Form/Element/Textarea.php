<?php
namespace Scion\Form\Element;

use Scion\Form\Element;

class Textarea extends Element {

	public function render() {
		return '<textarea' . $this->getAttributes() . '>' . $this->filter($this->value) . '</textarea>';
	}
}
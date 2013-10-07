<?php
namespace Scion\Form\Element;

use Scion\Form\Element;

class Option extends Element {

	protected $text;

	public function render() {
		return '<option' . $this->getAttributes() . $this->getValue() . '>' . $this->text . '</option>';
	}

	public function setText($text) {
		$this->text = $text;
		return $this;
	}

}
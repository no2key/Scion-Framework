<?php
namespace Scion\Form\Element\Input;

use Scion\Form\Element\Input;

class Radio extends Input {
	protected $attributes = ['type' => 'radio'];
	protected $options = [];

	public function render() {
		$str = '';
		foreach ($this->options as $value => $label) {
			$str .= '<input' . $this->getAttributes() . ' value="'.$value.'">' . $label;
		}
		return $str;
	}

	public function setOptions(array $options) {
		$this->options = $options;
	}
}
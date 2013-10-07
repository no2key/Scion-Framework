<?php
namespace Scion\Form\Element;

use Scion\Form\Element;

class OptGroup extends Element {
	protected $options = [];

	public function render() {
		$str = '<optgroup' . $this->getAttributes() . '>';

		foreach ($this->options as $option) {
			$str .= $option->render();
		}

		$str .= '</optgroup>';
		return $str;
	}

	public function addOption(Option $option) {
		$this->options[] = $option;

		return $this;
	}

	public function setLabel($label) {
		$this->attributes['label'] = $label;
	}
}
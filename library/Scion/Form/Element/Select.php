<?php
namespace Scion\Form\Element;

use Scion\Form\Element;

class Select extends Element {
	protected $options = [];
	protected $optGroup = [];

	public function render() {
		$str = '<select' . $this->getAttributes() . '>';

		foreach ($this->optGroup as $optGroup) {
			$str .= $optGroup->render();
		}

		foreach ($this->options as $option) {
			$str .= $option->render();
		}

		$str .= '</select>';
		return $str;
	}

	public function addOptGroup(OptGroup $optGroup) {
		$this->optGroup[] = $optGroup;
		return $this;
	}

	public function addOption(Option $option) {
		$this->options[] = $option;
		return $this;
	}
}
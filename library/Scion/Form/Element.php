<?php
namespace Scion\Form;

abstract class Element extends Base {
	protected $value;

	/**
	 * Set the element value
	 * @param $value
	 * @return $this
	 */
	public function setValue($value) {
		$this->value = $value;

		return $this;
	}

}
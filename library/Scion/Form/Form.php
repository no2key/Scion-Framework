<?php
namespace Scion\Form;

use Scion\Form\Element\Input\File;

class Form extends Base {

	protected $content;
	protected $attributes = [];
	protected $elements = [];

	/**
	 * Set form action
	 * @param $action
	 * @return $this
	 */
	public function setAction($action) {
		$this->attributes['action'] = $action;

		return $this;
	}

	/**
	 * Set form method
	 * @param $method
	 * @return $this
	 */
	public function setMethod($method) {
		$this->attributes['method'] = $method;

		return $this;
	}

	/**
	 * Add new element
	 * @param Element $element
	 * @return $this
	 */
	public function add(Element $element) {
		$this->elements[] = $element;

		// For ease-of-use, the form tag's enctype attribute is automatically set if the File element class is added.
		if($element instanceof File) {
			$this->attributes['enctype'] = 'multipart/form-data';
		}
		return $this;
	}

	/**
	 * Return the full form
	 * @return string
	 */
	public function createView() {
		$this->content = '<form' . $this->getAttributes() . '>';

		foreach ($this->elements as $element) {
			$this->content .= $element->render();
		}

		$this->content .= '</form>';

		return $this->content;
	}
}
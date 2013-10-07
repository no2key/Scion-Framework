<?php
namespace Scion\Form;

abstract class Base {

	/**
	 * Constructor
	 * @param null $name
	 */
	public function __construct($name = null) {
		if ($name !== null) {
			$this->attributes['name'] = $name;
		}
	}

	/**
	 * Set an attribute
	 * @param string $key
	 * @param string $value
	 * @return $this
	 */
	public function setAttribute($key, $value = '') {
		// Do not include the value in the list of attributes
		if ($key === 'value') {
			$this->value = $value;

			return $this;
		}
		$this->attributes[$key] = $value;

		return $this;
	}

	/**
	 * Set an array of attributes
	 * @param array $attributes
	 * @return $this
	 */
	public function setAttributes(array $attributes) {
		$this->attributes = array_merge($this->attributes, $attributes);

		return $this;
	}

	/**
	 * This method is used by the Form class and all Element classes to return a string of html attributes.
	 * There is an ignore parameter that allows special attributes from being included.
	 * @return string
	 */
	public function getAttributes() {
		$str = '';
		if (!empty($this->attributes)) {
			foreach ($this->attributes as $attr => $value) {
				$str .= ' ' . $attr;
				if ($value !== '') {
					$str .= '="' . $this->filter($value) . '"';
				}
			}
		}

		return $str;
	}

	/**
	 * Get passed value
	 * @return string
	 */
	public function getValue() {
		if ($this->filter($this->value) != '') {
			return ' value="' . $this->filter($this->value) . '"';
		}

		return '';
	}

	/**
	 * This method prevents double/single quotes in html attributes from breaking the markup.
	 * @param $str
	 * @return null|string
	 */
	protected function filter($str) {
		if (is_scalar($str)) {
			return htmlspecialchars($str);
		}

		return null;
	}

}
<?php
namespace Scion\Models;

trait Magic {

	/**
	 * Provide magic method getter
	 * @param $name
	 * @return null
	 */
	public function __get($name) {
		return (isset($this->$name)) ? $this->$name : null;
	}

	/**
	 * Provide magic method setter
	 * @param $name
	 * @param $value
	 * @throws \Exception
	 */
	public function __set($name, $value) {
		if (false === property_exists(get_class(), $name)) {
			throw new \Exception(get_class() . " does not have '" . $name . "' property.");
		}
		else {
			$this->$name = $value;
		}
	}

}
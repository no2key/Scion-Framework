<?php
namespace Scion\Mvc;

trait ModelController {

	/**
	 * Get service object
	 * @param $id
	 * @return mixed
	 */
	final public function get($id) {
		return $this->__getService($id, func_get_args());
	}

} 
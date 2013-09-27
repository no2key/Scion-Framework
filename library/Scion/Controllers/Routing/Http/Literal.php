<?php
namespace Scion\Controllers\Routing\Http;

use Scion\Models\Magic;

class Literal {
	use Magic;

	private $_options = [];

	/**
	 * @param \stdClass $options
	 */
	public function __construct(\stdClass $options) {
		$this->_options = (array) $options;
	}

}
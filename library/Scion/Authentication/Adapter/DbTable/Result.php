<?php
namespace Scion\Authentication\Adapter\DbTable;

class Result {

	protected $code;


	public function __construct() {

	}

	public function getCode() {
		return $this->code;
	}
}
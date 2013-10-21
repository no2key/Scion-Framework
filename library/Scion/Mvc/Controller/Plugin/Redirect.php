<?php
namespace Scion\Mvc\Controller\Plugin;

use Scion\Http\Header;

class Redirect {

	/**
	 * Redirect to a specific location
	 * @param $routeUrl
	 */
	public function to($routeUrl) {
		$header = Header::getInstance();
		$header->setLocation($routeUrl)->sendHeader();
	}

}
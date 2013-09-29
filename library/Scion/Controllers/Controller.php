<?php
namespace Scion\Controllers;

use Scion\Models\Loader\RouteLoader;

trait Controller {

	final public function getRouter() {
		return RouteLoader::getRouter();
	}
}
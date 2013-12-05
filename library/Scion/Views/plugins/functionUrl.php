<?php
namespace Scion\Views\plugins;

use Dwoo\Core;
use Scion\Loader\RouteLoader;
use Scion\Routing\Router;

function functionUrl(Core $core, $name, $array = []) {
	$params = [];

	if (! empty($array)) {
		$array = explode(',', $array);
		foreach ($array as $value) {
			$entry = explode(':', $value);
			if (is_numeric($entry[1])) {
				$params[$entry[0]] = (int)$entry[1];
			}
			else {
				$params[$entry[0]] = $entry[1];
			}
		}
	}

	try {
		return RouteLoader::getRouter()->generate($name, $params);
	}
	catch (\Exception $e) {
		//var_dump($e);
	}

	return null;
}
<?php
// Require Scion framework
require 'library/Scion/Scion.php';

// Register autoloader
\Scion\Scion::registerAutoloader('config/autoload.json');

// Instantiate a Scion application
$app = new \Scion\Scion();

$oRequest = new \Scion\Controllers\Routing\Request();
$oRouter = new \Scion\Controllers\Routing\Router();

$json = \Scion\Models\File\Json::decode(file_get_contents('config/routing.json'), true);
$routes = [];
foreach ($json['routes'] as $route => $values) {
	$routes[] = new \Scion\Controllers\Routing\Route($route, $values);
}
$oRouter->addRoutes($routes);

try {
	if ($oRouter->match($oRequest->getPath())) {
		echo 'Matched route with name: <b>' . $oRouter->getMatchedRoute()->getName() . '</b><br />';
		echo 'Parameters are: <br />';
		foreach ($oRouter->getParameters() as $key => $value) {

			/**
			 * Call controller
			 */
			if ($key == 'controller') {
				$literal = new \Scion\Controllers\Routing\Http\Literal(['controller' => $value]);

				// Specific format
				if (isset($oRouter->getParameters()['format'])) {
					$format = new \Scion\Controllers\Routing\Http\Format($literal->_methodContent, $oRouter->getParameters()['format']);

					if ($format->_valid === false)
					exit('There is no match for this route (bad format)!!!<br />');
				}

				// Specific method
				if (isset($oRouter->getParameters()['method'])) {

				}

				// Specific scheme
				if (isset($oRouter->getParameters()['scheme'])) {

				}

				// Specific regex
				if (isset($oRouter->getParameters()['regex'])) {

				}
			}

			echo $key . ' = <b>('.gettype($value).')</b>' . $value . '<br />';
		}
	}
	else {
		echo 'There is no match for this route!!!<br />';
	}

}
catch (icException $e) {
	echo 'Error: ' . $e->getMessage() . '<br />';
}

echo round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 3);

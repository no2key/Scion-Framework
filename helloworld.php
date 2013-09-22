<?php
// Require Scion framework
require 'library/Scion/Scion.php';

// Register autoloader
\Scion\Scion::registerAutoloader();

// Instantiate a Scion application
$app = new \Scion\Scion();

new \Dwoo\Core();
echo '<br>';
new \Scion\Views\TemplateEngine();

echo '<br><br>'. round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 3);
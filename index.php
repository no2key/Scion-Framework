<?php
// Require Scion framework
require 'library/Scion/Scion.php';

// Instantiate a Scion application
$app = new \Scion\Scion('config/configuration.json');

echo '<br><br><strong>'.round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']), 3) . 'seconds</strong>';

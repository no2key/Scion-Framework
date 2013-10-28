<?php
namespace Scion\Views\plugins;

use Dwoo\Core;
use Scion\Http\Request;

function functionJavascriptGlobals(Core $core) {

	$request = new Request();
	$dwoo_root = $request->getDynamicUrlPrefix() . $request->getRelativeUrlRoot() . DIRECTORY_SEPARATOR;

	$javascript = <<<JS
<script>
<!--
var dwoo_root = '{$dwoo_root}';
-->
</script>
JS;

	return $javascript;
}
<?php
namespace Scion\Authentication\Adapter\HybridAuth\thirdparty\Viadeo;

class ViadeoHelper {
	// == Helper tools ========================================================
	// ========================================================================

	// Retrieve the current page URL ------------------------------------------
	public static function getCurrentURL() {
		$pageURL = 'http';
		if (isset($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"] == "on")) {
			$pageURL .= "s";
		}
		$pageURL .= "://";

		$pageURL .= $_SERVER["SERVER_NAME"];
		if ($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= ":" . $_SERVER["SERVER_PORT"];
		}
		$pageURL .= $_SERVER["SCRIPT_NAME"];

		return $pageURL;
	}
}
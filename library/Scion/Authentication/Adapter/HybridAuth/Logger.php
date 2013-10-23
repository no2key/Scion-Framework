<?php
namespace Scion\Authentication\Adapter\HybridAuth;

use Scion\Stdlib\DateTime;

/**
 * Debugging and Logging manager
 */
class Logger {
	function __construct() {
		// if debug mode is set to true, then check for the writable log file
		if (Auth::$config['debug']['enabled']) {
			if (!file_exists(Auth::$config['debug']['file'])) {
				throw new \Exception("['debug']['mode'] is set to 'true', but the file " . Auth::$config['debug']['file'] . " in ['debug']['file'] does not exit.", 1);
			}

			if (!is_writable(Auth::$config['debug']['file'])) {
				throw new \Exception("['debug']['mode'] is set to 'true', but the given log file path ['debug']['file'] is not a writable file.", 1);
			}
		}
	}

	public static function debug($message, $object = null) {
		if (Auth::$config['debug']['enabled']) {
			$datetime = new DateTime();
			$datetime = $datetime->format(DATE_ATOM);

			file_put_contents(Auth::$config['debug']['file'], "DEBUG -- " . $_SERVER['REMOTE_ADDR'] . " -- " . $datetime . " -- " . $message . " -- " . print_r($object, true) . "\n", FILE_APPEND);
		}
	}

	public static function info($message) {
		if (Auth::$config['debug']['enabled']) {
			$datetime = new DateTime();
			$datetime = $datetime->format(DATE_ATOM);

			file_put_contents(Auth::$config['debug']['file'], "INFO -- " . $_SERVER['REMOTE_ADDR'] . " -- " . $datetime . " -- " . $message . "\n", FILE_APPEND);
		}
	}

	public static function error($message, $object = null) {
		if (Auth::$config['debug']['enabled']) {
			$datetime = new DateTime();
			$datetime = $datetime->format(DATE_ATOM);

			file_put_contents(Auth::$config['debug']['file'], "ERROR -- " . $_SERVER['REMOTE_ADDR'] . " -- " . $datetime . " -- " . $message . " -- " . print_r($object, true) . "\n", FILE_APPEND);
		}
	}
}

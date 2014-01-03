<?php
namespace Scion\File;

class Json {

	/**
	 * Returns the JSON representation of a value
	 * @param     $value
	 * @param int $options
	 * @param int $depth
	 *
	 * @return string
	 */
	public static function encode($value, $options = 0, $depth = 512) {
		return json_encode($value, $options, $depth);
	}

	/**
	 * Decodes a JSON string
	 *
	 * @param      $json
	 * @param bool $assoc
	 * @param int  $depth
	 * @param int  $options
	 *
	 * @return mixed
	 */
	public static function decode($json, $assoc = false, $depth = 512, $options = 0) {
		return json_decode($json, $assoc, $depth, $options);
	}

	/**
	 * Process PSR-0 autoload json file, return an array of values
	 * @param string $jsonUrl
	 * @param bool $default
	 * @return array
	 */
	public static function processConfigAutoload($jsonUrl, $default = false) {
		$data   = [];
		$content = file_get_contents($jsonUrl);

		if ($content != '') {
			foreach (self::decode($content, true)['autoload']['psr-0'] as $namespace => $includePath) {
				if ($default === true) {
					$data[$namespace] = dirname(dirname(SCION_DIR)) . DIRECTORY_SEPARATOR . $includePath;
				}
				else {
					$data[$namespace] = $includePath;
				}
			}
		}

		return $data;
	}
}
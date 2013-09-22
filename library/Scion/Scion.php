<?php
/**
 * Scion - a PHP5.5+ framework
 *
 * @author David Sanchez <david38.sanchez@gmail.com>
 * @Copyright 2013 David Sanchez
 * @version 1.0
 * @package Scion
 */
namespace Scion;

use Scion\Models\Loader\Autoloader;

define('SCION_DIR', __DIR__ . DIRECTORY_SEPARATOR);

class Scion {

	/**
	 * @const float Version number
	 */
	const VERSION = 1.0;

	/**
	 * Register Scion's PSR-0 autoloader
	 */
	public static function registerAutoloader() {
		require __DIR__ . '/Models/Loader/Autoloader.php';
		$autoloader = new Autoloader();
		$autoloader->_namespaces = [
			'Dwoo'	=> dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Dwoo',
			'Scion'	=> dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Scion'
		];
		$autoloader->register();
	}

}
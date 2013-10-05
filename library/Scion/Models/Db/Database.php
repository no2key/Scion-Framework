<?php
namespace Scion\Models\Db;

use Scion\Models\File\Json;

class Database {
	private static $_parameters = [];
	private static $_instanceSql = [];

	/**
	 * Parse config file
	 * @param $configFile
	 */
	public static function init($configFile) {
		$databases = Json::decode(file_get_contents($configFile));

		foreach ($databases->databases as $name => $parameters) {
			self::$_parameters[$name] = $parameters;
		}
	}

	public static function initSql($name = 'default') {
		if (!array_key_exists($name, self::$_instanceSql)) {
			try {
				return self::$_instanceSql[$name] = new Sql(self::$_parameters[$name]);
			}
			catch (\ReflectionException $e) {
				throw new \Exception($e->getMessage());
			}
		}

		return self::$_instanceSql[$name];
	}


	public static function initNoSql($name = 'default') {

	}
}
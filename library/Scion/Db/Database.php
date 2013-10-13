<?php
namespace Scion\Db;

use Scion\File\Json;
use Scion\Scion;

class Database {
	private static $_parameters = [];
	private static $_instanceSql = [];

	public static function initSql($name = 'default') {
		if (!array_key_exists($name, self::$_instanceSql)) {
			try {
				$databases = Json::decode(file_get_contents(Scion::getJsonConfiguration()->configuration->framework->database));

				foreach ($databases->databases as $name => $parameters) {
					self::$_parameters[$name] = $parameters;
				}

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

	public static function getSqlInstance($name = 'default') {
		return self::$_instanceSql[$name];
	}
}
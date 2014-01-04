<?php
namespace Scion\Db;

use Scion\File\Json;
use Scion\Scion;

class Database {
	private static $_parameters = [];
	private static $_instanceSql = [];

	/**
	 * Initialize SQL object
	 * @param string $name
	 * @return Sql
	 * @throws \Exception
	 */
	public static function initSql($name = 'default') {
		if (!array_key_exists($name, self::$_instanceSql)) {
			$configuration = Scion::getJsonConfiguration();
			if (property_exists($configuration, 'configuration')
				&& property_exists($configuration->configuration, 'framework')
				&& property_exists($configuration->configuration->framework, 'database')) {
				$databases = Json::decode(file_get_contents(Scion::getJsonConfiguration()->configuration->framework->database));

				foreach ($databases->databases as $name => $parameters) {
					self::$_parameters[$name] = $parameters;
				}

				return self::$_instanceSql[$name] = new Sql(self::$_parameters[$name]);
			}
			else {
				throw new \Exception('Do you forgot to configure the database ?');
			}
		}

		return self::$_instanceSql[$name];
	}

	/**
	 * Initialize NoSQL object
	 * @param string $name
	 * @return null
	 */
	public static function initNoSql($name = 'default') {
		return null;
	}

	/**
	 * Get an instance of the SQL object
	 * @param string $name
	 * @return mixed
	 */
	public static function getSqlInstance($name = 'default') {
		return self::$_instanceSql[$name];
	}
}
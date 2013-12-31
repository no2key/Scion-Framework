<?php
namespace Scion\Mvc;

use Scion\Authentication\AuthenticationService;
use Scion\Db\Database;
use Scion\Permissions\Rbac\Rbac;

trait Model {
	use ModelController;

	protected function __getService($service, $parameters = []) {
		switch ($service) {
			/**
			 * Get class name using this controller trait
			 * @return string
			 */
			case 'class':
				return __CLASS__;
				break;

			/**
			 * Get SQL provider (mysql, sqlite, ...) object
			 * @return \Scion\Db\Sql
			 */
			case 'sql':
				$instance = 'default';
				if (isset($parameters[1])) {
					$instance = $parameters[1];
				}
				return Database::initSql($instance);
				break;

			/**
			 * Get NoSQL object
			 * @return \Scion\Db\NoSql
			 */
			case 'no-sql':
				$instance = 'default';
				if (isset($parameters[1])) {
					$instance = $parameters[1];
				}
				return Database::initNoSql($instance);
				break;

			/**
			 * Get an AuthenticationService object
			 * @return \Scion\Authentication\AuthenticationService
			 */
			case 'authentication':
				return AuthenticationService::getInstance();
				break;

			/**
			 * Get a Rbac object
			 * @return \Scion\Permissions\Rbac\Rbac
			 */
			case 'rbac':
				$name = 'default';
				if (isset($parameters[1])) {
					$name = $parameters[1];
				}
				return new Rbac($name);
				break;

			case 'acl':
				break;

			default:
				break;
		}
	}
}
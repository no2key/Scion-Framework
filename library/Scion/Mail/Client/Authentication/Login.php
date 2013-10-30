<?php
namespace Scion\Mail\Client\Authentication;

use Scion\Mail\Client\Connection;
use Scion\Mail\Client\Authentication;
use Scion\Mail\Client\Authentication\AbstractAuthentication;
use \RuntimeException;
use Scion\Mail\Client\CommandInvoker;
use Scion\Mail\Client\Command\InputCommand;
use Scion\Mail\Client\Command\AUTHCommand;

class Login extends AbstractAuthentication implements Authentication {

	/**
	 * Perform an AUTH LOGIN in SMTP server to authenticate the user.
	 *
	 * @param Connection $connection the connection with SMTP server
	 *
	 * @link http://www.ietf.org/rfc/rfc2554.txt
	 * @return boolean
	 */
	public function authenticate(Connection $connection) {
		$username = $this->getUsername();
		$password = $this->getPassword();

		$invoker = new CommandInvoker();
		$invoker->invoke(new AUTHCommand($connection, "LOGIN"));
		$invoker->invoke(new InputCommand($connection, base64_encode($username)));

		if ($connection->read()->getCode() === Authentication::ACCEPTED) {
			$invoker->invoke(new InputCommand($connection, base64_encode($password)));

			return $connection->read()->getCode() === Authentication::AUTHENTICATION_PERFORMED;
		}
		return false;
	}

}
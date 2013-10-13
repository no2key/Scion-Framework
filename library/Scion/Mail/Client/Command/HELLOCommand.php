<?php

/**
 * @package    utils.net.SMTP.Client.Command
 * @author     Andrey Knupp Vital <andreykvital@gmail.com>
 * @filesource Scion\Mail\Client\Command\HELLOCommand.php
 */

namespace Scion\Mail\Client\Command;
use Scion\Mail\Client\AbstractCommand;
use \RuntimeException;

abstract class HELLOCommand extends AbstractCommand {

	/**
	 * Performs a specific command (HELO or EHLO) in the SMTP server.
	 *
	 * @throws RuntimeException if the command wasn't executed successfully
	 * @return boolean
	 */
	public function performEhloHelo($command) {
		if ($this->connection->write(sprintf("%s %s", $command, $this->connection->getHostname()))) {
			$response = $this->connection->read();
			if ($response->getCode() === 250) {
				return true;
			}
		}
		return false;
	}

}
<?php
namespace Scion\Mail\Client;

interface Command {

	/**
	 * Executes an given command
	 *
	 * @return void
	 */
	public function execute();
}
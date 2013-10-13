<?php
namespace Scion\Mail\Client\Connection;
use Scion\Mail\Client\AbstractConnection;

class SSLConnection extends AbstractConnection {

	/**
	 * Opens a connection with SMTP server using SSL protocol
	 *
	 * @param string  $host    valid SMTP server hostname
	 * @param integer $port    the SMTP server port
	 * @param integer $timeout timeout in seconds for wait a connection.
	 */
	public function __construct($hostname, $port, $timeout = 30) {
		parent::__construct();
		$this->open("ssl", $hostname, $port, $timeout);
	}

}
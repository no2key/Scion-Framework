<?php

namespace Scion\Mail\Client;
use Scion\Mail\Client\ConnectionState;
use Scion\Mail\Client\Authentication;

interface Connection {

	/**
	 * Authenticates an user in the SMTP server.
	 *
	 * @param Authentication $authentication the authentication mechanism to be used.
	 *
	 * @return boolean
	 */
	public function authenticate(Authentication $authentication);

	/**
	 * Opens a connection with an SMTP server.
	 *
	 * @param string  $protocol the protocol to be used to connect
	 * @param string  $hostname the server hostname to connect
	 * @param integer $port     the server listening port
	 * @param integer $timeout  the timeout to wait for a connection
	 *
	 * @return boolean|void
	 */
	public function open($protocol, $hostname, $port, $timeout = 30);

	/**
	 * Changes the connection state to a new one.
	 *
	 * @param ConnectionState $state the new state of the connection.
	 *
	 * @return void
	 */
	public function changeState(ConnectionState $connectionState);

	/**
	 * Closes the opened connection with the server
	 *
	 * @return void
	 */
	public function close();

	/**
	 * Writes data on the server stream.
	 *
	 * @param string $data the data to be written
	 *
	 * @return integer
	 */
	public function write($data);

	/**
	 * Reads a server reply.
	 *
	 * @return Message
	 */
	public function read();
}

    
<?php

namespace Scion\Mail\Client;
use Scion\Mail\Client\Connection;
use Scion\Mail\Client\Connection\State\Closed;
use Scion\Mail\Client\ConnectionState;
use Scion\Mail\Client\CommandInvoker;
use Scion\Mail\Client\Command\EHLOCommand;
use Scion\Mail\Client\Command\HELOCommand;
use \Exception;

abstract class AbstractConnection implements Connection {

	/**
	 * Current connection state
	 *
	 * @var ConnectionState
	 */
	private $state;

	/**
	 * Server name that we are connected (not resolved)
	 *
	 * @var string
	 */
	private $hostname;

	/**
	 * Sets the initial state of connection (Closed).
	 *
	 * @return Connection
	 */
	public function __construct() {
		$this->state = new Closed();
	}

	/**
	 * Changes the connection state to a new one.
	 *
	 * @param ConnectionState $state the new state of the connection.
	 *
	 * @return void
	 */
	public function changeState(ConnectionState $state) {
		$this->state = $state;
	}

	/**
	 * Opens a connection with an SMTP server.
	 *
	 * @param string  $protocol the protocol to be used to connect
	 * @param string  $hostname the server hostname to connect
	 * @param integer $port     the server listening port
	 * @param integer $timeout  the timeout to wait for a connection
	 *
	 * @throws Exception if the server greeting wasn't received successfully
	 * @return boolean|void
	 */
	public function open($protocol, $hostname, $port, $timeout = 30) {
		if ($this->state->open($protocol, $hostname, $port, $timeout, $this)) {
			$greeting = $this->read();
			if (($code = $greeting->getCode()) !== 220) {
				$message = "We haven't received the expected greeting";
				throw new Exception($message, $code);
			}

			$this->hostname = $hostname;
			$invoker        = new CommandInvoker();
			$invoker->invoke(new EHLOCommand($this));
			$invoker->invoke(new HELOCommand($this));

			return true;
		}
		return false;
	}

	/**
	 * Closes the opened connection with the server
	 *
	 * @return void
	 */
	public function close() {
		$this->state->close($this);
	}

	/**
	 * Reads a server reply.
	 *
	 * @return Message
	 */
	public function read() {
		return $this->state->read();
	}

	/**
	 * Writes data on the server stream.
	 *
	 * @param string $data the data to be written
	 *
	 * @return integer
	 */
	public function write($data) {
		return $this->state->write($data);
	}

	/**
	 * Authenticates an user in the SMTP server.
	 *
	 * @param Authentication $authentication the authentication mechanism to be used.
	 *
	 * @return boolean
	 */
	public function authenticate(Authentication $authentication) {
		return $this->state->authenticate($authentication, $this);
	}

	/**
	 * Retrieves the hostname of smtp server.
	 *
	 * @return string
	 */
	public function getHostname() {
		return $this->hostname;
	}

	/**
	 * Retrieves the stream connected with SMTP server.
	 *
	 * @return resource
	 */
	public function getStream() {
		return $this->state->getStream();
	}

	/**
	 * Retrieves the latest exchanged message with the server.
	 *
	 * @return Message
	 */
	public function getLatestMessage() {
		return $this->state->getLatestMessage();
	}

	/**
	 * Retrieves all exchanged messages with the server.
	 *
	 * @return array[Message]
	 */
	public function getExchangedMessages() {
		return $this->state->getExchangedMessages();
	}

}
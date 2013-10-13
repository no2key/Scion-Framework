<?php

    /**
     * @package utils.net.SMTP.Client
     * @author Andrey Knupp Vital <andreykvital@gmail.com>
     * @filesource Scion\Mail\Client\AbstractConnectionState.php
     */
    namespace Scion\Mail\Client;
    use Scion\Mail\Client\ConnectionState;
    use Scion\Mail\Client\Authentication;
    use \BadMethodCallException;
    use \RuntimeException;

    abstract class AbstractConnectionState implements ConnectionState
    {

        /**
         * Client stream connected to the SMTP server
         * @var resource
         */
        protected $stream;

        /**
         * Latest received message from the server.
         * @var Message
         */
        protected $lastMessage = NULL;

        /**
         * All messages exchanged with server.
         * @var array[Message]
         */
        protected $messages = array();

        /**
         * Authenticates the user with specified authentication method.
         * @param Authentication $authentication the authentication method to authenticate
         * @param Connection $context the connection with SMTP server
         * @throws RuntimeException if the user wasn't authenticated
         * @return boolean
         */
        public function authenticate(Authentication $authentication, Connection $context)
        {
            throw new BadMethodCallException("Method not implemented");
        }

        /**
         * Closes the connection with SMTP server
         * @param Connection $context the connection context
         * @throws BadMethodCallException
         */
        public function close(Connection $context)
        {
            throw new BadMethodCallException("Method not implemented");
        }

        /**
         * Opens an connection with SMTP server.
         * @param string $protocol the protocol to be used
         * @param string $hostname the SMTP server hostname
         * @param integer $port smtp server listening port
         * @param integer $timeout an valid timeout to wait for the connection
         * @param Connection $context the connection context
         * @throws \BadMethodCallException
         */
        public function open($protocol, $hostname, $port, $timeout = 30, Connection $context)
        {
            throw new BadMethodCallException("Method not implemented");
        }

        /**
         * Reads a server reply/message.
         * @return Message
         */
        public function read()
        {
            throw new BadMethodCallException("Method not implemented");
        }

        /**
         * Writes data on the server stream.
         * @param string $data the data to be written
         * @return boolean
         */
        public function write($data)
        {
            throw new BadMethodCallException("Method not implemented");
        }

        /**
         * Retrieves the stream connected with SMTP server.
         * @return resource|boolean
         */
        public function getStream()
        {
            if(is_resource($this->stream)) {
                if (($type = get_resource_type($this->stream)) === "stream") {
                    return $this->stream;
                }
                
                $message = "Trying to get stream, but the resource type: %s, wasn't expected";
                throw new RuntimeException(sprintf($message, $type));
            }

            return false;
        }

        /**
         * Retrieves the latest exchanged message with the server.
         * @return Message
         */
        public function getLatestMessage()
        {
            return $this->lastMessage;
        }

        /**
         * Retrieves all exchanged messages with the server.
         * @return array[Message]
         */
        public function getExchangedMessages()
        {
            return $this->messages;
        }

        /**
         * Changes the connection state to a new one.
         * @param ConnectionState $state the new connection state
         * @param Connection $context the context where state is changeable
         */
        protected function changeState(ConnectionState $state, Connection $context)
        {
            $state->stream = $this->stream;
            $state->messages = $this->messages;
            $state->lastMessage = $this->lastMessage;
            $context->changeState($state);
        }

    }
<?php

    /**
     * @package utils.net.SMTP.Client
     * @author Andrey Knupp Vital <andreykvital@gmail.com>
     * @filesource Scion\Mail\Client\ConnectionState.php
     */
    namespace Scion\Mail\Client;
    use Scion\Mail\Client\Connection;
    use Scion\Mail\Client\Authentication;
    
    interface ConnectionState
    {

        /**
         * Reads a server reply
         * @return Message
         */
        public function read();

        /**
         * Writes data on the server stream
         * @param string $data the data to be written
         * @return boolean
         */
        public function write($data);

        /**
         * Closes the connection with SMTP server
         * @param Connection $context the connection context
         * @return void
         */
        public function close(Connection $context);

        /**
         * Opens the connection with SMTP server
         * @param string $protocol the protocol to be used
         * @param string $hostname the SMTP server hostname
         * @param integer $port smtp server listening port
         * @param integer $timeout an valid timeout to wait for the connection
         * @param Connection $context the connection context
         */
        public function open($protocol, $hostname, $port, $timeout = 30, Connection $context);

        /**
         * Authenticates an user using provided authentication method
         * @param Authentication $authentication the authentication mechanism
         * @param Connection $context client connection with the server
         */
        public function authenticate(Authentication $authentication, Connection $context);

        /**
         * Retrieves the stream connected with SMTP server.
         * @return resource
         */
        public function getStream();

        /**
         * Retrieves the latest exchanged message with the server.
         * @return Message
         */
        public function getLatestMessage();

        /**
         * Retrieves all exchanged messages with the server.
         * @return array[Message]
         */
        public function getExchangedMessages();
    }
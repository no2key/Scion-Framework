<?php

    /**
     * @package utils.net.SMTP.Client
     * @author Andrey Knupp Vital <andreykvital@gmail.com>
     * @filesource Scion\Mail\Client\Authentication.php
     */
    namespace Scion\Mail\Client;
    use Scion\Mail\Client\Connection;

    interface Authentication
    {
        /**
         * Response code that means a authentication was performed successfully.
         * @const integer
         */

        const AUTHENTICATION_PERFORMED = 235;

        /**
         * When a SMTP server cannot perform authentication with specified mechanism
         * it would be return a message with response code 504 (Unrecognized authentication type).
         * @const integer
         */
        const UNRECOGNIZED_AUTHENTICATION_TYPE = 504;

        /**
         * When authentication step was accepted or the provided authentication mechanism was accepted. 
         * The server replies with a response containing code 334, it means the acceptance.
         * @const integer
         */
        const ACCEPTED = 334;

        /**
         * Performs the client authentication on the server.
         * 
         * @param Connection $connection the connection to authenticate
         * @return void|boolean
         */
        public function authenticate(Connection $connection);
    }
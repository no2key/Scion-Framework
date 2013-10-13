<?php

    /**
     * @package utils.net.SMTP.Client.Authentication
     * @author Andrey Knupp Vital <andreykvital@gmail.com>
     * @filesource Scion\Mail\Client\Authentication\Plain.php
     */

    namespace Scion\Mail\Client\Authentication;

use Scion\Mail\Client\Connection;
use Scion\Mail\Client\Authentication\AbstractAuthentication;
use Scion\Mail\Client\Authentication;
use \RuntimeException;
use Scion\Mail\Client\CommandInvoker;
use Scion\Mail\Client\Command\InputCommand;
use Scion\Mail\Client\Command\AUTHCommand;

    class Plain extends AbstractAuthentication implements Authentication
    {

        /**
         * Perform an AUTH PLAIN in SMTP server to authenticate the user.
         * @param Connection $connection the connection with SMTP server
         * @link http://www.ietf.org/rfc/rfc2554.txt
         * @return boolean
         */
        public function authenticate(Connection $connection)
        {
            $username = $this->getUsername();
            $password = $this->getPassword();

            $invoker = new CommandInvoker();
            $invoker->invoke(new AUTHCommand($connection, "PLAIN"));
            $invoker->invoke(new InputCommand($connection, base64_encode(sprintf("\0%s\0%s", $username, $password))));
            return $connection->read()->getCode() === Authentication::AUTHENTICATION_PERFORMED;
        }

    }
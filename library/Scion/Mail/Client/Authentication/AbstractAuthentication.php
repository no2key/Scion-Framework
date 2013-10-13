<?php

    /**
     * @package utils.net.SMTP.Client.Authentication
     * @author Andrey Knupp Vital <andreykvital@gmail.com>
     * @filesource Scion\Mail\Client\Authentication\AbstractAuthentication.php
     */

    namespace Scion\Mail\Client\Authentication;

    abstract class AbstractAuthentication
    {

        /**
         * Username to perform authentication
         * @var string|NULL
         */
        private $username = NULL;

        /**
         * Password to authenticate
         * @var string|NULL
         */
        private $password = NULL;

        /**
         * Sets user credentials to perform an authentication.
         * @param string $username the username to perform authentication
         * @param string $password the password to authenticate
         */
        public function __construct($username, $password)
        {
            $this->setUsername($username);
            $this->setPassword($password);
        }

        /**
         * Sets the username to perform an authentication.
         * @param string $username the username to use
         * @return void
         */
        public function setUsername($username)
        {
            $this->username = $username;
        }

        /**
         * Sets a password to authenticate.
         * @param string $password the password to authenticate
         * @return void
         */
        public function setPassword($password)
        {
            $this->password = $password;
        }

        /**
         * Retrieves the username to be used in the authentication.
         * @return string|NULL
         */
        public function getUsername()
        {
            return $this->username;
        }

        /**
         * Retrieves the password to be used in authentication.
         * @return string|NULL
         */
        public function getPassword()
        {
            return $this->password;
        }

    }
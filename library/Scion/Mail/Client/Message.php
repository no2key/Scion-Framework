<?php

    /**
     * @package utils.net.SMTP.Client
     * @author Andrey Knupp Vital <andreykvital@gmail.com>
     * @filesource Scion\Mail\Client\Message.php
     */
    namespace Scion\Mail\Client;

    class Message
    {
        /**
         * The end of line of any exchanged message
         * @const string
         */
        const EOL = "\r\n";

        /**
         * Sent or received message
         * @var string
         */
        private $message;

        /**
         * Sets an sent or received message to be parsed
         * @param string $message sent or received message
         */
        public function __construct($message = null)
        {
            $this->message = $message;
        }

        /**
         * Retrieves the a sent or received message without reply code.
         * @return string
         */
        public function getMessage()
        {
            return trim($this->getCode() !== 0 ? substr($this->getFullMessage(), 3) : $this->getFullMessage());
        }

        /**
         * Retrieves the response code from an given message
         * @return integer
         */
        public function getCode()
        {
            $code = substr($this->getFullMessage(), 0, 3);
            return is_numeric($code) ? intval($code) : 0;
        }

        /**
         * Retrieves full message received from server or sent to server
         * @return string
         */
        public function getFullMessage()
        {
            return $this->message;
        }

        /**
         * Retrieves the full message
         * @see Message::getFullMessage()
         * @return string
         */
        public function __toString()
        {
            return $this->getFullMessage();
        }

    }
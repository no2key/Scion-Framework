<?php

    /**
     * @package utils.net.SMTP.Client
     * @author Andrey Knupp Vital <andreykvital@gmail.com>
     * @filesource Scion\Mail\Client\AbstractCommand.php
     */
    namespace Scion\Mail\Client;
    use Scion\Mail\Client\Command;
    use Scion\Mail\Client\Connection;

    abstract class AbstractCommand implements Command
    {

        /**
         * The connection with server
         * @var Connection
         */
        protected $connection;

        /**
         * Sets the SMTP server connection to perform commands on it
         * @param Connection $connection the SMTP server $connection
         */
        public function __construct(Connection $connection)
        {
            $this->connection = $connection;
        }

    }
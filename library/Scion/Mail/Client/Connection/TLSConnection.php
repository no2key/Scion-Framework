<?php

    /**
     * @package utils.net.SMTP.Client.Client.Connection
     * @author Andrey Knupp Vital <andreykvital@gmail.com>
     * @filesource Scion\Mail\Client\Connection\TLSConnection.php
     */
    namespace Scion\Mail\Client\Connection;
    use Scion\Mail\Client\AbstractConnection;
    use Scion\Mail\Client\CommandInvoker;
    use Scion\Mail\Client\Command\EHLOCommand;
    use Scion\Mail\Client\Command\HELOCommand;
    use Scion\Mail\Client\Command\STARTTLSCommand;

    class TLSConnection extends AbstractConnection
    {

        /**
         * Opens a connection with SMTP server using TCP protocol 
         * But performs message exchanging over a TLS encryption.
         * 
         * @param string $host valid SMTP server hostname
         * @param integer $port the SMTP server port
         * @param integer $timeout timeout in seconds for wait a connection.
         */
        public function __construct($host, $port, $timeout = 30)
        {
            parent::__construct();
            if ($this->open("tcp", $host, $port, $timeout)) {
                $commandInvoker = new CommandInvoker();
                $commandInvoker->invoke(new STARTTLSCommand($this));
                $commandInvoker->invoke(new EHLOCommand($this));
                $commandInvoker->invoke(new HELOCommand($this));
            }
        }

    }
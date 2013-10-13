<?php

    /**
     * @package utils.net.SMTP.Client.Connection.State
     * @author Andrey Knupp Vital <andreykvital@gmail.com>
     * @filesource Scion\Mail\Client\Connection\State\Closed.php
     */
    namespace Scion\Mail\Client\Connection\State;
    use Scion\Mail\Client\Connection\State\Connected;
    use Scion\Mail\Client\AbstractConnectionState;
    use Scion\Mail\Client\Connection;
    use \Exception;
    use \LogicException;
    use \ErrorException;

    class Closed extends AbstractConnectionState
    {

        /**
         * Opens an connection with SMTP server.
         * @param string $protocol the protocol to be used
         * @param string $hostname the SMTP server hostname
         * @param integer $port smtp server listening port
         * @param integer $timeout an valid timeout to wait for the connection
         * @param Connection $context the connection context
         * @throws LogicException if the timeout is equals or lower than zero
         * @throws ErrorException if couldn't connect to the server
         * @return boolean
         */
        public function open($protocol, $hostname, $port, $timeout = 30, Connection $context)
        {
            if ($timeout <= 0) {
                $message = "Timeout must be greater than zero.";
                throw new LogicException($message);
            }

            $errno = 0;
            $errstr = NULL;

            $remote = sprintf("%s://%s:%d", $protocol, gethostbyname($hostname), $port);
            $stream = @stream_socket_client($remote, $errno, $errstr, $timeout);

            if ($stream === false) {
                $message = sprintf("Couldn't connect to SMTP server %s:%d", $hostname, $port);
                throw new Exception($message, $errno, new ErrorException($errstr, $errno));
            }

            $this->stream = $stream;
            $this->changeState(new Established(), $context);
            return true;
        }

    }
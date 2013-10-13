<?php

    /**
     * @package utils.net.SMTP.Client.Command
     * @author Andrey Knupp Vital <andreykvital@gmail.com>
     * @filesource \Scion\Mail\Command\STARTTLSCommand.php
     */
    namespace Scion\Mail\Client\Command;
    use Scion\Mail\Client\AbstractCommand;
    use \RuntimeException;

    class STARTTLSCommand extends AbstractCommand
    {

        /**
         * Tells the SMTP server we will being using an encrypted connection, using TLS.
         * @throws RuntimeException if the server wasn't able to negotiate over TLS
         */
        public function execute()
        {
            if ($this->connection->write("STARTTLS")) {
                $startTLSResponse = $this->connection->read();
                if (($responseCode = $startTLSResponse->getCode()) !== 220) {
                    $message = "Couldn't perform STARTTLS command.";
                    throw new RuntimeException($message, $responseCode);
                } else {
                    $type = STREAM_CRYPTO_METHOD_TLS_CLIENT;
                    if (!stream_socket_enable_crypto($this->connection->getStream(), true, $type)) {
                        $message = "Cannot encrypt the connection.";
                        throw new RuntimeException($message);
                    }
                }
            }
        }

    }
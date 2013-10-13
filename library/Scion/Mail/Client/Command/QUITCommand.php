<?php

    /**
     * @package utils.net.SMTP.Client.Command
     * @author Andrey Knupp Vital <andreykvital@gmail.com>
     * @filesource \Scion\Mail\Command\QUITCommand.php
     */
    namespace Scion\Mail\Client\Command;
    use Scion\Mail\Client\AbstractCommand;
    use \RuntimeException;

    class QUITCommand extends AbstractCommand
    {

        /**
         * Performs an correctly abortion on SMTP server, by executing QUIT command.
         * @throws RuntimeException if the abortion wasn't a success
         */
        public function execute()
        {
            if ($this->connection->write("QUIT")) {
                $response = $this->connection->read();
                if (($responseCode = $response->getCode()) !== 221) {
                    $message = "QUIT wasn't successfully performed.";
                    throw new RuntimeException($message, $responseCode);
                }
            }
        }

    }
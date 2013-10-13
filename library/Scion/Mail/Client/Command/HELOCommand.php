<?php

    /**
     * @package utils.net.SMTP.Client.Command
     * @author Andrey Knupp Vital <andreykvital@gmail.com>
     * @filesource \Scion\Mail\Client\Command\HELOCommand.php
     */
    namespace Scion\Mail\Client\Command;
    use Scion\Mail\Client\Command\HELLOCommand;
    use \RuntimeException;

    class HELOCommand extends HELLOCommand
    {

        /**
         * Executes the HELO command in the SMTP server.
         * @throws RuntimeException if the command wasn't executed successfully
         */
        public function execute()
        {
            if (!$this->performEhloHelo("HELO")) {
                $message = "Couldn't perform HELO command in SMTP server.";
                throw new RuntimeException($message);
            }
        }

    }
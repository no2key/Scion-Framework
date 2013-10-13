<?php

    /**
     * @package utils.net.SMTP.Client.Command
     * @author Andrey Knupp Vital <andreykvital@gmail.com>
     * @filesource Scion\Mail\Client\Command\EHLOCommand.php
     */

    namespace Scion\Mail\Client\Command;
    use Scion\Mail\Client\Command\HELLOCommand;
    use \RuntimeException;

    class EHLOCommand extends HELLOCommand
    {

        /**
         * Executes the EHLO command in the SMTP server.
         * @throws RuntimeException if the command wasn't executed successfully
         */
        public function execute()
        {
            if (!$this->performEhloHelo("EHLO")) {
                $message = "Couldn't perform EHLO command in SMTP server.";
                throw new RuntimeException($message);
            }
        }

    }
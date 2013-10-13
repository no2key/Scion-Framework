<?php

    /**
     * @package utils.net.SMTP.Client
     * @author Andrey Knupp Vital <andreykvital@gmail.com>
     * @filesource Scion\Mail\Client\CommandInvoker.php
     */
    namespace Scion\Mail\Client;
    use Scion\Mail\Client\Command;

    class CommandInvoker
    {

        /**
         * Invokes an specified command.
         * @param Command $command the command to be invoked
         * @return void
         */
        public function invoke(Command $command)
        {
            $command->execute();
        }

    }
<?php

    /**
     * @package utils.net.SMTP.Client.Command
     * @author Andrey Knupp Vital <andreykvital@gmail.com>
     * @filesource Scion\Mail\Client\Command\AUTHCommand.php
     */
    namespace Scion\Mail\Client\Command;
    use Scion\Mail\Client\AbstractCommand;
    use Scion\Mail\Client\Authentication;
    use Scion\Mail\Client\Connection;
    use \RuntimeException;

    class AUTHCommand extends AbstractCommand
    {

        /**
         * The authentication mechanism
         * @var string
         */
        private $mechanism = NULL;

        /**
         * - Constructor
         * @param Connection $connection the connection where command will be performed
         * @param string $mechanism an authentication mechanism to be used
         * @return AUTHCommand
         */
        public function __construct(Connection $connection, $mechanism)
        {
            parent::__construct($connection);
            $this->mechanism = strtoupper($mechanism);
        }

        /**
         * Performs an AUTH command with specified mechanism in the SMTP server.
         * @throws RuntimeException if the authentication mechanism wasn't accepted
         */
        public function execute()
        {
            if ($this->connection->write(sprintf("AUTH %s", $this->mechanism))) {
                $response = $this->connection->read();
                if ($response->getCode() === Authentication::UNRECOGNIZED_AUTHENTICATION_TYPE) {
                    $message = "Couldn't authenticate using the %s mechanism.";
                    throw new RuntimeException(sprintf($message, $this->mechanism));
                }
            }
        }

    }
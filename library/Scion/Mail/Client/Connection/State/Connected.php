<?php

    /**
     * @package utils.net.SMTP.Client.Connection.State
     * @author Andrey Knupp Vital <andreykvital@gmail.com>
     * @filesource Scion\Mail\Client\Connection\State\Connected.php
     */
    namespace Scion\Mail\Client\Connection\State;
    use Scion\Mail\Client\AbstractConnectionState;
    use Scion\Mail\Client\Connection;
    use Scion\Mail\Client\Message;
    use \Exception;
    use \LogicException;
    use \ErrorException;
    use Scion\Mail\Client\CommandInvoker;
    use Scion\Mail\Client\Command\QUITCommand;
    
    class Connected extends AbstractConnectionState
    {

        /**
         * Reads a server reply.
         * @return Message|bool
         */
        public function read()
        {
            while (!feof($this->stream)) {
                $message = new Message(stream_get_line($this->stream, 515, Message::EOL));
                $this->messages[] = $message;

                if (substr($message->getFullMessage(), 3, 1) === chr(0x20)) {
                    $this->lastMessage = $message;
                    return $message;
                }
            }
			return false;
        }

        /**
         * Writes data on the server stream.
         * @param string $data the data to be written
         * @return boolean
         */
        public function write($data)
        {
            $message = new Message($data);
            $this->messages[] = $message;
            $this->lastMessage = $message;
            return fwrite($this->stream, $data . Message::EOL) !== FALSE;
        }

        /**
         * Closes the connection with SMTP server
         * @param Connection $context the connection context
         * @return void
         */
        public function close(Connection $context)
        {
            $invoker = new CommandInvoker();
            $invoker->invoke(new QUITCommand($context));

            if (fclose($this->stream)) {
                $this->stream = NULL;
                $this->changeState(new Closed(), $context);
            }
        }

    }
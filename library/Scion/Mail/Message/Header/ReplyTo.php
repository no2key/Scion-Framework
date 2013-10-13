<?php

    /**
     * @package utils.net.SMTP.Message.Header
     * @filesource Scion\Mail\Message\Header\ReplyTo.php
     * @author Andrey Knupp Vital <andreykvital@gmail.com>
     */
    namespace Scion\Mail\Message\Header;
    use Scion\Mail\Message\AbstractAddressList;
    
    class ReplyTo extends AbstractAddressList
    {
        
        /**
         * @see AbstractAddressList::getName()
         * @return string
         */
        public function getName()
        {
            return "Reply-To";
        }
    
    }
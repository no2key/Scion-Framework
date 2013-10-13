<?php

    /**
     * @package utils.net.SMTP.Message.Header
     * @filesource Scion\Mail\Message\Header\Bcc.php
     * @author Andrey Knupp Vital <andreykvital@gmail.com>
     */
    namespace Scion\Mail\Message\Header;
    use Scion\Mail\Message\AbstractAddressList;
    
    class Bcc extends AbstractAddressList
    {
        
        /**
         * @see AbstractAddressList::getName()
         * @return string
         */
        public function getName()
        {
            return "Bcc";
        }
    
    }
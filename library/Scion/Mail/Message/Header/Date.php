<?php

    /**
     * @package utils.net.SMTP.Message.Header
     * @filesource Scion\Mail\Message\Header\Date.php
     * @author Andrey Knupp Vital <andreykvital@gmail.com>
     */
    namespace Scion\Mail\Message\Header;
    use Scion\Mail\Message\AbstractHeader;
    use \DateTime;
    
    class Date extends AbstractHeader
    {
        
        /**
         * Constructs a representation of mail "Date" header
         * @param DateTime $date the date to be defined on header
         */
        public function __construct(DateTime $date = NULL)
        {
            if(is_null($date)) {
                $date = new DateTime();
            }
            
            $value = $date->format(DateTime::RFC2822);
            parent::__construct("Date", $value);
        }
        
    }
<?php

    /**
     * @package utils.net.SMTP.Message.Header
     * @filesource \Scion\Mail\Message\Header\From.php
     * @author Andrey Knupp Vital <andreykvital@gmail.com>
     */
    namespace Scion\Mail\Message\Header;
    use Scion\Mail\Message\AbstractHeader;
    use Scion\Mail\Message\HeaderEncoder;
    use Scion\Mail\Message\Encoder;
    use InvalidArgumentException;
    
    class From extends AbstractHeader
    {
        
        /**
         * Constructs a representation of mail header "From"
         * @param string $email the "sender" email address
         * @param string $name the author name of email address
         * @param string $encoding header encoding (if null it will be ASCII)
         * @param string $encoder specific header encoder to encode an value 
         * (default is NULL, it's alias to the QuotedPrintable encoder)
         */
        public function __construct($email, $name = NULL, $encoding = NULL, Encoder $encoder = NULL)
        {
            $this->setEncoding($encoding);
            
            if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = "The email address %s is not valid";
                throw new InvalidArgumentException(sprintf($message, $email));
            }
            
            if(!is_null($name)) {
                $name = HeaderEncoder::encode($name, $this->getEncoding(), $encoder);
                $value = sprintf("%s <%s>", $name, $email);
            }
            
            parent::__construct("From", isset($value) ? $value : $email);
        }
    
    }
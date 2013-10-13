<?php

    /**
     * @package utils.net.SMTP.Message
     * @filesource \Scion\Mail\Message\AbstractAddressList.php
     * @author Andrey Knupp Vital <andreykvital@gmail.com>
     */
    namespace Scion\Mail\Message;
    use \ArrayIterator;
    use Scion\Mail\Message\HeaderEncoder;
    use Scion\Mail\Message\AddressList;
    use Scion\Mail\Message\Encodable;
    use Scion\Mail\Message\Header;
    
    abstract class AbstractAddressList implements AddressList, Header, Encodable
    {
        
        /**
         * Header encoding
         * @var string
         */
        private $encoding = "ASCII";
        
        /**
         * Addresses list
         * @var array[Address]
         */
        private $addresses = array();
        
        /**
         * Retrieves the encoding
         * @return string
         */
        public function getEncoding()
        {
            return $this->encoding;
        }

        /**
         * Sets the encoding
         * @param string $encoding
         */
        public function setEncoding($encoding)
        {
            $this->encoding = $encoding;
        }

        /**
         * Adds an address to the list
         * @param Address $address the address to be added
         */
        public function addAddress(Address $address)
        {
            $this->addresses[] = $address;
        }

        /**
         * Retrieves all attached addresses
         * @return array[Address]
         */
        public function getAddresses()
        {
            return $this->addresses;
        }

        /**
         * Retrieves count of attached addresses
         * @return integer
         */
        public function count()
        {
            return count($this->addresses);
        }

        /**
         * Retrieves an Iterator of address list
         * @return \ArrayIterator
         */
        public function getIterator()
        {
            return new ArrayIterator($this->addresses);
        }
        
        abstract public function getName();

        /**
         * Builds the header
         * @return string
         */
        public function getValue()
        {
            $emails = array();
            foreach($this->getAddresses() AS $address) {
                $name = $address->getName();
                $email = $address->getEmail();
                
                if (empty($name)) {
                    $emails[] = $email;
                    continue;
                }
                
                $name = strstr($name, ",") ? sprintf('"%s"', $name) : $name;
                $emails[] = sprintf("%s <%s>", HeaderEncoder::encode($name, $this->getEncoding()), $email);
            }
            
            $value = sprintf("%s\r\n", implode(", ", $emails));
            return count($this) ? sprintf("%s: %s", $this->getName(), $value) : NULL;
        }
        
        /**
         * @see Header::__toString()
         * @return string
         */
        public function __toString()
        {
            return (string) $this->getValue();
        }
    
    }
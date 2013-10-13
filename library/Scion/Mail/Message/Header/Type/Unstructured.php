<?php

    /**
     * @package utils.net.SMTP.Message.Header.Type
     * @author Andrey Knupp Vital <andreykvital@gmail.com>
     * @filesource \Scion\Mail\Message\Header\Type\Unstructured.php
     * @link http://tools.ietf.org/html/rfc2822#section-2.2.1
     */
    namespace Scion\Mail\Message\Header\Type;
    use Scion\Mail\Message\Header;

    interface Unstructured extends Header
    {
        /**
         * Retrieves the header encoding
         * @return string
         */
        public function getEncoding();
    }
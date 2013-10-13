<?php

    /**
     * @package utils.net.SMTP.Message.Encoder
     * @author Andrey Knupp Vital <andreykvital@gmail.com>
     * @filesource Scion\Mail\Message\Encoder\Base64.php
     */
    namespace Scion\Mail\Message\Encoder;
    use Scion\Mail\Message\Encoder;

    class Base64 implements Encoder
    {

        /**
         * Encodes the provided string in base64 format
         * @param string $string the string to be encoded
         * @see Encoder::encodeString()
         * @return string
         */
        public function encodeString($string)
        {
            return base64_encode($string);
        }

        /**
         * Encodes a header in BASE64 format
         * @param string $header the header to be encoded
         * @see Encoder::encodeHeader()
         * @return string
         */
        public function encodeHeader($header, $charset = "UTF-8")
        {
            $prefix = sprintf("=?%s?B?", $charset);
            $encoded = rtrim(chunk_split($this->encodeString($header), (70 - strlen($prefix)), "\n"));
            $encoded = str_replace("\n", sprintf("?=\n %s", $prefix), $encoded);
            return sprintf("%s%s?=", $prefix, $encoded);
        }

    }

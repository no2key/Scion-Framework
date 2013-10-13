<?php

    /**
     * @package utils.net.SMTP.Message.Encoder
     * @author Andrey Knupp Vital <andreykvital@gmail.com>
     * @filesource Scion\Mail\Message\Encoder\QuotedPrintable.php
     */
    namespace Scion\Mail\Message\Encoder;
    use Scion\Mail\Message\Encoder;

    class QuotedPrintable implements Encoder
    {

        /**
         * End of line in QP
         * @const string
         */
        const EOL = "=20";
        
        /**
         * Encodes the provided data in quoted printable format
         * @param string $data the data to be encoded
         * @see Encoder::encodeString()
         * @return string
         */
        public function encodeString($data)
        {
            $stream = fopen("php://temp", "r+");
            $write = fwrite($stream, $data);
            
            rewind($stream);
            $options = array("line-feed" => "\n", "line-length" => 73);
            stream_filter_append($stream, "convert.quoted-printable-encode", STREAM_FILTER_READ, $options);
            return stream_get_contents($stream);
        }

        /**
         * Encodes a header in quoted printable format
         * @param string $header the header to be encoded
         * @see Encoder::encodeHeader()
         * @return string
         */
        public function encodeHeader($header, $charset = "UTF-8")
        {
            $tmp = NULL;
            $lines = array(NULL);
            
            $prefix = sprintf("=?%s?Q?", $charset);
            $maxLength = (75 - strlen($prefix));
            
            $search = array(chr(0x3F), chr(0x5F), chr(0x20));
            $replacement = array("=3F", "=5F", QuotedPrintable::EOL);
            $encoded = str_replace($search, $replacement, $this->encodeString($header));
            
            while (strlen($encoded) > 0) {
                $token = substr($encoded, 0, (substr($encoded, 0, 1) === chr(0x3D)) ? 3 : 1);
                $encoded = substr($encoded, strlen($token));
                $tmp .= $token;

                $line = max(count($lines) - 1, 0);
                if ($token === QuotedPrintable::EOL) {
                    $oR = (strlen($lines[$line] . $tmp) > $maxLength);
                    $lines[$line + ($oR ? 1 : 0)] = $oR ? $tmp : $lines[$line] . $tmp;
                    $tmp = NULL;
                }

                if (strlen($encoded) === 0)
                    $lines[$line] .= $tmp;
            }

            for ($i = 0, $c = count($lines); $i < $c; ++$i)
                $lines[$i] = sprintf("%s%s%s?=", chr(0x20), $prefix, $lines[$i]);
            return trim(implode("\n", $lines));
        }

    }

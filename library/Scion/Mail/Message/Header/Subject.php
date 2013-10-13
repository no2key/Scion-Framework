<?php

    /**
     * @package utils.net.SMTP.Message.Header
     * @filesource \Scion\Mail\Message\Header\Subject.php
     * @author Andrey Knupp Vital <andreykvital@gmail.com>
     */
    namespace Scion\Mail\Message\Header;
    use Scion\Mail\Message\HeaderWrapper;
    use Scion\Mail\Message\AbstractHeader;
    use Scion\Mail\Message\Header\Type\Unstructured;

    class Subject extends AbstractHeader implements Unstructured
    {
        /**
         * Constructor, defines the mail message subject
         * @param string $subject the message subject
         * @return Subject
         */
        public function __construct($subject = null, $encoding = NULL)
        {
            parent::__construct("Subject", $subject);
            $this->setEncoding($encoding);
        }

        /**
         * Creates and returns the string representation of header
         * @link http://tools.ietf.org/html/rfc2822#section-2.2.3
         * @return string
         */
        public function __toString()
        {
            $value = HeaderWrapper::wrap($this);
            return sprintf("%s: %s", $this->getName(), $value);
        }

    }
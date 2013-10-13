<?php
namespace Scion\Mail\Message;
use Scion\Mail\Message\Header;

interface Encoder {
	/**
	 * Encodes the provided data
	 *
	 * @param string $data the data to be encoded
	 */
	public function encodeString($data);

	/**
	 * Encodes the provided header
	 *
	 * @param string $header  the header to be encoded
	 * @param string $charset the string character set
	 */
	public function encodeHeader($header, $charset);
}

    
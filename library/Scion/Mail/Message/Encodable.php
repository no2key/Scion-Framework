<?php
namespace Scion\Mail\Message;

interface Encodable {
	/**
	 * Retrieves the encoding
	 *
	 * @return string
	 */
	public function getEncoding();

	/**
	 * Sets the encoding
	 *
	 * @param string $encoding the encoding to be used
	 */
	public function setEncoding($encoding);
}
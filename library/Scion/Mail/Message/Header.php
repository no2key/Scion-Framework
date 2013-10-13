<?php
namespace Scion\Mail\Message;

interface Header {

	/**
	 * Retrieves the header name
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * Retrieves the header value
	 *
	 * @return string
	 */
	public function getValue();

	/**
	 * Creates and return a string representation of header
	 *
	 * @return string
	 */
	public function __toString();

}
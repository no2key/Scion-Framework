<?php
namespace Scion\Mail\Message;

use Scion\Mail\Message;
use Scion\Mail\Message\Header;
use Scion\Mail\Message\Header\Type\Structured;
use Scion\Mail\Message\Header\Type\Unstructured;
use Scion\Mail\Message\HeaderEncoder;
use Scion\Mail\Message\Encoder;
use \InvalidArgumentException;

abstract class HeaderWrapper {

	/**
	 * Wraps an structured header line
	 *
	 * @link http://tools.ietf.org/html/rfc2822#section-2.2.2
	 *
	 * @param Header $header the structured header to wrap
	 *
	 * @return string
	 */
	private static function wrapStructured(Structured $header) {
		$line      = null;
		$lines     = array();
		$value     = $header->getValue();
		$delimiter = $header->getDelimiter();

		for ($i = 0, $l = strlen($value); $i < $l; ++$i) {
			$line .= $value[$i];

			if ($value[$i] === $delimiter) {
				array_push($lines, $line);
				$line = null;
			}
		}

		return implode("\r\n", $lines);
	}

	/**
	 * Wraps an unstructured header line
	 *
	 * @link http://tools.ietf.org/html/rfc2822#section-2.2.1
	 *
	 * @param Unstructured $header  the unstructured header to be wrapped
	 * @param Encoder      $encoder an encoder to encode the header (default: QuotedPrintable)
	 *
	 * @return type
	 */
	private static function wrapUnstructured(Unstructured $header, Encoder $encoder = null) {
		$encoding = $header->getEncoding();

		return ($encoding === "ASCII") ? wordwrap($header->getValue(), 78, "\r\n") : HeaderEncoder::encode($header->getValue(), $encoding, $encoder);
	}

	/**
	 * Wraps an structured/unstructured header line
	 *
	 * @param Header  $header  the header to be wrapped
	 * @param Encoder $encoder the encoder to encode an unstructured header (default: QuotedPrintable)
	 *
	 * @throws \InvalidArgumentException if the provided header is not an instance of Structured or Unstructured
	 * @return string
	 */
	public static function wrap(Header $header, Encoder $encoder = null) {
		if ($header instanceof Structured) {
			return static::wrapStructured($header);
		}
		elseif ($header instanceof Unstructured) {
			return static::wrapUnstructured($header, $encoder);
		}

		$message = "We can wrap only structured or unstructured headers";
		throw new InvalidArgumentException($message);
	}

}
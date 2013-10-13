<?php
namespace Scion\Mail\Message;

use Scion\Mail\Message\Encoder;
use Scion\Mail\Message\Encoder\QuotedPrintable;
use Scion\Mail\Message\Encoder\Base64;

abstract class HeaderEncoder {

	/**
	 * Encodes an header value using specified encoder or QuotedPrintable as default.
	 *
	 * @param string  $value    the header value to be encoded
	 * @param string  $encoding the charset encoding
	 * @param Encoder $encoder  an valid encoder to encode
	 *
	 * @return string
	 */
	public static function encode($value, $encoding, Encoder $encoder = null) {
		if ($encoding === "ASCII") {
			return $value;
		}

		$encoder = is_null($encoder) ? new QuotedPrintable() : $encoder;

		return $encoder->encodeHeader($value, $encoding);
	}

}
<?php
namespace Scion\Validator;

class Ip {

	/**
	 * Constructor
	 */
	public function __construct() {

	}

	/**
	 * Returns true if and only if $value is a valid IP address
	 * @param $value
	 * @return bool
	 */
	public function isValid($value) {
		if (filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
			return false;
		}

		return true;
	}

	/**
	 * Validates an IPv4 address
	 * @param $value
	 * @return mixed
	 */
	public function validateIPv4($value) {
		return filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
	}

	/**
	 * Validates an IPv6 address
	 * @param $value
	 * @return mixed
	 */
	public function validateIPv6($value) {
		return filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
	}

	/**
	 * Validates a private IP
	 * @param $value
	 * @return bool
	 */
	public function validateIpPrivate($value) {
		return !filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
	}

	/**
	 * Validates an IPvFuture address.
	 * IPvFuture is loosely defined in the Section 3.2.2 of RFC 3986
	 * @param $value
	 * @return bool
	 */
	public function validateIPvFuture($value) {
		/**
		 * ABNF:
		 * IPvFuture  = "v" 1*HEXDIG "." 1*( unreserved / sub-delims / ":" )
		 * unreserved    = ALPHA / DIGIT / "-" / "." / "_" / "~"
		 * sub-delims    = "!" / "$" / "&" / "'" / "(" / ")" / "*" / "+" / ","
		 *               / ";" / "="
		 */
		static $regex = '/^v([[:xdigit:]]+)\.[[:alnum:]\-\._~!\$&\'\(\)\*\+,;=:]+$/';

		$result = (bool)preg_match($regex, $value, $matches);

		/**
		 * "As such, implementations must not provide the version flag for the
		 *  existing IPv4 and IPv6 literal address forms described below."
		 */
		return ($result && $matches[1] != 4 && $matches[1] != 6);
	}
}
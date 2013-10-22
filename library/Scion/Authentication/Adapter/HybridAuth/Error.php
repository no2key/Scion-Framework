<?php
namespace Scion\Authentication\Adapter\HybridAuth;
	/*!
	* HybridAuth
	* http://hybridauth.sourceforge.net | http://github.com/hybridauth/hybridauth
	* (c) 2009-2012, HybridAuth authors | http://hybridauth.sourceforge.net/licenses.html
	*/

/**
 * Errors manager
 *
 * HybridAuth errors are stored in Hybrid::storage() and not displayed directly to the end user
 */
class Error {
	/**
	 * store error in session
	 */
	public static function setError($message, $code = null, $trace = null, $previous = null) {
		Logger::info("Enter Error::setError( $message )");

		Auth::storage()->set("hauth_session.error.status", 1);
		Auth::storage()->set("hauth_session.error.message", $message);
		Auth::storage()->set("hauth_session.error.code", $code);
		Auth::storage()->set("hauth_session.error.trace", $trace);
		Auth::storage()->set("hauth_session.error.previous", $previous);
	}

	/**
	 * clear the last error
	 */
	public static function clearError() {
		Logger::info("Enter Error::clearError()");

		Auth::storage()->delete("hauth_session.error.status");
		Auth::storage()->delete("hauth_session.error.message");
		Auth::storage()->delete("hauth_session.error.code");
		Auth::storage()->delete("hauth_session.error.trace");
		Auth::storage()->delete("hauth_session.error.previous");
	}

	/**
	 * Checks to see if there is a an error.
	 *
	 * @return boolean True if there is an error.
	 */
	public static function hasError() {
		return (bool)Auth::storage()->get("hauth_session.error.status");
	}

	/**
	 * return error message
	 */
	public static function getErrorMessage() {
		return Auth::storage()->get("hauth_session.error.message");
	}

	/**
	 * return error code
	 */
	public static function getErrorCode() {
		return Auth::storage()->get("hauth_session.error.code");
	}

	/**
	 * return string detailled error backtrace as string.
	 */
	public static function getErrorTrace() {
		return Auth::storage()->get("hauth_session.error.trace");
	}

	/**
	 * @return string detailled error backtrace as string.
	 */
	public static function getErrorPrevious() {
		return Auth::storage()->get("hauth_session.error.previous");
	}
}

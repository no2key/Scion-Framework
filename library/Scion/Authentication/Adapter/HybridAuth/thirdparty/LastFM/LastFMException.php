<?php
namespace Scion\Authentication\Adapter\HybridAuth\thirdparty\LastFM;

/**
 * Thrown when an API call returns an exception.
 *
 * @author Filip Sobczak <f@digitalinvaders.pl>
 */
class LastFMException extends \Exception {

	/**
	 * The result from the API server that represents the exception information.
	 */
	protected $result;

	/**
	 * Make a new API Exception with the given result.
	 *
	 * @param Array $result the result from the API server
	 */
	public function __construct($result) {
		$this->result = $result;

		$code = isset($result['error']) ? $result['error'] : 0;

		if (isset($result['message'])) {
			$msg = $result['message'];
		} else {
			$msg = 'Unknown Error. Check getResult()';
		}

		parent::__construct($msg, $code);
	}

	/**
	 * Return the associated result object returned by the API server.
	 *
	 * @returns Array the result from the API server
	 */
	public function getResult() {
		return $this->result;
	}

	/**
	 * To make debugging easier.
	 *
	 * @returns String the string representation of the error
	 */
	public function __toString() {
		$str = '';
		if ($this->code != 0) {
			$str .= $this->code . ': ';
		}
		return $str . $this->message;
	}

}
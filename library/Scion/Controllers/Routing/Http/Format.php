<?php
namespace Scion\Controllers\Routing\Http;

class Format {

	const FILTER_VALIDATE_INT     = 'int';
	const FILTER_VALIDATE_INTEGER = 'integer';
	const FILTER_VALIDATE_BOOL    = 'bool';
	const FILTER_VALIDATE_BOOLEAN = 'boolean';
	const FILTER_VALIDATE_FLOAT   = 'float';
	const FILTER_VALIDATE_REGEXP  = 'regex';
	const FILTER_VALIDATE_URL     = 'url';
	const FILTER_VALIDATE_EMAIL   = 'email';
	const FILTER_VALIDATE_IP      = 'ip';
	const FILTER_VALIDATE_JSON    = 'json';
	const FILTER_VALIDATE_XML     = 'xml';
	const FILTER_VALIDATE_INI     = 'ini';

	private $_content;
	private $_format;
	public $_valid;

	/**
	 * Constructor
	 *
	 * @param        $content
	 * @param string $format
	 */
	public function __construct($content, $format = 'text') {
		$this->_content = $content;
		$this->_format  = $format;

		$this->_valid = $this->_validateFormat();
	}

	/**
	 * Validate each supported formats
	 *
	 * @return bool
	 */
	private function _validateFormat() {
		switch ($this->_format) {
			case self::FILTER_VALIDATE_INT:
			case self::FILTER_VALIDATE_INTEGER:
				return filter_var($this->_content, FILTER_VALIDATE_INT);
				break;

			case self::FILTER_VALIDATE_BOOL:
			case self::FILTER_VALIDATE_BOOLEAN:
				return filter_var($this->_content, FILTER_VALIDATE_BOOLEAN);
				break;

			case self::FILTER_VALIDATE_FLOAT:
				return filter_var($this->_content, FILTER_VALIDATE_FLOAT);
				break;

			case self::FILTER_VALIDATE_REGEXP:
				return filter_var($this->_content, FILTER_VALIDATE_REGEXP);
				break;

			case self::FILTER_VALIDATE_URL:
				return filter_var($this->_content, FILTER_VALIDATE_URL);
				break;

			case self::FILTER_VALIDATE_EMAIL:
				return filter_var($this->_content, FILTER_VALIDATE_EMAIL);
				break;

			case self::FILTER_VALIDATE_IP:
				return filter_var($this->_content, FILTER_VALIDATE_IP);
				break;

			case self::FILTER_VALIDATE_JSON:
				return $this->_jsonValidate();
				break;

			case self::FILTER_VALIDATE_XML:
				return $this->_xmlValidate();
				break;

			case self::FILTER_VALIDATE_INI:
				return $this->_iniValidate();
				break;
		}
	}

	/**
	 * Validate Json format
	 * @return bool|mixed
	 * @throws \Exception
	 */
	private function _jsonValidate() {
		// decode the JSON data
		$result = json_decode($this->_content);

		// switch and check possible JSON errors
		switch (json_last_error()) {
			case JSON_ERROR_NONE:
				// JSON is valid
				return $result;
				break;
			case JSON_ERROR_DEPTH:
				$error = 'Maximum stack depth exceeded.';
				break;
			case JSON_ERROR_STATE_MISMATCH:
				$error = 'Underflow or the modes mismatch.';
				break;
			case JSON_ERROR_CTRL_CHAR:
				$error = 'Unexpected control character found.';
				break;
			case JSON_ERROR_SYNTAX:
				$error = 'Syntax error, malformed JSON.';
				break;
			// only PHP 5.3+
			case JSON_ERROR_UTF8:
				$error = 'Malformed UTF-8 characters, possibly incorrectly encoded.';
				break;
			default:
				$error = 'Unknown JSON error occured.';
				break;
		}

		if ($error !== '') {
			throw new \Exception($error);
			return false;
		}

		// everything is OK
		return $result;
	}

	/**
	 * Validate XML format
	 * @return false|\SimpleXMLElement
	 */
	private function _xmlValidate() {
		libxml_use_internal_errors(true);
		try {
			return new \SimpleXMLElement($this->_content);
		}
		catch (\Exception $e){
			return false;
		}
	}

	/**
	 * Validate INI format
	 * @return array
	 */
	private function _iniValidate() {
		return @parse_ini_string($this->_content);
	}
}
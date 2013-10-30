<?php
namespace Scion\Registry;

/**
 * Class for reading from and writing to the Windows registry
 *
 * @author     H. Poort
 * @version    1.0
 * @copyright  2008
 * @license    BSD
 * @filesource http://www.phpclasses.org/package/3555-PHP-Read-from-and-write-to-the-Windows-registry.html
 */
class WindowsRegistry {

	private $registryObject;

	/**
	 * Declare HKEY addresses constants
	 */
	const HKEY_CLASSES_ROOT   = 0x80000000;
	const HKEY_CURRENT_USER   = 0x80000001;
	const HKEY_LOCAL_MACHINE  = 0x80000002;
	const HKEY_USERS          = 0x80000003;
	const HKEY_CURRENT_CONFIG = 0x80000005;

	/**
	 * Declare Registry type constansts
	 */
	const REG_NONE      = 0;
	const REG_SZ        = 1;
	const REG_EXPAND_SZ = 2;
	const REG_BINARY    = 3;
	const REG_DWORD     = 4;
	const REG_MULTI_SZ  = 7;

	/**
	 * Class constructor
	 *
	 * @throws Exception
	 * @access public
	 */
	public function __construct() {
		// Get the WMI StdRegProv class
		try {
			$this->registryObject = new \COM('WINMGMTS:{impersonationLevel=impersonate}!//./root/default:StdRegProv');
		}
		catch (Exception $ex) {
			$this->registryObject = null;
			HandlerException::setError('Could not get a connection to the registry.');
		}
	}

	/**
	 * Class destructor
	 *
	 * @access public
	 */
	public function __destruct() {
		unset($this->registryObject);
	}

	/**
	 * Read some value
	 *
	 * @param string $keyPath
	 * @param string $valueName
	 * @param string $asString
	 * @access public
	 * @return mixed
	 */
	public function readValue($keyPath, $valueName, $asString = false) {
		$hKeyId = 0;
		$subKey = '';
		if (!$this->_splitKeyPath($keyPath, $hKeyId, $subKey)) {
			return null;
		}

		$valueType = $this->_getValueType($hKeyId, $subKey, $valueName);
		if (-1 == $valueType) {
			return null;
		}

		switch ($valueType) {
			case self::REG_NONE: // No support for REG_NONE
				return null;
			case self::REG_SZ:
				return $this->_getStringValue($hKeyId, $subKey, $valueName);
			case self::REG_EXPAND_SZ:
				return $this->_getExpandedStringValue($hKeyId, $subKey, $valueName);
			case self::REG_BINARY:
				return $this->_getBinaryValue($hKeyId, $subKey, $valueName, $asString);
			case self::REG_DWORD:
				return $this->_getDWORDValue($hKeyId, $subKey, $valueName, $asString);
			case self::REG_MULTI_SZ:
				return $this->_getMultiStringValue($hKeyId, $subKey, $valueName, $asString);
		}
		return null;
	}

	/**
	 * Write some values
	 *
	 * @param string               $keyPath
	 * @param string               $valueName
	 * @param string|integer|array $valueContents
	 * @param integer              $forceType
	 * @access public
	 * @return bool
	 */
	public function writeValue($keyPath, $valueName, $valueContents, $forceType = -1) {
		$hKeyId = 0;
		$subKey = '';
		if (!$this->_splitKeyPath($keyPath, $hKeyId, $subKey)) {
			return false;
		}

		if (!$this->createKey($keyPath)) {
			return false;
		}

		if (-1 != $forceType) {
			$valueType = $forceType;
		}
		else {
			$valueType = $this->_getValueType($hKeyId, $subKey, $valueName);
		}

		if (self::REG_NONE == $valueType) // No support for REG_NONE
		{
			return false;
		}

		if (-1 == $valueType) { // valueType unknown
			if (is_string($valueContents)) {
				$valueType = self::REG_SZ;
			}
			else if (is_int($valueContents)) {
				$valueType = self::REG_DWORD;
			}
			else if (is_array($valueContents) && count($valueContents)) {
				if (is_string($valueContents[0])) {
					$valueType = self::REG_MULTI_SZ;
				}
				else if (is_int($valueContents[0])) {
					$valueType = self::REG_BINARY;
				}
			}
		}
		if (-1 == $valueType) // valueType still unknown, leave
		{
			return false;
		}

		$result = -1;

		if ((self::REG_SZ == $valueType) || (self::REG_EXPAND_SZ == $valueType)) {
			if (self::REG_SZ == $valueType) {
				$result = $this->registryObject->SetStringValue($hKeyId, $subKey, $valueName, $valueContents);
			}
			else {
				$result = $this->registryObject->SetExpandedStringValue($hKeyId, $subKey, $valueName, $valueContents);
			}
		}
		else if (self::REG_DWORD == $valueType) {
			$result = $this->registryObject->SetDWORDValue($hKeyId, $subKey, $valueName, $valueContents);
		}
		else if (self::REG_MULTI_SZ == $valueType) {
			if (!is_array($valueContents) || !is_string($valueContents[0])) {
				return false;
			}

			$result = $this->registryObject->SetMultiStringValue($hKeyId, $subKey, $valueName, $valueContents);
		}
		else if (self::REG_BINARY == $valueType) {
			if (!is_array($valueContents) || !is_int($valueContents[0])) {
				return false;
			}

			$result = $this->registryObject->SetBinaryValue($hKeyId, $subKey, $valueName, $valueContents);
		}

		return (0 == $result);
	}

	/**
	 * Delete some values
	 *
	 * @param string         $keyPath
	 * @param string|boolean $valueName
	 * @access public
	 * return boolean
	 */
	public function deleteValue($keyPath, $valueName) {
		$hKeyId = 0;
		$subKey = '';
		if (!$this->_splitKeyPath($keyPath, $hKeyId, $subKey)) {
			return false;
		}

		return ($this->registryObject->deleteValue($hKeyId, $subKey, $valueName) == 0);
	}

	/**
	 * List some value names
	 *
	 * @param string  $keyPath
	 * @param boolean $includeTypes
	 * @access public
	 */
	public function getValueNames($keyPath, $includeTypes = false) {
		$hKeyId = 0;
		$subKey = '';
		if (!$this->_splitKeyPath($keyPath, $hKeyId, $subKey)) {
			return false;
		}

		$valueList = array();
		if (!$this->_enumValues($hKeyId, $subKey, $valueList)) {
			return null;
		}

		if (!$includeTypes) {
			$valueNames = array();
			for ($i = 0, $cnt = count($valueList); $i < $cnt; $i++) {
				$valueNames[] = $valueList[$i][0];
			}

			return $valueNames;
		}
		else {
			return $valueList;
		}
	}

	/**
	 * Create some keys
	 *
	 * @param string $keyPath
	 * @access public
	 */
	public function createKey($keyPath) {
		$hKeyId = 0;
		$subKey = '';
		if (!$this->_splitKeyPath($keyPath, $hKeyId, $subKey)) {
			return false;
		}

		return ($this->registryObject->createKey($hKeyId, $subKey) == 0);
	}

	/**
	 * Delete some keys
	 *
	 * @param string  $keyPath
	 * @param boolean $deleteSubkeys
	 * @access public
	 */
	public function deleteKey($keyPath, $deleteSubkeys = false) {
		$hKeyId = 0;
		$subKey = '';
		if (!$this->_splitKeyPath($keyPath, $hKeyId, $subKey)) {
			return false;
		}

		if (!$deleteSubkeys) {
			return ($this->registryObject->deleteKey($hKeyId, $subKey) == 0);
		}
		else {
			if (!function_exists('deleteSubKeysRecursive')) {
				function deleteSubKeysRecursive(&$thisRef, $hKeyId, $subKey) {
					$mapHkeysToString = array(self::HKEY_CLASSES_ROOT   => 'HKEY_CLASSES_ROOT',
											  self::HKEY_CURRENT_USER   => 'HKEY_CURRENT_USER',
											  self::HKEY_LOCAL_MACHINE  => 'HKEY_LOCAL_MACHINE',
											  self::HKEY_USERS          => 'HKEY_USERS',
											  self::HKEY_CURRENT_CONFIG => 'HKEY_CURRENT_CONFIG'
					);

					if (!isset($mapHkeysToString[$hKeyId])) {
						return false;
					}

					$subKeys = $thisRef->getSubKeys($mapHkeysToString[$hKeyId] . '\\' . $subKey);
					if ($subKeys) {
						for ($i = 0, $cnt = count($subKeys); $i < $cnt; $i++) {
							if (!deleteSubKeysRecursive($thisRef, $hKeyId, $subKey . '\\' . $subKeys[$i])) {
								return false;
							}
						}
					}

					return ($thisRef->deleteKey($mapHkeysToString[$hKeyId] . '\\' . $subKey));
				}
			}

			return deleteSubKeysRecursive($this, $hKeyId, $subKey);
		}
	}

	/**
	 * List some key names
	 *
	 * @param string $keyPath
	 * @access public
	 */
	public function getSubKeys($keyPath) {
		$hKeyId = 0;
		$subKey = '';
		if (!$this->_splitKeyPath($keyPath, $hKeyId, $subKey)) {
			return false;
		}

		$keyList = array();
		if (!$this->_enumKeys($hKeyId, $subKey, $keyList)) {
			return null;
		}

		return $keyList;
	}

	/**
	 * Check if some keys exist
	 *
	 * @param string $keyPath
	 * @access public
	 */
	public function keyExists($keyPath) {
		$hKeyId = 0;
		$subKey = '';
		if (!$this->_splitKeyPath($keyPath, $hKeyId, $subKey)) {
			return false;
		}

		return ($this->registryObject->EnumValues($hKeyId, $subKey, new \VARIANT(), new \VARIANT()) == 0);
	}

	/**
	 *
	 * Enter description here ...
	 * @param string  $keyPath
	 * @param integer $hKeyIdResult
	 * @param string  $subKeyResult
	 * @access public
	 */
	private function _splitKeyPath($keyPath, &$hKeyIdResult, &$subKeyResult) {
		$hKeyIdResult = 0;
		$subKeyResult = 'foo';

		$splitPath = explode('\\', $keyPath, 2);

		if (false === $splitPath) {
			return false;
		}
		else if (count($splitPath) == 1) {
			$splitPath[1] = '';
		}

		$subKeyResult = $splitPath[1];

		switch ($splitPath[0]) {
			case 'HKEY_CLASSES_ROOT':
				$hKeyIdResult = self::HKEY_CLASSES_ROOT;
				break;
			case 'HKEY_CURRENT_USER':
				$hKeyIdResult = self::HKEY_CURRENT_USER;
				break;
			case 'HKEY_LOCAL_MACHINE':
				$hKeyIdResult = self::HKEY_LOCAL_MACHINE;
				break;
			case 'HKEY_USERS':
				$hKeyIdResult = self::HKEY_USERS;
				break;
			case 'HKEY_CURRENT_CONFIG':
				$hKeyIdResult = self::HKEY_CURRENT_CONFIG;
				break;
			default:
				return false;
		}

		return true;
	}

	/**
	 *
	 * Enter description here ...
	 * @param integer $hKeyId
	 * @param string  $subKey
	 * @param array   $keyList
	 */
	private function _enumKeys($hKeyId, $subKey, &$keyList) {
		$keyNames = new \VARIANT();
		if ($this->registryObject->EnumKey($hKeyId, $subKey, $keyNames) != 0) {
			return false;
		}

		$keyList = array();

		if (variant_get_type($keyNames) == (VT_VARIANT | VT_ARRAY)) {
			for ($i = 0, $cnt = count($keyNames); $i < $cnt; $i++) {
				$keyList[] = strval($keyNames[$i]);
			}
		}

		return true;
	}

	/**
	 *
	 * Enter description here ...
	 * @param integer $hKeyId
	 * @param string  $subKey
	 * @param array   $valueList
	 */
	private function _enumValues($hKeyId, $subKey, &$valueList) {
		$valueNames = new \VARIANT();
		$valueTypes = new \VARIANT();

		if ($this->registryObject->EnumValues($hKeyId, $subKey, $valueNames, $valueTypes) != 0) {
			return false;
		}

		$valueList = array();

		if (variant_get_type($valueNames) == (VT_VARIANT | VT_ARRAY)) {
			for ($i = 0, $cnt = count($valueNames); $i < $cnt; $i++) {
				$valueList[] = array(strval($valueNames[$i]), intval($valueTypes[$i]));
			}
		}
		else // Handle a bug in StdRegProv's _enumValues (http://groups.google.com/group/microsoft.public.win32.programmer.wmi/browse_thread/thread/d74c0ca865887e6b)
		{
			if ($this->_getStringValue($hKeyId, $subKey, '') != null) {
				$valueList[] = array('', self::REG_SZ);
			}
			else if ($this->_getDWORDValue($hKeyId, $subKey, '') != null) {
				$valueList[] = array('', self::REG_DWORD);
			}
			else if ($this->_getExpandedStringValue($hKeyId, $subKey, '') != null) {
				$valueList[] = array('', self::REG_EXPAND_SZ);
			}
			else if ($this->_getBinaryValue($hKeyId, $subKey, '') != null) {
				$valueList[] = array('', self::REG_BINARY);
			}
			else if ($this->_getMultiStringValue($hKeyId, $subKey, '') != null) {
				$valueList[] = array('', self::REG_MULTI_SZ);
			}
		}

		return true;
	}

	/**
	 *
	 * Enter description here ...
	 * @param integer $hKeyId
	 * @param string  $subKey
	 * @param array   $valueName
	 */
	private function _getValueType($hKeyId, $subKey, $valueName) {
		$valueList = array();
		if (!$this->_enumValues($hKeyId, $subKey, $valueList)) {
			return -1;
		}

		for ($i = 0, $cnt = count($valueList); $i < $cnt; $i++) {
			if ($valueList[$i][0] == $valueName) {
				return $valueList[$i][1];
			}
		}

		return -1;
	}

	/**
	 *
	 * Enter description here ...
	 * @param integer $hKeyId
	 * @param string  $subKey
	 * @param string  $valueName
	 */
	private function _getStringValue($hKeyId, $subKey, $valueName) {
		$stringValue = new \VARIANT();

		return (($this->registryObject->GetStringValue($hKeyId, $subKey, $valueName, $stringValue) == 0) ? strval($stringValue) : null);
	}

	/**
	 *
	 * Enter description here ...
	 * @param integer $hKeyId
	 * @param string  $subKey
	 * @param string  $valueName
	 */
	private function _getExpandedStringValue($hKeyId, $subKey, $valueName) {
		$expandStringValue = new \VARIANT();

		return ((0 == $this->registryObject->GetExpandedStringValue($hKeyId, $subKey, $valueName, $expandStringValue)) ? strval($expandStringValue) : null);
	}

	/**
	 *
	 * Enter description here ...
	 * @param integer $hKeyId
	 * @param string  $subKey
	 * @param string  $valueName
	 * @param boolean $asString
	 */
	private function _getBinaryValue($hKeyId, $subKey, $valueName, $asString = false) {
		$binaryValue = new \VARIANT();
		if ($this->registryObject->GetBinaryValue($hKeyId, $subKey, $valueName, $binaryValue) != 0) {
			return null;
		}

		if ($asString) {
			$result = '';
			for ($i = 0, $cnt = count($binaryValue); $i < $cnt; $i++) {
				$result .= dechex($binaryValue[$i]) . ((($cnt - 1) != $i) ? ' ' : '');
			}
		}
		else {
			$result = array();
			for ($i = 0, $cnt = count($binaryValue); $i < $cnt; $i++) {
				$result .= intval($binaryValue[$i]);
			}
		}

		return $result;
	}

	/**
	 *
	 * Enter description here ...
	 * @param integer $hKeyId
	 * @param string  $subKey
	 * @param string  $valueName
	 * @param boolean $asString
	 */
	private function _getDWORDValue($hKeyId, $subKey, $valueName, $asString = false) {
		$dwordValue = new \VARIANT();

		return (($this->registryObject->GetDWORDValue($hKeyId, $subKey, $valueName, $dwordValue) == 0) ? ($asString ? strval($dwordValue) : intval($dwordValue)) : null);
	}

	/**
	 *
	 * Enter description here ...
	 * @param integer $hKeyId
	 * @param string  $subKey
	 * @param string  $valueName
	 * @param boolean $asString
	 */
	private function _getMultiStringValue($hKeyId, $subKey, $valueName, $asString = false) {
		$multiStringValue = new \VARIANT();
		if ($this->registryObject->GetMultiStringValue($hKeyId, $subKey, $valueName, $multiStringValue) != 0) {
			return null;
		}

		if ($asString) {
			$result = '';
			for ($i = 0, $cnt = count($multiStringValue); $i < $cnt; $i++) {
				$result .= strval($multiStringValue[$i]) . ((($cnt - 1) != $i) ? "\n" : '');
			}
		}
		else {
			$result = array();
			for ($i = 0, $cnt = count($multiStringValue); $i < $cnt; $i++) {
				$result[] = strval($multiStringValue[$i]);
			}
		}

		return $result;
	}
}
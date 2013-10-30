<?php
namespace Scion\Crypt\Key\Derivation;

use Scion\Crypt\Hash;

class Pbkdf2 {

	// These constants may be changed without breaking existing hashes.
	const ITERATIONS = 50000;
	const HASH_BYTES = 128;

	public static function create($algo = Hash::ALGO_SHA512, $password, $salt, $iterations = self::ITERATIONS, $length = self::HASH_BYTES, $raw_output = false) {
		return hash_pbkdf2($algo, $password, $salt, $iterations, $length, $raw_output);
	}

	public static function validate($password, $hash, $salt, $algo = Hash::ALGO_SHA512, $iterations = self::ITERATIONS, $length = self::HASH_BYTES, $raw_output = false) {
		return self::_slowEquals($hash, hash_pbkdf2($algo, $password, $salt, $iterations, $length, $raw_output));
	}

	private static function _slowEquals($a, $b) {
		$diff = strlen($a) ^ strlen($b);
		for ($i = 0; $i < strlen($a) && $i < strlen($b); $i++) {
			$diff |= ord($a[$i]) ^ ord($b[$i]);
		}

		return $diff === 0;
	}
}
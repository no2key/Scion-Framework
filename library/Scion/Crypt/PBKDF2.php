<?php
namespace Scion\Crypt;

class PBKDF2 {
	// These constants may be changed without breaking existing hashes.
	const PBKDF2_HASH_ALGORITHM = "sha512";
	const PBKDF2_ITERATIONS = 50000;
	const PBKDF2_SALT_BYTES = 128;
	const PBKDF2_HASH_BYTES = 128;

	const HASH_SECTIONS = 4;
	const HASH_ALGORITHM_INDEX = 0;
	const HASH_ITERATION_INDEX = 1;
	const HASH_SALT_INDEX = 2;
	const HASH_PBKDF2_INDEX = 3;

	const SALT_1 = 'us_1dUDN4N-53/dkf7Sd?vbc_due1d?df!feg';
	const SALT_2 = 'Yu23ds09*d?u8SDv6sd?usi$_YSdsa24fd+83';
	const SALT_3 = '63fds.dfhsAdyISs_?&jdUsydbv92bf54ggvc';

	public function createHash($password) {
		return hash_pbkdf2(
			self::PBKDF2_HASH_ALGORITHM,
			base64_encode(
				str_rot13(hash(self::PBKDF2_HASH_ALGORITHM, str_rot13(self::SALT_1 . $password . self::SALT_2)))
			),
			self::SALT_3,
			self::PBKDF2_ITERATIONS,
			self::PBKDF2_HASH_BYTES
		);
	}

	public function validateHash($password, $hash)	{
		return $this->_slowEquals(
			$hash,
			hash_pbkdf2(
				self::PBKDF2_HASH_ALGORITHM,
				base64_encode(
					str_rot13(hash(self::PBKDF2_HASH_ALGORITHM, str_rot13(self::SALT_1 . $password . self::SALT_2)))
				),
				self::SALT_3,
				self::PBKDF2_ITERATIONS,
				self::PBKDF2_HASH_BYTES
			)
		);
	}

	private function _slowEquals($a, $b) {
		$diff = strlen( $a ) ^ strlen( $b );
		for( $i = 0; $i < strlen( $a ) && $i < strlen( $b ); $i++ )
		{
			$diff |= ord( $a[ $i ] ) ^ ord( $b[ $i ] );
		}
		return $diff === 0;
	}
}
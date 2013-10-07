<?php
namespace Scion\Crypt\Password;

class Bcrypt implements PasswordInterface {

	protected $cost = '10';
	protected $salt;

	/**
	 * Constructor
	 * @param array $options
	 * @throws \Exception
	 */
	public function __construct(array $options = []) {
		if (!empty($options)) {
			if (!is_array($options)) {
				throw new \Exception('The options parameter must be an array');
			}
			foreach ($options as $key => $value) {
				switch (strtolower($key)) {
					case 'salt':
						$this->salt = $value;
						break;
					case 'cost':
						$this->cost = $value;
						break;
				}
			}
		}
	}

	/**
	 * Create a secure password
	 * @param string $password
	 * @return string
	 */
	public function create($password) {
		$options = [];
		$options['cost'] = $this->cost;

		if (!empty($this->salt)) {
			$options['salt'] = $this->salt;
		}

		return password_hash($password, PASSWORD_BCRYPT, $options);
	}

	/**
	 * Verify if a password is correct against an hash value
	 * @param string $password
	 * @param string $hash
	 * @return bool
	 */
	public function verify($password, $hash) {
		return password_verify($password, $hash);
	}
}
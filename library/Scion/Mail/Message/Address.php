<?php

namespace Scion\Mail\Message;
use \InvalidArgumentException;

class Address {

	/**
	 * Author name of the address
	 *
	 * @var string
	 */
	private $name;

	/**
	 * Email address
	 *
	 * @var string
	 */
	private $email;

	/**
	 * Constructs the representation
	 *
	 * @param string $email the email address
	 * @param string $name  the author name
	 */
	public function __construct($email, $name = null) {
		$this->setName($name);
		$this->setEmail($email);
	}

	/**
	 * Retrieves author name
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Sets the author name
	 *
	 * @param string $name author name
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * Retrieves the email address
	 *
	 * @return string
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * Sets the email address
	 *
	 * @param string $email the email address
	 */
	public function setEmail($email) {
		if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$message = "The email address %s is not valid";
			throw new InvalidArgumentException(sprintf($message, $email));
		}
		$this->email = $email;
	}

}
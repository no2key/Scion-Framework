<?php
namespace Scion\Mail\Message;
use \Countable;
use \IteratorAggregate;
use Scion\Mail\Message\Address;
use Scion\Mail\Message\Header;

interface AddressList extends \Countable, IteratorAggregate {

	/**
	 * Retrieves all stacked address on the list
	 *
	 * @return array[Address]
	 */
	public function getAddresses();

	/**
	 * Adds an address in list
	 *
	 * @param Address $address the address to be added
	 */
	public function addAddress(Address $address);

}
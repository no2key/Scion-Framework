<?php
namespace Scion\Mail\Message;

use \Countable;
use \ArrayIterator;
use \IteratorAggregate;
use Scion\Mail\Message\Header;

class HeaderSet implements Countable, IteratorAggregate {

	/**
	 * Set of inserted headers for a mail message.
	 *
	 * @var array[Header]
	 */
	private $headers = array();

	/**
	 * Inserts an header in the current set
	 *
	 * @param Header $header the header to be inserted
	 *
	 * @return HeaderSet
	 */
	public function insert(Header $header) {
		$this->headers[$header->getName()] = $header;

		return $this;
	}

	/**
	 * Removes the specified header from the set
	 *
	 * @param string $header the header name to be removed
	 *
	 * @return boolean
	 */
	public function remove($header) {
		if ($this->contains($header)) {
			unset($this->headers[$header]);

			return true;
		}
		return false;
	}

	/**
	 * Retrieves a header from the set
	 *
	 * @return Header
	 */
	public function getHeader($header) {
		return $this->contains($header) ? $this->headers[$header] : false;
	}

	/**
	 * Checks if exists an named header on the set
	 *
	 * @param string $header the header name to check existence
	 *
	 * @return boolean
	 */
	public function contains($header) {
		return array_key_exists($header, $this->toArray());
	}

	/**
	 * Converts the collection to an array
	 *
	 * @return array[Header]
	 */
	public function toArray() {
		return $this->headers;
	}

	/**
	 * Amount of headers in the collection
	 *
	 * @return integer
	 */
	public function count() {
		return count($this->toArray());
	}

	/**
	 * Retrieves an iterator for collection
	 *
	 * @return ArrayIterator
	 */
	public function getIterator() {
		return new ArrayIterator($this->toArray());
	}

}
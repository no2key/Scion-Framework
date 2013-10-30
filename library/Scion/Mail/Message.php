<?php
namespace Scion\Mail;

use Scion\Mail\Message\Address;
use Scion\Mail\Message\AddressList;
use Scion\Mail\Message\Header;
use Scion\Mail\Message\HeaderSet;

use Scion\Mail\Message\Encodable;
use \ReflectionClass;

use Scion\Mail\Message\Header\From;
use Scion\Mail\Message\Header\Subject;
use Scion\Mail\Message\Header\Date;

class Message implements Encodable {

	/**
	 * Message encoding (default: ASCII)
	 *
	 * @var string
	 */
	private $encoding = "ASCII";

	/**
	 * An set of message headers
	 *
	 * @var HeaderSet
	 */
	private $headerSet;

	/**
	 * Message body
	 *
	 * @var string
	 */
	private $body;

	/**
	 * From representation
	 *
	 * @var Address
	 */
	private $from;

	/**
	 * - Constructor
	 * Adds some mail lists, to be filled in the future if necessary.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->headerSet = new HeaderSet();
		$this->headerSet->insert(new Date());
	}

	/**
	 * Adds an email address to a header that is composed by email(s)
	 *
	 * @param string $header the header name that is an AddressList
	 * @param string $email  the email address to be added in the list
	 * @param string $name   the author name of email address
	 *
	 * @return Message
	 */
	private function addAddressToList($header, $email, $name = null) {
		if (! $this->headerSet->contains($header)) {
			$reflection = new ReflectionClass(sprintf("%s\\Message\\Header\\%s", __NAMESPACE__, $header));
			$this->headerSet->insert($reflection->newInstance());
		}

		if (($header = $this->headerSet->getHeader($header)) instanceof AddressList) {
			$header->addAddress(new Address($email, $name));

			return $this;
		}
		return false;
	}

	/**
	 * Adds email address of a recipient.
	 *
	 * @param string $email the email address to be added
	 * @param string $name  the author name of email address
	 *
	 * @return Message
	 */
	public function addTo($email, $name = null) {
		return $this->addAddressToList("To", $email, $name);
	}

	/**
	 * Adds an carbon copy to specified email address
	 *
	 * @param string $email the email address to be added
	 * @param string $name  the author name of email address
	 *
	 * @return Message
	 */
	public function cc($email, $name = null) {
		return $this->addAddressToList("Cc", $email, $name);
	}

	/**
	 * Adds an blind carbon copy to specified email address
	 *
	 * @param string $email the email address to be added
	 * @param string $name  the author name of email address
	 *
	 * @return Message
	 */
	public function bcc($email, $name = null) {
		return $this->addAddressToList("Bcc", $email, $name);
	}

	/**
	 * Adds email address(es) of from sender(s)
	 *
	 * @param string $email the email address to be added
	 * @param string $name  the author name of email address
	 *
	 * @return Message
	 */
	public function addFrom($email, $name = null) {
		$this->from = new Address($email, $name);
		$this->headerSet->insert(new From($email, $name, $this->getEncoding()));

		return $this;
	}

	/**
	 * Defines the message subject
	 *
	 * @param string $subject the subject of message
	 *
	 * @return Message
	 */
	public function setSubject($subject) {
		$this->headerSet->insert(new Subject($subject, $this->getEncoding()));

		return $this;
	}

	/**
	 * Adds an address that receives the reply when someone replies the message.
	 *
	 * @param string $email the email address to be added
	 * @param string $name  the author name of email address
	 *
	 * @return Message
	 */
	public function replyTo($email, $name = null) {
		return $this->addAddressToList("ReplyTo", $email, $name);
	}

	/**
	 * Sets the message body
	 *
	 * @param string $body the message body
	 *
	 * @return Message
	 */
	public function setBody($body) {
		$this->body = $body;

		return $this;
	}

	/**
	 * @see Encodable::setEncoding()
	 * @return void
	 */
	public function setEncoding($encoding) {
		$this->encoding = $encoding;
		foreach ($this->getHeaderSet() AS $header) {
			$header->setEncoding($encoding);
		}
	}

	/**
	 * Retrieves an address list from specified header.
	 *
	 * @param string $header the header that is an address list
	 *
	 * @return AddressList
	 */
	private function getAddressListFromHeader($header) {
		if ($this->headerSet->contains($header)) {
			$header = $this->headerSet->getHeader($header);
			if ($header instanceof AddressList) {
				return $header->getAddresses();
			}
		}

		return array();
	}

	/**
	 * Retrieves all recipients
	 *
	 * @return array[Address]
	 */
	public function getTo() {
		return $this->getAddressListFromHeader("To");
	}

	/**
	 * Retrieves the sender address
	 *
	 * @return Address
	 */
	public function getFrom() {
		return $this->from;
	}

	/**
	 * Retrieves all addresses that receive an copy of message
	 *
	 * @return array[Address]
	 */
	public function getCc() {
		return $this->getAddressListFromHeader("Cc");
	}

	/**
	 * Retrieves all addresses that receive an blind copy of message
	 *
	 * @return array[Address]
	 */
	public function getBcc() {
		return $this->getAddressListFromHeader("Bcc");
	}

	/**
	 * Retrieves the list of addresses that receives the reply
	 * when someone replies the message.
	 *
	 * @return array[Address]
	 */
	public function getReplyTo() {
		return $this->getAddressListFromHeader("ReplyTo");
	}

	/**
	 * Retrieves the message body
	 *
	 * @return string
	 */
	public function getBody() {
		return $this->body;
	}

	/**
	 * @see Encodable::getEncoding()
	 * @return string
	 */
	public function getEncoding() {
		return $this->encoding;
	}

	/**
	 * Retrieves the headers of message
	 *
	 * @return HeaderSet
	 */
	public function getHeaderSet() {
		return $this->headerSet;
	}

}
<?php
namespace Scion\Http\Header;

use Scion\Http\Headers;

/**
 * The UserAgent class provides functions that help identify information
 * about the browser, mobile device, or robot visiting your site.
 * @package Scion\Http\Header
 */
class UserAgent {

	protected $uaHttpHeaders = [
		'HTTP_USER_AGENT', // The default User-Agent string.
		'HTTP_X_OPERAMINI_PHONE_UA', // Header can occur on devices using Opera Mini.
		// Vodafone specific header: http://www.seoprinciple.com/mobile-web-community-still-angry-at-vodafone/24/
		'HTTP_X_DEVICE_USER_AGENT',
		'HTTP_X_ORIGINAL_USER_AGENT',
		'HTTP_X_SKYFIRE_PHONE',
		'HTTP_X_BOLT_PHONE_UA',
		'HTTP_DEVICE_STOCK_UA',
		'HTTP_X_UCBROWSER_DEVICE_UA'
	];

	protected $uaHttpHeader = [];
	protected $userAgent = null;

	/**
	 * Constructor
	 */
	public function __construct(Headers $headers) {
		foreach ($this->uaHttpHeaders as $altHeader) {
			if (!empty($headers->getHttpHeaders()[$altHeader])) {
				$this->userAgent .= $headers->getHttpHeaders()[$altHeader] . " ";
			}
		}
	}

	/**
	 * Get the current user agent
	 * @return null|string
	 */
	public function get() {
		return $this->userAgent;
	}

}
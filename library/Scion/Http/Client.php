<?php
namespace Scion\Http;

use Scion\Validator\Ip;

class Client {

	/**
	 * Return IP address
	 * @return string
	 */
	public function getIp() {
		$ip = new Ip();

		if (isset($_SERVER['HTTP_CLIENT_IP']) && $ip->isValid($_SERVER['HTTP_CLIENT_IP'])) {
			return $_SERVER['HTTP_CLIENT_IP'];
		}
		if (isset($_SERVER['HTTP_X_FORWARDED']) && $ip->isValid($_SERVER['HTTP_X_FORWARDED'])) {
			return $_SERVER['HTTP_X_FORWARDED'];
		}
		if (isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && $ip->isValid($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
			return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
		}
		if (isset($_SERVER['HTTP_FORWARDED_FOR']) && $ip->isValid($_SERVER['HTTP_FORWARDED_FOR'])) {
			return $_SERVER['HTTP_FORWARDED_FOR'];
		}
		if (isset($_SERVER['HTTP_FORWARDED']) && $ip->isValid($_SERVER['HTTP_FORWARDED'])) {
			return $_SERVER['HTTP_FORWARDED'];
		}

		return $_SERVER['REMOTE_ADDR'];
	}

}
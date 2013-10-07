<?php
namespace Scion\Date;

class DateTime extends \DateTime {

	/**
	 * MySQL DateTime / Date / Time format
	 */
	const MYSQL_DATETIME = 'Y-m-d H:i:s';
	const MYSQL_DATE = 'Y-m-d';
	const MYSQL_TIME = 'H:i:s';

	/**
	 * Get DateTime now
	 * @param string $format
	 * @param string $modify
	 * @return string
	 */
	public function now($format, $modify = '+0') {
		parent::__construct('now');
		parent::modify($modify);
		return parent::format($format);
	}
}
<?php
namespace Scion\Db\Provider;

class Sqlite extends AbstractProvider {

	protected $timeFunc = 'datetime(current_timestamp)';

	/**
	 * @see AbstractProvider\getDsn()
	 * @return string
	 */
	public function getDsn() {
		return 'sqlite:' . $this->_parameters->dsn->database;
	}

}
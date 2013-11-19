<?php
namespace Scion\Db\Provider;

class PgSql extends AbstractProvider {

	protected $timeFunc = 'now';

	/**
	 * @see AbstractProvider\getDsn()
	 * @return string
	 */
	public function getDsn() {
		$dsn = 'pgsql:';

		if (!empty($this->_parameters->dsn->hostname)) {
			$dsn .= 'host=' . $this->_parameters->dsn->hostname . ' ';
		}
		if (!empty($this->_parameters->dsn->port)) {
			$dsn .= 'port=' . $this->_parameters->dsn->port . ';';
		}
		if (!empty($this->_parameters->dsn->database)) {
			$dsn .= 'dbname=' . $this->_parameters->dsn->database . ';';
		}

		return $dsn;
	}
} 
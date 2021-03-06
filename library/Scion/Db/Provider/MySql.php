<?php
namespace Scion\Db\Provider;

class MySql extends AbstractProvider {

	protected $timeFunc = 'NOW()';

	/**
	 * @see AbstractProvider\getDsn()
	 * @return string
	 */
	public function getDsn() {
		$dsn = 'mysql:';

		if (!empty($this->_parameters->dsn->hostname)) {
			$dsn .= 'host=' . $this->_parameters->dsn->hostname . ';';
		}
		if (!empty($this->_parameters->dsn->port)) {
			$dsn .= 'port=' . $this->_parameters->dsn->port . ';';
		}
		if (!empty($this->_parameters->dsn->database)) {
			$dsn .= 'dbname=' . $this->_parameters->dsn->database . ';';
		}
		if (!empty($this->_parameters->dsn->unixSocket)) {
			$dsn .= 'unix_socket=' . $this->_parameters->dsn->unixSocket . ';';
		}
		if (!empty($this->_parameters->dsn->charset)) {
			$dsn .= 'charset=' . $this->_parameters->dsn->charset . ';';
		}

		return $dsn;
	}
}
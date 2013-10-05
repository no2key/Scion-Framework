<?php
namespace Scion\Models\Db\Provider;

class Sqlite extends AbstractProvider {

	/**
	 * @see AbstractProvider\getDsn()
	 * @return string
	 */
	public function getDsn() {
		return 'sqlite:' . $this->_parameters->dsn->database;
	}

}
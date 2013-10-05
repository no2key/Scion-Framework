<?php
namespace Scion\Models\Db\Query;

use Scion\Models\Db\Provider\AbstractProvider;

class Select extends AbstractCommon {

	private $fromTable;
	private $fromAlias;

	public function __construct(AbstractProvider $fpdo, $from) {
		$clauses = array(
			'SELECT'   => ', ',
			'FROM'     => null,
			'JOIN'     => array($this, 'getClauseJoin'),
			'WHERE'    => ' AND ',
			'GROUP BY' => ',',
			'HAVING'   => ' AND ',
			'ORDER BY' => ', ',
			'LIMIT'    => null,
			'OFFSET'   => null,
			"\n--"     => "\n--",
		);
		parent::__construct($fpdo, $clauses);

		# initialize statements
		$fromParts       = explode(' ', $from);
		$this->fromTable = reset($fromParts);
		$this->fromAlias = end($fromParts);

		$this->statements['FROM']     = $from;
		$this->statements['SELECT'][] = $this->fromAlias . '.*';
		$this->joins[]                = $this->fromAlias;
	}

	/** Return table name from FROM clause
	 *
	 * @internal
	 */
	public function getFromTable() {
		return $this->fromTable;
	}

	/** Return table alias from FROM clause
	 *
	 * @internal
	 */
	public function getFromAlias() {
		return $this->fromAlias;
	}

	/** Returns a single column
	 *
	 * @param int $columnNumber
	 *
	 * @return string
	 */
	public function fetchColumn($columnNumber = 0) {
		if ($s = $this->execute()) {
			return $s->fetchColumn($columnNumber);
		}

		return false;
	}

	/** Fetch first row or column
	 *
	 * @param string $column column name or empty string for the whole row
	 *
	 * @return mixed string, array or false if there is no row
	 */
	public function fetch($column = '') {
		$return = $this->execute();
		if ($return === false) {
			return false;
		}
		$return = $return->fetch();
		if ($return && $column != '') {
			if (is_object($return)) {
				return $return->{$column};
			}
			else {
				return $return[$column];
			}
		}

		return $return;
	}

	/**
	 * Fetch pairs
	 *
	 * @param $key
	 * @param $value
	 * @param $object
	 *
	 * @return array of fetched rows as pairs
	 */
	public function fetchPairs($key, $value, $object = false) {
		if ($s = $this->select(null)->select("$key, $value")->asObject($object)->execute()) {
			return $s->fetchAll(PDO::FETCH_KEY_PAIR);
		}

		return false;
	}

	/** Fetch all row
	 *
	 * @param string $index       specify index column
	 * @param string $selectOnly  select columns which could be fetched
	 *
	 * @return array of fetched rows
	 */
	public function fetchAll($index = '', $selectOnly = '') {
		if ($selectOnly) {
			$this->select(null)->select($index . ', ' . $selectOnly);
		}
		if ($index) {
			$data = array();
			foreach ($this as $row) {
				if (is_object($row)) {
					$data[$row->{$index}] = $row;
				}
				else {
					$data[$row[$index]] = $row;
				}
			}

			return $data;
		}
		else {
			if ($s = $this->execute()) {
				return $s->fetchAll();
			}

			return false;
		}
	}
}
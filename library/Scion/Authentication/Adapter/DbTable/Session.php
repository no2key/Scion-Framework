<?php
namespace Scion\Authentication\Adapter\DbTable;

use Scion\Authentication\Adapter\DbTable;
use Scion\Db\Pdo;
use Scion\Http\Client;

class Session {

	private $_dbh;

	public function __construct($dbh) {
		$this->_dbh = $dbh;
	}

	/**
	 * Creates a session for a specified user id
	 * @param int    $uid
	 * @param string $expire
	 * @return array $data
	 */
	public function add($uid, $expire) {
		$data         = $this->_dbh->from('users')->select(null)->select('salt, lang')->where('user_id = ?', $uid)->execute()->fetch(Pdo::FETCH_ASSOC);
		$data['hash'] = sha1($data['salt'] . microtime());

		$agent = $_SERVER['HTTP_USER_AGENT'];

		$this->deleteFromUid($uid);

		$data['expire']     = date("Y-m-d H:i:s", strtotime($expire));
		$data['cookie_crc'] = sha1($data['hash'] . DbTable::SITE_KEY);

		$this->_dbh->insertInto('sessions', ['uid'       => $uid, 'hash' => $data['hash'],
											'expiredate' => $data['expire'], 'ip' => (new Client)->getIp(),
											'agent'      => $agent, 'cookie_crc' => $data['cookie_crc'],
											'lang'       => $data['lang']
											])->execute();

		return $data;
	}

	/**
	 * Removes all existing sessions for a given UID
	 * @param int $uid
	 * @return bool
	 */
	public function deleteFromUid($uid) {
		return $this->_dbh->deleteFrom('sessions')->where('uid', $uid)->execute();
	}

	/**
	 * Removes a session based on hash
	 * @param string $hash
	 * @return bool
	 */
	public function deleteFromHash($hash) {
		$query = $this->_dbh->deleteFrom('sessions')->where('hash', $hash)->execute();
		if ($query) {
			return $this->_dbh->from('sessions')->select(null)->select('uid')->where('hash', $hash)->execute()->fetch(Pdo::FETCH_ASSOC);
		}
		return false;
	}

	/**
	 * Updates the IP of a session (used if IP has changed, but agent has remained unchanged)
	 * @param int $sid
	 * @return bool
	 */
	public function updateIp($sid) {
		return $this->_dbh->update('sessions')->set('ip', (new Client())->getIp())->where('id', $sid)->execute();
	}

	/**
	 * Gets UID from Session hash
	 * @param string $hash
	 * @return int $uid
	 */
	/*public function sessionUID($hash) {
		if (strlen($hash) === 40) {
			$query = $this->dbh->prepare("SELECT uid FROM " . $this->config->table_sessions . " WHERE hash = ?");
			$query->execute(array($hash));
			$row = $query->fetch(\PDO::FETCH_ASSOC);

			if ($row) {
				return $row['uid'];
			}
		}

		return false;
	}*/

}
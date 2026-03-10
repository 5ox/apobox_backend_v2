<?php
/**
 * Node Session save handler. Allows saving session information into Node accessible Cache.
 */

App::uses('Cache', 'Cache');
App::uses('CakeSessionHandlerInterface', 'Model/Datasource/Session');
App::uses('CakeText', 'Utility');

/**
 * NodeSession provides methods for saving sessions into a Node accessible Cache engine.
 * Used with CakeSession.
 *
 * @codeCoverageIgnore
 */
class NodeSession implements CakeSessionHandlerInterface {

	/**
	 * Method called on open of a node session.
	 *
	 * @return bool Success
	 */
	public function open() {
		if (!isset($_COOKIE[session_name()])) {
			$id = $this->_generateSessionId();
			session_id($id);
		}
		return true;
	}

	/**
	 * Generates a Node.js compatible session id.
	 *
	 * @return string The new session id.
	 */
	protected function _generateSessionId() {
		$uuid = CakeText::uuid();
		$hmac = preg_replace('/\=+$/', '', base64_encode(hash_hmac('sha256', $uuid, Configure::read('Cookie.secret'), true)));
		return 's:' . $uuid . '.' . $hmac;
	}

	/**
	 * Method called on close of a node session.
	 *
	 * @return bool Success
	 */
	public function close() {
		return true;
	}

	/**
	 * Method used to read from a node session.
	 *
	 * @param string $id The key of the value to read.
	 * @return mixed The value of the key or false if it does not exist.
	 */
	public function read($id) {
		$data = $this->_getSession($id);
		return $this->_serializeData($data);
	}

	/**
	 * Helper function called on write for node sessions.
	 *
	 * @param int $id ID that uniquely identifies session in node.
	 * @param mixed $data The value of the data to be saved.
	 * @return bool True for successful write, false otherwise.
	 */
	public function write($id, $data) {
		$data = $this->_unserializeData($data);
		$data = $this->_ensureNodeData($data);
		$data = json_encode($data);
		if ($data === false) {
			return false;
		}
		return Cache::write($this->_decodeId($id), $data, Configure::read('Session.handler.config'));
	}

	/**
	 * Method called on the destruction of a node session.
	 *
	 * @param int $id ID that uniquely identifies session in cache
	 * @return bool True for successful delete, false otherwise.
	 */
	public function destroy($id) {
		return Cache::delete($id, Configure::read('Session.handler.config'));
	}

	/**
	 * Helper function called on gc for cache sessions.
	 *
	 * @param int $expires Timestamp (defaults to current time)
	 * @return bool Success
	 */
	public function gc($expires = null) {
		return Cache::gc(Configure::read('Session.handler.config'), $expires);
	}

	/**
	 * Gets the session and returns it if it exists.
	 *
	 * @param mixed $id The id
	 * @return mixed The session array or null if it does not exist.
	 */
	protected function _getSession($id) {
		$id = $this->_decodeId($id);
		return json_decode(Cache::read($id, Configure::read('Session.handler.config')), true);
	}

	/**
	 * Decodes a node session id.
	 *
	 * @param mixed $id The id
	 * @return mixed The session array or null if it does not exist.
	 */
	protected function _decodeId($id) {
		if (strpos($id, 's:') === 0) {
			return substr($id, 2, 36);
		}
		return $id;
	}

	/**
	 * Adds a cookie key to the session data for Node compatibility.
	 *
	 * @param mixed $data The data
	 * @return array The session data.
	 */
	protected function _ensureNodeData($data) {
		if (empty($data['cookie'])) {
			$cookie = session_get_cookie_params();
			$data['cookie'] = array(
				'originalMaxAge' => null, //'lifetime' => (int) 14400,
				'expires' => null,
				'secure' => $cookie['secure'],
				'httpOnly' => $cookie['httponly'],
				'domain' => $cookie['domain'],
				'path' => $cookie['path'],
			);
		}
		return $data;
	}

	/**
	 * Converts a php array into a session encoded string.
	 *
	 * @param string $sessionData A php session encoded string.
	 * @return array A decoded array of session data.
	 */
	protected function _serializeData($sessionData) {
		if (!$sessionData) {
			return null;
		}
		$returnArray = '';
		foreach ($sessionData as $key => $value) {
			$returnArray[] = $key . '|' . serialize($value);
		}
		return implode($returnArray, '');
	}

	/**
	 * Converts a session encoded string into a php array.
	 *
	 * @param string $sessionData A php session encoded string.
	 * @return array A decoded array of session data.
	 * @throws Exception
	 */
	protected function _unserializeData($sessionData) {
		$returnData = array();
		$offset = 0;
		$dataLength = strlen($sessionData);
		while ($offset < $dataLength) {
			if (!strstr(substr($sessionData, $offset), "|")) {
				throw new Exception("invalid data, remaining: " . substr($sessionData, $offset));
			}
			$pos = strpos($sessionData, "|", $offset);
			$num = $pos - $offset;
			$varname = substr($sessionData, $offset, $num);
			$offset += $num + 1;
			$data = unserialize(substr($sessionData, $offset));
			$returnData[$varname] = $data;
			$offset += strlen(serialize($data));
		}
		return $returnData;
	}
}


<?php

namespace Zendesk\API;

/**
 * The Groups class exposes ticket group information
 */
class Groups extends ClientAbstract {

	/*
	 * List all groups
	 */
	public function findAll(array $params = array()) {
		if($this->client->users()->getLastId() != null) {
			$params['user_id'] = $this->client->users()->getLastId();
			$this->client->users()->setLastId(null);
		}
		$endPoint = Http::prepare(
				(isset($params['user_id']) ? 'users/'.$params['user_id'].'/groups.json' : 
				(isset($params['assignable']) ? 'groups/assignable.json' : 'groups.json'))
		);
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		$this->client->setSideload(null);
		return $response;
	}

	/*
	 * Show a specific group
	 */
	public function find(array $params = array()) {
		if($this->lastId != null) {
			$params['id'] = $this->lastId;
			$this->lastId = null;
		}
		if(!$this->hasKeys($params, array('id'))) {
			throw new MissingParametersException(__METHOD__, array('id'));
		}
		$endPoint = Http::prepare('groups/'.$params['id'].'.json');
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		$this->client->setSideload(null);
		return $response;
	}

	/*
	 * Create a new group
	 */
	public function create(array $params) {
		$endPoint = Http::prepare('groups.json');
		$response = Http::send($this->client, $endPoint, array ('group' => $params), 'POST');
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 201)) {
			throw new ResponseException(__METHOD__);
		}
		return $response;
	}

	/*
	 * Update a group
	 */
	public function update(array $params) {
		if($this->lastId != null) {
			$params['id'] = $this->lastId;
			$this->lastId = null;
		}
		if(!$params['id']) {
			throw new MissingParametersException(__METHOD__, array('id'));
		}
		$id = $params['id'];
		unset($params['id']);
		$endPoint = Http::prepare('groups/'.$id.'.json');
		$response = Http::send($this->client, $endPoint, array ('group' => $params), 'PUT');
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		return $response;
	}

	/*
	 * Delete a group
	 */
	public function delete(array $params = array()) {
		if($this->lastId != null) {
			$params['id'] = $this->lastId;
			$this->lastId = null;
		}
		if(!$this->hasKeys($params, array('id'))) {
			throw new MissingParametersException(__METHOD__, array('id'));
		}
		$endPoint = Http::prepare('groups/'.$params['id'].'.json');
		$response = Http::send($this->client, $endPoint, null, 'DELETE');
		if ($this->client->getDebug()->lastResponseCode != 200) {
			throw new ResponseException(__METHOD__);
		}
		return true;
	}

	public function members($id = null) { return ($id != null ? $this->client->groupMemberships()->setLastId($id) : $this->client->groupMemberships()); }
	public function member($id) { return $this->client->groupMemberships()->setLastId($id); }

}

?>

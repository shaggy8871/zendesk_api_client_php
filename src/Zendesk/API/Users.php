<?php

namespace Zendesk\API;

/**
 * The Users class exposes user management methods
 * Note: you must authenticate as a user!
 */
class Users extends ClientAbstract {

	/*
	 * List all users
	 */
	public function findAll(array $params = array()) {
		$endPoint = Http::prepare(
				(isset($params['organization_id']) ? 'organizations/'.$params['organization_id'].'/users' : 
				(isset($params['group_id']) ? 'groups/'.$params['group_id'].'/users' : 'users')
			).'.json'.(isset($params['role']) ? (is_array($params['role']) ? '&role[]='.implode('&role[]=', $params['role']) : '?role='.$params['role']) : '').(isset($params['permission_set']) ? (isset($params['role']) ? '&' : '?').'permission_set='.$params['permission_set'] : ''), ((isset($params['sideload'])) && (is_array($params['sideload'])) ? $params['sideload'] : $this->client->getSideload()));
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		$this->client->setSideload(null);
		return $response;
	}

	/*
	 * Show a specific user
	 */
	public function find(array $params = array()) {
		if($this->lastId != null) {
			$params['id'] = $this->lastId;
			$this->lastId = null;
		}
		if(!$params['id']) {
			throw new MissingParametersException(__METHOD__, array('id'));
		}
		$endPoint = Http::prepare('users/'.$params['id'].'.json');
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		$this->client->setSideload(null);
		return $response;
	}

	/*
	 * Get related information about the user
	 */
	public function related(array $params = array()) {
		if($this->lastId != null) {
			$params['id'] = $this->lastId;
			$this->lastId = null;
		}
		if(!$params['id']) {
			throw new MissingParametersException(__METHOD__, array('id'));
		}
		$endPoint = Http::prepare('users/'.$params['id'].'/related.json');
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		$this->client->setSideload(null);
		return $response;
	}

	/*
	 * Create a new user
	 */
	public function create(array $params) {
		$endPoint = Http::prepare('users.json');
		$response = Http::send($this->client, $endPoint, array ('user' => $params), 'POST');
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 201)) {
			throw new ResponseException(__METHOD__);
		}
		return $response;
	}

	/*
	 * Merge the specified user (???)
	 */
	public function merge(array $params = array()) {
		if($this->lastId != null) {
			$params['id'] = $this->lastId;
			$this->lastId = null;
		}
		if(!$params['id']) {
			throw new MissingParametersException(__METHOD__, array('id'));
		}
		$id = $params['id'];
		unset($params['id']);
		$endPoint = Http::prepare('users/me/merge.json');
		$response = Http::send($this->client, $endPoint, array ('user' => $params), 'PUT');
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		return $response;
	}

	/*
	 * Create multiple new users
	 */
	public function createMany(array $params) {
		$endPoint = Http::prepare('users/create_many.json');
		$response = Http::send($this->client, $endPoint, array ('users' => $params), 'POST');
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		return $response;
	}

	/*
	 * Update a user
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
		$endPoint = Http::prepare('users/'.$id.'.json');
		$response = Http::send($this->client, $endPoint, array ('user' => $params), 'PUT');
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		return $response;
	}

	/*
	 * Suspend a user
	 */
	public function suspend(array $params = array()) {
		if($this->lastId != null) {
			$params['id'] = $this->lastId;
			$this->lastId = null;
		}
		if(!$params['id']) {
			throw new MissingParametersException(__METHOD__, array('id'));
		}
		$params['suspended'] = true;
		return $this->update($params);
	}

	/*
	 * Delete a user
	 */
	public function delete(array $params = array()) {
		if($this->lastId != null) {
			$params['id'] = $this->lastId;
			$this->lastId = null;
		}
		if(!$params['id']) {
			throw new MissingParametersException(__METHOD__, array('id'));
		}
		$id = $params['id'];
		$endPoint = Http::prepare('users/'.$id.'.json');
		$response = Http::send($this->client, $endPoint, null, 'DELETE');
		if ($this->client->getDebug()->lastResponseCode != 200) {
			throw new ResponseException(__METHOD__);
		}
		return true;
	}

	/*
	 * Search for users
	 */
	public function search(array $params) {
		$endPoint = Http::prepare('users/search.json?'.http_build_query($params), ((isset($params['sideload'])) && (is_array($params['sideload'])) ? $params['sideload'] : $this->client->getSideload()));
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		$this->client->setSideload(null);
		return $response;
	}

	/*
	 * Requests autocomplete for users
	 */
	public function autocomplete(array $params) {
		$endPoint = Http::prepare('users/autocomplete.json?'.http_build_query($params), ((isset($params['sideload'])) && (is_array($params['sideload'])) ? $params['sideload'] : $this->client->getSideload()));
		$response = Http::send($this->client, $endPoint, null, 'POST');
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		$this->client->setSideload(null);
		return $response;
	}

	/*
	 * Update a user's profile image
	 */
	public function updateProfileImage(array $params) {
		if($this->lastId != null) {
			$params['id'] = $this->lastId;
			$this->lastId = null;
		}
		if((!$params['id']) || (!$params['file'])) {
			throw new MissingParametersException(__METHOD__, array('id', 'file'));
		}
		if(!file_exists($params['file'])) {
			throw new CustomException('File '.$params['file'].' could not be found in '.__METHOD__);
		}
		$id = $params['id'];
		unset($params['id']);
		$endPoint = 'users/'.$id.'.json';
		$response = Http::send($this->client, $endPoint, array('user[photo][uploaded_data]' => '@'.$params['file']), 'PUT', (isset($params['type']) ? $params['type'] : 'application/binary'));
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		return $response;
	}

	/*
	 * Show the current user
	 */
	public function me(array $params = array()) {
		$params['id'] = 'me';
		return $this->find($params);
	}

	/*
	 * Sets a user's initial password
	 */
	public function setPassword(array $params) {
		if($this->lastId != null) {
			$params['id'] = $this->lastId;
			$this->lastId = null;
		}
		if(!$this->hasKeys($params, array('id', 'password'))) {
			throw new MissingParametersException(__METHOD__, array('id', 'password'));
		}
		$id = $params['id'];
		unset($params['id']);
		$endPoint = Http::prepare('users/'.$id.'/password.json');
		$response = Http::send($this->client, $endPoint, $params, 'POST');
		if ($this->client->getDebug()->lastResponseCode != 200) {
			throw new ResponseException(__METHOD__);
		}
		return $response;
	}

	/*
	 * Change a user's password
	 */
	public function changePassword(array $params) {
		if($this->lastId != null) {
			$params['id'] = $this->lastId;
			$this->lastId = null;
		}
		if(!$this->hasKeys($params, array('id', 'previous_password', 'password'))) {
			throw new MissingParametersException(__METHOD__, array('id', 'previous_password', 'password'));
		}
		$id = $params['id'];
		unset($params['id']);
		$endPoint = Http::prepare('users/'.$id.'/password.json');
		$response = Http::send($this->client, $endPoint, $params, 'PUT');
		if ($this->client->getDebug()->lastResponseCode != 200) {
			throw new ResponseException(__METHOD__);
		}
		return $response;
	}

}

?>

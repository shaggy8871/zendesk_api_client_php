<?php

namespace Zendesk\API;

/**
 * The Triggers class exposes methods as detailed on http://developer.zendesk.com/documentation/rest_api/triggers.html
 */
class Triggers extends ClientAbstract {

    const OBJ_NAME = 'trigger';
    const OBJ_NAME_PLURAL = 'triggers';

	/*
	 * List triggers
	 */
	public function findAll(array $params = array()) {
		$endPoint = Http::prepare((isset($params['active']) ? 'triggers/active.json' : 'triggers.json'));
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		$this->client->setSideload(null);
		return $response;
	}

	/*
	 * Show a specific trigger
	 */
	public function find(array $params = array()) {
		if($this->lastId != null) {
			$params['id'] = $this->lastId;
			$this->lastId = null;
		}
		if(!$this->hasKeys($params, array('id'))) {
			throw new MissingParametersException(__METHOD__, array('id'));
		}
        $endPoint = Http::prepare('triggers/'.$params['id'].'.json');
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		$this->client->setSideload(null);
		return $response;
	}

	/*
	 * Create a trigger
	 */
	public function create(array $params) {
        $endPoint = Http::prepare('triggers.json');
		$response = Http::send($this->client, $endPoint, array(self::OBJ_NAME => $params), 'POST');
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 201)) {
			throw new ResponseException(__METHOD__);
		}
		return $response;
	}

	/*
	 * Update a trigger
	 */
	public function update(array $params) {
		if($this->lastId != null) {
			$params['id'] = $this->lastId;
			$this->lastId = null;
		}
		if(!$this->hasKeys($params, array('id'))) {
			throw new MissingParametersException(__METHOD__, array('id'));
		}
        $endPoint = Http::prepare('triggers/'.$params['id'].'.json');
        $response = Http::send($this->client, $endPoint, array(self::OBJ_NAME => $params), 'PUT');
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		return $response;
	}

	/*
	 * Delete a trigger
	 */
	public function delete(array $params = array()) {
		if($this->lastId != null) {
			$params['id'] = $this->lastId;
			$this->lastId = null;
		}
		if(!$this->hasKeys($params, array('id'))) {
			throw new MissingParametersException(__METHOD__, array('id'));
		}
        $endPoint = Http::prepare('triggers/'.$params['id'].'.json');
        $response = Http::send($this->client, $endPoint, null, 'DELETE');
		if ($this->client->getDebug()->lastResponseCode != 200) {
			throw new ResponseException(__METHOD__);
		}
		return true;
	}

	public function active() { return $this->findAll(array('active' => true)); }

}

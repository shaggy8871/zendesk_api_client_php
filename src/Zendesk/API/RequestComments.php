<?php

namespace Zendesk\API;

/**
 * The RequestComments class exposes request comment management methods
 * Note: you must authenticate as a user!
 */
class RequestComments extends ClientAbstract {

    const OBJ_NAME = 'comment';
    const OBJ_NAME_PLURAL = 'comments';

	/*
	 * Get comments from a request
	 */
	public function findAll(array $params) {
		if($this->client->requests()->getLastId() != null) {
			$params['request_id'] = $this->client->requests()->getLastId();
			$this->client->requests()->setLastId(null);
		}
		if(!$this->hasKeys($params, array('request_id'))) {
			throw new MissingParametersException(__METHOD__, array('request_id'));
		}
		$endPoint = Http::prepare('requests/'.$params['request_id'].'/comments.json', null, $params);
		$response = Http::send($this->client, $endPoint);
        echo __METHOD__;
        print_r($this->client->getDebug());
        print_r($response);
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		$this->client->setSideload(null);
		return $response;
	}

	/*
	 * Show a specific request
	 */
	public function find(array $params = array()) {
		if($this->client->requests()->getLastId() != null) {
			$params['request_id'] = $this->client->requests()->getLastId();
			$this->client->requests()->setLastId(null);
		}
		if($this->lastId != null) {
			$params['id'] = $this->lastId;
			$this->lastId = null;
		}
		if(!$this->hasKeys($params, array('id', 'request_id'))) {
			throw new MissingParametersException(__METHOD__, array('id', 'request_id'));
		}
		$endPoint = Http::prepare('requests/'.$params['request_id'].'/comments/'.$params['id'].'.json');
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		$this->client->setSideload(null);
		return $response;
	}

}

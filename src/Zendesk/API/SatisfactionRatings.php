<?php

namespace Zendesk\API;

/**
 * The SatisfactionRatings class exposes methods as detailed on http://developer.zendesk.com/documentation/rest_api/satisfaction_ratings.html
 */
class SatisfactionRatings extends ClientAbstract {

    const OBJ_NAME = 'satisfaction_rating';
    const OBJ_NAME_PLURAL = 'satisfaction_ratings';

	/*
	 * List all satisfaction ratings
	 */
	public function findAll(array $params = array()) {
		$endPoint = Http::prepare('satisfaction_ratings.json');
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		$this->client->setSideload(null);
		return $response;
	}

	/*
	 * Show a specific satisfaction rating
	 */
	public function find(array $params = array()) {
		if($this->lastId != null) {
			$params['id'] = $this->lastId;
			$this->lastId = null;
		}
		if(!$this->hasKeys($params, array('id'))) {
			throw new MissingParametersException(__METHOD__, array('id'));
		}
		$endPoint = Http::prepare('satisfaction_ratings/'.$params['id'].'.json');
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		$this->client->setSideload(null);
		return $response;
	}

	/*
	 * Create a new satisfaction rating
	 */
	public function create(array $params) {
		if($this->client->tickets()->getLastId() != null) {
			$params['ticket_id'] = $this->client->tickets()->getLastId();
			$this->client->tickets()->setLastId(null);
		}
		$endPoint = Http::prepare('tickets/'.$params['ticket_id'].'/satisfaction_rating.json');
		$response = Http::send($this->client, $endPoint, array (self::OBJ_NAME => $params), 'POST');
        print_r($this->client->getDebug());
        print_r($response);
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		return $response;
	}

}

?>

<?php

namespace Zendesk\API;

/**
 * The RequestComments class exposes request comment management methods
 * Note: you must authenticate as a user!
 */
class RequestComments extends ClientAbstract {

	/*
	 * Get comments from a request
	 */
	public function findAll(array $params) {
		if($this->lastId != null) {
			$params['id'] = $this->lastId;
			$this->lastId = null;
		}
		if(!$params['id']) {
			throw new MissingParametersException(__METHOD__, array('id'));
		}
		$id = $params['id'];
		unset($params['id']);
		$endPoint = Http::prepare('requests/'.$id.'/comments.json');
		$response = Http::send($this->client, $endPoint);
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
		if($this->lastId != null) {
			throw new CustomException('Calls to '.__METHOD__.' may not chain from the comment() helper. Try $client->requests->comments() instead.');
		}
		if($this->client->requests()->getLastId() != null) {
			$params['ticket_id'] = $this->client->requests()->getLastId();
			$this->client->requests()->setLastId(null);
		}
		if(!$params['request_id']) {
			throw new MissingParametersException(__METHOD__, array('request_id'));
		}
		if($this->lastId != null) {
			$params['id'] = $this->lastId;
			$this->lastId = null;
		}
		if(!$params['id']) {
			throw new MissingParametersException(__METHOD__, array('id'));
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

?>

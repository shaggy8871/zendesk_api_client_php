<?php

/**
 * The Request_Comments class exposes request comment management methods
 * Note: you must authenticate as a user!
 */
class Request_Comments {

	private $client;
	/*
	 * Helpers:
	 */
	public $lastId;

	public function __construct($client) {
		$this->client = $client;
	}

	/*
	 * Get comments from a request
	 */
	public function all(array $params) {
		if($this->lastId != null) {
			$params['id'] = $this->lastId;
			$this->lastId = null;
		}
		if(!$params['id']) {
			$this->client->lastError = 'Missing parameter: \'id\' must be supplied for '.__METHOD__;
			return false;
		}
		$id = $params['id'];
		unset($params['id']);
		$endPoint = Http::prepare('requests/'.$id.'/comments.json');
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
		}
		return $response;
	}

	/*
	 * Show a specific request
	 */
	public function find(array $params = array()) {
		if($this->lastId != null) {
			$this->client->lastError = 'Calls to '.__METHOD__.' may not chain from the comment() helper. Try $client->requests->comments() instead.';
			return false;
		}
		if($this->client->requests->lastId != null) {
			$params['request_id'] = $this->client->requests->lastId;
			$this->client->requests->lastId = null;
		}
		if(!$params['request_id']) {
			$this->client->lastError = 'Missing parameter: \'request_id\' must be supplied for '.__METHOD__;
			return false;
		}
		if($this->lastId != null) {
			$params['id'] = $this->lastId;
			$this->lastId = null;
		}
		if(!$params['id']) {
			$this->client->lastError = 'Missing parameter: \'id\' must be supplied for '.__METHOD__;
			return false;
		}
		$endPoint = Http::prepare('requests/'.$params['request_id'].'/comments/'.$params['id'].'.json');
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
		}
		return $response;
	}

	/*
	 * Enable side-loading (beta) - flags until the next endpoint call
	 */
	public function sideload(array $fields) {
		$this->client->sideload = $fields;
		return $this;
	}

}

?>

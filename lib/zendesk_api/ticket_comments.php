<?php

/**
 * The Ticket_Comments class exposes comment methods for tickets
 */
class Ticket_Comments {

	private $client;
	/*
	 * Helpers:
	 */
	public $lastId;

	public function __construct($client) {
		$this->client = $client;
	}

	/*
	 * Returns all comments for a particular ticket
	 */
	public function all(array $params = array()) {
		if($this->lastId != null) {
			$this->client->lastError = 'Calls to '.__METHOD__.' may not chain from the comment() helper. Try $client->tickets->comments() instead.';
			return false;
		}
		if($this->client->tickets->lastId != null) {
			$params['ticket_id'] = $this->client->tickets->lastId;
			$this->client->tickets->lastId = null;
		}
		if(!$params['ticket_id']) {
			$this->client->lastError = 'Missing parameter: \'ticket_id\' must be supplied for '.__METHOD__;
			return false;
		}
		$endPoint = Http::prepare('tickets/'.$params['ticket_id'].'/comments.json');
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
		}
		return $response;
	}

	public function makePrivate(array $params = array()) {
		if($this->client->tickets->lastId != null) {
			$params['ticket_id'] = $this->client->tickets->lastId;
			$this->client->tickets->lastId = null;
		}
		if(!$params['ticket_id']) {
			$this->client->lastError = 'Missing parameter: \'ticket_id\' must be supplied for '.__METHOD__;
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
		$endPoint = Http::prepare('tickets/'.$params['ticket_id'].'/comments/'.$params['id'].'/make_private.json');
		$response = Http::send($this->client, $endPoint, null, 'PUT');
		if ($this->client->lastResponseCode != 200) {
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

	/*
	 * Syntactic sugar methods:
	 * Handy aliases:
	 */
	public function find(array $params = array()) {
		$this->client->lastError = 'Method '.__METHOD__.' does not exist. Try $client->tickets->comments() instead.';
		return false;
	}

}

?>

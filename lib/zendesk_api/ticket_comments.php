<?php

/**
 * The Ticket_Comments class exposes comment methods for tickets
 */
class Ticket_Comments {

	private $client;

	public function __construct($client) {
		$this->client = $client;
	}

	/*
	 * Returns all comments for a particular ticket
	 */
	public function all($params = array()) {
		if($this->client->lastTicket != null) {
			$params['ticket_id'] = $this->client->lastTicket;
			$this->client->lastTicket = null;
		}
		if(!$params['ticket_id']) {
			$this->client->lastError = 'No ticket_id supplied for '.__METHOD__;
			return false;
		}
		$endPoint = Http::prepare('tickets/'.$params['ticket_id'].'/comments.json');
		$response = Http::send($this->client, $endPoint);
		if ($this->client->lastResponseCode == 404) {
			$this->client->lastError = 'The ticket does not exist';
			return false;
		}
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
			return false;
		}
		return $response;
	}

	public function makePrivate($params) {
		if($this->client->lastTicket != null) {
			$params['ticket_id'] = $this->client->lastTicket;
			$this->client->lastTicket = null;
		}
		if(!$params['ticket_id']) {
			$this->client->lastError = 'No ticket_id supplied for '.__METHOD__;
			return false;
		}
		if(!$params['id']) {
			$this->client->lastError = 'No comment id supplied for '.__METHOD__;
			return false;
		}
		$endPoint = Http::prepare('tickets/'.$params['ticket_id'].'/comments/'.$params['id'].'/make_private.json');
		$response = Http::send($this->client, $endPoint, null, 'PUT');
		if ($this->client->lastResponseCode == 404) {
			$this->client->lastError = 'The ticket or comment id does not exist';
			return false;
		}
		if ($this->client->lastResponseCode != 200) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
			return false;
		}
		return $response;
	}

}

?>

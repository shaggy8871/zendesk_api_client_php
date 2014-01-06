<?php

/**
 * The Ticket_Fields class exposes field management methods for tickets
 */
class Ticket_Fields {

	private $client;

	public function __construct($client) {
		$this->client = $client;
	}

	/*
	 * List all ticket fields
	 */
	public function all() {
		$endPoint = Http::prepare('ticket_fields.json');
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
			return false;
		}
		return $response;
	}

	/*
	 * Show a specific ticket field
	 */
	public function find($params) {
		if(!$params['id']) {
			$this->client->lastError = 'No id supplied for '.__METHOD__;
			return false;
		}
		$endPoint = Http::prepare('ticket_fields/'.$params['id'].'.json');
		$response = Http::send($this->client, $endPoint);
		if ($this->client->lastResponseCode == 404) {
			$this->client->lastError = 'The ticket field does not exist';
			return false;
		}
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
			return false;
		}
		return $response;
	}

	/*
	 * Create a new ticket field
	 */
	public function create($params) {
		$endPoint = Http::prepare('ticket_fields.json');
		$response = Http::send($this->client, $endPoint, array ('ticket_field' => $params), 'POST');
		if ((!is_object($response)) || ($this->client->lastResponseCode != 201)) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
			return false;
		}
		return $response;
	}

	/*
	 * Update a ticket field
	 */
	public function update($params) {
		if(!$params['id']) {
			$this->client->lastError = 'No id supplied for '.__METHOD__;
			return false;
		}
		$id = $params['id'];
		unset($params['id']);
		$endPoint = Http::prepare('ticket_fields/'.$id.'.json');
		$response = Http::send($this->client, $endPoint, array ('ticket_field' => $params), 'PUT');
		if ($this->client->lastResponseCode == 404) {
			$this->client->lastError = 'The ticket field does not exist';
			return false;
		}
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
			return false;
		}
		return $response;
	}

	/*
	 * Delete a ticket field
	 */
	public function delete($params) {
		if(!$params['id']) {
			$this->client->lastError = 'No id supplied for '.__METHOD__;
			return false;
		}
		$id = $params['id'];
		$endPoint = Http::prepare('ticket_fields/'.$id.'.json');
		$response = Http::send($this->client, $endPoint, null, 'DELETE');
		if ($this->client->lastResponseCode == 404) {
			$this->client->lastError = 'The ticket field does not exist';
			return false;
		}
		if ($this->client->lastResponseCode != 200) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
			return false;
		}
		return true;
	}

}

?>

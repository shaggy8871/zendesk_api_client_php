<?php

/**
 * The Ticket_Forms class exposes form management methods for tickets
 */
class Ticket_Forms {

	private $client;
	/*
	 * Helpers:
	 */
	public $lastId;

	public function __construct($client) {
		$this->client = $client;
	}

	/*
	 * List all ticket forms
	 */
	public function all() {
		$endPoint = Http::prepare('ticket_forms.json');
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
		}
		return $response;
	}

	/*
	 * Show a specific ticket form
	 */
	public function find(array $params = array()) {
		if($this->lastId != null) {
			$params['id'] = $this->lastId;
			$this->lastId = null;
		}
		if(!$params['id']) {
			$this->client->lastError = 'Missing parameter: \'id\' must be supplied for '.__METHOD__;
			return false;
		}
		$endPoint = Http::prepare('ticket_forms/'.$params['id'].'.json');
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
		}
		return $response;
	}

	/*
	 * Create a new ticket field
	 */
	public function create(array $params) {
		$endPoint = Http::prepare('ticket_forms.json');
		$response = Http::send($this->client, $endPoint, array ('ticket_form' => $params), 'POST');
		if ((!is_object($response)) || ($this->client->lastResponseCode != 201)) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
		}
		return $response;
	}

	/*
	 * Update a ticket field
	 */
	public function update(array $params) {
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
		$endPoint = Http::prepare('ticket_forms/'.$id.'.json');
		$response = Http::send($this->client, $endPoint, array ('ticket_form' => $params), 'PUT');
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
		}
		return $response;
	}

	/*
	 * Delete a ticket field
	 */
	public function delete(array $params = array()) {
		if($this->lastId != null) {
			$params['id'] = $this->lastId;
			$this->lastId = null;
		}
		if(!$params['id']) {
			$this->client->lastError = 'Missing parameter: \'id\' must be supplied for '.__METHOD__;
			return false;
		}
		$id = $params['id'];
		$endPoint = Http::prepare('ticket_forms/'.$id.'.json');
		$response = Http::send($this->client, $endPoint, null, 'DELETE');
		if ($this->client->lastResponseCode != 200) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
		}
		return true;
	}

	/*
	 * Reorder fields
	 */
	function reorder(array $params) {
		$endPoint = Http::prepare('ticket_forms/reorder.json');
		$response = Http::send($this->client, $endPoint, $params, 'PUT');
		if ($this->client->lastResponseCode != 200) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
		}
		return true;
	}

	/*
	 * Clones an existing form (can't use 'clone' as method name)
	 */
	function cloneForm(array $params = array()) {
		if($this->lastId != null) {
			$params['id'] = $this->lastId;
			$this->lastId = null;
		}
		if(!$params['id']) {
			$this->client->lastError = 'Missing parameter: \'id\' must be supplied for '.__METHOD__;
			return false;
		}
		$id = $params['id'];
		$endPoint = Http::prepare('ticket_forms/'.$id.'/clone.json');
		$response = Http::send($this->client, $endPoint, null, 'POST');
		if ($this->client->lastResponseCode != 201) {
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

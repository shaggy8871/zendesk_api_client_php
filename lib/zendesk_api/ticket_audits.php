<?php

/**
 * The Ticket_Audits class exposes read only audit methods
 * Enable side-loading through two options:
 *		$client->ticketsAudits->sideload(array('users', 'groups')); // enables for all future endpoint calls
 *		$client->ticketsAudits->all(array('sideload' => array('users', 'groups'))); // enables only for this endpoint
 */
class Ticket_Audits {

	private $client;
	/*
	 * Helpers:
	 */
	public $lastId;

	public function __construct($client) {
		$this->client = $client;
	}

	/*
	 * Returns all audits for a particular ticket
	 */
	public function all(array $params = array()) {
		if($this->lastId != null) {
			$this->client->lastError = 'Calls to '.__METHOD__.' may not chain from the audit() helper. Try $client->tickets->audits() instead.';
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
		$endPoint = Http::prepare('tickets/'.$params['ticket_id'].'/audits.json', (is_array($params['sideload']) ? $params['sideload'] : $this->client->sideload));
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
		}
		$this->client->sideload = null;
		return $response;
	}

	/*
	 * Show a specific audit record
	 */
	public function find(array $params = array()) {
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
		$endPoint = Http::prepare('tickets/'.$params['ticket_id'].'/audits/'.$params['id'].'.json', (is_array($params['sideload']) ? $params['sideload'] : $this->client->sideload));
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
		}
		$this->client->sideload = null;
		return $response;
	}

	/*
	 * Mark the specified ticket as trusted
	 */
	public function markAsTrusted(array $params = array()) {
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
		$endPoint = Http::prepare('tickets/'.$params['ticket_id'].'/audits/'.$params['id'].'/trust.json');
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

}

?>

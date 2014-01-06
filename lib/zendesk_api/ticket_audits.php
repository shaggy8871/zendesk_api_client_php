<?php

/**
 * The Ticket_Audits class exposes read only audit methods
 * Enable side-loading through two options:
 *		$client->ticketsAudits->sideLoad(array('users', 'groups')); // enables for all future endpoint calls
 *		$client->ticketsAudits->all(array('sideload' => array('users', 'groups'))); // enables only for this endpoint
 */
class Ticket_Audits {

	private $client;

	public function __construct($client) {
		$this->client = $client;
	}

	/*
	 * Enable side-loading (beta) - flags until the next endpoint call
	 */
	public function withSideLoad($fields) {
		$this->client->sideLoad = $fields;
		return $this;
	}

	/*
	 * Returns all audits for a particular ticket
	 */
	public function all($params) {
		if($this->client->lastTicket != null) {
			$params['ticket_id'] = $this->client->lastTicket;
			$this->client->lastTicket = null;
		}
		if(!$params['ticket_id']) {
			$this->client->lastError = 'No ticket_id supplied for '.__METHOD__;
			return false;
		}
		$endPoint = Http::prepare('tickets/'.$params['ticket_id'].'/audits.json', (is_array($params['sideload']) ? $params['sideload'] : $this->client->sideLoad));
		$response = Http::send($this->client, $endPoint);
		if ($this->client->lastResponseCode == 404) {
			$this->client->lastError = 'The ticket does not exist';
			return false;
		}
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
			return false;
		}
		$this->client->sideLoad = null;
		return $response;
	}

	/*
	 * Show a specific audit record
	 */
	public function find($params) {
		if($this->client->lastTicket != null) {
			$params['ticket_id'] = $this->client->lastTicket;
			$this->client->lastTicket = null;
		}
		if(!$params['ticket_id']) {
			$this->client->lastError = 'No ticket_id supplied for '.__METHOD__;
			return false;
		}
		if(!$params['id']) {
			$this->client->lastError = 'No id supplied for '.__METHOD__;
			return false;
		}
		$endPoint = Http::prepare('tickets/'.$params['ticket_id'].'/audits/'.$params['id'].'.json', (is_array($params['sideload']) ? $params['sideload'] : $this->client->sideLoad));
		$response = Http::send($this->client, $endPoint);
		if ($this->client->lastResponseCode == 404) {
			$this->client->lastError = 'The ticket or audit id does not exist';
			return false;
		}
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
			return false;
		}
		$this->client->sideLoad = null;
		return $response;
	}

	/*
	 * Mark the specified ticket as trusted
	 */
	public function markAsTrusted($params) {
		if($this->client->lastTicket != null) {
			$params['ticket_id'] = $this->client->lastTicket;
			$this->client->lastTicket = null;
		}
		if(!$params['ticket_id']) {
			$this->client->lastError = 'No ticket_id supplied for '.__METHOD__;
			return false;
		}
		if(!$params['id']) {
			$this->client->lastError = 'No audit id supplied for '.__METHOD__;
			return false;
		}
		$endPoint = Http::prepare('tickets/'.$params['ticket_id'].'/audits/'.$params['id'].'/trust.json');
		$response = Http::send($this->client, $endPoint, null, 'PUT');
		if ($this->client->lastResponseCode == 404) {
			$this->client->lastError = 'The ticket or audit id does not exist';
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

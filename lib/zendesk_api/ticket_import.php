<?php

/**
 * The Ticket_Import class exposes import methods for tickets
 */
class Ticket_Import {

	private $client;

	public function __construct($client) {
		$this->client = $client;
	}

	/*
	 * Create a new ticket field
	 */
	public function import(array $params) {
		$endPoint = Http::prepare('imports/tickets.json');
		$response = Http::send($this->client, $endPoint, array ('ticket' => $params), 'POST');
		if ((!is_object($response)) || ($this->client->lastResponseCode != 201)) {
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

<?php

namespace Zendesk\API;

/**
 * The TicketImport class exposes import methods for tickets
 */
class TicketImport extends ClientAbstract {

	/*
	 * Create a new ticket field
	 */
	public function import(array $params) {
		$endPoint = Http::prepare('imports/tickets.json');
		$response = Http::send($this->client, $endPoint, array ('ticket' => $params), 'POST');
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 201)) {
			throw new ResponseException(__METHOD__);
		}
		return $response;
	}

}

?>

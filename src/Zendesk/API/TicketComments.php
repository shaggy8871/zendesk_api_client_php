<?php

namespace Zendesk\API;

/**
 * The TicketComments class exposes comment methods for tickets
 */
class TicketComments extends ClientAbstract {

	/*
	 * Returns all comments for a particular ticket
	 */
	public function findAll(array $params = array()) {
		if($this->lastId != null) {
			throw new CustomException('Calls to '.__METHOD__.' may not chain from the comment() helper. Try $client->ticket(ticket_id)->comments() instead.');
		}
		if($this->client->tickets()->getLastId() != null) {
			$params['ticket_id'] = $this->client->tickets()->getLastId();
			$this->client->tickets()->setLastId(null);
		}
		if(!$params['ticket_id']) {
			throw new MissingParametersException(__METHOD__, array('ticket_id'));
		}
		$endPoint = Http::prepare('tickets/'.$params['ticket_id'].'/comments.json');
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		$this->client->setSideload(null);
		return $response;
	}

	/*
	 * Make the specified comment private
	 */
	public function makePrivate(array $params = array()) {
		if($this->client->tickets()->getLastId() != null) {
			$params['ticket_id'] = $this->client->tickets()->getLastId();
			$this->client->tickets()->setLastId(null);
		}
		if($this->lastId != null) {
			$params['id'] = $this->lastId;
			$this->lastId = null;
		}
		if(!$params['ticket_id']) {
			throw new MissingParametersException(__METHOD__, array('ticket_id'));
		}
		if(!$params['id']) {
			throw new MissingParametersException(__METHOD__, array('id'));
		}
		$endPoint = Http::prepare('tickets/'.$params['ticket_id'].'/comments/'.$params['id'].'/make_private.json');
		$response = Http::send($this->client, $endPoint, null, 'PUT');
		if ($this->client->getDebug()->lastResponseCode != 200) {
			throw new ResponseException(__METHOD__, ' (hint: you can\'t make a private ticket private again)');
		}
		$this->client->setSideload(null);
		return $response;
	}

	/*
	 * Syntactic sugar methods:
	 * Handy aliases:
	 */
	public function find(array $params = array()) {
		throw new CustomException('Method '.__METHOD__.' does not exist. Try $client->ticket(ticket_id)->comments() instead.');
	}

}

?>

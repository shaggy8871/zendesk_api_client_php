<?php

namespace Zendesk\API;

/**
 * The TicketAudits class exposes read only audit methods
 */
class TicketAudits extends ClientAbstract {

	/*
	 * Returns all audits for a particular ticket
	 */
	public function findAll(array $params = array()) {
		if($this->lastId != null) {
			throw new CustomException('Calls to '.__METHOD__.' may not chain from the audit() helper. Try $client->ticket(ticket_id)->audit(id)->find() instead.');
		}
		if($this->client->tickets()->getLastId() != null) {
			$params['ticket_id'] = $this->client->tickets()->getLastId();
			$this->client->tickets()->setLastId(null);
		}
		if(!$params['ticket_id']) {
			throw new MissingParametersException(__METHOD__, array('ticket_id'));
		}
		$endPoint = Http::prepare('tickets/'.$params['ticket_id'].'/audits.json', ((isset($params['sideload'])) && (is_array($params['sideload'])) ? $params['sideload'] : $this->client->getSideload()));
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		$this->client->setSideload(null);
		return $response;
	}

	/*
	 * Show a specific audit record
	 */
	public function find(array $params = array()) {
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
		$endPoint = Http::prepare('tickets/'.$params['ticket_id'].'/audits/'.$params['id'].'.json', ((isset($params['sideload'])) && (is_array($params['sideload'])) ? $params['sideload'] : $this->client->getSideload()));
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		$this->client->setSideload(null);
		return $response;
	}

	/*
	 * Mark the specified ticket as trusted
	 */
	public function markAsTrusted(array $params = array()) {
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
		$endPoint = Http::prepare('tickets/'.$params['ticket_id'].'/audits/'.$params['id'].'/trust.json');
		$response = Http::send($this->client, $endPoint, null, 'PUT');
		if ($this->client->getDebug()->lastResponseCode != 200) {
			throw new ResponseException(__METHOD__);
		}
		$this->client->setSideload(null);
		return $response;
	}

}

?>

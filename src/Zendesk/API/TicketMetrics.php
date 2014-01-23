<?php

namespace Zendesk\API;

/**
 * The TicketMetrics class exposes metrics methods for tickets
 */
class TicketMetrics extends ClientAbstract {

	/*
	 * List all ticket metrics
	 */
	public function findAll(array $params = array()) {
		if($this->lastId != null) {
			throw new CustomException('Calls to '.__METHOD__.' may not chain from the metric() helper. Try $client->ticket(ticket_id)->metric(id)->find() instead.');
		}
		if($this->client->tickets()->getLastId() != null) {
			$params['ticket_id'] = $this->client->tickets()->getLastId();
			$this->client->tickets()->setLastId(null);
		}
		$endPoint = Http::prepare((isset($params['ticket_id']) ? 'tickets/'.$params['ticket_id'].'/metrics.json' : 'ticket_metrics.json'));
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		return $response;
	}

	/*
	 * Show a specific ticket metric
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
		if(!$this->hasAnyKey($params, array('id', 'ticket_id'))) {
			throw new MissingParametersException(__METHOD__, array('id', 'ticket_id'));
		}
		$endPoint = Http::prepare((isset($params['ticket_id']) ? 'tickets/'.$params['ticket_id'].'/metrics.json' : 'ticket_metrics/'.$params['id'].'.json'));
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		return $response;
	}

}

?>

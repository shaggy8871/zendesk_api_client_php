<?php

namespace Zendesk\API;

/**
 * The Requests class exposes request management methods
 * Note: you must authenticate as a user!
 */
class Requests extends ClientAbstract {

	/*
	 * Public objects:
	 */
	protected $comments;

	public function __construct($client) {
		$this->client = $client;
		$this->comments = new RequestComments($client);
	}

	/*
	 * List all requests
	 */
	public function findAll() {
		$endPoint = Http::prepare(
				($params['organization_id'] ? 'organizations/'.$params['organization_id'].'/requests' : 
				($params['user_id'] ? 'users/'.$params['user_id'].'/requests' : 
				($params['ccd'] ? 'requests/ccd' : 
				($params['solved'] ? 'requests/solved' : 
				($params['open'] ? 'requests/open' : 'requests'))))
			).'.json'.($params['status'] ? '?status='.$params['status'] : ''), (is_array($params['sideload']) ? $params['sideload'] : $this->client->getSideload()));
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		$this->client->setSideload(null);
		return $response;
	}

	/*
	 * Show a specific request
	 */
	public function find(array $params = array()) {
		if($this->lastId != null) {
			$params['id'] = $this->lastId;
			$this->lastId = null;
		}
		if(!$params['id']) {
			throw new MissingParametersException(__METHOD__, array('id'));
		}
		$endPoint = Http::prepare('requests/'.$params['id'].'.json');
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		$this->client->setSideload(null);
		return $response;
	}

	/*
	 * Create a new request
	 */
	public function create(array $params) {
		$endPoint = Http::prepare('requests.json');
		$response = Http::send($this->client, $endPoint, array ('request' => $params), 'POST');
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 201)) {
			throw new ResponseException(__METHOD__);
		}
		return $response;
	}

	/*
	 * Update a request
	 */
	public function update(array $params) {
		if($this->lastId != null) {
			$params['id'] = $this->lastId;
			$this->lastId = null;
		}
		if(!$params['id']) {
			throw new MissingParametersException(__METHOD__, array('id'));
		}
		$id = $params['id'];
		unset($params['id']);
		$endPoint = Http::prepare('requests/'.$id.'.json');
		$response = Http::send($this->client, $endPoint, array ('request' => $params), 'PUT');
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		return $response;
	}

	/*
	 * Syntactic sugar methods:
	 * Handy aliases:
	 */
	public function comments(array $params = array()) { return $this->comments->findAll($params); }
	/*
	 * Helpers:
	 */
	public function comment($id) { return $this->comments->setLastId($id); }
	public function open(array $params = array()) { $params['open'] = true; return $this->findAll($params); }
	public function solved(array $params = array()) { $params['solved'] = true; return $this->findAll($params); }
	public function ccd(array $params = array()) { $params['ccd'] = true; return $this->findAll($params); }

}

?>

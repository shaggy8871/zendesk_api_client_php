<?php

/**
 * The Requests class exposes request management methods
 * Note: you must authenticate as a user!
 */
class Requests {

	private $client;

	/*
	 * Public objects:
	 */
	public $comments;
	/*
	 * Helpers:
	 */
	public $lastId;

	public function __construct($client) {
		$this->client = $client;
		$this->comments = new Request_Comments($client);
	}

	/*
	 * List all requests
	 */
	public function all() {
		$endPoint = Http::prepare(
				($params['organization_id'] ? 'organizations/'.$params['organization_id'].'/requests' : 
				($params['user_id'] ? 'users/'.$params['user_id'].'/requests' : 
				($params['ccd'] ? 'requests/ccd' : 
				($params['solved'] ? 'requests/solved' : 
				($params['open'] ? 'requests/open' : 'requests'))))
			).'.json'.($params['status'] ? '?status='.$params['status'] : ''), (is_array($params['sideload']) ? $params['sideload'] : $this->client->sideload));
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
		}
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
			$this->client->lastError = 'Missing parameter: \'id\' must be supplied for '.__METHOD__;
			return false;
		}
		$endPoint = Http::prepare('requests/'.$params['id'].'.json');
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
		}
		return $response;
	}

	/*
	 * Create a new request
	 */
	public function create(array $params) {
		$endPoint = Http::prepare('requests.json');
		$response = Http::send($this->client, $endPoint, array ('request' => $params), 'POST');
		if ((!is_object($response)) || ($this->client->lastResponseCode != 201)) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
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
			$this->client->lastError = 'Missing parameter: \'id\' must be supplied for '.__METHOD__;
			return false;
		}
		$id = $params['id'];
		unset($params['id']);
		$endPoint = Http::prepare('requests/'.$id.'.json');
		$response = Http::send($this->client, $endPoint, array ('request' => $params), 'PUT');
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
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

	/*
	 * Syntactic sugar methods:
	 * Handy aliases:
	 */
	public function comments(array $params = array()) { return $this->comments->all($params); }
	/*
	 * Helpers:
	 */
	public function comment($id) { $this->comments->lastId = $id; return $this->comments; }
	public function open(array $params = array()) { $params['open'] = true; return $this->all($params); }
	public function solved(array $params = array()) { $params['solved'] = true; return $this->all($params); }
	public function ccd(array $params = array()) { $params['ccd'] = true; return $this->all($params); }

}

?>

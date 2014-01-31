<?php

namespace Zendesk\API;

/**
 * The Views class exposes view management methods
 */
class Views extends ClientAbstract {

    const OBJ_NAME = 'view';
    const OBJ_NAME_PLURAL = 'views';

	/*
	 * List all views
	 */
	public function findAll(array $params = array()) {
		$endPoint = Http::prepare(
				(isset($params['active']) ? 'views/active.json' : 
				(isset($params['compact']) ? 'views/compact.json' : 'views.json')), 
               ((isset($params['sideload'])) && (is_array($params['sideload'])) ? $params['sideload'] : $this->client->getSideload())
            );
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		$this->client->setSideload(null);
		return $response;
	}

	/*
	 * Show a specific view
	 */
	public function find(array $params = array()) {
		if($this->lastId != null) {
			$params['id'] = $this->lastId;
			$this->lastId = null;
		}
		if(!$this->hasKeys($params, array('id'))) {
			throw new MissingParametersException(__METHOD__, array('id'));
		}
		$endPoint = Http::prepare('views/'.$params['id'].'.json', ((isset($params['sideload'])) && (is_array($params['sideload'])) ? $params['sideload'] : $this->client->getSideload()));
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		$this->client->setSideload(null);
		return $response;
	}

	/*
	 * Create a new view
	 */
	public function create(array $params) {
		$endPoint = Http::prepare('views.json');
		$response = Http::send($this->client, $endPoint, array (self::OBJ_NAME => $params), 'POST');
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 201)) {
			throw new ResponseException(__METHOD__);
		}
		return $response;
	}

	/*
	 * Update a view
	 */
	public function update(array $params) {
		if($this->lastId != null) {
			$params['id'] = $this->lastId;
			$this->lastId = null;
		}
		if(!$this->hasKeys($params, array('id'))) {
			throw new MissingParametersException(__METHOD__, array('id'));
		}
		$id = $params['id'];
		unset($params['id']);
		$endPoint = Http::prepare('views/'.$id.'.json');
		$response = Http::send($this->client, $endPoint, array (self::OBJ_NAME => $params), 'PUT');
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		return $response;
	}

	/*
	 * Delete a view
	 */
	public function delete(array $params = array()) {
		if($this->lastId != null) {
			$params['id'] = $this->lastId;
			$this->lastId = null;
		}
		if(!$this->hasKeys($params, array('id'))) {
			throw new MissingParametersException(__METHOD__, array('id'));
		}
		$id = $params['id'];
		$endPoint = Http::prepare('views/'.$id.'.json');
		$response = Http::send($this->client, $endPoint, null, 'DELETE');
		if ($this->client->getDebug()->lastResponseCode != 200) {
			throw new ResponseException(__METHOD__);
		}
		return true;
	}

	/*
	 * Execute a specific view
	 */
	public function execute(array $params = array()) {
		if($this->lastId != null) {
			$params['id'] = $this->lastId;
			$this->lastId = null;
		}
		if(!$this->hasKeys($params, array('id'))) {
			throw new MissingParametersException(__METHOD__, array('id'));
		}
		$endPoint = Http::prepare('views/'.$params['id'].'/execute.json'.(isset($params['sort_by']) ? '?sort_by='.$params['sort_by'].(isset($params['sort_order']) ? '&sort_order='.$params['sort_order'] : '') : ''));
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		$this->client->setSideload(null);
		return $response;
	}

	/*
	 * Get tickets from a specific view
	 */
	public function tickets(array $params = array()) {
		if($this->lastId != null) {
			$params['id'] = $this->lastId;
			$this->lastId = null;
		}
		if(!$this->hasKeys($params, array('id'))) {
			throw new MissingParametersException(__METHOD__, array('id'));
		}
		$endPoint = Http::prepare('views/'.$params['id'].'/tickets.json', ((isset($params['sideload'])) && (is_array($params['sideload'])) ? $params['sideload'] : $this->client->getSideload()));
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		$this->client->setSideload(null);
		return $response;
	}

	/*
	 * Count tickets (estimate) from a specific view or list of views
	 */
	public function count(array $params = array()) {
		if($this->lastId != null) {
			$params['id'] = $this->lastId;
			$this->lastId = null;
		}
		if(!$this->hasKeys($params, array('id'))) {
			throw new MissingParametersException(__METHOD__, array('id'));
		}
		$endPoint = Http::prepare('views/'.(is_array($params['id']) ? 'count_many.json?ids='.implode(',', $params['id']) : $params['id'].'/count.json'), ((isset($params['sideload'])) && (is_array($params['sideload'])) ? $params['sideload'] : $this->client->getSideload()));
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		$this->client->setSideload(null);
		return $response;
	}

	/*
	 * Export a view
	 */
	public function export(array $params = array()) {
		if($this->lastId != null) {
			$params['id'] = $this->lastId;
			$this->lastId = null;
		}
		if(!$this->hasKeys($params, array('id'))) {
			throw new MissingParametersException(__METHOD__, array('id'));
		}
		$endPoint = Http::prepare('views/'.$params['id'].'/export.json', ((isset($params['sideload'])) && (is_array($params['sideload'])) ? $params['sideload'] : $this->client->getSideload()));
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		$this->client->setSideload(null);
		return $response;
	}

	/*
	 * Preview a view
	 */
	public function preview(array $params) {
		$endPoint = Http::prepare('views/preview.json', ((isset($params['sideload'])) && (is_array($params['sideload'])) ? $params['sideload'] : $this->client->getSideload()));
		$response = Http::send($this->client, $endPoint, array('view' => $params), 'POST');
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		$this->client->setSideload(null);
		return $response;
	}

	/*
	 * Ticket count for a view preview
	 */
	public function previewCount(array $params) {
		$endPoint = Http::prepare('views/preview/count.json', ((isset($params['sideload'])) && (is_array($params['sideload'])) ? $params['sideload'] : $this->client->getSideload()));
		$response = Http::send($this->client, $endPoint, array('view' => $params), 'POST');
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		$this->client->setSideload(null);
		return $response;
	}

}

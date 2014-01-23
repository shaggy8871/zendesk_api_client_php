<?php

namespace Zendesk\API;

/**
 * The ForumSubscriptions class exposes forum information
 */
class ForumSubscriptions extends ClientAbstract {

	/*
	 * List all forum subscriptions
	 */
	public function findAll(array $params = array()) {
		if($this->client->forums()->getLastId() != null) {
			$params['forum_id'] = $this->client->forums()->getLastId();
			$this->client->forums()->setLastId(null);
		}
		$endPoint = Http::prepare((isset($params['forum_id']) ? 'forum/'.$params['forum_id'].'/subscriptions.json' : 'forum_subscriptions.json'));
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		$this->client->setSideload(null);
		return $response;
	}

	/*
	 * Show a specific forum subscription
	 */
	public function find(array $params = array()) {
		if($this->lastId != null) {
			$params['id'] = $this->lastId;
			$this->lastId = null;
		}
		if(!$this->hasKeys($params, array('id'))) {
			throw new MissingParametersException(__METHOD__, array('id'));
		}
		$endPoint = Http::prepare('forum_subscriptions/'.$params['id'].'.json');
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		$this->client->setSideload(null);
		return $response;
	}

	/*
	 * Create a new forum subscription
	 */
	public function create(array $params) {
		if($this->client->users()->getLastId() != null) {
			$params['user_id'] = $this->client->users()->getLastId();
			$this->client->users()->setLastId(null);
		}
		if($this->client->forums()->getLastId() != null) {
			$params['forum_id'] = $this->client->forums()->getLastId();
			$this->client->forums()->setLastId(null);
		}
		if(!$this->hasKeys($params, array('user_id', 'forum_id'))) {
			throw new MissingParametersException(__METHOD__, array('user_id', 'forum_id'));
		}
		$endPoint = Http::prepare('forum_subscriptions.json');
		$response = Http::send($this->client, $endPoint, array ('forum_subscription' => $params), 'POST');
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 201)) {
			throw new ResponseException(__METHOD__);
		}
		return $response;
	}

	/*
	 * Delete a forum subscription
	 */
	public function delete(array $params = array()) {
		if($this->lastId != null) {
			$params['id'] = $this->lastId;
			$this->lastId = null;
		}
		if(!$this->hasKeys($params, array('id'))) {
			throw new MissingParametersException(__METHOD__, array('id'));
		}
		$endPoint = Http::prepare('forum_subscriptions/'.$params['id'].'.json');
		$response = Http::send($this->client, $endPoint, null, 'DELETE');
		if ($this->client->getDebug()->lastResponseCode != 200) {
			throw new ResponseException(__METHOD__);
		}
		return true;
	}

}

?>

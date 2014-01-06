<?php

/**
 * The Twitter class exposes methods for managing and monitoring Twitter posts
 */
class Twitter {

	private $client;

	public function __construct($client) {
		$this->client = $client;
	}

	/*
	 * Return a list of monitored handles
	 */
	public function handles() {
		$endPoint = 'channels/twitter/monitored_twitter_handles.json';
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to twitter->handles is not valid. See $client->lastResponseHeaders for details';
			return false;
		}
		return $response;
	}

	/*
	 * Responds with details of a specific handle
	 */
	public function handleById($params) {
		if(!$params['id']) {
			$this->client->lastError = 'No id supplied for twitter->handleById';
			return false;
		}
		$endPoint = 'channels/twitter/monitored_twitter_handles/'.$params['id'].'.json';
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to twitter->handleById is not valid. See $client->lastResponseHeaders for details';
			return false;
		}
		return $response;
	}

}

?>

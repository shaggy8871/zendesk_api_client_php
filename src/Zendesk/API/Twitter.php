<?php

namespace Zendesk\API;

/**
 * The Twitter class exposes methods for managing and monitoring Twitter posts
 */
class Twitter extends ClientAbstract {

	/*
	 * Return a list of monitored handles
	 */
	public function handles() {
		$endPoint = 'channels/twitter/monitored_twitter_handles.json';
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		return $response;
	}

	/*
	 * Responds with details of a specific handle
	 */
	public function handleById(array $params) {
		if(!$params['id']) {
			$this->client->lastError = 'Missing parameter: \'id\' must be supplied for '.__METHOD__;
			return false;
		}
		$endPoint = 'channels/twitter/monitored_twitter_handles/'.$params['id'].'.json';
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		return $response;
	}

}

?>

<?php

namespace Zendesk\API;

/**
 * The Settings class exposes methods for retrieving settings parameters
 */
class Settings extends ClientAbstract {

	/*
	 * Returns a range of settings
	 */
	public function findAll(array $params = array ()) {
		$endPoint = Http::prepare('account/settings.json');
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		$this->client->setSideload(null);
		return $response;
	}

	/*
	 * Update one or more settings
	 */
	public function update(array $params) {
		$endPoint = Http::prepare('account/settings.json');
		$response = Http::send($this->client, $endPoint, array ('settings' => $params), 'PUT');
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		return $response;
	}

}

?>

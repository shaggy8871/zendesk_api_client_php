<?php

namespace Zendesk\API;

/**
 * The CustomRoles class exposes access to custom roles
 */
class CustomRoles extends ClientAbstract {

	/*
	 * List all custom roles
	 */
	public function findAll(array $params = array()) {
		$endPoint = Http::prepare('custom_roles.json');
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		$this->client->setSideload(null);
		return $response;
	}

}

?>

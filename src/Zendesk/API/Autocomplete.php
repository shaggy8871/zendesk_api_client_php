<?php

namespace Zendesk\API;

/**
 * The Autocomplete class is as per http://developer.zendesk.com/documentation/rest_api/autocomplete.html
 */
class Autocomplete extends ClientAbstract {

	/*
	 * Submits a request for matching tags
	 */
	public function tags(array $params) {
		if(!$this->hasKeys($params, array('name'))) {
			throw new MissingParametersException(__METHOD__, array('name'));
		}
		$endPoint = Http::prepare('autocomplete/tags.json');
		$response = Http::send($this->client, $endPoint, array('name' => $params['name']), 'POST');
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		return $response;
	}

}

?>
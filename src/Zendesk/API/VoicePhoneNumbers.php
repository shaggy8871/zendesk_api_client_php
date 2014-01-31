<?php

namespace Zendesk\API;

/**
 * The VoicePhoneNumbers class exposes methods as outlined in http://developer.zendesk.com/documentation/rest_api/voice.html
 */
class VoicePhoneNumbers extends ClientAbstract {

    const OBJ_NAME = 'phone_number';
    const OBJ_NAME_PLURAL = 'phone_numbers';

	/*
	 * List all voice phone numbers
	 */
	public function findAll(array $params = array()) {
		$endPoint = Http::prepare('channels/voice/phone_numbers.json');
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		return $response;
	}

	/*
	 * Show a specific voice phone number
	 */
	public function find(array $params = array()) {
		if($this->lastId != null) {
			$params['id'] = $this->lastId;
			$this->lastId = null;
		}
		if(!$this->hasKeys($params, array('id'))) {
			throw new MissingParametersException(__METHOD__, array('id'));
		}
		$endPoint = Http::prepare('channels/voice/phone_numbers/'.$params['id'].'.json');
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		return $response;
	}

	/*
	 * Search for a voice phone number
	 */
	public function search(array $params) {
		$endPoint = Http::prepare('channels/voice/phone_numbers/search.json?'.http_build_query($params));
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		return $response;
	}

	/*
	 * Create a voice phone number
	 */
	public function create(array $params) {
		$endPoint = Http::prepare('channels/voice/phone_numbers.json');
		$response = Http::send($this->client, $endPoint, array (self::OBJ_NAME => $params), 'POST');
        print_r($this->client->getDebug());
        print_r($response);
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 201)) {
			throw new ResponseException(__METHOD__);
		}
		return $response;
	}

	/*
	 * Update a voice phone number
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
		$endPoint = Http::prepare('channels/voice/phone_numbers/'.$id.'.json');
		$response = Http::send($this->client, $endPoint, array (self::OBJ_NAME => $params), 'PUT');
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		return $response;
	}

	/*
	 * Delete a voice phone number
	 */
	public function delete(array $params = array()) {
		if($this->lastId != null) {
			$params['id'] = $this->lastId;
			$this->lastId = null;
		}
		if(!$params['id']) {
			throw new MissingParametersException(__METHOD__, array('id'));
		}
		$id = $params['id'];
		$endPoint = Http::prepare('channels/voice/phone_numbers/'.$id.'.json');
		$response = Http::send($this->client, $endPoint, null, 'DELETE');
		if ($this->client->getDebug()->lastResponseCode != 200) {
			throw new ResponseException(__METHOD__);
		}
		return true;
	}

}

?>

<?php

namespace Zendesk\API;

/**
 * The OrganizationFields class exposes methods as detailed on http://developer.zendesk.com/documentation/rest_api/organization_fields.html
 */
class OrganizationFields extends ClientAbstract {

    const OBJ_NAME = 'organization_field';
    const OBJ_NAME_PLURAL = 'organization_fields';

	/*
	 * List all organization fields
	 */
	public function findAll(array $params = array()) {
		$endPoint = Http::prepare('organization_fields.json');
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		$this->client->setSideload(null);
		return $response;
	}

	/*
	 * Show a specific organization field
	 */
	public function find(array $params = array()) {
		if($this->lastId != null) {
			$params['id'] = $this->lastId;
			$this->lastId = null;
		}
		if(!$this->hasKeys($params, array('id'))) {
			throw new MissingParametersException(__METHOD__, array('id'));
		}
		$endPoint = Http::prepare('organization_fields/'.$params['id'].'.json');
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		$this->client->setSideload(null);
		return $response;
	}

	/*
	 * Create a new organization field
	 */
	public function create(array $params) {
		$endPoint = Http::prepare('organization_fields.json');
		$response = Http::send($this->client, $endPoint, array (self::OBJ_NAME => $params), 'POST');
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 201)) {
			throw new ResponseException(__METHOD__);
		}
		return $response;
	}

	/*
	 * Update an organization field
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
		$endPoint = Http::prepare('organization_fields/'.$id.'.json');
		$response = Http::send($this->client, $endPoint, array (self::OBJ_NAME => $params), 'PUT');
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		return $response;
	}

	/*
	 * Delete an organization field
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
		$endPoint = Http::prepare('organization_fields/'.$id.'.json');
		$response = Http::send($this->client, $endPoint, null, 'DELETE');
		if ($this->client->getDebug()->lastResponseCode != 200) {
			throw new ResponseException(__METHOD__);
		}
		return true;
	}

	/*
	 * Reorder organization fields
	 */
	public function reorder(array $params) {
		if(!$this->hasKeys($params, array('user_fields_ids'))) {
			throw new MissingParametersException(__METHOD__, array('user_fields_ids'));
		}
		$endPoint = Http::prepare('organization_fields/reorder.json');
		$response = Http::send($this->client, $endPoint, array('user_fields_ids' => $params['user_fields_ids']), 'PUT');
        print_r($this->client->getDebug());
        print_r($response);
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		$this->client->setSideload(null);
		return $response;
	}

}

?>

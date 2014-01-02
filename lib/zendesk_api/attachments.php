<?php

/**
 * The Attachments class exposes methods for uploading and retrieving attachments
 */
class Attachments {

	private $client;

	public function __construct($client) {
		$this->client = $client;
	}

	/*
	 * Upload an attachment
	 * $params must include:
	 *		'file' - an attribute with the absolute local file path on the server
	 *		'type' - the MIME type of the file
	 * Optional:
	 *		'optional_token' - an existing token
	 */
	public function upload($params) {
		if(!$params['file']) {
			$this->client->lastError = 'No file supplied for attachments->upload';
			return false;
		}
		if(!file_exists($params['file'])) {
			$this->client->lastError = 'File '.$params['file'].' could not be found in attachments->upload';
			return false;
		}
		if(!$params['type']) {
			$this->client->lastError = 'No type parameter supplied for attachments->upload';
			return false;
		}
		$endPoint = 'uploads.json?filename='.$params['file'].($params['optional_token'] ? '&token='.$params['optional_token'] : '');
		$response = Http::send($this->client, $endPoint, array('filename' => '@'.$params['file']), 'POST', ($params['type'] ? $params['type'] : 'application/binary'));
		if ((!is_object($response)) || ($this->client->lastResponseCode != 201)) {
			$this->client->lastError = 'Response to attachments->upload is not valid. See $client->lastResponseHeaders for details';
			return false;
		}
		return $response;
	}

	/*
	 * Delete one or more attachments by token
	 * $params must include:
	 *		'token' - the token given to you after the original upload
	 */
	public function delete($params) {
		if(!$params['token']) {
			$this->client->lastError = 'No token supplied for attachments->delete';
			return false;
		}
		$endPoint = 'uploads/'.$params['token'].'.json';
		$response = Http::send($this->client, $endPoint, null, 'DELETE');
		if ($this->client->lastResponseCode == 401) {
			$this->client->lastError = 'The token does not exist';
			return false;
		}
		if ($this->client->lastResponseCode != 200) {
			$this->client->lastError = 'Response to attachments->delete is not valid. See $client->lastResponseHeaders for details';
			return false;
		}
		return true;
	}

	/*
	 * Get a list of uploaded attachments (by id)
	 * $params must include:
	 *		'id' - ???
	 */
	public function get($params) {
		if(!$params['id']) {
			$this->client->lastError = 'No id supplied for attachments->get';
			return false;
		}
		$id = $params['id'];
		$endPoint = 'attachments/'.$id.'.json';
		$response = Http::send($this->client, $endPoint);
		if ($this->client->lastResponseCode == 401) {
			$this->client->lastError = 'The attachment id does not exist';
			return false;
		}
		if ($this->client->lastResponseCode != 200) {
			$this->client->lastError = 'Response to attachments->get is not valid. See $client->lastResponseHeaders for details';
			return false;
		}
		return $response;
	}

	/*
	 * Delete one or more attachments by id
	 * $params must include:
	 *		'id' - ???
	 */
	public function deleteById($params) {
		if(!$params['id']) {
			$this->client->lastError = 'No id supplied for attachments->deleteById';
			return false;
		}
		$endPoint = 'attachments/'.$params['id'].'.json';
		$response = Http::send($this->client, $endPoint, null, 'DELETE');
		if ($this->client->lastResponseCode == 401) {
			$this->client->lastError = 'The attachment id does not exist';
			return false;
		}
		if ($this->client->lastResponseCode != 200) {
			$this->client->lastError = 'Response to attachments->deleteById is not valid. See $client->lastResponseHeaders for details';
			return false;
		}
		return true;
	}

}

?>

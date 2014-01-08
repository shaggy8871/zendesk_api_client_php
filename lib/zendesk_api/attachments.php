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
	public function upload(array $params) {
		if(!$params['file']) {
			$this->client->lastError = 'Missing parameter: \'file\' must be supplied for '.__METHOD__;
			return false;
		}
		if(!file_exists($params['file'])) {
			$this->client->lastError = 'File '.$params['file'].' could not be found in '.__METHOD__;
			return false;
		}
		$endPoint = 'uploads.json?filename='.$params['file'].($params['optional_token'] ? '&token='.$params['optional_token'] : '');
		$response = Http::send($this->client, $endPoint, array('filename' => '@'.$params['file']), 'POST', ($params['type'] ? $params['type'] : 'application/binary'));
		if ((!is_object($response)) || ($this->client->lastResponseCode != 201)) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
		}
		return $response;
	}

	/*
	 * Delete one or more attachments by token or id
	 * $params must include one of these:
	 *		'token' - the token given to you after the original upload
	 *		'id' - the id of the attachment
	 */
	public function delete(array $params) {
		if((!$params['token']) && (!$params['id'])) {
			$this->client->lastError = 'Missing parameter: \'id\' or \'token\' must be supplied for '.__METHOD__;
			return false;
		}
		$endPoint = ($params['token'] ? 'uploads/'.$params['token'] : 'attachments/'.$params['id']).'.json';
		$response = Http::send($this->client, $endPoint, null, 'DELETE');
		if ($this->client->lastResponseCode != 200) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
			return $response;
		}
		return true;
	}

	/*
	 * Get a list of uploaded attachments (by id)
	 * $params must include:
	 *		'id' - the id of the attachment
	 */
	public function find(array $params) {
		if(!$params['id']) {
			$this->client->lastError = 'Missing parameter: \'id\' must be supplied for '.__METHOD__;
			return false;
		}
		$id = $params['id'];
		$endPoint = 'attachments/'.$id.'.json';
		$response = Http::send($this->client, $endPoint);
		if ($this->client->lastResponseCode != 200) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
		}
		return $response;
	}

	/*
	 * Enable side-loading (beta) - flags until the next endpoint call
	 */
	public function sideload(array $fields) {
		$this->client->sideload = $fields;
		return $this;
	}

}

?>

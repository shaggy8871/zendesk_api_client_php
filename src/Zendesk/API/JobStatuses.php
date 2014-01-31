<?php

namespace Zendesk\API;

/**
 * The JobStatuses class exposes information about the status of a job
 */
class JobStatuses extends ClientAbstract {

	/*
	 * Show a specific job status
	 */
	public function find(array $params = array()) {
		if($this->lastId != null) {
			$params['id'] = $this->lastId;
			$this->lastId = null;
		}
		if(!$this->hasKeys($params, array('id'))) {
			throw new MissingParametersException(__METHOD__, array('id'));
		}
		$endPoint = Http::prepare('job_statuses/'.$params['id'].'.json');
		$response = Http::send($this->client, $endPoint);
        echo __METHOD__;
        print_r($this->client->getDebug());
        print_r($response);
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		$this->client->setSideload(null);
		return $response;
	}

}

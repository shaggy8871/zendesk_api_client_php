<?php

namespace Zendesk\API;

/**
 * The Tickets class exposes key methods for reading and updating ticket data
 */
class Tickets extends ClientAbstract {

	protected $audits;
	protected $comments;
	protected $metrics;
	protected $import;
	/*
	 * Helpers:
	 */
	protected $lastAttachments = array();

	public function __construct($client) {
		parent::__construct($client);
		$this->audits = new TicketAudits($client);
		$this->comments = new TicketComments($client);
		$this->metrics = new TicketMetrics($client);
		$this->import = new TicketImport($client);
	}

	/*
	 * Returns all recent tickets overall, per user or per organization
	 */
	public function findAll(array $params = array ()) {
		$endPoint = Http::prepare(
				(isset($params['organization_id']) ? 'organizations/'.$params['organization_id'].'/tickets' : 
				(isset($params['user_id']) ? 'users/'.$params['user_id'].'/tickets/'.($params['ccd'] ? 'ccd' : 'requested') : 
				(isset($params['recent']) ? 'tickets/recent' : 'tickets'))
			).'.json', ((isset($params['sideload'])) && (is_array($params['sideload'])) ? $params['sideload'] : $this->client->getSideload()));
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		$this->client->setSideload(null);
		return $response;
	}

	/*
	 * Find a specific ticket by id or series of ids
	 */
	public function find(array $params = array()) {
		if($this->lastId != null) {
			$params['id'] = $this->lastId;
			$this->lastId = null;
		}
		if(!$params['id']) {
			throw new MissingParametersException(__METHOD__, array('id'));
		}
		$endPoint = Http::prepare((is_array($params['id']) ? 'tickets/show_many.json?ids='.implode(',', $params['id']) : 'tickets/'.$params['id'].'.json'), ((isset($params['sideload'])) && (is_array($params['sideload'])) ? $params['sideload'] : $this->client->getSideload()));
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		$this->client->setSideload(null);
		return $response;
	}

	/*
	 * Find a specific twitter generated ticket by id
	 */
	public function findTwicket(array $params = array()) {
		if($this->lastId != null) {
			$params['id'] = $this->lastId;
			$this->lastId = null;
		}
		if(!$params['id']) {
			throw new MissingParametersException(__METHOD__, array('id'));
		}
		$endPoint = Http::prepare('channels/twitter/tickets/'.$params['id'].'/statuses.json'.(is_array($params['comment_ids']) ? '?'.implode(',', $params['comment_ids']) : ''), ((isset($params['sideload'])) && (is_array($params['sideload'])) ? $params['sideload'] : $this->client->getSideload()));
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		$this->client->setSideload(null);
		return $response;
	}

	/*
	 * Create a ticket
	 */
	public function create(array $params) {
		if(count($this->lastAttachments)) {
			$params['comment']['uploads'] = $this->lastAttachments;
			$this->lastAttachments = array();
		}
		$endPoint = Http::prepare('tickets.json');
		$response = Http::send($this->client, $endPoint, array ('ticket' => $params), 'POST');
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 201)) {
			throw new ResponseException(__METHOD__);
		}
		return $response;
	}

	/*
	 * Create a ticket from a tweet
	 */
	public function createFromTweet(array $params) {
		if((!$params['twitter_status_message_id']) || (!$params['monitored_twitter_handle_id'])) {
			throw new MissingParametersException(__METHOD__, array('twitter_status_message_id', 'monitored_twitter_handle_id'));
		}
		$endPoint = Http::prepare('channels/twitter/tickets.json');
		$response = Http::send($this->client, $endPoint, array ('ticket' => $params), 'POST');
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 201)) {
			throw new ResponseException(__METHOD__, ($this->client->getDebug()->lastResponseCode == 422 ? ' (hint: you can\'t create two tickets from the same tweet)' : ''));
		}
		return $response;
	}

	/*
	 * Update a ticket or series of tickets
	 */
	public function update(array $params) {
		if($this->lastId != null) {
			$params['id'] = $this->lastId;
			$this->lastId = null;
		}
		if(!$params['id']) {
			throw new MissingParametersException(__METHOD__, array('id'));
		}
		if(count($this->lastAttachments)) {
			$params['comment']['uploads'] = $this->lastAttachments;
			$this->lastAttachments = array();
		}
		$id = $params['id'];
		unset($params['id']);
		$endPoint = Http::prepare((is_array($id) ? 'tickets/update_many.json?ids='.implode(',', $id) : 'tickets/'.$id.'.json'));
		$response = Http::send($this->client, $endPoint, array ('ticket' => $params), 'PUT');
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__, ($this->client->getDebug()->lastResponseCode == 422 ? ' (hint: you can\'t update a closed ticket)' : ''));
		}
		return $response;
	}

	/*
	 * Mark a ticket as spam
	 */
	public function markAsSpam(array $params = array()) {
		if($this->lastId != null) {
			$params['id'] = $this->lastId;
			$this->lastId = null;
		}
		if(!$params['id']) {
			throw new MissingParametersException(__METHOD__, array('id'));
		}
		$id = $params['id'];
		$endPoint = Http::prepare('tickets/'.$id.'/mark_as_spam.json');
		$response = Http::send($this->client, $endPoint, null, 'PUT');
		// Seems to be a bug in the service, it may respond with 422 even when it succeeds
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__, ($this->client->getDebug()->lastResponseCode == 422 ? ' (note: there\'s currently a bug in the service so this call may have succeeded; call tickets->find to see if it still exists.)' : ''));
		}
		return $response;
	}

	/*
	 * Get related ticket information
	 */
	public function related(array $params = array()) {
		if($this->lastId != null) {
			$params['id'] = $this->lastId;
			$this->lastId = null;
		}
		if(!$params['id']) {
			throw new MissingParametersException(__METHOD__, array('id'));
		}
		$id = $params['id'];
		$endPoint = Http::prepare('tickets/'.$id.'/related.json', ((isset($params['sideload'])) && (is_array($params['sideload'])) ? $params['sideload'] : $this->client->getSideload()));
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		$this->client->setSideload(null);
		return $response;
	}

	/*
	 * Delete a ticket or series of tickets
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
		$endPoint = Http::prepare((is_array($id) ? 'tickets/destroy_many.json?ids='.implode(',', $id) : 'tickets/'.$id.'.json'));
		$response = Http::send($this->client, $endPoint, null, 'DELETE');
		if ($this->client->getDebug()->lastResponseCode != 200) {
			throw new ResponseException(__METHOD__);
		}
		return true;
	}

	/*
	 * List collaborators for a ticket
	 */
	public function collaborators(array $params) {
		if($this->lastId != null) {
			$params['id'] = $this->lastId;
			$this->lastId = null;
		}
		if(!$params['id']) {
			throw new MissingParametersException(__METHOD__, array('id'));
		}
		$id = $params['id'];
		$endPoint = Http::prepare('tickets/'.$id.'/collaborators.json', ((isset($params['sideload'])) && (is_array($params['sideload'])) ? $params['sideload'] : $this->client->getSideload()));
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		$this->client->setSideload(null);
		return $response;
	}

	/*
	 * List incidents for a ticket
	 */
	public function incidents(array $params) {
		if($this->lastId != null) {
			$params['id'] = $this->lastId;
			$this->lastId = null;
		}
		if(!$params['id']) {
			throw new MissingParametersException(__METHOD__, array('id'));
		}
		$id = $params['id'];
		$endPoint = Http::prepare('tickets/'.$id.'/incidents.json', ((isset($params['sideload'])) && (is_array($params['sideload'])) ? $params['sideload'] : $this->client->getSideload()));
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		$this->client->setSideload(null);
		return $response;
	}

	/*
	 * List problems for a ticket
	 */
	public function problems(array $params) {
		if($this->lastId != null) {
			$params['id'] = $this->lastId;
			$this->lastId = null;
		}
		if(!$params['id']) {
			throw new MissingParametersException(__METHOD__, array('id'));
		}
		$id = $params['id'];
		$endPoint = Http::prepare('tickets/'.$id.'/problems.json', ((isset($params['sideload'])) && (is_array($params['sideload'])) ? $params['sideload'] : $this->client->getSideload()));
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		$this->client->setSideload(null);
		return $response;
	}

	/*
	 * Add a problem autocomplete
	 */
	public function problemAutoComplete(array $params) {
		if($this->lastId != null) {
			$params['id'] = $this->lastId;
			$this->lastId = null;
		}
		if(!$params['id']) {
			throw new MissingParametersException(__METHOD__, array('id'));
		}
		if(!$params['text']) {
			throw new MissingParametersException(__METHOD__, array('text'));
		}
		$id = $params['id'];
		$endPoint = Http::prepare('tickets/'.$id.'/problems/autocomplete.json');
		$response = Http::send($this->client, $endPoint, array('text' => $params['text']), 'POST');
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		return $response;
	}

	/*
	 * Incremental ticket exports with a supplied start_time
	 */
	public function export(array $params) {
		if(!$params['start_time']) {
			throw new MissingParametersException(__METHOD__, array('start_time'));
		}
		$endPoint = Http::prepare('exports/tickets.json?start_time='.$params['start_time']);
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		return $response;
	}

	/*
	 * For testing of incremental tickets only
	 */
	public function exportSample(array $params) {
		if(!$params['start_time']) {
			throw new MissingParametersException(__METHOD__, array('start_time'));
		}
		$endPoint = Http::prepare('exports/tickets/sample.json?start_time='.$params['start_time']);
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
			throw new ResponseException(__METHOD__);
		}
		return $response;
	}

	/*
	 * Syntactic sugar methods:
	 * Handy aliases:
	 */
	public function audits(array $params = array()) { return $this->audits->findAll($params); }
	public function audit($id) { return $this->audits->setLastId($id); }
	public function comments(array $params = array()) { return $this->comments->findAll($params); }
	public function comment($id) { return $this->comments->setLastId($id); }
	public function metrics(array $params = array()) { return $this->metrics->findAll($params); }
	public function metric($id) { return $this->metrics->setLastId($id); }
	public function import(array $params) { return $this->import->import($params); }
	public function attach(array $params = array()) {
		if(!$params['file']) {
			throw new MissingParametersException(__METHOD__, array('file'));
		}
		$upload = $this->client->attachments()->upload($params);
		if((!is_object($upload->upload)) || (!$upload->upload->token)) {
			throw new ResponseException(__METHOD__);
		}
		$this->lastAttachments[] = $upload->upload->token;
		return $this;
	}

}

?>

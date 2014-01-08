<?php

/**
 * The Tickets class exposes key methods for reading and updating ticket data
 * Examples:
 *		$client = new ZendeskAPI($subdomain, $username);
 *		$client->setToken($token);
 *		$tickets = $client->tickets->all();
 *			... will place an array of ticket objects in the $tickets variable
 *		$tickets = $client->tickets->find(array('id' => 123));
 *			... will place a single ticket object in the $tickets variable
 * Many methods accept an array of ids as follows:
 *		$tickets = $client->tickets->find(array('id' => array(123, 456)));
 * Enable side-loading through two options:
 *		$client->tickets->sideload(array('users', 'groups'))->all(); // enabled via chain
 *		$client->tickets->all(array('sideload' => array('users', 'groups'))); // send as part of parameters
 */
class Tickets {

	private $client;

	/*
	 * Public objects:
	 */
	public $audits;
	public $comments;
	public $fields;
	public $forms;
	public $import;
	public $metrics;
	/*
	 * Helpers:
	 */
	public $lastId;
	public $lastAttachments = array();

	public function __construct($client) {
		$this->client = $client;
		$this->audits = new Ticket_Audits($client);
		$this->comments = new Ticket_Comments($client);
		$this->fields = new Ticket_Fields($client);
		$this->forms = new Ticket_Forms($client);
		$this->import = new Ticket_Import($client);
		$this->metrics = new Ticket_Metrics($client);
	}

	/*
	 * Returns all recent tickets overall, per user or per organization
	 */
	public function all(array $params = array ()) {
		$endPoint = Http::prepare(
				($params['organization_id'] ? 'organizations/'.$params['organization_id'].'/tickets' : 
				($params['user_id'] ? 'users/'.$params['user_id'].'/tickets/'.($params['ccd'] ? 'ccd' : 'requested') : 
				($params['recent'] ? 'tickets/recent' : 'tickets'))
			).'.json', (is_array($params['sideload']) ? $params['sideload'] : $this->client->sideload));
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
		}
		$this->client->sideload = null;
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
			$this->client->lastError = 'Missing parameter: \'id\' must be supplied for '.__METHOD__;
			return false;
		}
		$endPoint = Http::prepare((is_array($params['id']) ? 'tickets/show_many.json?ids='.implode(',', $params['id']) : 'tickets/'.$params['id'].'.json'), (is_array($params['sideload']) ? $params['sideload'] : $this->client->sideload));
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
		}
		$this->client->sideload = null;
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
			$this->client->lastError = 'Missing parameter: \'id\' must be supplied for '.__METHOD__;
			return false;
		}
		$endPoint = Http::prepare('channels/twitter/tickets/'.$params['id'].'/statuses.json'.(is_array($params['comment_ids']) ? '?'.implode(',', $params['comment_ids']) : ''), (is_array($params['sideload']) ? $params['sideload'] : $this->client->sideload));
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
		}
		$this->client->sideload = null;
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
		if ((!is_object($response)) || ($this->client->lastResponseCode != 201)) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
		}
		return $response;
	}

	/*
	 * Create a ticket from a tweet
	 */
	public function createFromTweet(array $params) {
		if((!$params['twitter_status_message_id']) || (!$params['monitored_twitter_handle_id'])) {
			$this->client->lastError = 'Missing parameter: both \'twitter_status_message_id\' and \'monitored_twitter_handle_id\' must be supplied for '.__METHOD__;
			return false;
		}
		$endPoint = Http::prepare('channels/twitter/tickets.json');
		$response = Http::send($this->client, $endPoint, array ('ticket' => $params), 'POST');
		if ((!is_object($response)) || ($this->client->lastResponseCode != 201)) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details'.($this->client->lastResponseCode == 422 ? ' (hint: you can\'t create two tickets from the same tweet)' : '');
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
			$this->client->lastError = 'Missing parameter: \'id\' must be supplied for '.__METHOD__;
			return false;
		}
		if(count($this->lastAttachments)) {
			$params['comment']['uploads'] = $this->lastAttachments;
			$this->lastAttachments = array();
		}
		$id = $params['id'];
		unset($params['id']);
		$endPoint = Http::prepare((is_array($id) ? 'tickets/update_many.json?ids='.implode(',', $id) : 'tickets/'.$id.'.json'));
		$response = Http::send($this->client, $endPoint, array ('ticket' => $params), 'PUT');
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details'.($this->client->lastResponseCode == 422 ? ' (hint: you can\'t update a closed ticket)' : '');
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
			$this->client->lastError = 'Missing parameter: \'id\' must be supplied for '.__METHOD__;
			return false;
		}
		$id = $params['id'];
		$endPoint = Http::prepare('tickets/'.$id.'/mark_as_spam.json');
		$response = Http::send($this->client, $endPoint, null, 'PUT');
		// Seems to be a bug in the service, it may respond with 422 even when it succeeds
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details'.($this->client->lastResponseCode == 422 ? ' (note: there\'s currently a bug in the service so this call may have succeeded; call tickets->find to see if it still exists.)' : '');
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
			$this->client->lastError = 'Missing parameter: \'id\' must be supplied for '.__METHOD__;
			return false;
		}
		$id = $params['id'];
		$endPoint = Http::prepare('tickets/'.$id.'/related.json', (is_array($params['sideload']) ? $params['sideload'] : $this->client->sideload));
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
		}
		$this->client->sideload = null;
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
			$this->client->lastError = 'Missing parameter: \'id\' must be supplied for '.__METHOD__;
			return false;
		}
		$id = $params['id'];
		$endPoint = Http::prepare((is_array($id) ? 'tickets/destroy_many.json?ids='.implode(',', $id) : 'tickets/'.$id.'.json'));
		$response = Http::send($this->client, $endPoint, null, 'DELETE');
		if ($this->client->lastResponseCode != 200) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
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
			$this->client->lastError = 'Missing parameter: \'id\' must be supplied for '.__METHOD__;
			return false;
		}
		$id = $params['id'];
		$endPoint = Http::prepare('tickets/'.$id.'/collaborators.json', (is_array($params['sideload']) ? $params['sideload'] : $this->client->sideload));
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
		}
		$this->client->sideload = null;
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
			$this->client->lastError = 'Missing parameter: \'id\' must be supplied for '.__METHOD__;
			return false;
		}
		$id = $params['id'];
		$endPoint = Http::prepare('tickets/'.$id.'/incidents.json', (is_array($params['sideload']) ? $params['sideload'] : $this->client->sideload));
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
		}
		$this->client->sideload = null;
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
			$this->client->lastError = 'Missing parameter: \'id\' must be supplied for '.__METHOD__;
			return false;
		}
		$id = $params['id'];
		$endPoint = Http::prepare('tickets/'.$id.'/problems.json', (is_array($params['sideload']) ? $params['sideload'] : $this->client->sideload));
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
		}
		$this->client->sideload = null;
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
			$this->client->lastError = 'Missing parameter: \'id\' must be supplied for '.__METHOD__;
			return false;
		}
		if(!$params['text']) {
			$this->client->lastError = 'Missing parameter: \'text\' must be supplied for '.__METHOD__;
			return false;
		}
		$id = $params['id'];
		$endPoint = Http::prepare('tickets/'.$id.'/problems/autocomplete.json');
		$response = Http::send($this->client, $endPoint, array('text' => $params['text']), 'POST');
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
		}
		return $response;
	}

	/*
	 * Incremental ticket exports with a supplied start_time
	 */
	public function export(array $params) {
		if(!$params['start_time']) {
			$this->client->lastError = 'Missing parameter: \'start_time\' must be supplied for '.__METHOD__;
			return false;
		}
		$endPoint = Http::prepare('exports/tickets.json?start_time='.$params['start_time']);
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
		}
		return $response;
	}

	/*
	 * For testing of incremental tickets only
	 */
	public function exportSample(array $params) {
		if(!$params['start_time']) {
			$this->client->lastError = 'Missing parameter: \'start_time\' must be supplied for '.__METHOD__;
			return false;
		}
		$endPoint = Http::prepare('exports/tickets/sample.json?start_time='.$params['start_time']);
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
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

	/*
	 * Syntactic sugar methods:
	 * Handy aliases:
	 */
	public function recent(array $params = array()) { $params['recent'] = true; return $this->all($params); }
	public function audits(array $params = array()) { return $this->audits->all($params); }
	public function comments(array $params = array()) { return $this->comments->all($params); }
	public function fields(array $params = array()) { return $this->fields->all($params); }
	public function forms(array $params = array()) { return $this->forms->all($params); }
	public function import(array $params = array()) { return $this->import->import($params); }
	public function metrics(array $params = array()) { return $this->metrics->all($params); }
	/*
	 * Helpers:
	 */
	public function audit($id) { $this->audits->lastId = $id; return $this->audits; }
	public function comment($id) { $this->comments->lastId = $id; return $this->comments; }
	public function field($id) { $this->fields->lastId = $id; return $this->fields; }
	public function form($id) { $this->forms->lastId = $id; return $this->forms; }
	public function metric($id) { $this->metrics->lastId = $id; return $this->metrics; }
	public function attach(array $params = array()) {
		if(!$params['file']) {
			$this->client->lastError = 'Missing parameter: \'file\' must be supplied for '.__METHOD__;
			return false;
		}
		$upload = $this->client->attachments->upload($params);
		if((!is_object($upload->upload)) || (!$upload->upload->token)) {
			$this->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
		}
		$this->lastAttachments[] = $upload->upload->token;
		return $this;
	}

}

?>

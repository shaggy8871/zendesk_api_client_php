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
 *		$client->tickets->sideLoad(array('users', 'groups'))->all(); // enabled via chain
 *		$client->tickets->all(array('sideload' => array('users', 'groups'))); // send as part of parameters
 */
class Tickets {

	private $client;

	public $audits;
	public $comments;
	public $fields;

	public function __construct($client) {
		$this->client = $client;
		$this->audits = new Ticket_Audits($client);
		$this->comments = new Ticket_Comments($client);
		$this->fields = new Ticket_Fields($client);
	}

	/*
	 * Enable side-loading (beta) - flags until the next endpoint call
	 */
	public function withSideLoad($fields) {
		$this->client->sideLoad = $fields;
		return $this;
	}

	/*
	 * Returns all recent tickets overall, per user or per organization
	 */
	public function all($params = array ()) {
		$endPoint = Http::prepare(
				($params['organization_id'] ? 'organizations/'.$params['organization_id'].'/tickets' : 
				($params['user_id'] ? 'users/'.$params['user_id'].'/tickets/'.($params['ccd'] ? 'ccd' : 'requested') : 
				($params['recent'] ? 'tickets/recent' : 'tickets'))
			).'.json', (is_array($params['sideload']) ? $params['sideload'] : $this->client->sideLoad));
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
			return false;
		}
		$this->client->sideLoad = null;
		return $response;
	}

	/*
	 * Find a specific ticket by id or series of ids
	 */
	public function find($params) {
		if($this->client->lastTicket != null) {
			$params['id'] = $this->client->lastTicket;
			$this->client->lastTicket = null;
		}
		if(!$params['id']) {
			$this->client->lastError = 'No id supplied for '.__METHOD__;
			return false;
		}
		$endPoint = Http::prepare((is_array($params['id']) ? 'tickets/show_many.json?ids='.implode(',', $params['id']) : 'tickets/'.$params['id'].'.json'), (is_array($params['sideload']) ? $params['sideload'] : $this->client->sideLoad));
		$response = Http::send($this->client, $endPoint);
		if ((!is_array($id)) && ($this->client->lastResponseCode == 404)) {
			$this->client->lastError = 'The ticket does not exist';
			return false;
		}
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
			return false;
		}
		$this->client->sideLoad = null;
		return $response;
	}

	/*
	 * Find a specific twitter generated ticket by id
	 */
	public function findTwicket($params) {
		if($this->client->lastTicket != null) {
			$params['id'] = $this->client->lastTicket;
			$this->client->lastTicket = null;
		}
		if(!$params['id']) {
			$this->client->lastError = 'No id supplied for '.__METHOD__;
			return false;
		}
		$endPoint = Http::prepare('channels/twitter/tickets/'.$params['id'].'/statuses.json'.(is_array($params['comment_ids']) ? '?'.implode(',', $params['comment_ids']) : ''), (is_array($params['sideload']) ? $params['sideload'] : $this->client->sideLoad));
		$response = Http::send($this->client, $endPoint);
		if ($this->client->lastResponseCode == 404) {
			$this->client->lastError = 'The ticket does not exist';
			return false;
		}
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
			return false;
		}
		$this->client->sideLoad = null;
		return $response;
	}

	/*
	 * Create a ticket
	 */
	public function create($params) {
		$endPoint = Http::prepare('tickets.json');
		$response = Http::send($this->client, $endPoint, array ('ticket' => $params), 'POST');
		if ((!is_object($response)) || ($this->client->lastResponseCode != 201)) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
			return false;
		}
		return $response;
	}

	/*
	 * Create a ticket with an attachment (helper function)
	 */
	public function createWithAttachment($params) {
		if((!$params['attachment']) || (!is_array($params['attachment']))) {
			$this->client->lastError = 'Missing parameter: \'attachment\' must be supplied as an Array for '.__METHOD__;
			return false;
		}
		$upload = $this->client->attachments->upload($params['attachment']);
		if((!is_object($upload->upload)) && (!$upload->upload->token)) {
			$this->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
			return false;
		}
		unset($params['attachment']);
		$params['comment']['uploads'] = array($upload->upload->token); // attach
		$endPoint = Http::prepare('tickets.json');
		$response = Http::send($this->client, $endPoint, array ('ticket' => $params), 'POST');
		if ((!is_object($response)) || ($this->client->lastResponseCode != 201)) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
			return false;
		}
		return $response;
	}

	/*
	 * Create a ticket from a tweet
	 */
	public function createFromTweet($params) {
		if((!$params['twitter_status_message_id']) || (!$params['monitored_twitter_handle_id'])) {
			$this->client->lastError = 'Missing parameter: both \'twitter_status_message_id\' and \'monitored_twitter_handle_id\' must be supplied for '.__METHOD__;
			return false;
		}
		$endPoint = Http::prepare('channels/twitter/tickets.json');
		$response = Http::send($this->client, $endPoint, array ('ticket' => $params), 'POST');
		if ($this->client->lastResponseCode == 422) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details (hint: you can\'t create two tickets from the same tweet)';
			return false;
		}
		if ((!is_object($response)) || ($this->client->lastResponseCode != 201)) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
			return false;
		}
		return $response;
	}

	/*
	 * Update a ticket or series of tickets
	 */
	public function update($params) {
		if($this->client->lastTicket != null) {
			$params['id'] = $this->client->lastTicket;
			$this->client->lastTicket = null;
		}
		if(!$params['id']) {
			$this->client->lastError = 'No id supplied for '.__METHOD__;
			return false;
		}
		$id = $params['id'];
		unset($params['id']);
		$endPoint = Http::prepare((is_array($id) ? 'tickets/update_many.json?ids='.implode(',', $id) : 'tickets/'.$id.'.json'));
		$response = Http::send($this->client, $endPoint, array ('ticket' => $params), 'PUT');
		if ((!is_array($id)) && ($this->client->lastResponseCode == 404)) {
			$this->client->lastError = 'The ticket does not exist';
			return false;
		}
		if ((!is_array($id)) && ($this->client->lastResponseCode == 422)) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details (hint: you can\'t update a closed ticket)';
			return false;
		}
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
			return false;
		}
		return $response;
	}

	/*
	 * Mark a ticket as spam
	 */
	public function markAsSpam($params) {
		if($this->client->lastTicket != null) {
			$params['id'] = $this->client->lastTicket;
			$this->client->lastTicket = null;
		}
		if(!$params['id']) {
			$this->client->lastError = 'No id supplied for '.__METHOD__;
			return false;
		}
		$id = $params['id'];
		$endPoint = Http::prepare('tickets/'.$id.'/mark_as_spam.json');
		$response = Http::send($this->client, $endPoint, null, 'PUT');
		// Seems to be a bug in the service, it may respond with 422 even when it succeeds
		if ($this->client->lastResponseCode == 404) {
			$this->client->lastError = 'The ticket does not exist';
			return false;
		}
		if ($this->client->lastResponseCode == 422) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details (note: there\'s currently a bug in the service so this call may have succeeded; call tickets->find to see if it still exists.)';
			return false;
		}
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
			return false;
		}
		return $response;
	}

	/*
	 * Get related ticket information
	 */
	public function related($params) {
		if($this->client->lastTicket != null) {
			$params['id'] = $this->client->lastTicket;
			$this->client->lastTicket = null;
		}
		if(!$params['id']) {
			$this->client->lastError = 'No id supplied for '.__METHOD__;
			return false;
		}
		$id = $params['id'];
		$endPoint = Http::prepare('tickets/'.$id.'/related.json', (is_array($params['sideload']) ? $params['sideload'] : $this->client->sideLoad));
		$response = Http::send($this->client, $endPoint);
		if ($this->client->lastResponseCode == 404) {
			$this->client->lastError = 'The ticket does not exist';
			return false;
		}
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
			return false;
		}
		$this->client->sideLoad = null;
		return $response;
	}

	/*
	 * Delete a ticket or series of tickets
	 */
	public function delete($params) {
		if($this->client->lastTicket != null) {
			$params['id'] = $this->client->lastTicket;
			$this->client->lastTicket = null;
		}
		if(!$params['id']) {
			$this->client->lastError = 'No id supplied for '.__METHOD__;
			return false;
		}
		$id = $params['id'];
		$endPoint = Http::prepare((is_array($id) ? 'tickets/destroy_many.json?ids='.implode(',', $id) : 'tickets/'.$id.'.json'));
		$response = Http::send($this->client, $endPoint, null, 'DELETE');
		if ((!is_array($id)) && ($this->client->lastResponseCode == 404)) {
			$this->client->lastError = 'The ticket does not exist';
			return false;
		}
		if ($this->client->lastResponseCode != 200) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
			return false;
		}
		return true;
	}

	/*
	 * List collaborators for a ticket
	 */
	public function collaborators($params) {
		if($this->client->lastTicket != null) {
			$params['id'] = $this->client->lastTicket;
			$this->client->lastTicket = null;
		}
		if(!$params['id']) {
			$this->client->lastError = 'No id supplied for '.__METHOD__;
			return false;
		}
		$id = $params['id'];
		$endPoint = Http::prepare('tickets/'.$id.'/collaborators.json', (is_array($params['sideload']) ? $params['sideload'] : $this->client->sideLoad));
		$response = Http::send($this->client, $endPoint);
		if ($this->client->lastResponseCode == 404) {
			$this->client->lastError = 'The ticket does not exist';
			return false;
		}
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
			return false;
		}
		$this->client->sideLoad = null;
		return $response;
	}

	/*
	 * List incidents for a ticket
	 */
	public function incidents($params) {
		if($this->client->lastTicket != null) {
			$params['id'] = $this->client->lastTicket;
			$this->client->lastTicket = null;
		}
		if(!$params['id']) {
			$this->client->lastError = 'No id supplied for '.__METHOD__;
			return false;
		}
		$id = $params['id'];
		$endPoint = Http::prepare('tickets/'.$id.'/incidents.json', (is_array($params['sideload']) ? $params['sideload'] : $this->client->sideLoad));
		$response = Http::send($this->client, $endPoint);
		if ($this->client->lastResponseCode == 404) {
			$this->client->lastError = 'The ticket does not exist';
			return false;
		}
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
			return false;
		}
		$this->client->sideLoad = null;
		return $response;
	}

	/*
	 * List problems for a ticket
	 */
	public function problems($params) {
		if($this->client->lastTicket != null) {
			$params['id'] = $this->client->lastTicket;
			$this->client->lastTicket = null;
		}
		if(!$params['id']) {
			$this->client->lastError = 'No id supplied for '.__METHOD__;
			return false;
		}
		$id = $params['id'];
		$endPoint = Http::prepare('tickets/'.$id.'/problems.json', (is_array($params['sideload']) ? $params['sideload'] : $this->client->sideLoad));
		$response = Http::send($this->client, $endPoint);
		if ($this->client->lastResponseCode == 404) {
			$this->client->lastError = 'The ticket does not exist';
			return false;
		}
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
			return false;
		}
		$this->client->sideLoad = null;
		return $response;
	}

	/*
	 * Add a problem autocomplete
	 */
	public function problemAutoComplete($params) {
		if($this->client->lastTicket != null) {
			$params['id'] = $this->client->lastTicket;
			$this->client->lastTicket = null;
		}
		if(!$params['id']) {
			$this->client->lastError = 'No id supplied for '.__METHOD__;
			return false;
		}
		if(!$params['text']) {
			$this->client->lastError = 'No text supplied for '.__METHOD__;
			return false;
		}
		$id = $params['id'];
		$endPoint = Http::prepare('tickets/'.$id.'/problems/autocomplete.json');
		$response = Http::send($this->client, $endPoint, array('text' => $params['text']), 'POST');
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
			return false;
		}
		return $response;
	}

	/*
	 * Incremental ticket exports with a supplied start_time
	 */
	public function export($params) {
		if(!$params['start_time']) {
			$this->client->lastError = 'No start_time parameter supplied for '.__METHOD__;
			return false;
		}
		$endPoint = Http::prepare('exports/tickets.json?start_time='.$params['start_time']);
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
			return false;
		}
		return $response;
	}

	/*
	 * For testing of incremental tickets only
	 */
	public function exportSample($params) {
		if(!$params['start_time']) {
			$this->client->lastError = 'No start_time parameter supplied for '.__METHOD__;
			return false;
		}
		$endPoint = Http::prepare('exports/tickets/sample.json?start_time='.$params['start_time']);
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
			return false;
		}
		return $response;
	}

	/*
	 * Syntactic sugar methods:
	 * Methods that read better for developers, such as $client->tickets->audits() instead of $client->tickets->audits->all()
	 * GETter methods only (for now)
	 */
	public function fields($params) { return $this->fields->all($params); }

}

?>

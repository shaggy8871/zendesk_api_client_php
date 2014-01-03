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
 */
class Tickets {

	private $client;

	public function __construct($client) {
		$this->client = $client;
	}

	/*
	 * Returns all recent tickets overall, per user or per organization
	 */
	public function all($params = array ()) {
		$endPoint = ($params['organization_id'] ? 'organizations/'.$params['organization_id'].'/tickets' : 
					($params['user_id'] ? 'users/'.$params['user_id'].'/tickets/'.($params['ccd'] ? 'ccd' : 'requested') : 'tickets/recent')).'.json';
		$response = Http::send($this->client, $endPoint);
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to tickets->all is not an object';
			return false;
		}
		return $response;
	}

	/*
	 * Find a specific ticket by id or series of ids
	 */
	public function find($params) {
		$endPoint = (is_array($params['id']) ? 'tickets/show_many.json?ids='.implode(',', $params['id']) : 'tickets/'.$params['id'].'.json');
		$response = Http::send($this->client, $endPoint);
		if ((!is_array($id)) && ($this->client->lastResponseCode == 401)) {
			$this->client->lastError = 'The ticket does not exist';
			return false;
		}
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to tickets->find is not an object';
			return false;
		}
		return $response;
	}

	/*
	 * Find a specific twitter generated ticket by id
	 */
	public function findTwicket($params) {
		$endPoint = 'channels/twitter/tickets/'.$params['id'].'/statuses.json'.(is_array($params['comment_ids']) ? '?'.implode(',', $params['comment_ids']) : '');
		$response = Http::send($this->client, $endPoint);
		if ((!is_array($id)) && ($this->client->lastResponseCode == 401)) {
			$this->client->lastError = 'The ticket does not exist';
			return false;
		}
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to tickets->findTwicket is not an object';
			return false;
		}
		return $response;
	}

	/*
	 * Create a ticket
	 */
	public function create($params) {
		$endPoint = 'tickets.json';
		$response = Http::send($this->client, $endPoint, array ('ticket' => $params), 'POST');
		if ((!is_object($response)) || ($this->client->lastResponseCode != 201)) {
			$this->client->lastError = 'Response to tickets->create is not valid. See $client->lastResponseHeaders for details';
			return false;
		}
		return $response;
	}

	/*
	 * Create a ticket from a tweet
	 */
	public function createFromTweet($params) {
		if((!$params['twitter_status_message_id']) || (!$params['monitored_twitter_handle_id'])) {
			$this->client->lastError = 'Missing parameter: both \'twitter_status_message_id\' and \'monitored_twitter_handle_id\' must be supplied for tickets->createFromTweet';
			return false;
		}
		$endPoint = 'channels/twitter/tickets.json';
		$response = Http::send($this->client, $endPoint, array ('ticket' => $params), 'POST');
		if ($this->client->lastResponseCode == 422) {
			$this->client->lastError = 'Response to tickets->createFromTweet is not valid. See $client->lastResponseHeaders for details (hint: you can\'t create two tickets from the same tweet)';
			return false;
		}
		if ((!is_object($response)) || ($this->client->lastResponseCode != 201)) {
			$this->client->lastError = 'Response to tickets->createFromTweet is not valid. See $client->lastResponseHeaders for details';
			return false;
		}
		return $response;
	}

	/*
	 * Update a ticket or series of tickets
	 */
	public function update($params) {
		if(!$params['id']) {
			$this->client->lastError = 'No id supplied for tickets->update';
			return false;
		}
		$id = $params['id'];
		unset($params['id']);
		$endPoint = (is_array($id) ? 'tickets/update_many.json?ids='.implode(',', $id) : 'tickets/'.$id.'.json');
		$response = Http::send($this->client, $endPoint, array ('ticket' => $params), 'PUT');
		if ((!is_array($id)) && ($this->client->lastResponseCode == 401)) {
			$this->client->lastError = 'The ticket does not exist';
			return false;
		}
		if ((!is_array($id)) && ($this->client->lastResponseCode == 422)) {
			$this->client->lastError = 'Response to tickets->update is not valid. See $client->lastResponseHeaders for details (hint: you can\'t update a closed ticket)';
			return false;
		}
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to tickets->update is not valid. See $client->lastResponseHeaders for details';
			return false;
		}
		return $response;
	}

	/*
	 * Mark a ticket as spam
	 */
	public function markAsSpam($params) {
		if(!$params['id']) {
			$this->client->lastError = 'No id supplied for tickets->markAsSpam';
			return false;
		}
		$id = $params['id'];
		$endPoint = 'tickets/'.$id.'/mark_as_spam.json';
		$response = Http::send($this->client, $endPoint, null, 'PUT');
		// Seems to be a bug in the service, it may respond with 422 even when it succeeds
		if ($this->client->lastResponseCode == 401) {
			$this->client->lastError = 'The ticket does not exist';
			return false;
		}
		if ($this->client->lastResponseCode == 422) {
			$this->client->lastError = 'Response to tickets->markAsSpam is not valid. See $client->lastResponseHeaders for details (note: there\'s currently a bug in the service so this call may have succeeded; call tickets->find to see if it still exists.)';
			return false;
		}
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to tickets->markAsSpam is not valid. See $client->lastResponseHeaders for details';
			return false;
		}
		return $response;
	}

	/*
	 * Get related ticket information
	 */
	public function related($params) {
		if(!$params['id']) {
			$this->client->lastError = 'No id supplied for tickets->related';
			return false;
		}
		$id = $params['id'];
		$endPoint = 'tickets/'.$id.'/related.json';
		$response = Http::send($this->client, $endPoint);
		if ($this->client->lastResponseCode == 401) {
			$this->client->lastError = 'The ticket does not exist';
			return false;
		}
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to tickets->related is not valid. See $client->lastResponseHeaders for details';
			return false;
		}
		return $response;
	}

	/*
	 * Delete a ticket or series of tickets
	 */
	public function delete($params) {
		if(!$params['id']) {
			$this->client->lastError = 'No id supplied for tickets->delete';
			return false;
		}
		$id = $params['id'];
		$endPoint = (is_array($id) ? 'tickets/destroy_many.json?ids='.implode(',', $id) : 'tickets/'.$id.'.json');
		$response = Http::send($this->client, $endPoint, null, 'DELETE');
		if ((!is_array($id)) && ($this->client->lastResponseCode == 401)) {
			$this->client->lastError = 'The ticket does not exist';
			return false;
		}
		if ($this->client->lastResponseCode != 200) {
			$this->client->lastError = 'Response to tickets->delete is not valid. See $client->lastResponseHeaders for details';
			return false;
		}
		return true;
	}

	/*
	 * List collaborators for a ticket
	 */
	public function collaborators($params) {
		if(!$params['id']) {
			$this->client->lastError = 'No id supplied for tickets->collaborators';
			return false;
		}
		$id = $params['id'];
		$endPoint = 'tickets/'.$id.'/collaborators.json';
		$response = Http::send($this->client, $endPoint);
		if ((!is_array($id)) && ($this->client->lastResponseCode == 401)) {
			$this->client->lastError = 'The ticket does not exist';
			return false;
		}
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to tickets->collaborators is not valid. See $client->lastResponseHeaders for details';
			return false;
		}
		return $response;
	}

	/*
	 * List incidents for a ticket
	 */
	public function incidents($params) {
		if(!$params['id']) {
			$this->client->lastError = 'No id supplied for tickets->incidents';
			return false;
		}
		$id = $params['id'];
		$endPoint = 'tickets/'.$id.'/incidents.json';
		$response = Http::send($this->client, $endPoint);
		if ((!is_array($id)) && ($this->client->lastResponseCode == 401)) {
			$this->client->lastError = 'The ticket does not exist';
			return false;
		}
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to tickets->incidents is not valid. See $client->lastResponseHeaders for details';
			return false;
		}
		return $response;
	}

	/*
	 * List problems for a ticket
	 */
	public function problems($params) {
		if(!$params['id']) {
			$this->client->lastError = 'No id supplied for tickets->problems';
			return false;
		}
		$id = $params['id'];
		$endPoint = 'tickets/'.$id.'/problems.json';
		$response = Http::send($this->client, $endPoint);
		if ((!is_array($id)) && ($this->client->lastResponseCode == 401)) {
			$this->client->lastError = 'The ticket does not exist';
			return false;
		}
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to tickets->problems is not valid. See $client->lastResponseHeaders for details';
			return false;
		}
		return $response;
	}

	/*
	 * Add a problem autocomplete
	 */
	public function problemAutoComplete($params) {
		if(!$params['text']) {
			$this->client->lastError = 'No text supplied for tickets->problemAutoComplete';
			return false;
		}
		$id = $params['id'];
		$endPoint = 'tickets/'.$id.'/problems/autocomplete.json';
		$response = Http::send($this->client, $endPoint, array('text' => $params['text']), 'POST');
		if ((!is_object($response)) || ($this->client->lastResponseCode != 200)) {
			$this->client->lastError = 'Response to tickets->problemAutoComplete is not valid. See $client->lastResponseHeaders for details';
			return false;
		}
		return $response;
	}

}

?>

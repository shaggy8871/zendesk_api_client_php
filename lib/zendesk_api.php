<?php
require_once ("lib/zendesk_api/http.php");
require_once ("lib/zendesk_api/tickets.php");
require_once ("lib/zendesk_api/ticket_audits.php");
require_once ("lib/zendesk_api/ticket_comments.php");
require_once ("lib/zendesk_api/ticket_fields.php");
require_once ("lib/zendesk_api/attachments.php");
require_once ("lib/zendesk_api/twitter.php");

class ZendeskAPI {

	private $subdomain;
	private $username;
	private $password;
	private $token;
	private $oAuthToken;
	private $apiUrl;
	private $apiVer = 'v2';
	private $debug = false;

	/*
	 * Query these for information on the last http request
	 */
	public $lastError;
	public $lastRequestHeaders;
	public $lastResponseCode;
	public $lastResponseHeaders;
	public $lastTicket; // helper
	public $sideLoad; // helper

	/*
	 * Public objects
	 */
	public $tickets;
	public $ticketAudits;
	public $ticketComments;
	public $ticketFields;
	public $attachments;
	public $twitter;

	public function __construct($subdomain, $username) {
		$this->subdomain = $subdomain;
		$this->username = $username;
		$this->apiUrl = 'https://'.$subdomain.'.zendesk.com/api/'.$this->apiVer.'/';
		$this->tickets = new Tickets($this);
		$this->ticketAudits = new Ticket_Audits($this);
		$this->ticketComments = new Ticket_Comments($this);
		$this->ticketFields = new Ticket_Fields($this);
		$this->attachments = new Attachments($this);
		$this->twitter = new Twitter($this);
	}

	/*
	 * Configure the authorization method
	 */
	public function setAuth($method, $value) {
		switch($method) {
			case 'password':	$this->password = $value;
								$this->token = '';
								$this->oAuthToken = '';
								break;
			case 'token':		$this->password = '';
								$this->token = $value;
								$this->oAuthToken = '';
								break;
			case 'oauth_token':	$this->password = '';
								$this->token = '';
								$this->oAuthToken = $value;
								break;
		}
	}

	/*
	 * Returns the supplied subdomain
	 */
	public function getSubdomain() {
		return $this->subdomain;
	}

	/*
	 * Returns the generated api URL
	 */
	public function getApiUrl() {
		return $this->apiUrl;
	}

	/*
	 * Returns a text value indicating the type of authorization configured
	 */
	public function getAuthType() {
		return ($this->oAuthToken ? 'oauth_token' : ($this->token ? 'token' : 'password'));
	}

	/*
	 * Compiles an auth string with either token, password or OAuth credentials
	 */
	public function getAuthText() {
		return ($this->oAuthToken ? $this->oAuthToken : $this->username.($this->token ? '/token:'.$this->token : ':'.$this->password));
	}

	/*
	 * Sets the debug flag on or off
	 */
	public function setDebug($debug) {
		$this->debug = $debug;
	}

	/*
	 * Returns true if debugging is enabled
	 */
	public function debugging() {
		return $this->debug;
	}

	/*
	 * Syntactic sugar methods (used for chaining):
	 */
	public function ticket($id) { $this->lastTicket = $id; return $this; }
	public function withSideLoad($fields) { $this->sideLoad = $fields; return $this; } // must be called before helper
	/*
	 * Ticket helpers:
	 */
	public function find($params = array()) { return $this->tickets->find($params); }
	public function update($params = array()) { return $this->tickets->update($params); }
	public function delete($params = array()) { return $this->tickets->delete($params); }
	public function markAsSpam($params = array()) { return $this->tickets->markAsSpam($params); } // I know, not a GETter method, but it makes sense :)
	public function related($params = array()) { return $this->tickets->related($params); }
	public function collaborators($params = array()) { return $this->tickets->collaborators($params); }
	public function incidents($params = array()) { return $this->tickets->incidents($params); }
	public function problems($params = array()) { return $this->tickets->problems($params); }
	public function audits($params = array()) { return $this->tickets->audits->all($params); }
	public function audit($params = array()) { return $this->tickets->audits->find($params); }
	public function markAuditAsTrusted($params = array()) { return $this->tickets->audits->markAsTrusted($params); }
	public function comments($params = array()) { return $this->tickets->comments->all($params); }
	public function makeCommentPrivate($params = array()) { return $this->tickets->comments->makePrivate($params); }

	/*
	 * Technically not a helper function, but it's handy
	 */
	public function attach($params = array()) {
		if((!$params['file']) || (!$params['type']) || (!$params['body'])) {
			$this->client->lastError = 'Missing parameter: \'file\', \'type\' and \'body\' must be supplied for '.__METHOD__;
			return false;
		}
		$upload = $this->attachments->upload($params);
		if((!is_object($upload->upload)) && (!$upload->upload->token)) {
			$this->lastError = 'Response to '.__METHOD__.' is not valid. See $client->lastResponseHeaders for details';
			return false;
		}
		return $this->tickets->update(array('comment' => array('body' => $params['body'], 'uploads' => array($upload->upload->token))));
	}

}

?>
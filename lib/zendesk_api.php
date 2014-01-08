<?php
require_once ("lib/zendesk_api/http.php");
require_once ("lib/zendesk_api/tickets.php");
require_once ("lib/zendesk_api/ticket_audits.php");
require_once ("lib/zendesk_api/ticket_comments.php");
require_once ("lib/zendesk_api/ticket_fields.php");
require_once ("lib/zendesk_api/ticket_forms.php");
require_once ("lib/zendesk_api/ticket_import.php");
require_once ("lib/zendesk_api/ticket_metrics.php");
require_once ("lib/zendesk_api/requests.php");
require_once ("lib/zendesk_api/request_comments.php");
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
	/*
	 * Public objects
	 */
	public $tickets;
	public $attachments;
	public $twitter;
	public $requests;
	/*
	 * Helpers:
	 */
	public $sideload;

	public function __construct($subdomain, $username) {
		$this->subdomain = $subdomain;
		$this->username = $username;
		$this->apiUrl = 'https://'.$subdomain.'.zendesk.com/api/'.$this->apiVer.'/';
		$this->tickets = new Tickets($this);
		$this->attachments = new Attachments($this);
		$this->twitter = new Twitter($this);
		$this->requests = new Requests($this);
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
	 * Enable side-loading (beta) - flags until the next endpoint call
	 */
	public function sideload(array $fields) {
		$this->sideload = $fields;
		return $this;
	}

	/*
	 * Syntactic sugar methods (used for chaining):
	 */
	public function tickets(array $id) { $this->tickets->lastId = $id; return $this->tickets; }
	public function ticket($id) { $this->tickets->lastId = $id; return $this->tickets; }
	public function request($id) { $this->requests->lastId = $id; return $this->requests; }

}

?>
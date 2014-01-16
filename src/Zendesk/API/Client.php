<?php

namespace Zendesk\API;

/*
 * Dead simple autoloader
 */
//spl_autoload_register(function($c){@include 'src/'.preg_replace('#\\\|_(?!.+\\\)#','/',$c).'.php';});

/*
 * Client class, base level access
 */
class Client {

	protected $subdomain;
	protected $username;
	protected $password;
	protected $token;
	protected $oAuthToken;
	protected $apiUrl;
	protected $apiVer = 'v2';
	protected $sideload;

	protected $tickets;
	protected $ticketFields;
	protected $ticketForms;
	protected $ticketImport;
	protected $twitter;
	protected $attachments;
	protected $requests;
	protected $users;
	protected $debug;

	public function __construct($subdomain, $username) {
		$this->subdomain = $subdomain;
		$this->username = $username;
		$this->apiUrl = 'https://'.$subdomain.'.zendesk.com/api/'.$this->apiVer.'/';
		$this->debug = new Debug();
		$this->tickets = new Tickets($this);
		$this->ticketFields = new TicketFields($this);
		$this->ticketForms = new TicketForms($this);
		$this->ticketImport = new TicketImport($this);
		$this->twitter = new Twitter($this);
		$this->attachments = new Attachments($this);
		$this->requests = new Requests($this);
		$this->users = new Users($this);
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
	 * Set debug information as an object
	 */
	public function setDebug($lastRequestHeaders, $lastResponseCode, $lastResponseHeaders) {
		$this->debug->lastRequestHeaders = $lastRequestHeaders;
		$this->debug->lastResponseCode = $lastResponseCode;
		$this->debug->lastResponseHeaders = $lastResponseHeaders;
	}

	/*
	 * Returns debug information in an object
	 */
	public function getDebug() {
		return $this->debug;
	}

	/*
	 * Sideload setter
	 */
	public function setSideload($fields = null) {
		$this->sideload = $fields;
		return $this;
	}

	/*
	 * Sideload getter
	 */
	public function getSideload() {
		return $this->sideload;
	}

	/*
	 * Syntactic sugar getters and setters
	 */
	public function tickets($id = null) {
		if ($id !== null) {
			return $this->tickets->setLastId($id);
		} else {
			return $this->tickets;
		}
	}
	public function ticket($id) {
		return $this->tickets->setLastId($id);
	}

	public function ticketFields() {
		return $this->ticketFields;
	}
	public function ticketField($id) {
		return $this->ticketFields->setLastId($id);
	}
	public function ticketForms() {
		return $this->ticketForms;
	}
	public function ticketForm($id) {
		return $this->ticketForms->setLastId($id);
	}
	public function ticketImport() {
		return $this->ticketImport;
	}
	public function twitter() {
		return $this->twitter;
	}
	public function attachments() {
		return $this->attachments;
	}
	public function requests() {
		return $this->requests;
	}
	public function request($id) {
		return $this->requests->setLastId($id);
	}
	public function users() {
		return $this->users;
	}
	public function user($id) {
		return $this->users->setLastId($id);
	}

}

?>
<?php
require_once ("lib/zendesk_api/http.php");
require_once ("lib/zendesk_api/tickets.php");

class ZendeskAPI {

	private $username;
	private $token;
	private $password;
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

	public function __construct($subdomain, $username) {
		$this->username = $username;
		$this->apiUrl = 'https://'.$subdomain.'.zendesk.com/api/'.$this->apiVer.'/';
		$this->tickets = new Tickets($this);
	}

	/*
	 * Call either setToken OR setPassword, but not both
	 */
	public function setToken($token) {
		$this->token = $token;
	}

	/*
	 * Call either setToken OR setPassword, but not both
	 */
	public function setPassword($password) {
		$this->password = $password;
	}

	/*
	 * Returns the generated api URL
	 */
	public function getApiUrl() {
		return $this->apiUrl;
	}

	/*
	 * Compiles an auth string with either token or password
	 */
	public function getAuthString() {
		return $this->username.($this->token ? '/token:'.$this->token : ':'.$this->password);
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

}

?>
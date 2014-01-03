<?php
require_once ("lib/zendesk_api/http.php");
require_once ("lib/zendesk_api/tickets.php");
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

	public function __construct($subdomain, $username) {
		$this->subdomain = $subdomain;
		$this->username = $username;
		$this->apiUrl = 'https://'.$subdomain.'.zendesk.com/api/'.$this->apiVer.'/';
		$this->tickets = new Tickets($this);
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

}

?>
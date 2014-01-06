<?php

require_once ("lib/zendesk_api.php");

/**
 * Ticket Audits test class
 */
class TicketCommentsTest extends PHPUnit_Framework_TestCase {

	private $client;
	private $subdomain;
	private $username;
	private $password;
	private $token;
	private $oAuthToken;

	public function __construct() {
		$this->subdomain = $GLOBALS['SUBDOMAIN'];
		$this->username = $GLOBALS['USERNAME'];
		$this->password = $GLOBALS['PASSWORD'];
		$this->token = $GLOBALS['TOKEN'];
		$this->oAuthToken = $GLOBALS['OAUTH_TOKEN'];
		$this->client = new ZendeskAPI($this->subdomain, $this->username);
		$this->client->setAuth('token', $this->token);
	}

	public function testCredentials() {
		$this->assertEquals($GLOBALS['SUBDOMAIN'] != '', true, 'Expecting GLOBALS[SUBDOMAIN] parameter; does phpunit.xml exist?');
		$this->assertEquals($GLOBALS['TOKEN'] != '', true, 'Expecting GLOBALS[TOKEN] parameter; does phpunit.xml exist?');
		$this->assertEquals($GLOBALS['USERNAME'] != '', true, 'Expecting GLOBALS[USERNAME] parameter; does phpunit.xml exist?');
	}

	public function testAuthToken() {
		$this->client->setAuth('token', $this->token);
		$tickets = $this->client->tickets->all();
		$this->assertEquals($this->client->lastResponseCode, '200', 'Does not return HTTP code 200');
	}

	/**
	 * @depends testAuthToken
	 */
	public function testAll() {
		$comments = $this->client->ticket(76)->comments(); // Don't delete ticket #76
		$this->assertEquals(is_object($comments), true, 'Should return an object');
		$this->assertEquals(is_array($comments->comments), true, 'Should return an object containing an array called "comments"');
		$this->assertGreaterThan(0, $comments->comments[0]->id, 'Returns a non-numeric id in first audit');
		$this->assertEquals($this->client->lastError, '', 'Throws an error: '.$this->client->lastError);
		$this->assertEquals($this->client->lastResponseCode, '200', 'Does not return HTTP code 200');
	}

	/*
	 * Test make private
	 */
	public function testMakePrivate() {
		$this->markTestSkipped(
			'Skipped for now because it requires a new (unique) comment id for each test'
		);
		$comments = $this->client->ticket(76)->makeCommentPrivate(array('id' => '16303442242'));
		$this->assertEquals($this->client->lastError, '', 'Throws an error: '.$this->client->lastError);
		$this->assertEquals($this->client->lastResponseCode, '200', 'Does not return HTTP code 200');
	}

}

?>
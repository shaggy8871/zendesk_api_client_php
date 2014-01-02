<?php

require_once ("lib/zendesk_api.php");

/**
 * Attachments test class
 */
class AttachmentsTest extends PHPUnit_Framework_TestCase {

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

	public function testAuthToken() {
		$tickets = $this->client->tickets->all();
		$this->assertEquals($this->client->lastResponseCode, '200', 'Does not return HTTP code 200');
	}

	/**
	 * @depends testAuthToken
	 */
	public function testUploadAttachment() {
		$attachment = $this->client->attachments->upload(array(
			'file' => getcwd().'/test/unit/UK.png',
			'type' => 'image/png'
		));
		$this->assertEquals($this->client->lastError, '', 'Throws an error: '.$this->client->lastError);
		$this->assertEquals($this->client->lastResponseCode, '201', 'Does not return HTTP code 201');
		$this->assertEquals(is_object($attachment), true, 'Should return an object');
		$this->assertEquals(is_object($attachment->upload), true, 'Should return an object called "upload"');
		$this->assertEquals(($attachment->upload->token != ''), true, 'Should return a token');
		$this->assertEquals(is_array($attachment->upload->attachments), true, 'Should return an array called "upload->attachments"');
		$this->assertGreaterThan(0, $attachment->upload->attachments[0]->id, 'Returns a non-numeric id for upload->attachments[0]');
		$stack = array($attachment);
		return $stack;
	}

	/**
	 * @depends testUploadAttachment
	 */
	public function testDeleteAttachment(array $stack) {
		$attachment = array_pop($stack);
		$this->assertEquals(($attachment->upload->token != ''), true, 'Cannot find a token to test with. Did testUploadAttachment fail?');
		$confirmed = $this->client->attachments->delete(array(
			'token' => $attachment->upload->token
		));
		$this->assertEquals($this->client->lastResponseCode, '200', 'Does not return HTTP code 200');
	}

}

?>

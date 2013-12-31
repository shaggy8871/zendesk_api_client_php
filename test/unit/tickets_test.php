<?php

require_once ("lib/zendesk_api.php");

/**
 * Tickets test class
 */
class TicketsTest extends PHPUnit_Framework_TestCase {

	private $client;
	private $subdomain;
	private $username;
	private $token;

	public function __construct() {
		$this->subdomain = $GLOBALS['SUBDOMAIN'];
		$this->$username = $GLOBALS['USERNAME'];
		$this->$token = $GLOBALS['TOKEN'];
		$this->client = new ZendeskAPI($this->subdomain, $this->username);
		$this->client->setToken($this->token);
	}

	public function testAll() {
		$tickets = $this->client->tickets->all();
		$this->assertEquals(is_object($tickets), true, 'Should return an object');
		$this->assertEquals(is_array($tickets->tickets), true, 'Should return an object containing an array called "tickets"');
		$this->assertGreaterThan(0, $tickets->tickets[0]->id, 'Returns a non-numeric id in first ticket');
		$this->assertContains($tickets->tickets[0]->priority, array ('low', 'normal', 'high', 'urgent'), 'Returns an invalid priority in first ticket');
		$this->assertEquals($this->client->lastError, '', 'Throws an error: '.$this->client->lastError);
		$this->assertEquals($this->client->lastResponseCode, '200', 'Does not return HTTP code 200');
	}

	public function testFindSingle() {
		$tickets = $this->client->tickets->find(array('id' => 2)); // ticket #2 must never be deleted
		$this->assertEquals(is_object($tickets), true, 'Should return an object');
		$this->assertEquals(is_object($tickets->ticket), true, 'Should return an object called "ticket"');
		$this->assertEquals($this->client->lastError, '', 'Throws an error: '.$this->client->lastError);
		$this->assertEquals($this->client->lastResponseCode, '200', 'Does not return HTTP code 200');
	}

	public function testFindMultiple() {
		$tickets = $this->client->tickets->find(array('id' => array(2, 3)));
		$this->assertEquals(is_object($tickets), true, 'Should return an object');
		$this->assertEquals(is_array($tickets->tickets), true, 'Should return an array called "tickets"');
		$this->assertEquals(is_object($tickets->tickets[0]), true, 'Should return an object as first "tickets" array element');
		$this->assertEquals($this->client->lastError, '', 'Throws an error: '.$this->client->lastError);
		$this->assertEquals($this->client->lastResponseCode, '200', 'Does not return HTTP code 200');
	}

	public function testCreate() {
		$testTicket = array(
			'subject' => 'The quick brown fox jumps over the lazy dog', 
			'comment' => array (
				'body' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'
			), 
			'priority' => 'normal'
		);
		$ticket = $this->client->tickets->create($testTicket);
		$this->assertEquals(is_object($ticket), true, 'Should return an object');
		$this->assertEquals(is_object($ticket->ticket), true, 'Should return an object called "ticket"');
		$this->assertGreaterThan(0, $ticket->ticket->id, 'Returns a non-numeric id for ticket');
		$this->assertEquals($ticket->ticket->subject, $testTicket['subject'], 'Subject of test ticket does not match');
		$this->assertEquals($ticket->ticket->description, $testTicket['comment']['body'], 'Description of test ticket does not match');
		$this->assertEquals($ticket->ticket->priority, $testTicket['priority'], 'Priority of test ticket does not match');
		$this->assertEquals($this->client->lastError, '', 'Throws an error: '.$this->client->lastError);
		$this->assertEquals($this->client->lastResponseCode, '201', 'Does not return HTTP code 201');
		$testTicket['id'] = $ticket->ticket->id;
		$stack = array($testTicket);
		return $stack;
	}

	/**
	 * @depends testCreate
	 */
	public function testUpdate(array $stack) {
		$testTicket = array_pop($stack);
		$this->assertGreaterThan(0, $testTicket['id'], 'Cannot find a ticket id to test with. Did testCreate fail?');
		$testTicket['subject'] = 'Updated subject';
		$testTicket['priority'] = 'urgent';
		$ticket = $this->client->tickets->update($testTicket);
		$this->assertEquals(is_object($ticket->ticket), true, 'Should return an object called "ticket"');
		$this->assertGreaterThan(0, $ticket->ticket->id, 'Returns a non-numeric id for ticket');
		$this->assertEquals($ticket->ticket->subject, $testTicket['subject'], 'Subject of test ticket does not match');
		$this->assertEquals($ticket->ticket->description, $testTicket['comment']['body'], 'Description of test ticket does not match');
		$this->assertEquals($ticket->ticket->priority, $testTicket['priority'], 'Priority of test ticket does not match');
		$this->assertEquals($this->client->lastError, '', 'Throws an error: '.$this->client->lastError);
		$this->assertEquals($this->client->lastResponseCode, '200', 'Does not return HTTP code 200');
		$stack = array($testTicket);
		return $stack;
	}

	/**
	 * @depends testCreate
	 */
	public function testDeleteSingle(array $stack) {
		$testTicket = array_pop($stack);
		$this->assertGreaterThan(0, $testTicket['id'], 'Cannot find a ticket id to test with. Did testCreate fail?');
		$ticket = $this->client->tickets->delete(array('id' => $testTicket['id']));
		$this->assertEquals($this->client->lastError, '', 'Throws an error: '.$this->client->lastError);
		$this->assertEquals($this->client->lastResponseCode, '200', 'Does not return HTTP code 200');
		$stack = array($testTicket);
		return $stack;
	}

	public function testDeleteMultiple() {
		// Assume testCreate works so we can go ahead and create two new tickets
		$testTicket = array(
			'subject' => 'The quick brown fox jumps over the lazy dog', 
			'comment' => array (
				'body' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'
			), 
			'priority' => 'normal'
		);
		$ticket1 = $this->client->tickets->create($testTicket);
		$this->assertEquals(is_object($ticket1), true, 'Ticket1: Should return an object');
		$this->assertEquals(is_object($ticket1->ticket), true, 'Ticket1: Should return an object called "ticket"');
		$this->assertGreaterThan(0, $ticket1->ticket->id, 'Ticket1: Returns a non-numeric id for ticket');
		$ticket2 = $this->client->tickets->create($testTicket);
		$this->assertEquals(is_object($ticket2), true, 'Ticket2: Should return an object');
		$this->assertEquals(is_object($ticket2->ticket), true, 'Ticket2: Should return an object called "ticket"');
		$this->assertGreaterThan(0, $ticket2->ticket->id, 'Ticket2: Returns a non-numeric id for ticket');
		// Test delete
		$this->client->tickets->delete(array('id' => array($ticket1->ticket->id, $ticket2->ticket->id)));
		$this->assertEquals($this->client->lastError, '', 'Throws an error: '.$this->client->lastError);
		$this->assertEquals($this->client->lastResponseCode, '200', 'Does not return HTTP code 200');
	}

}

?>

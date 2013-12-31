<?php
require_once ("lib/zendesk_api.php");

$subdomain = "subdomain";
$username = "username";
$token = "6wiIBWbGkBMo1mRDMuVwkw1EPsNkeUj95PIz2akv"; // replace this with your token
//$password = "123456";

$client = new ZendeskAPI($subdomain, $username);
$client->setToken($token); // set either token or password (not both)
//$client->setPassword($password);

// Get all tickets
$tickets = $client->tickets->all();
print_r ($tickets);

// Create a new ticket
$newTicket = $client->tickets->create(array (
	'subject' => 'The quick brown fox jumps over the lazy dog', 
	'comment' => array (
		'body' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'
	), 
	'priority' => 'normal'
));
print_r ($newTicket);

// Update multiple tickets
$client->tickets->update(array (
	'id' => array (123, 456), 
	'status' => 'urgent'
));

// Delete a ticket
$client->tickets->delete(array(
	'id' => 123
));
?>
<?php
/**
 * HTTP functions via curl
 */
class Http {

	public static function send($client, $endPoint, $json = null, $method = 'GET') {

		$url = $client->getApiUrl().$endPoint;
		$method = strtoupper($method);

		if ($method == 'POST') {
			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, ($json == null ? (object) null : json_encode($json)));
		} else
		if ($method == 'PUT') {
			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
			curl_setopt($curl, CURLOPT_POSTFIELDS, ($json == null ? (object) null : json_encode($json)));
		} else {
			$curl = curl_init($url.($json != null ? '?'.http_build_query(json_encode($json)) : ''));
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, ($method ? $method : 'GET'));
		}
		curl_setopt($curl, CURLOPT_USERPWD, $client->getAuthString());
		curl_setopt($curl, CURLINFO_HEADER_OUT, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($curl, CURLOPT_HEADER, true);
		curl_setopt($curl, CURLOPT_VERBOSE, true);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_MAXREDIRS, 3);
		$response = curl_exec($curl);
		$headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
		$responseBody = substr($response, $headerSize);
		$client->lastRequestHeaders = curl_getinfo($curl, CURLINFO_HEADER_OUT);
		$client->lastResponseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$client->lastResponseHeaders = substr($response, 0, $headerSize);
		curl_close($curl);

		return json_decode($responseBody);

	}

}

?>

<?php
/**
 * HTTP functions via curl
 */
class Http {

	/*
	 * Use the send method to call every endpoint except for oauth/tokens
	 */
	public static function send($client, $endPoint, $json = null, $method = 'GET', $contentType = 'application/json') {

		$url = $client->getApiUrl().$endPoint;
		$method = strtoupper($method);
		$json = ($json == null ? (object) null : ($contentType == 'application/json' ? json_encode($json) : $json));

		if($method == 'POST') {
			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
		} else
		if($method == 'PUT') {
			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
			curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
		} else {
			$curl = curl_init($url.($json != null ? '?'.http_build_query($json) : ''));
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, ($method ? $method : 'GET'));
		}
		if($client->getAuthType() == 'oauth_token') {
			curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: '.$contentType, 'Accept: application/json', 'Authorization: Bearer '.$client->getAuthText()));
		} else {
			curl_setopt($curl, CURLOPT_USERPWD, $client->getAuthText());
			curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: '.$contentType, 'Accept: application/json'));
		}
		curl_setopt($curl, CURLINFO_HEADER_OUT, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
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

	/*
	 * Specific case for OAuth. Run /oauth.php via your browser to get an access token
	 */
	public static function oauth($client, $code, $oAuthId, $oAuthSecret) {

		$url = 'https://'.$client->getSubdomain().'.zendesk.com/oauth/tokens';
		$method = 'POST';

		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(array(
			'grant_type' => 'authorization_code',
			'code' => $code,
			'client_id' => $oAuthId,
			'client_secret' => $oAuthSecret,
			'redirect_uri' => ($_SERVER['HTTPS'] ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'],
			'scope' => 'read'
		)));
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($curl, CURLINFO_HEADER_OUT, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
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

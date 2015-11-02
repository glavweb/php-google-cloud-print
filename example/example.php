<?php

require_once 'vendor/autoload.php';

$printerId      = 'YOUR_PRINTER_ID';
$privateKeyPath = 'YOUR_GOOGLE_PRIVATE_KEY_PATH';
$client_email   = 'YOUR_GOOGLE_SERVICE_ACCOUNT_EMAIL';

if (!$printerId) {
	throw new \Glavweb\GoogleCloudPrint\Exception('Printer ID not defined.');
}

if (!is_file($privateKeyPath)) {
	throw new \Glavweb\GoogleCloudPrint\Exception('Private Key not found.');
}

$privateKey   = file_get_contents($privateKeyPath);
$scopes       = array('https://www.googleapis.com/auth/cloudprint');

$credentials = new \Google_Auth_AssertionCredentials(
	$client_email,
	$scopes,
	$privateKey
);

$client = new \Google_Client();
$client->setAssertionCredentials($credentials);

if ($client->getAuth()->isAccessTokenExpired()) {
	$client->getAuth()->refreshTokenWithAssertion();
}

$authToken = json_decode($client->getAuth()->getAccessToken())->access_token;

if(!$accessToken) {
	throw new \Glavweb\GoogleCloudPrint\Exception('Cannot login to CloudPrint.');
}

$content = 'Any HTML body.';

$gcp = new \Glavweb\GoogleCloudPrint\GoogleCloudPrint($accessToken);
$response = $gcp->submit(array(
	'printerid'   => $printerId,
	'title'       => (string)$order,
	'content'     => $content,
	'contentType' => 'text/html',
	'tag'         => $tag,
	'ticket' => json_encode(array(
		'version' => '1.0',
	))
));

if (!$response->success){
	throw new \Glavweb\GoogleCloudPrint\Exception('An error occured while printing the doc. Error code:' . $response->errorCode . ', Message:' . $response->message);
}

return $response->job->id;

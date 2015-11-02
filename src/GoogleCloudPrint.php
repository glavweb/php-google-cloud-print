<?php

namespace Glavweb\GoogleCloudPrint;

/**
 * Class GoogleCloudPrint
 * @package Glavweb\GoogleCloudPrint
 */
class GoogleCloudPrint
{
	/**
	 * Constants of URLs
	 */
	const URL_LOGIN      = 'https://www.google.com/accounts/ClientLogin';
	const URL_SEARCH     = 'https://www.google.com/cloudprint/search';
	const URL_SUBMIT     = 'https://www.google.com/cloudprint/submit';
	const URL_DELETE_JOB = 'https://www.google.com/cloudprint/deletejob';
	const URL_JOBS       = 'https://www.google.com/cloudprint/jobs';
	const URL_PRINTER    = 'https://www.google.com/cloudprint/printer';

	/**
	 * @var string
	 */
	private $accessToken;

	/**
	 * CloudPrint constructor.
	 * @param string $accessToken
	 */
	public function __construct($accessToken)
	{
		$this->accessToken = $accessToken;
	}

	/**
	 * @return mixed
	 */
	public function getAccessToken()
	{
		return $this->accessToken;
	}

	/**
	 * @param mixed $accessToken
	 */
	public function setAccessToken($accessToken)
	{
		$this->accessToken = $accessToken;
	}

	/**
	 * Returns a list of printers accessible to the authenticated user, filtered by various search options.
	 *
	 * @param array  $postFields Array of post fields to be posted
	 * @param array  $headers    Array of http headers
	 * @return mixed
	 */
	public function search($postFields = array(), $headers = array())
	{
		$headers = array_merge($headers, array(
			"GData-Version: 3.0",
		));

		$response = $this->makeHttpCall(self::URL_SEARCH, $postFields, $headers);
		return json_decode($response);
	}

	/**
	 * Sends document to the printer
	 *
	 * @param array  $postFields Array of post fields to be posted
	 * @param array  $headers    Array of http headers
	 * @return mixed
	 * @throws Exception
	 */
	public function submit($postFields = array(), $headers = array())
	{
		if (empty($postFields['printerid'])) {
			throw new Exception('Please provide printer ID.');
		}

		if (empty($postFields['title'])) {
			throw new Exception('Please provide title.');
		}

		$response = $this->makeHttpCall(self::URL_SUBMIT, $postFields, $headers);
		return json_decode($response);
	}

	/**
	 * Deletes the given print job.
	 *
	 * @param string $jobId      The ID of the print job to be deleted.
	 * @param array  $postFields Array of post fields to be posted
	 * @param array  $headers    Array of http headers
	 * @return mixed
	 */
	public function deleteJob($jobId, $postFields = array(), $headers = array())
	{
		$postFields = array_merge($postFields, array(
			'jobid' => $jobId,
		));

		$response = $this->makeHttpCall(self::URL_DELETE_JOB, $postFields, $headers);
		return json_decode($response);
	}

	/**
	 * Returns a list of print jobs which the authenticated user has permission to view, filtered by various search options.
	 *
	 * @param array  $postFields Array of post fields to be posted
	 * @param array  $headers    Array of http headers
	 * @return mixed
	 */
	public function jobs($postFields = array(), $headers = array())
	{
		$response = $this->makeHttpCall(self::URL_JOBS, $postFields, $headers);
		return json_decode($response);
	}

	/**
	 * @param array $postFields
	 * @param array $headers
	 * @return mixed
	 */
	public function printer($postFields = array(), $headers = array())
	{
		$response = $this->makeHttpCall(self::URL_PRINTER, $postFields, $headers);
		return json_decode($response);
	}

	/**
	 * Makes http calls to Google Cloud Print using curl
	 *
	 * @param string $url        Http url to hit
	 * @param array  $postFields Array of post fields to be posted
	 * @param array  $headers    Array of http headers
	 * @return mixed
	 */
	private function makeHttpCall($url, $postFields = array(), $headers = array())
	{
		$headers = array_merge($headers, array(
			"Authorization: Bearer " . $this->getAccessToken()
		));

		$curl = curl_init($url);

		if (!empty($postFields)) {
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $postFields);
		}

		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		// Execute the curl and return response
		$response = curl_exec($curl);
		curl_close($curl);

		return $response;
	}
}

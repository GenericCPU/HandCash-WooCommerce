<?php

// Define the HandCash Pay API class.
require_once('wp-content/plugins/woocommerce/woocommerce.php');

class HandCashPayApi {
  // Set the default API URL and authentication credentials.
  protected $apiUrl = 'https://cloud.handcash.io/v2';
  protected $appId = '';
  protected $appSecret = '';

  // Initialize the API class and set the settings.
  public function __construct($settings) {
    $this->settings = $settings;
  }

  // Authenticate using the app ID and app secret.
  public function authenticate($appId, $appSecret) {
    // Set the appId and appSecret for authentication.
    $this->appId = $appId;
    $this->appSecret = $appSecret;
  }

  // Create a payment request using the HandCash Pay API.
  public function createPaymentRequest($data) {
    // Build the API URL and request body.
    $url = $this->apiUrl . '/paymentRequests';
    $body = json_encode($data);

    // Set the request headers.
    $headers = [
      'Accept: application/json',
      'App-ID: ' . $this->appId,
      'App-Secret: ' . $this->appSecret,
      'Content-Type: application/json',
    ];

    // Initialize cURL and set the options.
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Make the API request and retrieve the response.
    $response = curl_exec($ch);
    curl_close($ch);

    // Return the API response as an array.
    return json_decode($response, true);
  }
}
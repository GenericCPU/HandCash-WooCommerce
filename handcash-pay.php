<?php
/*
Plugin Name: HandCash Pay for WooCommerce
Version: 1.0.0
Author: Your Name
Description: A WooCommerce plugin that integrates with HandCash Pay.
*/
require_once('wp-content/plugins/woocommerce/woocommerce.php');

// Include the HandCash Pay API class.
require_once 'handcash-pay-api.php';

// Define the HandCash Pay gateway for WooCommerce.
add_action('plugins_loaded', 'init_handcash_pay_gateway_class');
function init_handcash_pay_gateway_class()
{
  class WC_Gateway_HandCash_Pay extends WC_Payment_Gateway
  {
    // Initialize the payment gateway and set the API credentials.
    public function __construct()
    {
      $this->id = 'handcash_pay';
      $this->method_title = 'HandCash Pay';
      $this->method_description = 'Pay with HandCash.';
      $this->has_fields = true;
      $this->init_form_fields();
      $this->init_settings();
      $this->title = $this->get_option('title');
      $this->description = $this->get_option('description');
      $this->api = new HandCashPayApi($this->settings);
      $this->api->authenticate('', '');
      $this->supports = [
        'products',
        'refunds',
      ];
      add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
    }

    // Initialize the plugin settings form fields.
    public function init_form_fields()
    {
      $this->form_fields = array(
        'title' => array(
          'title' => 'Title',
          'type' => 'text',
          'default' => 'HandCash Pay',
        ),
        'description' => array(
          'title' => 'Description',
          'type' => 'textarea',
          'default' => 'Pay with HandCash.',
        ),
      );
    }

    // Process the payment and return the result.
    public function process_payment($order_id)
    {
      // Get the order details.
      $order = wc_get_order($order_id);

      // Create the payment request using the HandCash Pay API.
      $payment_request = $this->api->createPaymentRequest([
        'product' => [
          'name' => $order->get_name(),
          'description' => $order->get_description(),
          'imageUrl' => $order->get_image_url(),
        ],
        'receivers' => [
          [
            'sendAmount' => $order->get_total(),
            'currencyCode' => $order->get_currency(),
            'destination' => 'brandonc',
          ],
        ],
        'requestedUserData' => ['paymail', 'phoneNumber'],
        'notifications' => [
          'webhook' => [
            'customParameters' => ['newKey' => 'New Value'],
            'webhookUrl' => 'https://tobedefined.com',
          ],
          'email' => 'brandonc@handcash.io',
        ],
        'expirationType' => 'onPaymentCompleted',
        'redirectUrl' => $this->get_return_url($order),
      ]);

      // Return the payment request URL and redirect the customer.
      return [
        'result' => 'success',
        'redirect' => $payment_request['paymentRequestUrl'],
      ];
    }
  }
}

// Add the HandCash Pay gateway to WooCommerce.
add_filter('woocommerce_payment_gateways', 'add_handcash_pay_gateway_class');
function add_handcash_pay_gateway_class($methods)
{
  $methods[] = 'WC_Gateway_HandCash_Pay';
  return $methods;
}
?>
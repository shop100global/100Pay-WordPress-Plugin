<?php
/*
Plugin Name: 100Pay Plugin for WooCommerce

Plugin URI: https://100pay.co/ 

Description: We power the payment/crypto banking infrastructure for developers/businesses to accept/process crypto payments at any scale.

Version: 0.2 

Author: Chika Precious Benjamin 

Author URI: https://100pay.co/

License: GPLv2

Text Domain: 100pay

*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WC_100PAY_MAIN_FILE', __FILE__ );
define( 'WC_100PAY_URL', untrailingslashit( plugins_url( '/', __FILE__ ) ) );

define( 'WC_100PAY_VERSION', '0.2' );

// Ensure WooCommerce is active
if (! in_array('woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option('active_plugins') ) ) ) return;


// Register the custom payment gateway
add_filter('woocommerce_payment_gateways', 'add_100pay_payment_gateway');
function add_100pay_payment_gateway($gateways) {
    $gateways[] = 'WC_100Pay_Payment_Gateway';
    return $gateways;
}


// Include our custom payment gateway class
add_action('plugins_loaded', 'init_100pay_payment_gateway', 11);
function init_100pay_payment_gateway() {

    if ( class_exists('WC_Payment_Gateway') ) {
        class WC_100Pay_Payment_Gateway extends WC_Payment_Gateway {
            
            // Constructor
            public function __construct() {
                $this->id = '100pay';
                $this->method_title = '100Pay Payment Gateway';
                $this->title = __('Pay with 100Pay Gateway', '100pay');
                $this->icon = apply_filters( '100pay_icon', plugins_url( '/assets/images/100pay.svg', __FILE__ ) );
                $this->has_fields = false;
                $this->enabled = $this->get_option('enabled');
                $this->method_description = __('Pay 100Pay Crypto Gateway', '100pay');

                $this->init_form_fields();
                $this->init_settings();

                add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
            }
            
            // Initialize form fields
            public function init_form_fields() {
                $this->form_fields = array(
                    'enabled' => array(
                        'title' => __('Enable/Disable', '100pay'),
                        'type' => 'checkbox',
                        'label' => __('Enable 100Pay Payment Gateway', '100pay'),
                        'default' => 'yes',
                    ),
                    'business_name' => array(
                        'title' => __('Business Name', '100pay'),
                        'type' => 'text',
                        'description' => __('Enter the Business Name on your 100Pay Account', '100pay'),
                        'default' => __('Enter Business Name', '100pay'),
                    ),
                    'description' => array(
                        'title' => __('Description', '100pay'),
                        'type' => 'text',
                        'description' => __('Pay for your Product using 100Pay Payment Gateway', '100pay'),
                        'default' => __('Pay securely with 100Pay Gateway.', '100pay'),
                    ),
                    'secret_key' => array(
                        'title' => __('Secret Key', '100pay'),
                        'type' => 'text',
                        'description' => __('Enter your 100Pay Private Key', '100pay'),
                        'default' => __('Replay with your 100Pay Secret Key', '100pay'),
                    ),
                    'public_key' => array(
                        'title' => __('Public Key', '100pay'),
                        'type' => 'text',
                        'description' => __('Enter your 100Pay Public Key', '100pay'),
                        'default' => __('Replay with your 100Pay Public Key', '100pay'),
                    ),
                    'currency' => array(
                        'title' => __('Currency', '100pay'),
                        'type' => 'select',
                        'options' => array(
                            'USD' => __('USD', '100pay'),
                            'EUR' => __('EUR', '100pay'),
                            'GBP' => __('GBP', '100pay'),
                            'NGN' => __('NGN', '100pay'),
                        ),
                        'description' => __('Select the currency for your 100Pay transactions', '100pay'),
                        'default' => __('USD', '100pay'),
                    ),
                    'vat' => array(
                        'title' => __('VAT', '100pay'),
                        'type' => 'text',
                        'description' => __('Enter VAT Amount for your 100Pay transactions', '100pay'),
                        'default' => __('0', '100pay'),
                    ),
                );
            }
            
            // Render payment fields on checkout page
            public function payment_fields() {
                echo wpautop($this->description);
            }
            
            // Process payment
            public function process_payment($order_id) {
                $order = wc_get_order($order_id);
                $cart_items = $order->get_items();

                $business_name = strtoupper(get_option('business_name'));
                $currency = get_option( 'currency' );
                $vat = (float) get_option( 'vat' );


                $ref_id = "INVOICE" . $business_name . rand(1, 1000000000);

                foreach ( $cart_items as $cart_item ) {
                    // Get Product ID, name, quantity, price

                    $first_name = $cart_item->get_first_name();
                    $last_name = $cart_item->get_last_name();
                    $email = $cart_item->get_email();
                    $amount = $cart_item->get_amount();
                    $phone = $cart_item->get_phone();
                    
                }; 

                $data = json_encode(array(
                    'ref_id' => $ref_id,
                    'api_key' => $currency,
                    'customer' => array(
                        'name' => $first_name . " " . $last_name,
                        'phone' => $phone,
                        'email' => $email
                    ),
                    'billing' => array(
                        'amount' => (float) $amount,
                        'currency' => $currency,
                        'description' => "",
                        'country' => "USA",
                        'vat' => (float) $vat * (float) $amount, 
                        'pricing_type' => "fixed_price" // or partial
                    ),
                    'metadata' => array(
                        'is_approved' => "yes",
                    ),
                    'call_back_url' => "http://localhost:8000/verifyorder/"
                ));

                echo $data;
                

                
                
                // Call your URL here using cURL or WordPress HTTP API
                $response = wp_remote_post('https://100pay.co/payment-processor', array(
                    'body' => json_encode(array(
                        'order_id' => $order_id,
                        'amount' => $order->get_total(),
                    )),
                    'headers' => array(
                        'Content-Type' => 'application/json',
                    ),
                ));
                if (is_wp_error($response)) {
                    $error_message = $response->get_error_message();
                    wc_add_notice(__('Payment error: ', '100pay') . $error_message, 'error');
                    return;
                }

                
                // Process the response and return appropriate result
                return array(
                    'result' => 'success',
                    'redirect' => $this->get_return_url($order),
                );
            }
        }
    }
}


add_action( 'woocommerce_blocks_loaded', 'load_100pay_gateway_block_support' );
function load_100pay_gateway_block_support() {

    require_once __DIR__ . '/includes/class-wc-100pay-gateway-blocks-support.php';

    add_action(
		'woocommerce_blocks_payment_method_type_registration',
		function( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
			$payment_method_registry->register( new WC_100Pay_Gateway_Blocks_Support );
		}
	);
}

add_action( 'before_woocommerce_init', 'check_100pay_blocks_compatibility' );

function check_100pay_blocks_compatibility() {

    if( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
				'cart_checkout_blocks',
				__FILE__,
				true // you can set it to false, if you want it to display to the user that the plugin is not compatible
			);
    }
		
}

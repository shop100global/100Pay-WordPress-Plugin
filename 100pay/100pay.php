<?php
/*
Plugin Name: 100Pay Checkout

Plugin URI: https://100pay.co/checkout 

Description: Accept crypto, bank transfer & card payments woocommerce store from 150+ countries on your straight to your bank account instantly not next day.

Version: 1.0

Author: 100Pay

Author URI: https://100pay.co/

License: GPLv2 

Text Domain: 100pay-checkout

*/

defined('ABSPATH') || exit;

/**
 * Check if WooCommerce is active
 * 
 * @return bool True if WooCommerce is active, false otherwise
 */
function pay100_check_woocommerce_active() {
    return class_exists('WooCommerce');
}

/**
 * Display admin notice when WooCommerce is not active
 */
function pay100_woocommerce_missing_notice() {
    ?>
    <div class="notice notice-error is-dismissible">
        <p>
            <strong>100Pay Checkout</strong> requires WooCommerce to be installed and activated. 
            <a href="<?php echo esc_url( admin_url( 'plugin-install.php?s=woocommerce&tab=search&type=term' ) ); ?>">
                Install WooCommerce
            </a>
        </p>
    </div>
    <?php
}

// Hook admin notice if WooCommerce is not active
if ( ! pay100_check_woocommerce_active() ) {
    add_action( 'admin_notices', 'pay100_woocommerce_missing_notice' );
}

register_activation_hook( __FILE__, 'pay100_plugin_activation' );

function pay100_plugin_activation() {
    // Keep existing WC() check - only create table if WooCommerce is active
    if ( ! function_exists( 'WC' ) ) {
        return;
    }

    // Create Table with error handling
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'pay100_transactions';

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        reference_id varchar(255) NOT NULL,
        customer_id varchar(255) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    // Ensure proper use of WordPress-defined ABSPATH
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    
    // Execute table creation with error handling
    $result = dbDelta( $sql );
    
    // Log any database errors
    if ( ! empty( $wpdb->last_error ) ) {
        error_log( '100Pay Plugin Activation Error: ' . $wpdb->last_error );
    }
    
    // Flush rewrite rules to register REST API endpoints
    flush_rewrite_rules();
    
    error_log( '100Pay: Plugin activated, rewrite rules flushed' );

    return true;
}

register_uninstall_hook(__FILE__, 'pay100_uninstall');

function pay100_uninstall() {


    // Delete Transaction Data
    global $wpdb;
    $table_name = $wpdb->prefix .'pay100_transactions';
    $wpdb->query("DROP TABLE IF EXISTS $table_name");

    // Delete Option

    delete_option( 'secret_key' );
    delete_option( 'business_name' );
    delete_option( 'public_key' );
    delete_option( 'secret_key' );
    delete_option( 'pay_verification_token' );
    delete_option( 'webhook_url' );
    delete_option( 'enabled' );


    // Delete Plugin Settings
    delete_option('woocommerce_pay100_settings');
}

register_deactivation_hook(__FILE__, 'pay100_deactivate');

// Deactivation Function
function pay100_deactivate() {
    update_option('enabled', 'no');
}

// Uninstall Function

function add_100Pay_gateway_class( $gateways ) {
    $gateways[] = 'WC_100Pay_Gateway';
    return $gateways;
}

add_action( 'plugins_loaded', 'init_100Pay_gateway_class');
function init_100Pay_gateway_class() {
    
    // Check if WooCommerce is active before defining gateway class
    if ( ! pay100_check_woocommerce_active() ) {
        return;
    }

    // Ensure WC_Payment_Gateway class is available before extending
    if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
        return;
    }
    
    // Register gateway with WooCommerce
    add_filter( 'woocommerce_payment_gateways', 'add_100Pay_gateway_class' );

    class WC_100Pay_Gateway extends WC_Payment_Gateway {

        public function __construct() {

            $this->id = 'pay100';
            $this->icon = dirname(__FILE__) . '/image/100pay.svg';
            $this->has_fields = false;
            $this->method_title = '100Pay Checkout';
            $this->method_description = 'Accept crypto, bank transfer & card payments from 150+ countries straight to your bank account instantly not next day.';
            $this->method_icon = dirname(__FILE__) . '/image/100pay.svg';
            $this->method_version = '1.0';
            $this->supports = array(
                'products',
                'subscriptions'
            );

            $this->init_form_fields();
            $this->init_settings();

            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');
            $this->enabled = $this->get_option('enabled');
            $this->webhook_url = $this->get_option('webhook_url');
            $this->verification_token = $this->get_option('pay_verification_token');

            // This action hook saves the settings
            add_filter( 'wc_order_statuses', array( $this, 'add_additional_custom_statuses_to_order_statuses' ) );
            add_action( 'woocommerce_register_shop_order_post_statuses', array( $this, 'pay100_register_product_status') );
            add_action( 'woocommerce_api_100pay_webhook', array( $this, 'pay100_webhook_verification' ) );
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
            add_action( 'woocommerce_thankyou', array( $this, 'payment_modal_trigger' ) );
        }

        public function init_form_fields() {
            $this->form_fields = array(
                'setup_guide' => array(
                    'title'       => '',
                    'type'        => 'title',
                    'description' => '
                        <div class="pay100-setup-header" style="padding: 40px; border-radius: 12px; margin: 20px 0; box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);">
                            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 30px;">
                                <div style="flex: 1; min-width: 300px;">
                                 
                                
                                    <a href="https://100pay.co/blog/how-to-accept-crypto-payments-on-your-wordpress-woocommerce-store-with-100-pay-checkout" target="_blank" style="display: inline-block; background: white; color: black; padding: 12px 30px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); transition: transform 0.2s;">
                                        📖 Read Setup Guide
                                    </a>
                                </div>
                                <div style="flex: 0 0 auto;">
                                    <img src="https://res.cloudinary.com/estaterally/image/upload/v1762649945/Screenshot_2025-11-09_at_01.57.35_gvbcwx.png" alt="100Pay Checkout Preview" style="max-width: 400px; width: 100%; border-radius: 8px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); border: 3px solid rgba(255,255,255,0.2);">
                                </div>
                            </div>
                        </div>
                        
                      
                        
                  
                    ',
                ),
                'enabled' => array(
                    'title'       => 'Enable/Disable',
                    'label'       => 'Enable 100Pay Checkout',
                    'type'        => 'checkbox',
                    'description' => '',
                    'default'     => 'yes'
                ),
                'title' => array(
                    'title'       => 'Payment Method Name',
                    'type'        => 'text',
                    'description' => 'give this payment method a name',
                    'default'     => 'Pay with Crypto/Card',
                    'desc_tip'    => true,
                ),
                'description' => array(
                    'title'       => 'Description',
                    'type'        => 'textarea',
                    'description' => 'give your payment method a description.',
                    'default'     => 'Pay with 22+ crypto and stable coins, bank transfer, debit/credit card',
                ),
                'business_name' => array(
                    'title'       => '100Pay Business Name',
                    'type'        => 'text',
                    'description' => 'Enter your business name from your 100Pay dashboard',
                    'default'     => '',
                    'desc_tip'    => true,
                ),
                'public_key' => array(
                    'title'       => 'Public Key',
                    'type'        => 'textarea',
                    'description' => 'Get this from your 100Pay dashboard under Settings → API Keys',
                    'default'     => '',
                ),
                'secret_key' => array(
                    'title'       => 'Secret Key (API Key)',
                    'type'        => 'password',
                    'description' => 'Get this from your 100Pay dashboard under Settings → API Keys. Keep this secure!',
                    'default'     => '',
                ),
                'pay_verification_token' => array(
                    'title'       => 'Verification Token',
                    'type'        => 'password',
                    'description' => 'Get this from your 100Pay dashboard under Settings → Webhooks. Used to verify webhook requests.',
                    'default'     => '',
                ),
                'webhook_url' => array(
                    'title'       => 'Webhook URL',
                    'type'        => 'textarea',
                    'description' => 'Please copy this webhook URL and paste on the webhook section on your 100Pay dashboard',
                    'default'     => $this->generate_webhook_url(),
                    'custom_attributes' => array(
                        'readonly' => 'readonly',
                    ),
                ),
                'modal_width' => array(
                    'title'       => 'Payment Modal Width (optional)',
                    'type'        => 'text',
                    'description' => 'Set the maximum width of the payment modal (e.g., 400px, 500px, 600px). Leave empty for default.',
                    'default'     => '500px',
                    'desc_tip'    => true,
                    'placeholder' => '500px',
                ),
            );
        }

        public function generate_webhook_url() {
            // Use WooCommerce's built-in wc-api endpoint which works automatically
            // No need for custom REST routes or permalink flushing
            return add_query_arg( 'wc-api', '100pay_webhook', home_url( '/' ) );
        }

        public function process_payment( $order_id ) {
            $order = wc_get_order( $order_id );
            
            // Keep order as pending payment until 100Pay confirms
            $order->update_status( 'pending', '100Pay payment pending - awaiting customer payment.' );
            
            // Add order note with timestamp
            $order->add_order_note( 
                sprintf(
                    '100Pay payment initiated at %s. Awaiting payment confirmation. Order will remain PENDING until webhook confirms payment.',
                    current_time('mysql')
                )
            );
            
            // Log for debugging
            error_log( sprintf( 
                '100Pay: Order #%d created with status PENDING. Redirecting to payment page.', 
                $order_id 
            ) );

            $pay_redirect_url = add_query_arg( 'wp_pg', '100Pay', $this->get_return_url( $order ) );

            return array(
                'result' => 'success',
                'redirect' => $pay_redirect_url,
            );
        }

        public function display_pay_button( $order ) {
            $order_key = $order->get_order_key();
            $pay_url = add_query_arg( 
                array(
                    'wp_pg' => '100Pay',
                    'key' => $order_key
                ),
                $this->get_return_url( $order )
            );
            ?>
            <style>
                .pay100-pending-notice {
                    background: #fff3cd;
                    border: 1px solid #ffc107;
                    border-radius: 5px;
                    padding: 20px;
                    margin: 20px 0;
                    text-align: center;
                }
                .pay100-pending-notice h3 {
                    color: #856404;
                    margin-top: 0;
                }
                .pay100-pending-notice p {
                    color: #856404;
                    margin: 10px 0;
                }
                .pay100-pay-button {
                    display: inline-block;
                    background: #4caf50;
                    color: white;
                    padding: 12px 30px;
                    text-decoration: none;
                    border-radius: 5px;
                    font-size: 16px;
                    font-weight: bold;
                    margin-top: 10px;
                    transition: background 0.3s;
                }
                .pay100-pay-button:hover {
                    background: #45a049;
                    color: white;
                }
            </style>
            
            <div class="pay100-pending-notice">
                <h3>⏳ Payment Pending</h3>
                <p>Your order is awaiting payment. Click the button below to complete your payment.</p>
                <a href="<?php echo esc_url( $pay_url ); ?>" class="pay100-pay-button">
                    💳 Pay Now with 100Pay
                </a>
                <p style="font-size: 14px; margin-top: 15px;">
                    <strong>Order #<?php echo $order->get_order_number(); ?></strong> | 
                    Total: <strong><?php echo $order->get_formatted_order_total(); ?></strong>
                </p>
            </div>
            <?php
        }

        public function payment_modal_trigger() {

            if ( ! is_wc_endpoint_url( 'order-received' ) ) {
                return;
            }
            
            // Get order from URL parameter
            $order_id = isset( $_GET['key'] ) ? wc_get_order_id_by_order_key( $_GET['key'] ) : 0;
            if ( ! $order_id ) {
                return;
            }
            
            $order = wc_get_order( $order_id );
            if ( ! $order ) {
                return;
            }
            
            // Check if order is pending and uses 100Pay gateway
            if ( $order->get_payment_method() === 'pay100' && $order->has_status( 'pending' ) ) {
                // Show pay button for pending orders
                $this->display_pay_button( $order );
            }

            if ( isset( $_GET['wp_pg'] ) ) {
                $order_id = wc_get_order_id_by_order_key( $_GET['key'] );
                $order_key = $_GET['key'];
                $order = wc_get_order( $order_id );
                $order_data = $order->get_data();
                $siteurl = get_option('siteurl');
                    
                $api_key = $this->get_option( 'secret_key' );
                $business_name = $this->get_option( 'business_name' );
                $modal_width = $this->get_option( 'modal_width', '400px' );
                
                // Generate Order Redirect URL
                $redirect_url = $this->get_return_url( $order );
                
                // Generate unique reference ID
                $reference_id = strtoupper($business_name) . '-' . 
                               strtoupper($order_data['billing']['first_name']) . 
                               strtoupper($order_data['billing']['last_name']) . '-' . 
                               bin2hex(random_bytes(4));
                
                // Prepare order data for JavaScript
                $order_json = json_encode(array(
                    'id' => $order_id,
                    'key' => $order_key,
                    'total' => $order_data['total'],
                    'currency' => $order_data['currency'],
                    'billing' => array(
                        'first_name' => $order_data['billing']['first_name'],
                        'last_name' => $order_data['billing']['last_name'],
                        'email' => $order_data['billing']['email'],
                        'phone' => $order_data['billing']['phone'],
                        'country' => $order_data['billing']['country'],
                    )
                ));
                ?>
                
                <!-- Load 100Pay Checkout Library -->
                <script src="https://js.100pay.co"></script>
                
                <style>
                    #pay100-payment-status {
                        position: fixed;
                        top: 20px;
                        right: 20px;
                        padding: 15px 20px;
                        border-radius: 5px;
                        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                        z-index: 999999;
                        display: none;
                        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                    }
                    #pay100-payment-status.success {
                        background-color: #4caf50;
                        color: white;
                    }
                    #pay100-payment-status.error {
                        background-color: #f44336;
                        color: white;
                    }
                    #pay100-payment-status.info {
                        background-color: #2196F3;
                        color: white;
                    }
                    #pay100-payment-status.processing {
                        background-color: #ff9800;
                        color: white;
                    }
                </style>
                
                <!-- Required container for 100Pay modal -->
                <div id="show100Pay"></div>
                
                <!-- Notification container -->
                <div id="pay100-payment-status"></div>
                
                <script>
                (function() {
                    'use strict';
                    
                    const orderData = <?php echo $order_json; ?>;
                    const apiKey = "<?php echo esc_js($api_key); ?>";
                    const businessName = "<?php echo esc_js($business_name); ?>";
                    const referenceId = "<?php echo esc_js($reference_id); ?>";
                    const redirectUrl = "<?php echo esc_js($redirect_url); ?>";
                    const siteUrl = "<?php echo esc_js($siteurl); ?>";
                    const modalWidth = "<?php echo esc_js($modal_width); ?>";
                    
                    let paymentCompleted = false;
                    
                    // Prevent page navigation until payment is complete
                    window.addEventListener('beforeunload', function(e) {
                        if (!paymentCompleted) {
                            e.preventDefault();
                            e.returnValue = 'Payment is in progress. Are you sure you want to leave?';
                            return e.returnValue;
                        }
                    });
                    
                    // Notification helper
                    function showNotification(message, type = 'info', duration = 5000) {
                        const notification = document.getElementById('pay100-payment-status');
                        notification.textContent = message;
                        notification.className = type;
                        notification.style.display = 'block';
                        
                        if (duration > 0) {
                            setTimeout(() => {
                                notification.style.display = 'none';
                            }, duration);
                        }
                    }
                    
                    // Initialize 100Pay Checkout
                    function initiate100PayCheckout() {
                        try {
                            showNotification('Initializing payment...', 'info', 3000);
                            
                            // Check if shop100Pay library is loaded
                            if (typeof shop100Pay === 'undefined') {
                                console.error('100Pay Error: shop100Pay object not found');
                                console.log('Available global objects:', Object.keys(window));
                                throw new Error('100Pay library not loaded. The shop100Pay object is not available.');
                            }
                            
                            // Check if setup method exists
                            if (typeof shop100Pay.setup !== 'function') {
                                console.error('100Pay Error: shop100Pay.setup is not a function');
                                console.log('shop100Pay object:', shop100Pay);
                                throw new Error('100Pay library loaded but setup method not found.');
                            }
                            
                            console.log('100Pay: Library loaded successfully, initializing payment...');
                            
                            // Setup payment with order details
                            shop100Pay.setup({
                                ref_id: referenceId,
                                api_key: apiKey,
                                customer: {
                                    user_id: orderData.billing.email,
                                    name: orderData.billing.first_name + ' ' + orderData.billing.last_name,
                                    phone: orderData.billing.phone || '',
                                    email: orderData.billing.email
                                },
                                billing: {
                                    amount: parseFloat(orderData.total),
                                    currency: orderData.currency,
                                    description: 'Order #' + orderData.id + ' - ' + businessName,
                                    country: orderData.billing.country || 'US',
                                    vat: 0,
                                    pricing_type: 'fixed_price'
                                },
                                metadata: {
                                    order_id: orderData.key,
                                    order_number: orderData.id,
                                    source: 'woocommerce'
                                },
                                call_back_url: redirectUrl,
                                onClose: function(msg) {
                                    console.log('Payment modal closed:', msg);
                                    paymentCompleted = true;
                                    showNotification('Payment cancelled. Redirecting...', 'info', 3000);
                                    
                                    // Redirect back after a delay
                                    setTimeout(() => {
                                        window.location.href = redirectUrl;
                                    }, 3000);
                                },
                                onPayment: function(reference) {
                                    console.log('Payment successful with reference:', reference);
                                    paymentCompleted = true;
                                    showNotification('✓ Payment successful! Verifying...', 'success', 0);
                                    
                                    // Wait for webhook to process
                                    setTimeout(() => {
                                        showNotification('✓ Payment verified! Redirecting...', 'success', 2000);
                                        setTimeout(() => {
                                            window.location.href = redirectUrl;
                                        }, 2000);
                                    }, 2000);
                                },
                                onError: function(error) {
                                    console.error('Payment error:', error);
                                    paymentCompleted = true;
                                    showNotification('✗ Payment failed: ' + (error.message || 'Unknown error'), 'error', 0);
                                    
                                    // Allow user to retry
                                    setTimeout(() => {
                                        if (confirm('Payment failed. Would you like to try again?')) {
                                            paymentCompleted = false;
                                            initiate100PayCheckout();
                                        } else {
                                            window.location.href = redirectUrl;
                                        }
                                    }, 3000);
                                }
                            }, {
                                maxWidth: modalWidth
                            });
                            
                            showNotification('Payment modal ready. Complete your payment to continue.', 'info', 5000);
                            
                        } catch (error) {
                            console.error('Failed to initialize 100Pay:', error);
                            paymentCompleted = true;
                            showNotification('✗ Failed to initialize payment: ' + error.message, 'error', 0);
                            
                            setTimeout(() => {
                                if (confirm('Failed to load payment system. Would you like to try again?')) {
                                    paymentCompleted = false;
                                    location.reload();
                                } else {
                                    window.location.href = redirectUrl;
                                }
                            }, 3000);
                        }
                    }
                    
                    // Wait for 100Pay library to load
                    function waitFor100PayLibrary(callback, maxAttempts = 20) {
                        let attempts = 0;
                        const checkInterval = setInterval(() => {
                            attempts++;
                            console.log('100Pay: Checking if library is loaded... Attempt', attempts);
                            
                            if (typeof shop100Pay !== 'undefined' && typeof shop100Pay.setup === 'function') {
                                console.log('100Pay: Library loaded successfully!');
                                clearInterval(checkInterval);
                                callback();
                            } else if (attempts >= maxAttempts) {
                                console.error('100Pay: Library failed to load after', attempts, 'attempts');
                                clearInterval(checkInterval);
                                paymentCompleted = true;
                                showNotification('✗ Failed to load 100Pay library. Please refresh the page.', 'error', 0);
                                setTimeout(() => {
                                    if (confirm('Failed to load payment system. Would you like to refresh the page?')) {
                                        location.reload();
                                    } else {
                                        window.location.href = redirectUrl;
                                    }
                                }, 2000);
                            }
                        }, 250); // Check every 250ms
                    }
                    
                    // Start payment when page loads
                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', () => {
                            waitFor100PayLibrary(initiate100PayCheckout);
                        });
                    } else {
                        waitFor100PayLibrary(initiate100PayCheckout);
                    }
                    
                })();
                </script>
                
                <?php
            }
        }

        private function send_webhook_response($data, $status_code = 200) {
            status_header($status_code);
            header('Content-Type: application/json');
            die(json_encode($data));
        }

        public function pay100_webhook_verification() {
            // Get the raw POST data
            $request_body = file_get_contents('php://input');
            $request = json_decode($request_body, true);
            
            // Log webhook received
            error_log('100Pay Webhook: Received webhook call');
            error_log('100Pay Webhook: Request body: ' . print_r($request, true));
            
            if (empty($request)) {
                error_log('100Pay Webhook: Empty request body');
                $this->send_webhook_response(array('error' => 'Empty request body'), 400);
            }
            
            // Validate webhook structure
            if (!isset($request['data']['charge']['metadata']['order_id'])) {
                error_log('100Pay Webhook: Invalid webhook structure - missing order_id');
                $this->send_webhook_response(array('error' => 'Invalid webhook structure'), 400);
            }
            
            $order_key = $request['data']['charge']['metadata']['order_id'];
            $order_id = wc_get_order_id_by_order_key( $order_key );
			$order = wc_get_order( $order_id );

            if ($order) {

                $order_details = $order->get_data();
                
                // Get verification token from header (100Pay uses "verification-token")
                $headers = getallheaders();
                $incoming_verification_token = '';
                
                // Check for verification token in various header formats
                if (isset($headers['verification-token'])) {
                    $incoming_verification_token = $headers['verification-token'];
                } elseif (isset($headers['Verification-Token'])) {
                    $incoming_verification_token = $headers['Verification-Token'];
                } elseif (isset($_SERVER['HTTP_VERIFICATION_TOKEN'])) {
                    $incoming_verification_token = $_SERVER['HTTP_VERIFICATION_TOKEN'];
                }
                
                // Log for debugging
                error_log( sprintf(
                    '100Pay Webhook: Received request for Order #%d. Token present: %s',
                    $order_id,
                    $incoming_verification_token ? 'Yes' : 'No'
                ) );

                $current_verification_token = $this->get_option('pay_verification_token', '');
                $payment_status = $request['data']['charge']['status']['value'];
                $payment_amount = $request['data']["charge"]["status"]["total_paid"];
                $payment_currency = $request['data']["charge"]["billing"]["currency"];
                $customer_id = $request['data']['charge']['customer']['email'];
                $reference_id = $request['data']['charge']['ref_id'];
                
                // Log payment details
                error_log( sprintf(
                    '100Pay Webhook: Payment details - Status: %s, Amount: %s %s, Expected: %s %s',
                    $payment_status,
                    $payment_amount,
                    $payment_currency,
                    $order_details["total"],
                    $order_details["currency"]
                ) );

                $if_ref_exist = $this->pay100_get_transaction($customer_id, $reference_id);

                if ($if_ref_exist == null) {

                    // Check if the Incoming Verification Token matches the current Token
                    if ($incoming_verification_token != null and $incoming_verification_token == $current_verification_token) {
                        
                        // Compare Webhooks and Invoice Currency
                        if ($payment_currency == $order_details["currency"]) {
                            // Payment Completed
                            if ($payment_amount == $order_details["total"] and $payment_status == "paid") {
                                $new_status = 'processing';
                                
                                // Log webhook processing
                                error_log( sprintf(
                                    '100Pay Webhook: Updating Order #%d from %s to PROCESSING. Reference: %s, Amount: %s %s',
                                    $order_id,
                                    $order->get_status(),
                                    $reference_id,
                                    $payment_amount,
                                    $payment_currency
                                ) );
                                
                                $order->set_status( $new_status );
                                
                                // Add order note for audit trail
                                $order->add_order_note(
                                    sprintf(
                                        '100Pay payment completed at %s. Reference: %s, Amount: %s %s. Order updated by webhook.',
                                        current_time('mysql'),
                                        $reference_id,
                                        $payment_amount,
                                        $payment_currency
                                    )
                                );
                
                                $order->save();
    
    
                                // Save transaction to DB
                                $save_transaction = $this->pay100_save_transaction( $customer_id, $reference_id );

                                if ($save_transaction['status'] === true) {
                                    $this->send_webhook_response(array(
                                        'status'=> 'success',
                                        'remark'=> 'Payment Completed',
                                    ), 200);
                                } elseif ($save_transaction['status'] === false) {
                                    $this->send_webhook_response(array(
                                        'status'=> 'error',
                                        'remark'=> 'Failed to save transaction',
                                    ), 400);
                                }
                            
                            } 
                            // OverPaid && UnderPaid Checks
                            elseif ( $payment_amount != $order_details["total"]) {
                                // OverPaid
                              if ( $payment_status == "overpaid" ) {
                                    $new_status = 'overpaid';
                                    $order->set_status( $new_status );
                                    
                                    // Add order note
                                    $order->add_order_note(
                                        sprintf(
                                            '100Pay payment overpaid. Reference: %s, Expected: %s, Paid: %s %s',
                                            $reference_id,
                                            $order_details["total"],
                                            $payment_amount,
                                            $payment_currency
                                        )
                                    );
                    
                                    $order->save();
    
                                    $this->send_webhook_response(array(
                                        'status'=> 'success',
                                        'remark'=> 'Customer Overpaid',
                                    ), 200);
                                } 
                                // UnderPaid - FIXED: Compare payment_status instead of payment_amount
                                elseif ( $payment_status == "underpaid" ) {
                                    $new_status = 'underpaid';
                                    $order->set_status( $new_status );
                                    
                                    // Add order note
                                    $order->add_order_note(
                                        sprintf(
                                            '100Pay payment underpaid. Reference: %s, Expected: %s, Paid: %s %s',
                                            $reference_id,
                                            $order_details["total"],
                                            $payment_amount,
                                            $payment_currency
                                        )
                                    );
                    
                                    $order->save();
    
                                    $this->send_webhook_response(array(
                                        'status'=> 'success',
                                        'remark'=> 'Customer Underpaid',
                                    ), 200);
                                }
                                // Unhandled payment status
                                else {
                                    $this->send_webhook_response(array(
                                        'status'=> 'error',
                                        'remark'=> 'Unhandled payment status: ' . $payment_status,
                                    ), 400);
                                }
        
                            }
                            // No matching condition - return error
                            else {
                                $this->send_webhook_response(array(
                                    'status'=> 'error',
                                    'remark'=> 'Payment amount matches but status is: ' . $payment_status,
                                ), 400);
                            }
                            
                        } else {
                            // FIXED: Proper currency mismatch check
                            $this->send_webhook_response(array(
                                "error" => "Currency does not match. Expected: " . $order_details["currency"] . ", Received: " . $payment_currency,
                            ), 400);
                        }
        
                        
                    } else {
                        $this->send_webhook_response(array(
                            "error" => "Invalid Verification Token",
                        ), 400);
                    }
                
                } elseif ($if_ref_exist != null) {
                    $this->send_webhook_response(array(
                        "error" => "Transaction with Reference ID exists"
                    ), 400);
                }
    

            } else {
                $this->send_webhook_response(array(
                    "error" => "Order does not exist",
                ), 400);
            }




        }

        public function pay100_register_product_status($order_statuses) {

            $order_statuses['wc-underpaid'] = array(
                'label' => __('Underpaid'),
                'public' => true,
                'exclude_from_search' => false,
                'show_in_admin_all_list' => true,
                'label_count' => _n_noop('Underpaid <span class="count">(%s)</span>', 'Underpaid <span class="count">(%s)</span>')
            );

            $order_statuses['wc-overpaid'] = array(
                'label' => __('Overpaid'),
                'public' => true,
                'exclude_from_search' => false,
                'show_in_admin_all_list' => true,
                'label_count' => _n_noop('Overpaid <span class="count">(%s)</span>', 'Overpaid <span class="count">(%s)</span>')
            );
            
            return $order_statuses;
        }

        
        public function add_additional_custom_statuses_to_order_statuses( $order_statuses ) {

            $order_statuses['wc-overpaid'] = 'Overpaid';
            $order_statuses['wc-underpaid'] = 'Underpaid';
            return $order_statuses;
        }


        public function pay100_save_transaction($customer_id, $reference_id) {
            global $wpdb;

            $table_name =  $wpdb->prefix .'pay100_transactions';

            $transaction_status = $wpdb->insert(
                $table_name,
                array(
                    'reference_id' => $reference_id,
                    'customer_id' => $customer_id
                )
            );

            // $wpdb->insert() returns number of rows inserted (1) on success, false on error
            if ($transaction_status === false) {
                error_log('100Pay: Failed to save transaction - ' . $wpdb->last_error);
                return array(
                    "status" => false,
                    "message" => $wpdb->last_error,
                );
            } elseif ($transaction_status > 0) {
                error_log('100Pay: Transaction saved successfully - Ref: ' . $reference_id);
                return array(
                    "status"=> true,
                    "message" => "success"
                );
            } else {
                error_log('100Pay: Unexpected transaction status - ' . $transaction_status);
                return array(
                    "status"=> false,
                    "message"=> $wpdb->last_error ? $wpdb->last_error : 'Unknown error',
                );
            }

        }

        public function pay100_get_transaction($customer_id, $reference_id) {
            global $wpdb;
            $table_name = $wpdb->prefix .'pay100_transactions';

            $result = $wpdb->get_results(
                $wpdb->prepare(
                "SELECT * FROM $table_name WHERE customer_id = %s AND reference_id = %s",
                $customer_id,
                $reference_id
                )
            );

            if (!empty($result)) {
                return $result;
            } else {
                return null;
            }
        }
   
    }

    add_action( 'woocommerce_blocks_loaded', 'pay100_gateway_block_support' );
    function pay100_gateway_block_support() {
        // Verify that the required WooCommerce Blocks class exists
        if ( ! class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
            return;
        }

        require_once __DIR__ . '/includes/class-wc-100pay-gateway-blocks-support.php';

        add_action(
            'woocommerce_blocks_payment_method_type_registration',
            function( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
                $payment_method_registry->register( new WC_100Pay_Gateway_Blocks_Support() );
            }
        );
    }

    add_action( 'before_woocommerce_init', 'pay100_cart_checkout_blocks_compatibility' );
    function pay100_cart_checkout_blocks_compatibility() {

        if( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
                    'cart_checkout_blocks',
                    __FILE__,
                    true // you can set it to false, if you want it to display to the user that the plugin is not compatible
                );
        }

    }
}



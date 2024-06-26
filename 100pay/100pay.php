<?php
/*
Plugin Name: 100Pay Checkout for WooCommerce

Plugin URI: https://100pay.co/ 

Description: We power the payment/crypto banking infrastructure for developers/businesses to accept/process crypto payments at any scale.

Version: 1.0

Author: Chika Precious Benjamin 

Author URI: https://100pay.co/

License: GPLv2 

Text Domain: 100pay

*/

if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', dirname( __FILE__ ) . '/' );
};

register_activation_hook( __FILE__, 'pay100_plugin_activation' );

function pay100_plugin_activation() {
    if ( ! function_exists( 'WC' ) ) {
        return;
    }

    // Create Table
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix .'pay100_transactions';

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        reference_id varchar(255) NOT NULL,
        customer_id varchar(255) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once ( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

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


add_action( 'woocommerce_payment_gateways', 'add_100Pay_gateway_class' );
function add_100Pay_gateway_class( $gateways ) {
    $gateways[] = 'WC_100Pay_Gateway';
    return $gateways;
}


add_action( 'plugins_loaded', 'init_100Pay_gateway_class');
function init_100Pay_gateway_class() {

    class WC_100Pay_Gateway extends WC_Payment_Gateway {

        public function __construct() {

            $this->id = 'pay100';
            $this->icon = dirname(__FILE__) . '/image/100pay.svg';
            $this->has_fields = false;
            $this->method_title = '100Pay Payment Gateway';
            $this->method_description = 'You can make your crypto payments using our payment gateway.';
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
            add_action( 'rest_api_init', array( $this, 'register_webhooks_endpoint') );
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
            add_action( 'woocommerce_thankyou', array( $this, 'payment_modal_trigger' ) );
        }

        public function init_form_fields() {
            $this->form_fields = array(
                'enabled' => array(
                    'title'       => 'Enable/Disable',
                    'label'       => 'Enable 100Pay Payment Gateway',
                    'type'        => 'checkbox',
                    'description' => '',
                    'default'     => 'yes'
                ),
                'title' => array(
                    'title'       => '100Pay Payment Gateway',
                    'type'        => 'text',
                    'description' => 'Pay with 100Pay.',
                    'default'     => 'Pay With 100Pay',
                    'desc_tip'    => true,
                ),
                'description' => array(
                    'title'       => 'Description',
                    'type'        => 'textarea',
                    'description' => 'You can make crypto payment using our payment gateway.',
                    'default'     => 'You can make crypto payment using our payment gateway.',
                ),
                'business_name' => array(
                    'title'       => '100Pay Business Name',
                    'type'        => 'text',
                    'description' => '100Pay Business Name',
                    'default'     => '',
                    'desc_tip'    => true,
                ),
                'public_key' => array(
                    'title'       => 'Public Key',
                    'type'        => 'textarea',
                    'description' => '100Pay Public Key',
                    'default'     => '',
                ),
                'secret_key' => array(
                    'title'       => 'Secret Key',
                    'type'        => 'textarea',
                    'description' => '100Pay Secret Key',
                    'default'     => '',
                ),
                'pay_verification_token' => array(
                    'title'       => 'Verification Token',
                    'type'        => 'textarea',
                    'description' => '100Pay Merchant Verification Token',
                    'default'     => '',
                ),
                'webhook_url' => array(
                    'title'       => 'Webhook URL',
                    'type'        => 'textarea',
                    'description' => 'Please copy this webhook URL and paste on the webhook section on your dashboard',
                    'default'     => $this->generate_webhook_url(),
                    'custom_attributes' => array(
                        'readonly' => 'readonly',
                    ),
                ),
            );
        }

        public function generate_webhook_url() {
            // Check if the webhook url is already set in the options
            $webhook_url = get_option('webhook_url');

            // Generate a webhook url if not set
            if (empty($webhook_url)) {
                
                $site_domain = get_option('siteurl');
                $route_value = bin2hex(random_bytes(10));
                $webhook_url = $site_domain . '/wp-json/webhook/' .$route_value;
                

                // Save the webhhok URL to options
                update_option('webhook_url', $webhook_url);
            }

            return $webhook_url;
        }

        public function split_domain_url($url) {

            $parsed_url = parse_url($url);

            $path = isset($parsed_url['path']) ? $parsed_url['path'] :'';

            $parts = explode('/', trim($path, '/'));

            return array(
                'path' => $parts[2],
                'id' => '/' . $parts[3],
            );
        }

        public function process_payment( $order_id ) {
            $order = wc_get_order( $order_id );
            
            

            $order->update_status( 'processing' );

            $pay_redirect_url = add_query_arg( 'wp_pg', '100Pay', $this->get_return_url( $order ) );

    
            //
            // $order->payment_complete();
            // $order->reduce_order_stock();

            // $order->add_order_note( 'Hey, your order is paid! Thank You', true);

            // WC()->cart->empty_cart();

            return array(
                'result' => 'success',
                'redirect' => $pay_redirect_url,
            );
            

        }

        public function payment_modal_trigger() {

            if ( ! is_wc_endpoint_url( 'order-received' ) ) {
                return;
            }

            if ( isset( $_GET['wp_pg'] ) ) {
                $order_id = wc_get_order_id_by_order_key( $_GET['key'] );
                $order_key = $_GET['key'];
                $order = wc_get_order( $order_id );
                $siteurl = get_option('siteurl');
                    

                $api_key = $this->get_option( 'secret_key' );
                $business_name = $this->get_option( 'business_name' );

                

                // Generate Order Redirect URL
                $redirect_url = $this->get_return_url( $order );

                // Load 100Pay CDN
                $js_url = 'https://js.100pay.co/';
				$curl = curl_init();
				
				curl_setopt($curl, CURLOPT_URL, $js_url);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				
				$js_content = curl_exec($curl);
				
				curl_close($curl);
				
                
                echo <<<EOD
                <script type='text/javascript'>$js_content</script>

                <script>
                var divTag = document.createElement('div');
                var api_key = "{$api_key}";
                var business_name = "{$business_name}";

                
                divTag.id = 'show100Pay';

                
                document.body.appendChild(divTag);


                // Ensure shop100Pay global object exists
                // var shop100Pay = shop100Pay || {};

                paywith100_Pay($order)

                function generateRandomAlphanumeric(length) {
                    var result = '';
                    var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                    var charactersLength = characters.length;
                    for (var i = 0; i < length; i++) {
                        result += characters.charAt(Math.floor(Math.random() * charactersLength));
                    }
                    return result;
                }

                
    

                async function paywith100_Pay(order) {

                    // first_name, last_name, api_key, phone, email, amount, currency_symbol, business_name, country, vat

                    var firstName = order.billing.first_name;
                    var lastName = order.billing.last_name;
                    var DateInstance = new Date();
                    var currentDate = DateInstance.getFullYear() + (DateInstance.getMonth() + 1) + DateInstance.getDate();
                    var businessName = business_name;


                        
                    var user_ID = businessName.toUpperCase() + "-" + firstName.toUpperCase() + lastName.toUpperCase() + "-" + generateRandomAlphanumeric(8);

                    // Setup payment
                    shop100Pay.setup({
                        ref_id:  user_ID,
                        api_key: api_key,
                        customer: {
                            user_id: order.billing.email,
                            name: order.billing.first_name + ' ' + order.billing.last_name,
                            phone: order.billing.phone,
                            email: order.billing.email
                        },
                        billing: {
                            amount: order.total,
                            currency: order.currency,
                            description: "Shop Payment",
                            country: order.billing.country,
                            vat: 0, // optional
                            pricing_type: "fixed_price" // or partial
                        },
                        metadata: {
                            is_approved: "yes",
                            order_id: "<?php echo $order_key; ?>", // optional
                            charge_ref: "REF" // optional, you can add more fields
                        },
                        call_back_url: "<?php echo $siteurl; ?>"/verifyorder/",
                        onClose: function(msg) {
                            alert("You just closed the crypto payment modal.");
                            window.location.href = "<?php echo $redirect_url; ?>"
                        },
                        onPayment: function(reference) {
                            alert("New Payment detected with reference " + reference);
                            // Handle payment confirmation
                        },
                        onError: function(error) {
                            console.log(error);
                            alert("Sorry, something went wrong. Please try again.");
                        }
                    });
                }
                </script>

                EOD;
            }
        }

        public function register_webhooks_endpoint() {
            $url = $this->get_option('webhook_url');
            $split_result = $this->split_domain_url( $url );
            
            $webhook_path = $split_result['path'];
            $route_value = $split_result['id'];

            register_rest_route(
                $webhook_path,
                $route_value,
                array(
                    'methods' => 'POST',
                    'callback' => array( $this, 'pay100_webhook_verification' ),
                    'permission_callback' => '__return_true',
                ),

            );
        }

        public function pay100_webhook_verification( $request ) {
            $order_key = $request['charge']['metadata']['order_id'];
            $order_id = wc_get_order_id_by_order_key( $order_key );
			$order = wc_get_order( $order_id );

            if ($order) {

                $order_details = $order->get_data();
                
                $incoming_verification_token = $request->get_header('HTTP_VERIFICATION_TOKEN');

                $current_verification_token = $this->get_option('pay_verification_token', '');
                $payment_status = $request['charge']['status']['value'];
                $payment_amount = $request["charge"]["status"]["total_paid"];
                $payment_currency = $request["charge"]["billing"]["currency"];
                $customer_id = $request['charge']['customer']['email'];
                $reference_id = $request['charge']['ref_id'];

                $if_ref_exist = $this->pay100_get_transaction($customer_id, $reference_id);

                if ($if_ref_exist == null) {

                    // Check if the Incoming Verification Token matches the current Token
                    if ($incoming_verification_token != null and $incoming_verification_token == $current_verification_token) {
                        
                        // Compare Webhooks and Invoice Currency
                        if ($payment_currency == $order_details["currency"]) {
                            // Payment Completed
                            if ($payment_amount == $order_details["total"] and $payment_status == "completed") {
                                $new_status = 'completed';
                                $order->set_status( $new_status );
                
                                $order->save();
    
    
                                // Save transaction to DB
                                $save_transaction = $this->pay100_save_transaction( $customer_id, $reference_id );

                                if ($save_transaction['status'] === true) {
    
                                    return new WP_REST_Response(
                                        array(
                                            'status'=> 'success',
                                            'remark'=> 'Payment Completed',
                                        ),
                                        200
                                    );
                                } elseif ($save_transaction['status'] === false) {
                                    return new WP_REST_Response(
                                        array(
                                            'status'=> 'error',
                                        ),
                                        400
                                    );
                                }
                            
                            } 
                            // OverPaid && UnderPaid Checks
                            elseif ( $payment_amount != $order_details["total"]) {
                                // OverPaid
                                if ( $payment_status == "overpaid" ) {
                                    $new_status = 'overpaid';
                                    $order->set_status( $new_status );
                    
                                    $order->save();
    
                                    return new WP_REST_Response(
                                        array(
                                            'status'=> 'success',
                                            'remark'=> 'Customer Overpaid '.$order_details['charge']['status']['context']['value'],
                                        ),
                                        200
                                    );
                                } 
                                // UnderPaid
                                elseif ( $payment_amount == "underpaid" ) {
                                    $new_status = 'underpaid';
                                    $order->set_status( $new_status );
                    
                                    $order->save();
    
                                    return new WP_REST_Response(
                                        array(
                                            'status'=> 'success',
                                            'remark'=> 'Customer Underpaid '.$order_details['charge']['status']['context']['value'],
                                        ),
                                        200
                                    );
                                }
        
                            }
                            
                        } elseif ($payment_amount == $order_details["currency"]) {
                            return new WP_REST_Response( 
                                array(
                                    "error" => "Currency Does not match",
                                ),
                                400
                            );
                        }
        
                        
                    } else {
                        return new WP_REST_Response( 
                            array(
                                "error" => "Invalid Verification Token",
                            ),
                            400
                        );
                    }
                
                } elseif ($if_ref_exist != null) {
                    return new WP_REST_Response(
                        array(
                            "error" => "Transaction with Reference ID exists"
                        ),
                        400
                    );
                }
    

            } else {
                return new WP_REST_Response( 
                    array(
                        "error" => "Order does not exist",
                    ),
                    400
                );
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

            if ($transaction_status === false) {
                return array(
                    "status" => false,
                    "message" => $wpdb->last_error,
                );
            } elseif ($transaction_status === true) {
                return array(
                    "status"=> true,
                    "message" => "success"
                );
            } else {
                return array(
                    "status"=> false,
                    "message"=> $wpdb->last_error,
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



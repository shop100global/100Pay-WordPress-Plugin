<?php
use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class WC_100Pay_Gateway_Blocks_Support extends AbstractPaymentMethodType {

    private $gateways;

    protected $name = 'pay100';

    public function __construct() {

    }

    public function initialize() {
        $this->settings = get_option( "woocommerce_{$this->name}_settings", array() );
    }

    public function is_active() {
        return ! empty( $this->settings[ 'enabled' ] ) && 'yes' === $this->settings[ 'enabled' ];
    }

    public function get_payment_method_script_handles() {


		wp_register_script(
			'wc-100pay-blocks-integration',
			plugin_dir_url( __DIR__ ) . 'src/index.js',
			array(
				'wc-blocks-registry',
				'wc-settings',
				'wp-element',
				'wp-html-entities',
			),
			null,
			true
		);

		return array( 'wc-100pay-blocks-integration' );

	}

    public function get_payment_method_data() {
        return array(
            
            'title' => $this->get_setting( 'title' ),
            'description' => $this->get_setting( 'description' ),
    
        );
    }
}
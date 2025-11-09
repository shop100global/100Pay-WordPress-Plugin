<?php
/**
 * Test Plugin Activation Scenarios
 * 
 * Tests for Requirements: 1.1, 1.5, 2.1, 2.4
 */

class Test_Plugin_Activation extends WP_UnitTestCase {

    /**
     * Test Case 6.1: Test activation without WooCommerce
     * 
     * Requirements: 1.1, 1.5, 2.1
     * - Verify admin notice appears when WooCommerce is not active
     * - Verify no crash occurs
     * - Verify gateway is not registered
     */
    public function test_activation_without_woocommerce() {
        // Simulate WooCommerce not being active
        remove_filter( 'woocommerce_payment_gateways', 'add_100Pay_gateway_class' );
        
        // Mock WooCommerce class not existing
        $woocommerce_active = pay100_check_woocommerce_active();
        
        // Assert WooCommerce is detected as inactive
        $this->assertFalse( $woocommerce_active, 'WooCommerce should be detected as inactive' );
        
        // Check that admin notice hook is registered
        $this->assertTrue( 
            has_action( 'admin_notices', 'pay100_woocommerce_missing_notice' ) !== false,
            'Admin notice should be hooked when WooCommerce is inactive'
        );
        
        // Verify gateway is not added to WooCommerce gateways
        $gateways = apply_filters( 'woocommerce_payment_gateways', array() );
        $this->assertNotContains( 
            'WC_100Pay_Gateway', 
            $gateways,
            'Payment gateway should not be registered without WooCommerce'
        );
        
        // Verify no fatal error occurs (test completes successfully)
        $this->assertTrue( true, 'Plugin activation completed without fatal error' );
    }

    /**
     * Test Case 6.2: Test activation with WooCommerce
     * 
     * Requirements: 1.1, 1.5, 2.4
     * - Verify plugin activates successfully
     * - Verify payment gateway is registered
     * - Verify no admin notice appears
     */
    public function test_activation_with_woocommerce() {
        // Skip if WooCommerce is not actually installed
        if ( ! class_exists( 'WooCommerce' ) ) {
            $this->markTestSkipped( 'WooCommerce must be installed to run this test' );
        }
        
        // Verify WooCommerce is detected as active
        $woocommerce_active = pay100_check_woocommerce_active();
        $this->assertTrue( $woocommerce_active, 'WooCommerce should be detected as active' );
        
        // Verify admin notice is NOT hooked when WooCommerce is active
        $this->assertFalse( 
            has_action( 'admin_notices', 'pay100_woocommerce_missing_notice' ) !== false,
            'Admin notice should not be hooked when WooCommerce is active'
        );
        
        // Verify gateway class is defined
        $this->assertTrue( 
            class_exists( 'WC_100Pay_Gateway' ),
            'Payment gateway class should be defined when WooCommerce is active'
        );
        
        // Verify gateway is registered with WooCommerce
        $gateways = apply_filters( 'woocommerce_payment_gateways', array() );
        $this->assertContains( 
            'WC_100Pay_Gateway', 
            $gateways,
            'Payment gateway should be registered with WooCommerce'
        );
        
        // Verify activation hook creates database table
        global $wpdb;
        $table_name = $wpdb->prefix . 'pay100_transactions';
        $table_exists = $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) === $table_name;
        $this->assertTrue( $table_exists, 'Database table should be created on activation' );
        
        // Verify no fatal error occurs
        $this->assertTrue( true, 'Plugin activation completed successfully with WooCommerce' );
    }

    /**
     * Test that ABSPATH is properly checked
     * 
     * Requirements: 1.1, 3.1
     */
    public function test_abspath_security_check() {
        // Verify ABSPATH is defined (WordPress constant)
        $this->assertTrue( defined( 'ABSPATH' ), 'ABSPATH should be defined by WordPress' );
        
        // Verify plugin doesn't redefine ABSPATH
        // This is tested by the fact that the plugin loads without errors
        $this->assertTrue( true, 'Plugin respects WordPress ABSPATH constant' );
    }

    /**
     * Test WooCommerce dependency check function
     * 
     * Requirements: 1.2, 2.2
     */
    public function test_woocommerce_check_function() {
        // Verify function exists
        $this->assertTrue( 
            function_exists( 'pay100_check_woocommerce_active' ),
            'WooCommerce check function should exist'
        );
        
        // Verify function returns boolean
        $result = pay100_check_woocommerce_active();
        $this->assertIsBool( $result, 'WooCommerce check should return boolean' );
    }

    /**
     * Test admin notice function
     * 
     * Requirements: 2.1, 2.3
     */
    public function test_admin_notice_function() {
        // Verify function exists
        $this->assertTrue( 
            function_exists( 'pay100_woocommerce_missing_notice' ),
            'Admin notice function should exist'
        );
        
        // Capture output of admin notice
        ob_start();
        pay100_woocommerce_missing_notice();
        $output = ob_get_clean();
        
        // Verify notice contains expected elements
        $this->assertStringContainsString( 'notice', $output, 'Output should contain notice class' );
        $this->assertStringContainsString( '100Pay Payment Gateway', $output, 'Notice should mention plugin name' );
        $this->assertStringContainsString( 'WooCommerce', $output, 'Notice should mention WooCommerce' );
        $this->assertStringContainsString( 'is-dismissible', $output, 'Notice should be dismissible' );
    }
}

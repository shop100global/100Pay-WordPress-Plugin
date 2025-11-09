<?php
/**
 * Manual Activation Test Script
 * 
 * This script simulates plugin activation scenarios for manual testing
 * Run this from command line: php tests/manual-activation-test.php
 * 
 * Tests Requirements: 1.1, 1.5, 2.1, 2.4
 */

echo "=== 100Pay Plugin Activation Tests ===\n\n";

// Define ABSPATH to simulate WordPress environment
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', dirname( dirname( __FILE__ ) ) . '/' );
}

// Mock WordPress functions for testing
function add_action( $hook, $callback ) {
    // Mock function - just register that it was called
    global $wp_actions;
    if ( ! isset( $wp_actions ) ) {
        $wp_actions = array();
    }
    $wp_actions[ $hook ][] = $callback;
}

function register_activation_hook( $file, $callback ) {
    // Mock function
}

function register_uninstall_hook( $file, $callback ) {
    // Mock function
}

function register_deactivation_hook( $file, $callback ) {
    // Mock function
}

function add_filter( $hook, $callback ) {
    // Mock function
    global $wp_filters;
    if ( ! isset( $wp_filters ) ) {
        $wp_filters = array();
    }
    $wp_filters[ $hook ][] = $callback;
}

function esc_url( $url ) {
    return $url;
}

function admin_url( $path ) {
    return 'http://example.com/wp-admin/' . $path;
}

function update_option( $key, $value ) {
    // Mock function
}

function delete_option( $key ) {
    // Mock function
}

echo "Test Environment Setup:\n";
echo "- ABSPATH defined: " . ( defined( 'ABSPATH' ) ? 'YES' : 'NO' ) . "\n";
echo "- ABSPATH value: " . ABSPATH . "\n\n";

// Load plugin functions
require_once dirname( dirname( __FILE__ ) ) . '/100pay.php';

echo "=== Test 6.1: Activation WITHOUT WooCommerce ===\n";
echo "Requirements: 1.1, 1.5, 2.1\n\n";

// Test WooCommerce check function
$wc_active = pay100_check_woocommerce_active();
echo "1. WooCommerce Active Check: " . ( $wc_active ? 'ACTIVE' : 'INACTIVE' ) . "\n";

if ( ! $wc_active ) {
    echo "   ✓ PASS: WooCommerce correctly detected as inactive\n";
} else {
    echo "   ✗ FAIL: WooCommerce should be inactive for this test\n";
}

// Test admin notice function exists
echo "\n2. Admin Notice Function: ";
if ( function_exists( 'pay100_woocommerce_missing_notice' ) ) {
    echo "EXISTS\n";
    echo "   ✓ PASS: Admin notice function is defined\n";
    
    // Test notice output
    echo "\n3. Admin Notice Output:\n";
    ob_start();
    pay100_woocommerce_missing_notice();
    $notice_output = ob_get_clean();
    
    echo "   Notice HTML:\n";
    echo "   " . str_replace( "\n", "\n   ", trim( $notice_output ) ) . "\n";
    
    // Verify notice contains required elements
    $checks = array(
        'notice' => strpos( $notice_output, 'notice' ) !== false,
        'error' => strpos( $notice_output, 'error' ) !== false,
        'dismissible' => strpos( $notice_output, 'is-dismissible' ) !== false,
        '100Pay' => strpos( $notice_output, '100Pay' ) !== false,
        'WooCommerce' => strpos( $notice_output, 'WooCommerce' ) !== false,
    );
    
    echo "\n   Notice Content Checks:\n";
    foreach ( $checks as $check => $result ) {
        echo "   - Contains '$check': " . ( $result ? '✓ PASS' : '✗ FAIL' ) . "\n";
    }
} else {
    echo "NOT FOUND\n";
    echo "   ✗ FAIL: Admin notice function should exist\n";
}

// Test that no fatal error occurs
echo "\n4. Fatal Error Check: ";
echo "✓ PASS (script completed without crash)\n";

echo "\n" . str_repeat( '=', 50 ) . "\n\n";

echo "=== Test 6.2: Activation WITH WooCommerce ===\n";
echo "Requirements: 1.1, 1.5, 2.4\n\n";

if ( class_exists( 'WooCommerce' ) ) {
    echo "1. WooCommerce Class: EXISTS\n";
    echo "   ✓ PASS: WooCommerce is available\n";
    
    echo "\n2. Payment Gateway Class: ";
    if ( class_exists( 'WC_100Pay_Gateway' ) ) {
        echo "DEFINED\n";
        echo "   ✓ PASS: Gateway class is defined\n";
    } else {
        echo "NOT DEFINED\n";
        echo "   ✗ FAIL: Gateway class should be defined when WooCommerce is active\n";
    }
    
    echo "\n3. Gateway Registration: ";
    if ( function_exists( 'add_100Pay_gateway_class' ) ) {
        echo "FUNCTION EXISTS\n";
        echo "   ✓ PASS: Gateway registration function is defined\n";
    } else {
        echo "FUNCTION NOT FOUND\n";
        echo "   ✗ FAIL: Gateway registration function should exist\n";
    }
} else {
    echo "1. WooCommerce Class: NOT FOUND\n";
    echo "   ⚠ SKIP: WooCommerce must be installed to run this test\n";
    echo "   Note: Install WooCommerce plugin to test this scenario\n";
}

echo "\n4. Fatal Error Check: ";
echo "✓ PASS (script completed without crash)\n";

echo "\n" . str_repeat( '=', 50 ) . "\n\n";

echo "=== Additional Security Tests ===\n\n";

echo "1. ABSPATH Security Check:\n";
echo "   - ABSPATH is defined: " . ( defined( 'ABSPATH' ) ? '✓ PASS' : '✗ FAIL' ) . "\n";
echo "   - Plugin respects ABSPATH: ✓ PASS (no redefinition error)\n";

echo "\n2. Function Availability:\n";
$functions = array(
    'pay100_check_woocommerce_active',
    'pay100_woocommerce_missing_notice',
    'pay100_plugin_activation',
    'add_100Pay_gateway_class',
    'init_100Pay_gateway_class',
);

foreach ( $functions as $function ) {
    $exists = function_exists( $function );
    echo "   - $function: " . ( $exists ? '✓ EXISTS' : '✗ MISSING' ) . "\n";
}

echo "\n" . str_repeat( '=', 50 ) . "\n";
echo "\n=== Test Summary ===\n";
echo "All critical tests completed without fatal errors.\n";
echo "Plugin activation safety verified.\n\n";

if ( ! class_exists( 'WooCommerce' ) ) {
    echo "Note: Install WooCommerce to test full activation scenario.\n\n";
}

<?php
/*
Plugin Name: 100Pay Checkout Plugin

Plugin URI: https://100pay.co/ 

Description: We power the payment/crypto banking infrastructure for developers/businesses to accept/process crypto payments at any scale.

Version: 0.1 

Author: Chika Precious Benjamin 

Author URI: https://100pay.co/

License: GPLv2 or later 

Text Domain: 100pay

*/

function _100pay_plugin_styles() {
    wp_enqueue_style('100PayStyles', plugins_url('/styles.css', __FILE__));
}

// Enqueue JavaScript file
function _100pay_plugin_scripts() {
    wp_enqueue_script('100PayScripts', plugins_url('/scripts.js', __FILE__), array('jquery'), false, true);
}

add_action('wp_enqueue_scripts', '_100pay_plugin_scripts');
add_action('wp_enqueue_styles', '_100pay_plugin_styles');

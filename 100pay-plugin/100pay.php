<?php
/*
Plugin Name: 100Pay Checkout

Plugin URI: https://100pay.co/ 

Description: We power the payment/crypto banking infrastructure for developers/businesses to accept/process crypto payments at any scale.

Version: 0.1 

Author: Chika Precious Benjamin 

Author URI: https://100pay.co/

License: GPLv2 or later 

Text Domain: 100pay

*/

define( 'WC_100PAY_PATH', plugin_dir_path( __FILE__ ) );
define( 'WC_100PAY_URL', plugin_dir_url( __FILE__ ) );
define ( 'WC_100PAY_VERSION', '0.1' );
define( 'WC_100PAY_BASE_DIR', plugin_dir_path( __FILE__ ) );


add_action('wp_enqueue_styles', 'wc_100pay_plugin_styles');
// Enqueue CSS file
function wc_100pay_plugin_styles() {
    wp_enqueue_style('100PayStyles', plugins_url('assets/css/styles.css', __FILE__));
};

add_action('wp_enqueue_scripts', 'wc_100pay_plugin_scripts');
// Enqueue JavaScript file
function wc_100pay_plugin_scripts() {
    wp_enqueue_script('100PayScripts', plugins_url('assets/js/scripts.js', __FILE__), array('jquery'), false, true);
};

// Add 100Pay Plugin Submenu to WP Admin
// Submenu details 
$page_title = '100Pay Settings Page';
$menu_title = '100Pay';
$capability = 'manage_options';
$menu_slug = '100-pay';
$icon_url = 'dashicons-lock';
$callback = 'wc_100pay_settings_page';
$position = '99';
$option_menu_slug = '100pay';
$settings_group_name = 'wc_100pay_credentials';

add_action('admin_menu', 'create_wc_100pay_menu');
function create_wc_100pay_menu() {
    global $page_title;
    global $menu_title; 
    global $capability; 
    global $menu_slug;
    global $callback;
    global $icon_url; 
    global $position;

    // Create Custom Top-level Menu
    add_menu_page(
        $page_title, 
        $menu_title, 
        $capability, 
        $menu_slug,
        $callback,
        $icon_url, 
        $position
    );

    // create submenu items
    add_submenu_page(
        $menu_slug, // Parent Menu Slug
        'Overview | 100Pay Checkout Plugin', // Page Title
        'Overview', // Menu Title
        $capability,
        'wcp-settings-overview', // Menu Slug
        'callback_submenu_overview' // Callback
    );

    add_submenu_page(
        $menu_slug, // Parent Menu Slug
        'Settings | 100Pay Checkout Plugin', // Parent Menu Slug
        'Settings', // Page Title
        $capability,
        'wcp-settings-page', // Menu Slug
        'callback_wc_100pay_option_page' // Callback
        // 'callback_submenu_settings' // Callback
    );
    
    add_submenu_page(
        $menu_slug, // Parent Menu Slug
        'Uninstall | 100Pay Checkout Plugin', // Parent Menu Slug
        'Uninstall', // Page Title
        $capability,
        'wcp-uninstall-page', // Menu Slug
        'callback_submenu_uninstall' // Callback
    );

};

function callback_submenu_overview() {
    ?>
    <div class="wrap">
        <h2>100Pay Checkout Overview</h2>
        <!-- Your settings page content goes here -->
        <p>This is where you can configure settings for My Plugin.</p>
    </div>
    <?php
};

// Not In use 
function callback_submenu_settings() {
    ?>
    <div class="wrap">
        <h2>100Pay Checkout Settings</h2>
        <!-- Your settings page content goes here -->
        <p>This is where you can configure settings for My Plugin.</p>

        <form action="post">
            <div>
                <label for="bname">Business Name: </label>
                <input type="text">
            </div>
            <div>
                <label for="public">Public Key: </label>
                <input type="text">
            </div>
            <div>
                <label for="secret">Private Key: </label>
                <input type="text">
            </div>

            <div>
                <button>Save Changes</button>
            </div>
        </form>
    </div>
    <?php
};

// add_action('admin_menu', 'create_wc_100pay_submenu');
function create_wc_100pay_submenu() {
    global $page_title;
    global $menu_title; 
    global $capability; 
    global $option_menu_slug;
    global $callback;
    
    add_options_page(
        $page_title,
        $menu_title,
        $capability,
        $option_menu_slug,
        'callback_wc_100pay_option_page'
    );
};

add_action('admin_menu', 'wc_100pay_settings_page');
// Add Menu for Option Page
function wc_100pay_settings_page() {
    global $page_title;
    global $menu_title; 
    global $capability; 
    global $option_menu_slug;

    add_options_page(
        $page_title,
        $menu_title,
        $capability,
        $option_menu_slug, // Menu Slug
        'callback_wc_100pay_option_page' // Callback
    );
};

// Create the Option Page
function callback_wc_100pay_option_page() {
    global $option_menu_slug;
    global $settings_group_name;

    if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
        // Settings updated successfully, display success message
        echo '<div id="message" class="updated notice is-dismissible"><p>Settings saved successfully!</p></div>';
    }

    ?>
    <div class="wrap">
        <?php
        // Check for any settings errors
        settings_errors();
        ?>
        <form action="options.php" method="post">
            <?php
            settings_fields( $settings_group_name );
            do_settings_sections( $option_menu_slug );
            submit_button( 'Save Changes', 'primary' );
            ?>
        </form>
    </div>
    <?php
};

add_action( 'admin_init', 'wc_100pay_admin_int' );
// Register and define the settings 
function wc_100pay_admin_int() {
    global $settings_group_name;
    global $option_menu_slug;
    $db_secret_key_field = 'wc_100pay_secret_key';
    $db_public_key_field = 'wc_100pay_public_key';
    $db_business_name_field = 'wc_100pay_business_name';

    $args = array(
        'type' => 'string',
        'default' => NULL
    );
    
    // Register New Settings 
    register_setting( $settings_group_name, $db_business_name_field );
    register_setting( $settings_group_name, $db_secret_key_field );
    register_setting( $settings_group_name, $db_public_key_field );

    // Adds a New Section to a Settings Page
    add_settings_section(
        'wc_100pay_credentials_section', // id
        '100Pay Checkout Settings', // title
        'wc_100Pay_section_text', // Callback
        $option_menu_slug // Settings Page Slug: Settings page on which to show the section 
    ); 
    
    // Add a New Field to a Section of a Settings Page
    add_settings_field(
        'wc_100pay_name', // id
        'Business Name', // title(Form Label)
        'wc_100pay_setting_name', // Callback
        $option_menu_slug, // Settings Page Slug: Settings page on which to show the section 
        'wc_100pay_credentials_section' // Section of the settings page in which to show the field, as defined previously by the add_settings_section() function call
    ); 

    add_settings_field(
        'wc_100pay_public_key', // id
        'Public API Key', // title(Form Label)
        'wc_100pay_setting_public', // Callback
        $option_menu_slug, // Settings Page Slug: Settings page on which to show the section 
        'wc_100pay_credentials_section' // Section of the settings page in which to show the field, as defined previously by the add_settings_section() function call
    ); 

    add_settings_field(
        'wc_100pay_secret_key', // id
        'Secret API Key', // title(Form Label)
        'wc_100pay_setting_secret', // Callback
        $option_menu_slug, // Settings Page Slug: Settings page on which to show the section 
        'wc_100pay_credentials_section' // Section of the settings page in which to show the field, as defined previously by the add_settings_section() function call
    ); 

};

// Draw the section header
function wc_100Pay_section_text() {
    echo '<p>Enter your settings here.</p>';
};

// Display and fill the Name form field
function wc_100pay_setting_name() {
    global $db_business_name_field;

    // get option 'text_string' value from the database
    $db_business_name_value = get_option($db_business_name_field, '');
    $business_name_value = isset($db_business_name_value["name"]) ? $db_business_name_value["name"] : '';

    // if ($db_business_name_value === false || $db_business_name_value === null) {
    //     echo "<p>Error: Failed to retrieve option value or option value is invalid.</p>";
    // };

    // echo the field
    echo "<input id='wc_100pay_business_name' name='wc_100pay_business_name[name]' type='text' value='" . esc_attr($business_name_value) . "'/>";
};

function wc_100pay_setting_secret() {
    global $db_secret_key_field;

    $db_secret_value = get_option($db_secret_key_field, '');
    // $secret_value = $db_secret_value["wc_100pay_secret_key"];
    $secret_value = isset($db_secret_value["secret_key"]) ? $db_secret_value["secret_key"] : '';

    // if ($db_secret_value === false || $db_secret_value === null) {
    //     echo "<p>Error: Failed to retrieve option value or option value is invalid.</p>";
    //     return;
    // };

    echo "<input id='wc_100pay_secret_key' name='wc_100pay_secret_key[secret_key]' type='text' value='" . esc_attr( $secret_value ) . "'/>";
};

function wc_100pay_setting_public() {
    global $db_public_key_field;

    $db_public_value = get_option($db_public_key_field, '');
    // $public_value = $db_public_value["wc_100pay_public_key"];
    $public_value = isset($db_public_value["public_key"]) ? $db_public_value["public_key"] : '';
    
    // if ($db_public_value === false || $db_public_value === null) {
    //     echo "<p>Error: Failed to retrieve option value or option value is invalid.</p>";
    //     return;
    // };

    echo "<input id='wc_100pay_public_key' name='wc_100pay_public_key[public_key]' type='text' value='" . esc_attr( $public_value ) . "'/>";
};

function wc_100pay_validate_options( $input ) {

    $valid = array();
    $valid['name'] = preg_replace(
        '/[^a-zA-Z\s]/',
        '',
        $input['name']
    );
    return $valid;
};


# Implementation Plan

- [x] 1. Fix ABSPATH constant redefinition issue
  - Remove the incorrect ABSPATH redefinition block (lines 17-19)
  - Replace with proper security check: `defined('ABSPATH') || exit;`
  - _Requirements: 1.1, 1.4, 3.1_

- [x] 2. Implement WooCommerce dependency checking
  - [x] 2.1 Create WooCommerce availability check function
    - Write `pay100_check_woocommerce_active()` function
    - Use `class_exists('WooCommerce')` for reliable detection
    - _Requirements: 1.2, 2.2_
  
  - [x] 2.2 Create admin notice for missing WooCommerce
    - Write `pay100_woocommerce_missing_notice()` function
    - Hook to `admin_notices` action
    - Include dismissible notice with WooCommerce installation link
    - _Requirements: 2.1, 2.3_
  
  - [x] 2.3 Add dependency check to plugin initialization
    - Wrap gateway class definition in WooCommerce existence check
    - Only call `add_100Pay_gateway_class` if WooCommerce is active
    - _Requirements: 1.2, 2.2, 2.4_

- [x] 3. Refactor plugin initialization for safe loading
  - [x] 3.1 Update `init_100Pay_gateway_class()` function
    - Add WooCommerce class existence check before defining gateway class
    - Ensure `WC_Payment_Gateway` is available before extending
    - _Requirements: 1.2, 3.2_
  
  - [x] 3.2 Update activation hook safety
    - Keep existing WC() check in `pay100_plugin_activation()`
    - Ensure proper use of WordPress-defined ABSPATH
    - Add error handling for database table creation
    - _Requirements: 1.5, 3.3_

- [x] 4. Add proper plugin header security check
  - Add `defined('ABSPATH') || exit;` at the top of main plugin file
  - Ensure it's the first executable line after the plugin header comment
  - _Requirements: 1.1, 3.1_

- [x] 5. Verify blocks support file safety
  - Ensure `class-wc-100pay-gateway-blocks-support.php` is only loaded when needed
  - Verify the file has proper security checks
  - Confirm it only loads when WooCommerce Blocks is available
  - _Requirements: 1.2, 3.2_

- [x] 6. Test plugin activation scenarios
  - [x] 6.1 Test activation without WooCommerce
    - Deactivate WooCommerce
    - Activate 100Pay plugin
    - Verify admin notice appears and no crash occurs
    - _Requirements: 1.1, 1.5, 2.1_
  
  - [x] 6.2 Test activation with WooCommerce
    - Ensure WooCommerce is active
    - Activate 100Pay plugin
    - Verify plugin activates successfully
    - Check that payment gateway appears in WooCommerce settings
    - _Requirements: 1.1, 1.5, 2.4_

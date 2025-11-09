# Design Document

## Overview

This design addresses the critical WordPress plugin crash caused by improper constant definition and missing dependency checks in the 100Pay payment gateway plugin. The solution focuses on removing the ABSPATH redefinition, implementing proper WooCommerce dependency checks, and adding graceful error handling.

## Architecture

### Plugin Initialization Flow

```
Plugin Activation
    ↓
Check if ABSPATH already defined (WordPress Core)
    ↓
Define plugin-specific constants only
    ↓
Check WooCommerce availability
    ↓
    ├─→ WooCommerce Active: Register payment gateway
    └─→ WooCommerce Inactive: Display admin notice
```

### Key Components

1. **Constant Management**: Remove ABSPATH redefinition, keep only plugin-specific constants
2. **Dependency Checker**: Verify WooCommerce is active before initializing gateway
3. **Admin Notice System**: Inform administrators about missing dependencies
4. **Safe Initialization**: Wrap WooCommerce-dependent code in conditional checks

## Components and Interfaces

### 1. Constant Definitions

**Current Issue:**
```php
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', dirname( __FILE__ ) . '/' );  // WRONG - redefines WordPress constant
}
```

**Fixed Approach:**
```php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
```

### 2. WooCommerce Dependency Check

**Function:** `pay100_check_woocommerce_active()`
- Returns: boolean
- Purpose: Check if WooCommerce plugin is active
- Implementation: Use `class_exists('WooCommerce')` or `in_array()` with active plugins

**Function:** `pay100_woocommerce_missing_notice()`
- Returns: void
- Purpose: Display admin notice when WooCommerce is missing
- Hook: `admin_notices`

### 3. Conditional Gateway Registration

**Current Issue:**
- Gateway class is registered even when WooCommerce is not available
- Causes fatal errors when trying to extend `WC_Payment_Gateway`

**Fixed Approach:**
- Wrap all WooCommerce-dependent code in `plugins_loaded` hook
- Check for WooCommerce before defining gateway class
- Only register gateway if WooCommerce exists

### 4. Activation Hook Safety

**Function:** `pay100_plugin_activation()`
- Current: Returns early if WC() doesn't exist
- Issue: Still tries to use ABSPATH incorrectly
- Fix: Remove ABSPATH redefinition, use WordPress-defined ABSPATH

## Data Models

No database schema changes required. Existing transaction table structure remains:

```sql
CREATE TABLE {prefix}pay100_transactions (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    reference_id varchar(255) NOT NULL,
    customer_id varchar(255) NOT NULL,
    PRIMARY KEY (id)
)
```

## Error Handling

### 1. Missing WooCommerce

**Scenario:** WooCommerce is not installed/activated
**Response:** 
- Display admin notice with clear message
- Provide link to WooCommerce plugin
- Prevent gateway registration
- No fatal errors

### 2. Direct File Access

**Scenario:** Plugin file accessed directly (not through WordPress)
**Response:**
- Check for ABSPATH constant
- Exit immediately if not defined
- Prevent any code execution

### 3. Activation Errors

**Scenario:** Database table creation fails
**Response:**
- Log error using WordPress error logging
- Allow activation to complete
- Display notice to check database permissions

## Testing Strategy

### Manual Testing

1. **Test Case 1: Activation without WooCommerce**
   - Deactivate WooCommerce
   - Activate 100Pay plugin
   - Expected: Admin notice displayed, no crash

2. **Test Case 2: Activation with WooCommerce**
   - Ensure WooCommerce is active
   - Activate 100Pay plugin
   - Expected: Plugin activates successfully, payment gateway appears

3. **Test Case 3: WooCommerce Deactivation**
   - With both plugins active
   - Deactivate WooCommerce
   - Expected: Admin notice appears, gateway disappears from checkout

4. **Test Case 4: Direct File Access**
   - Access plugin file directly via URL
   - Expected: Blank page or exit, no errors

### Code Review Checklist

- [ ] ABSPATH redefinition removed
- [ ] All WooCommerce class references wrapped in existence checks
- [ ] Admin notices properly hooked and dismissible
- [ ] Plugin constants use unique prefixes
- [ ] No global variable conflicts
- [ ] Proper use of WordPress hooks

## Implementation Notes

### Files to Modify

1. **100pay/100pay.php** (Primary file)
   - Remove lines 17-19 (ABSPATH redefinition)
   - Add WooCommerce dependency check
   - Add admin notice function
   - Wrap gateway initialization in conditional

2. **100pay/includes/class-wc-100pay-gateway-blocks-support.php**
   - Already has proper structure
   - Verify it's only loaded when WooCommerce Blocks is available

### WordPress Hooks to Use

- `plugins_loaded` - Check for WooCommerce and initialize gateway
- `admin_notices` - Display dependency warnings
- `admin_init` - Check dependencies on admin pages

### Best Practices Applied

1. Use `defined('ABSPATH') || exit;` pattern for security
2. Check class existence before extending: `class_exists('WC_Payment_Gateway')`
3. Use WordPress conditional functions: `is_plugin_active()`
4. Provide helpful error messages to administrators
5. Fail gracefully without breaking the site

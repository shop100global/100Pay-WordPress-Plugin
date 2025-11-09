# 100Pay WooCommerce Payment Gateway

![100Pay Logo](https://100pay.co/img/100pay-logo.svg)

Accept cryptocurrency payments on your WooCommerce store with 100Pay. Enable your customers to pay with 22+ cryptocurrencies, stablecoins, bank transfers, and cards.

## 🚀 Features

- **22+ Cryptocurrencies** - Bitcoin, Ethereum, USDT, USDC, and more
- **Instant Settlement** - Automatic order status updates via webhooks
- **Secure Payments** - Bank-grade security with encrypted transactions
- **No Redirects** - Payment modal opens directly on your site
- **Custom Statuses** - Handle overpaid and underpaid scenarios
- **Retry Payments** - Customers can retry failed payments easily
- **Mobile Optimized** - Works seamlessly on all devices
- **Customizable Modal** - Adjust modal width to match your theme

## 📋 Requirements

- WordPress 5.0 or higher
- WooCommerce 3.0 or higher
- PHP 7.4 or higher
- SSL Certificate (HTTPS) recommended
- 100Pay merchant account ([Sign up here](https://100pay.co))

## 📦 Installation

### Option 1: WordPress Plugin Directory (Recommended)

1. Go to **WordPress Admin → Plugins → Add New**
2. Search for **"100Pay WooCommerce Payment Gateway"**
3. Click **Install Now** then **Activate**

### Option 2: Manual Installation

1. Download the latest release from [GitHub Releases](https://github.com/yourusername/100pay-wordpress-plugin/releases)
2. Go to **WordPress Admin → Plugins → Add New → Upload Plugin**
3. Choose the downloaded ZIP file
4. Click **Install Now** then **Activate**

### Option 3: FTP Upload

1. Download and extract the plugin ZIP file
2. Upload the `100pay` folder to `/wp-content/plugins/`
3. Go to **WordPress Admin → Plugins**
4. Activate **100Pay Checkout for WooCommerce**

## ⚙️ Configuration

### Step 1: Get Your 100Pay Credentials

1. Log in to your [100Pay Dashboard](https://dashboard.100pay.co)
2. Go to **Settings → API Keys**
3. Copy your:
   - Business Name
   - Public Key
   - Secret Key (API Key)
4. Go to **Settings → Webhooks**
5. Copy your Verification Token

📖 **Need help?** Read our [complete setup guide](https://100pay.co/blog/how-to-accept-crypto-payments-on-your-wordpress-woocommerce-store-with-100-pay-checkout)

### Step 2: Configure the Plugin

1. Go to **WooCommerce → Settings → Payments**
2. Find **100Pay Payment Gateway** and click **Manage**
3. Fill in your credentials:
   - **Enable 100Pay Checkout** - Check to enable
   - **Payment Method Name** - Display name (e.g., "Pay with Crypto")
   - **Description** - Customer-facing description
   - **Business Name** - Your 100Pay business name
   - **Public Key** - Your 100Pay public key
   - **Secret Key** - Your 100Pay API key
   - **Verification Token** - Your webhook verification token
   - **Modal Width** - Optional (default: 500px)
4. Click **Save changes**

### Step 3: Configure Webhook in 100Pay Dashboard

1. Copy the **Webhook URL** from the plugin settings
2. Go to your [100Pay Dashboard → Settings → Webhooks](https://dashboard.100pay.co/settings/webhooks)
3. Paste the webhook URL
4. Save the settings

## 🎯 How It Works

### Payment Flow

```
Customer places order
    ↓
Order status: Pending
    ↓
Customer redirected to order page
    ↓
100Pay payment modal opens automatically
    ↓
Customer completes crypto payment
    ↓
100Pay sends webhook to your site
    ↓
Order status updated to: Processing
    ↓
Customer sees success message
    ↓
Store owner fulfills order
```

### Order Statuses

- **Pending** - Awaiting payment
- **Processing** - Payment received, ready to fulfill
- **Overpaid** - Customer paid more than required
- **Underpaid** - Customer paid less than required
- **Completed** - Order fulfilled (manual)

## 🔧 Recent Fixes & Improvements

### Version 1.1 (Latest)

**Critical Fixes:**
- ✅ Fixed ABSPATH constant redefinition crash
- ✅ Fixed WooCommerce dependency checking
- ✅ Fixed webhook currency validation logic
- ✅ Fixed underpaid status detection
- ✅ Fixed transaction save error (wpdb->insert return value)
- ✅ Fixed payment gateway not appearing in WooCommerce settings
- ✅ Fixed webhook 404 errors with new wc-api endpoint

**New Features:**
- ✅ Integrated official 100Pay checkout library
- ✅ Added "Pay Now" button for pending orders
- ✅ Added customizable modal width setting
- ✅ Added beautiful setup page with branding
- ✅ Added order notes for audit trail
- ✅ Added comprehensive error handling
- ✅ Added payment retry functionality

**Security Improvements:**
- ✅ Secret key and verification token fields now masked (password type)
- ✅ Multiple security checks in webhook verification
- ✅ Duplicate transaction prevention
- ✅ Token-based webhook authentication
- ✅ Currency and amount validation

**User Experience:**
- ✅ Visual payment status notifications
- ✅ Automatic payment modal trigger
- ✅ Prevent page navigation during payment
- ✅ Clear error messages and retry options
- ✅ Mobile-responsive design
- ✅ Setup guide with helpful links

## 🧪 Testing

### Test Payment Flow

1. Add a product to cart
2. Proceed to checkout
3. Select "100Pay Payment Gateway"
4. Complete checkout
5. Payment modal should open automatically
6. Complete payment with test credentials
7. Verify order status updates to "Processing"

### Test Webhook

Use Postman or cURL to test the webhook:

```bash
curl -X POST "http://yoursite.com/?wc-api=100pay_webhook" \
  -H "Content-Type: application/json" \
  -H "verification-token: YOUR_TOKEN" \
  -d '{
    "data": {
      "charge": {
        "metadata": {
          "order_id": "wc_order_xxx"
        },
        "status": {
          "value": "paid",
          "total_paid": 10.00
        },
        "billing": {
          "currency": "USD"
        },
        "customer": {
          "email": "test@example.com"
        },
        "ref_id": "TEST-REF-123"
      }
    }
  }'
```

## 🤝 Contributing

We welcome contributions from the community! Here's how you can help:

### Getting Started

1. **Fork the repository**
   ```bash
   git clone https://github.com/yourusername/100pay-wordpress-plugin.git
   cd 100pay-wordpress-plugin
   ```

2. **Create a feature branch**
   ```bash
   git checkout -b feature/your-feature-name
   ```

3. **Make your changes**
   - Follow WordPress coding standards
   - Test thoroughly on local WordPress installation
   - Add comments to complex code

4. **Test your changes**
   - Test with WooCommerce active and inactive
   - Test payment flow end-to-end
   - Test webhook with sample data
   - Check for PHP errors in debug log

5. **Commit and push**
   ```bash
   git add .
   git commit -m "Add: your feature description"
   git push origin feature/your-feature-name
   ```

6. **Create a Pull Request**
   - Describe your changes clearly
   - Reference any related issues
   - Include screenshots if UI changes

### Development Setup

1. **Install WordPress locally** (XAMPP, Local, or Docker)
2. **Install WooCommerce plugin**
3. **Clone this repository** to `wp-content/plugins/`
4. **Activate the plugin**
5. **Enable WordPress debugging** in `wp-config.php`:
   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   ```

### Code Standards

- Follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)
- Use meaningful variable and function names
- Add PHPDoc comments to functions
- Keep functions focused and single-purpose
- Test on PHP 7.4+ and WordPress 5.0+

### Testing Guidelines

- Test with WooCommerce active and inactive
- Test payment flow with test API keys
- Test webhook with sample payloads
- Test on different WordPress installations (root, subdirectory)
- Test on mobile devices
- Check browser console for JavaScript errors
- Check WordPress debug log for PHP errors

### Areas for Contribution

- 🐛 **Bug Fixes** - Report or fix bugs
- ✨ **Features** - Add new payment features
- 📝 **Documentation** - Improve guides and docs
- 🎨 **UI/UX** - Enhance user interface
- 🧪 **Testing** - Add automated tests
- 🌍 **Translations** - Add language support
- ♿ **Accessibility** - Improve accessibility
- 🔒 **Security** - Enhance security measures

### Reporting Issues

When reporting bugs, please include:

- WordPress version
- WooCommerce version
- PHP version
- Plugin version
- Steps to reproduce
- Expected behavior
- Actual behavior
- Error messages (from browser console and WordPress debug log)
- Screenshots if applicable

### Feature Requests

We love new ideas! When suggesting features:

- Describe the use case
- Explain the expected behavior
- Provide examples if possible
- Consider backward compatibility

## 📚 Documentation

- [Setup Guide](https://100pay.co/blog/how-to-accept-crypto-payments-on-your-wordpress-woocommerce-store-with-100-pay-checkout)
- [100Pay API Documentation](https://100pay.co/docs)
- [100Pay Dashboard](https://dashboard.100pay.co)
- [Support](https://100pay.co/support)

## 🔒 Security

### Reporting Security Issues

If you discover a security vulnerability, please email security@100pay.co instead of using the issue tracker.

### Security Features

- Webhook verification with token authentication
- Duplicate transaction prevention
- Currency and amount validation
- Secure credential storage (password fields)
- ABSPATH security checks
- SQL injection prevention (prepared statements)

## 📄 License

This plugin is licensed under the GPLv2 (or later).

## 💬 Support

- **Documentation**: [100Pay Docs](https://100pay.co/docs)
- **Email**: support@100pay.co
- **Website**: [100pay.co](https://100pay.co)

## 🙏 Credits

Developed by [Chika Precious Benjamin](https://100pay.co)

Maintained by the 100Pay team and community contributors.

## 📝 Changelog

### Version 1.1.0 (2025-11-09)

**Critical Fixes:**
- Fixed ABSPATH constant redefinition causing WordPress crashes
- Fixed WooCommerce dependency checking
- Fixed payment gateway not appearing in settings
- Fixed webhook 404 errors (now uses wc-api endpoint)
- Fixed currency validation logic error
- Fixed underpaid status detection bug
- Fixed transaction save error (wpdb return value)

**New Features:**
- Integrated official 100Pay checkout library
- Added "Pay Now" button for pending orders
- Added customizable modal width setting
- Added beautiful branded setup page
- Added payment status notifications
- Added order notes for audit trail
- Added payment retry functionality
- Added comprehensive error handling

**Security:**
- Changed secret key field to password type
- Changed verification token field to password type
- Enhanced webhook security validation
- Added duplicate transaction prevention

**UX Improvements:**
- Added setup guide with documentation link
- Added helpful field descriptions
- Added visual payment notifications
- Added prevent-navigation during payment
- Improved error messages
- Mobile-responsive design

### Version 1.0.0 (Initial Release)

- Initial plugin release
- Basic payment gateway functionality
- Webhook integration
- Custom order statuses

## 🌟 Show Your Support

If you find this plugin helpful, please:
- ⭐ Star the repository
- 🐛 Report bugs
- 💡 Suggest features
- 🤝 Contribute code
- 📢 Share with others

---

**Made with ❤️ by the 100Pay team** 100Pay WooCommerce Payment Gateway

![100Pay Logo](https://100pay.co/img/100pay-logo.svg)

Accept cryptocurrency payments on your WooCommerce store with 100Pay. Enable your customers to pay with 22+ cryptocurrencies, stablecoins, bank transfers, and cards - all in one seamless checkout experience.

![100Pay Checkout Preview](https://res.cloudinary.com/estaterally/image/upload/v1762649945/Screenshot_2025-11-09_at_01.57.35_gvbcwx.png)

## 🚀 Features

### Payment Options
- **22+ Cryptocurrencies** - Bitcoin, Ethereum, Litecoin, and more
- **Stablecoins** - USDT, USDC, DAI, BUSD
- **Traditional Methods** - Bank transfer, debit/credit cards
- **Multiple Networks** - Ethereum, BSC, Polygon, Tron, Avalanche

### Plugin Features
- ✅ **Seamless Integration** - Works perfectly with WooCommerce
- ✅ **No Redirects** - Payment modal opens on your site
- ✅ **Instant Updates** - Automatic order status via webhooks
- ✅ **Retry Payments** - "Pay Now" button for pending orders
- ✅ **Custom Statuses** - Handle overpaid/underpaid scenarios
- ✅ **Mobile Optimized** - Responsive design for all devices
- ✅ **Secure** - Bank-grade encryption and fraud protection
- ✅ **Easy Setup** - Configure in minutes with guided setup

## 📋 Requirements

- WordPress 5.0 or higher
- WooCommerce 3.0 or higher
- PHP 7.4 or higher
- SSL Certificate (HTTPS) recommended
- 100Pay merchant account ([Sign up here](https://100pay.co))

## 📦 Installation

### Option 1: WordPress Plugin Directory (Recommended)

1. Go to **WordPress Admin → Plugins → Add New**
2. Search for **"100Pay WooCommerce Payment Gateway"**
3. Click **Install Now** then **Activate**

### Option 2: Manual Installation

1. Download the latest release ZIP file
2. Go to **WordPress Admin → Plugins → Add New → Upload Plugin**
3. Choose the downloaded ZIP file
4. Click **Install Now** then **Activate**

### Option 3: FTP Upload

1. Download and extract the plugin ZIP file
2. Upload the `100pay` folder to `/wp-content/plugins/`
3. Go to **WordPress Admin → Plugins**
4. Activate **100Pay Checkout for WooCommerce**

**Important:** Ensure WooCommerce is installed and activated before activating this plugin.

## ⚙️ Configuration

### Step 1: Get Your 100Pay Credentials

1. Log in to your [100Pay Dashboard](https://dashboard.100pay.co)
2. Navigate to **Settings → API Keys**
3. Copy your credentials:
   - Business Name
   - Public Key
   - Secret Key (API Key)
4. Go to **Settings → Webhooks**
5. Copy your Verification Token

📖 **Detailed Guide:** [How to Accept Crypto Payments on WordPress](https://100pay.co/blog/how-to-accept-crypto-payments-on-your-wordpress-woocommerce-store-with-100-pay-checkout)

### Step 2: Configure Plugin Settings

1. Go to **WooCommerce → Settings → Payments**
2. Find **100Pay Payment Gateway** and click **Manage**
3. Configure the following:

| Field | Description | Required |
|-------|-------------|----------|
| Enable/Disable | Check to enable the gateway | Yes |
| Payment Method Name | Display name for customers | Yes |
| Description | Customer-facing description | Yes |
| Business Name | Your 100Pay business name | Yes |
| Public Key | From 100Pay dashboard | Yes |
| Secret Key | Your API key (masked) | Yes |
| Verification Token | For webhook security (masked) | Yes |
| Webhook URL | Auto-generated (read-only) | Auto |
| Modal Width | Optional (default: 500px) | No |

4. Click **Save changes**

### Step 3: Configure Webhook

1. Copy the **Webhook URL** from plugin settings (format: `http://yoursite.com/?wc-api=100pay_webhook`)
2. Go to [100Pay Dashboard → Webhooks](https://dashboard.100pay.co/settings/webhooks)
3. Paste the webhook URL
4. Save settings

✅ **Done!** Your store is now ready to accept crypto payments!

## 🎯 How It Works

### For Customers

1. Add products to cart
2. Proceed to checkout
3. Select "Pay with Crypto/Card" (or your custom name)
4. Complete checkout form
5. Payment modal opens automatically
6. Choose payment method (crypto, card, bank)
7. Complete payment
8. See success message
9. Order confirmed!

### For Store Owners

1. Customer completes payment
2. Webhook automatically updates order to "Processing"
3. Order note added with payment details
4. Transaction saved to database
5. You fulfill the order
6. Mark as "Completed" when shipped

## 🔧 Troubleshooting

### Payment Modal Doesn't Open

**Solutions:**
- Check browser console for JavaScript errors
- Verify API key is correctly configured
- Ensure `https://js.100pay.co` is accessible
- Clear browser cache
- Disable conflicting plugins temporarily

### Webhook Not Working

**Solutions:**
- Verify webhook URL is correct in 100Pay dashboard
- Check verification token matches
- Ensure site is accessible from internet (not localhost for production)
- Check WordPress debug log: `/wp-content/debug.log`
- Test webhook with Postman

### Order Status Not Updating

**Solutions:**
- Verify webhook is configured correctly
- Check order notes for webhook activity
- Enable WordPress debugging
- Check debug log for webhook errors
- Verify verification token is correct

### Gateway Not Showing in WooCommerce

**Solutions:**
- Ensure WooCommerce is installed and activated
- Deactivate and reactivate 100Pay plugin
- Check for PHP errors in debug log
- Verify WordPress and WooCommerce versions meet requirements

## 🤝 Contributing

We welcome contributions! Here's how you can help improve the plugin:

### Ways to Contribute

- 🐛 **Report Bugs** - Found an issue? Let us know!
- ✨ **Suggest Features** - Have ideas? We'd love to hear them!
- 💻 **Submit Code** - Fix bugs or add features
- 📝 **Improve Docs** - Help others understand the plugin
- 🌍 **Translate** - Add support for your language
- ⭐ **Star the Repo** - Show your support!

### Development Setup

1. **Clone the repository**
   ```bash
   git clone https://github.com/100pay/100pay-wordpress-plugin.git
   cd 100pay-wordpress-plugin
   ```

2. **Set up local WordPress**
   - Install WordPress locally (XAMPP, Local, MAMP, or Docker)
   - Install WooCommerce plugin
   - Copy plugin to `wp-content/plugins/100pay`

3. **Enable debugging**
   Add to `wp-config.php`:
   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   define('WP_DEBUG_DISPLAY', false);
   ```

4. **Make your changes**
   - Edit files in the `100pay` folder
   - Test thoroughly
   - Check for errors in `/wp-content/debug.log`

### Coding Standards

- Follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- Use meaningful variable names
- Add PHPDoc comments to functions
- Keep functions focused and single-purpose
- Sanitize and validate all inputs
- Escape all outputs
- Use WordPress functions (don't reinvent the wheel)

### Pull Request Process

1. **Create a feature branch**
   ```bash
   git checkout -b feature/amazing-feature
   ```

2. **Make your changes**
   - Write clean, documented code
   - Test thoroughly
   - Follow coding standards

3. **Commit your changes**
   ```bash
   git commit -m "Add: amazing feature description"
   ```

4. **Push to your fork**
   ```bash
   git push origin feature/amazing-feature
   ```

5. **Open a Pull Request**
   - Describe what you changed and why
   - Reference any related issues
   - Include screenshots for UI changes
   - List testing steps

### Testing Checklist

Before submitting a PR, ensure:

- [ ] Plugin activates without errors
- [ ] Works with WooCommerce active
- [ ] Shows admin notice when WooCommerce inactive
- [ ] Payment gateway appears in WooCommerce settings
- [ ] Payment modal opens correctly
- [ ] Payment completes successfully
- [ ] Webhook updates order status
- [ ] Order notes are added
- [ ] Transaction saved to database
- [ ] No PHP errors in debug log
- [ ] No JavaScript errors in console
- [ ] Works on mobile devices
- [ ] Follows WordPress coding standards

### Reporting Issues

When reporting bugs, include:

**Environment:**
- WordPress version
- WooCommerce version
- PHP version
- Plugin version
- Theme name

**Issue Details:**
- Clear description
- Steps to reproduce
- Expected behavior
- Actual behavior
- Error messages (PHP and JavaScript)
- Screenshots if applicable

**Example:**
```
**Environment:**
- WordPress 6.4
- WooCommerce 8.5
- PHP 8.1
- 100Pay Plugin 1.1.0
- Storefront theme

**Issue:**
Payment modal doesn't open after clicking "Place Order"

**Steps:**
1. Add product to cart
2. Go to checkout
3. Select 100Pay payment method
4. Click "Place Order"
5. Redirected to order page but modal doesn't open

**Expected:** Payment modal should open automatically
**Actual:** Page loads but no modal appears

**Console Error:** 
shop100Pay is not defined

**Screenshot:** [attach screenshot]
```

## 📚 Resources

- **Setup Guide**: [Complete WordPress Integration Guide](https://100pay.co/blog/how-to-accept-crypto-payments-on-your-wordpress-woocommerce-store-with-100-pay-checkout)
- **API Documentation**: [100Pay API Docs](https://100pay.co/docs)
- **Dashboard**: [100Pay Merchant Dashboard](https://dashboard.100pay.co)
- **Support**: support@100pay.co
- **Website**: [100pay.co](https://100pay.co)

## 🔒 Security

### Reporting Security Vulnerabilities

If you discover a security vulnerability, please email **security@100pay.co** instead of using the public issue tracker. We take security seriously and will respond promptly.

### Security Features

- ✅ Webhook verification with token authentication
- ✅ Duplicate transaction prevention
- ✅ Currency and amount validation
- ✅ Secure credential storage (masked fields)
- ✅ SQL injection prevention (prepared statements)
- ✅ XSS prevention (escaped outputs)
- ✅ CSRF protection (WordPress nonces)

## 📄 License

This plugin is licensed under the **GPLv2 (or later)**.

```
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

## 📝 Changelog

### Version 1.1.0 (2025-11-09)

**🔥 Critical Fixes:**
- Fixed ABSPATH constant redefinition causing WordPress crashes
- Fixed WooCommerce dependency checking to prevent fatal errors
- Fixed payment gateway not appearing in WooCommerce settings
- Fixed webhook 404 errors (migrated to wc-api endpoint)
- Fixed currency validation logic error
- Fixed underpaid status detection bug
- Fixed transaction save error (wpdb->insert return value check)
- Fixed order status now correctly set to "processing" for successful payments

**✨ New Features:**
- Integrated official 100Pay checkout library from js.100pay.co
- Added "Pay Now" button on order page for pending payments
- Added customizable modal width setting
- Added beautiful branded setup page with logo and preview
- Added visual payment status notifications
- Added payment retry functionality
- Added order notes for complete audit trail
- Added comprehensive error handling and logging

**🔒 Security Improvements:**
- Secret key field now masked (password type)
- Verification token field now masked (password type)
- Enhanced webhook verification with multiple header formats
- Added duplicate transaction prevention
- Added currency and amount validation
- Added request structure validation

**🎨 UX Improvements:**
- Beautiful gradient setup page with 100Pay branding
- Feature cards highlighting key benefits
- Setup guide link prominently displayed
- Helpful descriptions for all fields
- Visual notifications for payment status
- Prevent page navigation during payment
- Clear error messages with retry options
- Mobile-responsive design throughout

**🛠️ Technical Improvements:**
- Simplified webhook system using WooCommerce's wc-api
- Improved library loading with retry mechanism
- Enhanced error logging for debugging
- Better header detection for verification token
- Automatic permalink flushing on activation
- Cleaner, more maintainable code

### Version 1.0.0 (Initial Release)

- Initial plugin release
- Basic payment gateway functionality
- Webhook integration for order updates
- Custom order statuses (overpaid, underpaid)
- WooCommerce Blocks support

## 🙏 Acknowledgments

Special thanks to:
- The WordPress community
- WooCommerce team
- All contributors and testers
- 100Pay users providing feedback

## 💬 Support & Community

- **Email**: support@100pay.co
- **Website**: [100pay.co](https://100pay.co)
- **Documentation**: [100Pay Docs](https://100pay.co/docs)
- **Dashboard**: [100Pay Dashboard](https://dashboard.100pay.co)

---

**Made with ❤️ by the 100Pay team**

*Empowering businesses to accept crypto payments at scale*

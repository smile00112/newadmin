=== Tochka Bank: Internet-acquiring ===
Contributors: tochkabank
Tags: woocommerce, payment gateway, tochka, payments, credit card
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
WC requires at least: 8.0
WC tested up to: 10.1.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Payment gateway for Tochka Bank in WooCommerce. Accept payments via bank cards and Faster Payments System (SBP).

== Description ==

This plugin integrates the Tochka Bank payment gateway with your WooCommerce store, allowing you to securely and conveniently accept online payments from your customers.

The plugin provides a seamless experience for the buyer: after placing an order, they are redirected to the secure Tochka Bank payment page, where they can choose a convenient method — a bank card or SBP.

**Key Features:**

*   **Payment Acceptance:** Bank cards (MIR, Visa, Mastercard) and the Faster Payments System (SBP).
*   **Fiscalization Support (54-FZ):** Automatic transfer of order composition data to the bank for generating fiscal receipts (requires a cloud-based online cash register service to be enabled in your Tochka Bank account).
*   **Full WooCommerce Integration:** Correct handling of order statuses and automatic cart clearing after successful payment.
*   **Block Checkout Compatibility:** Support for the modern block-based checkout experience (WooCommerce Blocks).
*   **HPOS Compatible:** The plugin is fully compatible with High-Performance Order Storage.
*   **Easy Configuration:** A user-friendly and straightforward settings page right in the WooCommerce admin panel.
*   **Security:** The entire payment data processing occurs on the secure side of Tochka Bank.

== External Services ==

This plugin relies on the **Tochka Bank Payment API** to process payments.

*   **Service:** Tochka Bank Internet Acquiring
*   **Feature:** Payment processing and fiscalization
*   **Data Sent:** Order ID, order amount, customer name, email, phone number, and cart items (for fiscalization).
*   **Privacy Policy:** https://tochka.com/links/personal-data/
*   **Terms of Service:** https://tochka.com/links/bank-rules-ao/update-20251202/

== Installation ==

**Method 1: Installation via WordPress Admin Panel (Recommended)**

1.  Navigate to `Plugins > Add New` in your admin panel.
2.  In the search bar, type "**Tochka Bank: Internet-acquiring**".
3.  Find the plugin in the search results and click "Install Now".
4.  After installation, click "**Activate**".

**Method 2: Manual Installation**

1.  Download the plugin's ZIP archive from the WordPress.org page.
2.  Go to `Plugins > Add New` and click the "**Upload Plugin**" button at the top.
3.  Choose the downloaded ZIP file and click "**Install Now**".
4.  After installation, activate the plugin.

**After Activation:**

1.  Go to `WooCommerce > Settings > Payments`.
2.  Find the "**Точка Банк: Интернет-эквайринг**" (Tochka Bank: Internet-acquiring) payment method and click "**Manage**".
3.  Enter your credentials (`Login (Логин)` and `Secret Key (Секретное слово)`) provided by Tochka Bank.
4.  Configure the remaining settings as needed and save changes. The payment gateway is ready to use!

== Frequently Asked Questions ==

= Where do I get the Login and Secret Key? =

You can find this information in your Tochka Bank merchant dashboard under `Services` - `Internet Acquiring` - Select your site - `Integrations` - Select 'Wordpress' from the dropdown list.

= How does fiscalization (54-FZ) work? =

If the **"Send receipt data"** option is enabled in the plugin settings, the plugin automatically collects information about products, discounts, shipping, and VAT rates from the order and transmits it to the bank in a structured format. The bank, in turn, uses this data to send to the online cash register and generate a fiscal receipt, if a cloud-based online cash register service is enabled in your Tochka Bank account.

== Screenshots ==

1. The plugin settings page in the WooCommerce admin panel.
2. The "Tochka Bank" payment method selection on the checkout page.

== Changelog ==

= 1.0.0 =
* Initial public release.

== Upgrade Notice ==

= 1.0.0 =
This is the first version of the plugin. Please review and save your settings carefully after installation.
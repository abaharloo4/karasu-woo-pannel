<!--
Version: 1.0.3
Date: 2026-06-23
-->
# Installation Guide — KarasuWooPannel

This guide outlines the system requirements, installation, and setup instructions for **KarasuWooPannel**.

---

## 1. Prerequisites & Dependencies

Before installing, ensure the host environment meets the following requirements:
* **WordPress:** Version 6.0 or higher.
* **WooCommerce:** Version 7.0 or higher (Must be active).
* **PHP:** Version 8.0 or higher (PHP 8.1 / 8.2 recommended).
* **Elementor (Optional):** Required to use the custom design-configurable login button widget.
* **MeliPayamak Account (Optional):** Required to send SMS transaction notifications.

---

## 2. Installation Steps

### Method A: Install via Zip Archive
1. Download the latest packaged release ZIP `karasu-woo-pannel.zip`.
2. Go to **WordPress Admin -> Plugins -> Add New -> Upload Plugin**.
3. Choose the ZIP file and click **Install Now**.
4. Click **Activate Plugin**.

### Method B: Manual FTP/Local Setup
1. Copy the `karasu-woo-pannel` folder into `/wp-content/plugins/` directory of your WordPress site.
2. Go to **WordPress Admin -> Plugins -> Installed Plugins**.
3. Locate **KarasuWooPannel** and click **Activate**.

---

## 3. Post-Installation Configuration

Once activated, configure the plugin defaults:
1. Navigate to the new menu **WooCommerce -> KarasuWooPannel** in your WP Admin Dashboard.
2. **Panel Slug:** Configure the custom URL path for your panel (default is `store-admin`).
3. **Session Lifetime:** Set the cookie session validity in hours (default is `24`).
4. **SMS Credentials:** If using MeliPayamak, fill in your Username, Password, and Line number. Save settings.
5. **Enable Notification Events:** Toggle customer order status updates and admin stock warnings.
6. **Save Settings:** Save configurations to flush rewrite rules.

---

## 4. Verification & Testing

1. **Standalone Panel URL:** Visit `http://yourdomain.com/store-admin/` (or your custom slug) to load the RTL login screen.
2. **Role Restrictions:** Create a user with the role `shop_manager_custom`. Log in to the standalone panel. Verify they can manage products, categories, coupons, and view reports, but cannot access `/wp-admin/`.
3. **Elementor Widget:** Open any page in Elementor editor. Search for the "دکمه ورود KarasuWooPannel" widget under the WooCommerce category. Customize colors, hover states, and typography.
4. **GitHub Updates:** Push tags in format `vX.Y.Z` to trigger automatic update detections in WordPress.

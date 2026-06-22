=== KarasuWooPannel ===
Contributors: abaharloo4
Tags: woocommerce, panel, shop manager, rtl, melipayamak, chart, reports, coupons, elementor
Requires at least: 6.0
Tested up to: 6.5
Stable tag: 1.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A completely independent, RTL, TailwindCSS-based store management panel for WooCommerce.

== Description ==

KarasuWooPannel is a premium administration panel designed specifically for WooCommerce shop managers. It decouples the store management workflow from `/wp-admin` by providing a secure, high-performance, and responsive standalone dashboard.

= Features =
* **Standalone Panel:** De-coupled from wp-admin to protect core site configurations.
* **Variable & Simple Products:** Create and edit products, permute attributes, and manage variation details.
* **Categories Management:** Nested category tree structure with live CRUD operations.
* **Solar Jalali Reports:** High-performance reports utilizing transients caching and visual Chart.js dashboards.
* **Coupons Management:** Native WC Coupon integrations with Jalali date conversions.
* **SMS Notifications Gateway:** Automated SMS alerts via MeliPayamak REST API.
* **Elementor Login Widget:** Fully style-customizable login button widget for Elementor.

== Installation ==

1. Upload the `karasu-woo-pannel` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to WooCommerce -> KarasuWooPannel in your WP Admin dashboard to configure your SMS credentials and custom panel URL.
4. Access the store panel via the custom URL slug configured.

== Frequently Asked Questions ==

= Does it require WooCommerce? =
Yes, this plugin is an extension for WooCommerce and will automatically deactivate if WooCommerce is missing.

= How do automatic updates work? =
Updates are queried directly from the public GitHub release tags. When a new release is published on GitHub, WordPress will notify you in your plugins list.

== Changelog ==

= 1.0.1 =
* Fixed PHP reserved keyword activation error.
* Fixed abstract method declaration conflict in WSM_REST_Controller.
* Refactored automatic updater to use upgrader_source_selection hook.

= 1.0.0 =
* Initial release containing products editor, variation combos, categories tree, Jalali reports, MeliPayamak SMS notifier, Elementor login widget, and GitHub updater.

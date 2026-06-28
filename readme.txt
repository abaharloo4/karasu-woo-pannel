=== KarasuWooPannel ===
Contributors: abaharloo4
Tags: woocommerce, panel, shop manager, rtl, melipayamak, chart, reports, coupons, elementor
Requires at least: 6.0
Tested up to: 6.5
Stable tag: 1.1.8
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

= 1.1.8 =
* Fixed receipt file lookup 404 error when High-Performance Order Storage (HPOS) is active.
* Swapped get_post_meta with HPOS-safe order meta getter.
* Redesigned receipt images in order details to automatically show in-line preview without clicking.
* Created a markdown integration guide for the custom card-to-card plugin compatibility.

= 1.1.7 =
* Fixed receipt file access: now accepts both panel session and standard WordPress admin cookies.
* Added download and preview buttons for receipt files in order details.
* Added fullscreen lightbox modal for inline image receipt preview with download/open-in-tab actions.
* Added ?action=download query parameter support for forced file download.

= 1.1.6 =
* Fixed PHP syntax bracket parse error inside class-wsm-sms-service.php send_sms method.

= 1.1.5 =
* Added support for Melipayamak API Token / Auth Key console sending, bypassing username/password requirements.
* Added a detailed explanation/guide for Melipayamak API return and status codes inside the Logs Viewer tab.
* Fixed configured status checks in status tab when password option is empty in post rendering.

= 1.1.4 =
* Added Logs Viewer tab in WP-Admin featuring SMS logs table and custom error log viewer.
* Added support for Melipayamak shared pattern/template service sending (BaseServiceNumberShared).
* Integrated custom fallback to user's dedicated line standard SendSMS when pattern sending fails.

= 1.1.3 =
* Localized all plugin titles and header brands to Persian when system locale is set to Farsi.
* Added custom color pickers for primary and accent theme styling on the frontend panel.
* Split page custom editors into separate HTML, CSS, and JS collapsible input areas.
* Added settings to globally toggle each panel section/page on or off.
* Enforced strict UAC capability validation for all administrators to allow selective section disabling.

= 1.1.2 =
* Added ability to customize and edit all frontend pages via the WP Admin panel.
* Fixed stock status loading error by safeguarding .toLocaleString() calls.
* Added support for displaying card-to-card receipts in order details.

= 1.1.1 =
* Fixed session token exposure in JavaScript by removing raw tokens and relying on secure HttpOnly cookies.
* Enabled SSL verification in automatic updater and added host validation with path traversal check protection.
* Prevented IP spoofing by adding Trust Proxies setting and implemented dual rate limiting for IP and username.
* Added server-side validation for media uploads and enforced 5MB limit.
* Encrypted SMS password storage in database using AES-256-CBC with salts.
* Added dedicated UAC capability wsm_manage_sms for SMS settings.
* Added daily database tables cleanup cron job.
* Optimized stock level checks query in database.
* Integrated local copies of Jalali datepicker to eliminate external CDN dependencies.

= 1.0.10 =
* Added User Access Control (UAC) tab to WP Admin Settings page to manage individual user capabilities.
* Added SMS Templates editing tab to WP Admin Settings page.
* Dynamically granted access capabilities to built-in administrator and WooCommerce shop_manager roles to prevent redirection issues.
* Added password visibility toggle (eye icon) to the standalone login page.

= 1.0.3 =
* Hooked to site_transient_update_plugins for dynamic update injection on retrieval.
* Added force-check bypass logic in updater to clear GitHub release info cache when clicking "Check Again".

= 1.0.2 =
* Redesigned WP Admin settings page into a premium tabbed layout with AJAX SMS gateway validation.
* Fixed empty standalone panel login page caused by mismatched template directory path.

= 1.0.1 =
* Fixed PHP reserved keyword activation error.
* Fixed abstract method declaration conflict in WSM_REST_Controller.
* Refactored automatic updater to use upgrader_source_selection hook.

= 1.0.0 =
* Initial release containing products editor, variation combos, categories tree, Jalali reports, MeliPayamak SMS notifier, Elementor login widget, and GitHub updater.

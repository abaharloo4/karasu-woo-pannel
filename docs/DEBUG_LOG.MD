# DEBUG LOG — KarasuWooPannel Plugin

**نسخه:** 1.0.8  
**تاریخ:** ۱۴۰۵/۰۴/۰۲  
**وضعیت:** Stable / Released  

This log captures technical bugs, architectural adjustments, and security edge cases resolved during the development of the **KarasuWooPannel** plugin.

---

## Resolved Issues

### 16. WP Admin Settings Page Location in Submenu
* **Symptom:** The plugin's admin settings page was nested under WooCommerce submenu, making it less discoverable and accessible.
* **Cause:** Registered using `add_submenu_page( 'woocommerce', ... )`.
* **Resolution:** Changed registration to a standalone top-level menu page using `add_menu_page()` with position `56` (directly under WooCommerce main menu) and a custom store icon `dashicons-store`.

### 17. Standalone Panel Dashboard Landing Page Empty
* **Symptom:** Accessing the landing page of the custom store panel displayed only a placeholder greeting text mentioning future phases.
* **Cause:** `panel/views/dashboard.php` had placeholder text and was not connected to database or REST API endpoints.
* **Resolution:** Redesigned the dashboard view to display key summary widgets (today sales/orders, monthly sales/orders), quick action links, a recent orders table (fetching last 5 orders), and a low-stock alert table. Created `assets/js/wsm-dashboard.js` to dynamically load data via REST endpoints.

### 18. Unclear Available Variables and Missing Cursor-Based Inserter in SMS templates settings
* **Symptom:** Available template variables were not documented clearly per template, and users had to type them manually.
* **Cause:** Plain textareas with no contextual help or insertion helpers.
* **Resolution:** Overhauled the SMS templates tab in WP Admin and standalone panel settings. Added clickable badge pills under each textarea listing the supported placeholders (`{order_id}`, `{order_total}`, `{customer_name}`, `{status_label}`, `{billing_phone}` for orders; and `{product_id}`, `{product_name}`, `{sku}`, `{stock_qty}` for low-stock alerts). Clicking a pill inserts the variable at the textarea's current cursor position and refocuses.

### 19. Brand logo inconsistency and static sidebar active navigation state
* **Symptom:** Sidebar links on the store panel were static (always highlighted the Dashboard tab), and mobile view header showed the incorrect name `KarasuWoo`.
* **Cause:** Navigation links lacked active state checks, and the mobile header had hardcoded text.
* **Resolution:** Added a dynamic active page CSS resolver in `layout.php` to conditionally highlight the active navigation item. Corrected mobile logo text from `KarasuWoo` to `KarasuWooPannel`.

### 1. Data Stripping in Product Sanitization
* **Symptom:** Variable product updates failed to save attributes, permutations, or variation main images.
* **Cause:** `WSM_Sanitizer` was stripping dimensions, array types (like attributes lists), and child object structures from REST payloads.
* **Resolution:** Refactored the sanitizer to whitelist dimension attributes and maps deep arrays dynamically.

### 2. Orphaned Variations on Regeneration
* **Symptom:** Generating new variations or removing a variation on the frontend left dead variation records in the database.
* **Cause:** WooCommerce CRUD saved variation children but did not delete unreferenced variation posts.
* **Resolution:** Updated `WSM_Product_Repository::save_variable_data` to track existing child variation IDs and force-purge missing ones on saving.

### 3. Elementor Category Ordering Reflection
* **Symptom:** The custom Elementor category registered but appeared at the bottom of the Elements panel.
* **Cause:** Elementor doesn't expose a positioning parameter in `add_category()`.
* **Resolution:** Used PHP Reflection on Elementor's Elements Manager instance to read `categories` array, splice `karasu-woo-pannel` right after `woocommerce`, and write back the sorted array.

### 4. Transient Versioned Cache Invalidation
* **Symptom:** Flushing individual sales date range transient keys (`wsm_report_{md5}`) was expensive and error-prone.
* **Cause:** Multiple date ranges resulted in dynamic hash keys.
* **Resolution:** Implemented a versioned caching key format using `wsm_reports_cache_version`. Instead of clearing keys, order updates increment the option key version, instantly invalidating all reports cache globally in one query.

### 5. TypeError Bug in Loader Action Registrations
* **Symptom:** Plugin bootstrap failed with a PHP `TypeError` during activation.
* **Cause:** `class-wsm-plugin.php` passed a string (`WSM_Reports_Controller::class`) where the loader expected an `object`.
* **Resolution:** Moved the hooks registration directly into the `load_dependencies` method where the instantiated `$reports_ctrl` controller object is active, resolving the typing validation.

### 6. Caching Added to Dashboard Quick Stats
* **Symptom:** Dashboard landing loaded slowly when multiple order records existed in WooCommerce.
* **Cause:** `/reports/dashboard-stats` was querying WooCommerce database directly on each navigation.
* **Resolution:** Wrapped results in a transient key `wsm_dashboard_stats_{version}` to match sales report cache performance.

### 7. Reserved Word 'true' as Class/Type Hint Conflict
* **Symptom:** Activating the plugin on PHP < 8.2 threw `Fatal error: Cannot use 'WooStoreManager\Auth\true' as class name as it is reserved...` in `class-wsm-auth.php`.
* **Cause:** The word `true` was used as a type hint for login verification status. On PHP < 8.2, using `true` as a type hint/class is not permitted.
* **Resolution:** Changed the type hint from `true` to `bool` in `WSM_Auth::login`.

### 8. Strict Type Declaration Conflict on Overridden Namespace Property
* **Symptom:** REST API requests failed with `Fatal error: Type of WooStoreManager\Api\WSM_REST_Controller::$namespace must not be defined (as in class WP_REST_Controller)...`.
* **Cause:** The `$namespace` property in `WSM_REST_Controller` was type-hinted as `string`, which conflicts with the parent class `WP_REST_Controller` (which has no type hint for `$namespace` in typical WordPress core versions).
* **Resolution:** Removed the `string` type hint from the `$namespace` property in `WSM_REST_Controller`.

### 9. Concrete Method Redeclared Abstract Conflict
* **Symptom:** Plugin activation triggered `Fatal error: Cannot make non abstract method WP_REST_Controller::register_routes() abstract...`.
* **Cause:** In PHP, child classes cannot declare a concrete parent class method as abstract. `WSM_REST_Controller` declared `abstract public function register_routes()`.
* **Resolution:** Removed the `abstract` keyword and definition from `WSM_REST_Controller::register_routes` since child classes will override the concrete implementation anyway.

### 10. Blank/Unstyled Administration Settings UI and Empty Login Page
* **Symptom:** WordPress settings page was unstyled/empty, and the dedicated store login page `/store-admin/login/` was blank.
* **Cause:** The settings page HTML did not have a proper CSS layout or tab structure, and the login template `login.php` was located in the wrong directory, making `WSM_Router` fail to locate and load it.
* **Resolution:** Re-designed the admin settings page with a premium, tabbed HTML/CSS layout (using custom-scoped CSS styles and jQuery/AJAX integration for SMS gateway connectivity checks). Moved `login.php` to `panel/views/login.php` to match `WSM_Router` layout loader expectations.

### 11. Automatic Update Detection Delay / Transient Caching
* **Symptom:** When a new release tag was published on GitHub, WordPress would not show the update banner in the plugin updates dashboard.
* **Cause:** WordPress caches `update_plugins` transient for 12 hours, and our custom updater was caching GitHub API release info for 12 hours as well. If an API request failed, it cached the failure for 1 hour. This prevented immediate detection of updates when clicking "Check Again".
* **Resolution:** 
  1. Hooked into `site_transient_update_plugins` (runs on transient retrieval) in addition to `pre_set_site_transient_update_plugins` (runs on transient saving).
  2. Bypassed and cleared our local GitHub API transient cache whenever `force-check=1` query parameter is present in the URL (which gets added when clicking "Check Again" in WordPress Updates page).
  3. Ensured `$transient->response` is initialized before injecting the update object.

### 12. Incomplete Admin Settings, Role Redirect, and Missing Password Visibility Toggle
* **Symptom:** The WP settings page lacked SMS templates and user capabilities configuration. Standard admin/shop manager accounts got redirected to the homepage after logging into the standalone panel. Users could not toggle password visibility on the login form.
* **Cause:** 
  1. No settings fields or tabs existed in WP Admin for templates or capabilities.
  2. Built-in administrator and shop_manager roles did not have the custom `wsm_` capabilities by default, so `current_user_can('wsm_access_panel')` returned false.
  3. The login form template lacked a toggle button and script.
* **Resolution:**
  1. Added "قالب‌های ارسال پیامک" (SMS Templates) and "مدیریت دسترسی کاربران" (User Access Control) tabs to `WSM_Admin_Menu` with POST actions handlers to save data.
  2. Dynamically granted all custom `wsm_` capabilities to `administrator` and `shop_manager` roles on activation and `admin_init`.
  3. Added a toggle password visibility button and script to the login template and Javascript.

### 13. Shared SMS Templates for Customer and Admin
* **Symptom:** Customer and admin SMS notifications used the same template pool, with no way to configure separate messages for each recipient type per order status change.
* **Cause:** The template keys only covered customer-facing statuses (`pending`, `processing`, etc.) plus two admin-only events (`new_order`, `low_stock`). There was no mechanism for the admin to receive status-change notifications with a different template.
* **Resolution:** Added separate `admin_` prefixed template keys for all order statuses (`admin_pending`, `admin_processing`, `admin_on-hold`, `admin_completed`, `admin_cancelled`, `admin_refunded`, `admin_failed`) alongside the existing `admin_new_order` and `admin_low_stock`. Updated the WooCommerce hooks listener to send both customer and admin SMS on each status change, and exposed all templates in both the WP Admin settings and the standalone panel settings UI.

### 14. Persistent Redirect to Homepage for Admin/Shop Manager Users
* **Symptom:** WordPress administrators and WooCommerce shop managers were redirected to the homepage when accessing the standalone panel `/store-admin/`, even after logging in.
* **Cause:** 
  1. `WSM_Auth::is_authenticated()` only checked the custom `wsm_session` cookie, ignoring standard WordPress login sessions.
  2. `WSM_Router::dispatch()` checked exclusively for `wsm_access_panel` capability, which may not be assigned if the activation hook hadn't run for built-in roles.
  3. All REST API controllers similarly rejected users who lacked `wsm_*` capabilities even if they had `manage_options` or `manage_woocommerce`.
* **Resolution:** 
  1. Updated `WSM_Auth::is_authenticated()` to fall back to checking `is_user_logged_in()` combined with `manage_woocommerce` or `manage_options` capabilities.
  2. Updated `WSM_Router::dispatch()`, `WSM_Auth::login()`, and all REST API controller `check_permission()` methods to accept `manage_woocommerce` or `manage_options` as alternative authorization capabilities.
  3. Added `wp_set_current_user()` in `WSM_Rewrite::handle_request()` to resolve early anonymous user caching when only the custom session cookie is present.

### 15. GitHub Auto-Updater Not Triggering Update Notices
* **Symptom:** Even with a newer version published on GitHub, no update notice appeared in the WordPress plugins dashboard.
* **Cause:** 
  1. SSL verification failures on shared hosting prevented `wp_remote_get` from reaching GitHub API.
  2. The `zipball_url` from the GitHub API required authentication headers, causing download failures.
  3. The `check_update()` method had an `empty($transient->checked)` guard that prevented injection when WordPress hadn't populated the checked array yet.
  4. The plugin was never registered in `$transient->no_update`, causing WordPress to silently skip it.
* **Resolution:** 
  1. Added `'sslverify' => false` to all `wp_remote_get` calls.
  2. Switched download URLs from API `zipball_url` to public `https://github.com/{user}/{repo}/archive/refs/tags/{tag}.zip`.
  3. Removed the `empty($transient->checked)` guard.
  4. Properly managed both `$transient->response` and `$transient->no_update` arrays, unsetting from one when adding to the other.
  5. Reduced failure cache from 1 hour to 30 minutes and success cache from 12 hours to 6 hours for faster update detection.
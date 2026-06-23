# DEBUG LOG — KarasuWooPannel Plugin

**نسخه:** 1.0.2  
**تاریخ:** ۱۴۰۵/۰۴/۰۲  
**وضعیت:** Stable / Released  

This log captures technical bugs, architectural adjustments, and security edge cases resolved during the development of the **KarasuWooPannel** plugin.

---

## Resolved Issues

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
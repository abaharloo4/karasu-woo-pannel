# DEBUG LOG — KarasuWooPannel Plugin

**نسخه:** 1.0.0  
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
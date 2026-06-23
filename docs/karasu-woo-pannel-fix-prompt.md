# پرامپت رفع مشکلات افزونه KarasuWooPannel

> این فایل را مستقیماً به یک ابزار کدنویسی هوش مصنوعی (مثل Claude Code) بدهید و بخواهید همه‌ی موارد زیر را در پروژه‌ی `karasu-woo-pannel` پیاده‌سازی کند. هر مورد شامل فایل، مشکل و راه‌حل دقیق است تا بدون ابهام اجرا شود.

---

## دستور کلی

در پروژه‌ی افزونه‌ی وردپرس `karasu-woo-pannel` (namespace: `WooStoreManager`)، تمام موارد زیر را به ترتیب اولویت اصلاح کن. بعد از هر بخش، تغییرات را خلاصه کن. کدهای فعلی را تا حد امکان حفظ کن و فقط بخش‌های لازم را تغییر بده. نسخه‌ی افزونه را در `karasu-woo-pannel.php` و در ابتدای فایل‌های تغییریافته به `1.1.0` ارتقا بده.

---

## ۱. بحرانی — افشای توکن نشست در جاوااسکریپت (Critical)

**فایل:** `panel/layout.php`

**مشکل:** کوکی `wsm_session` با `httponly => true` ساخته می‌شود اما مقدار خامش این‌طور در `window.wsmConfig` چاپ می‌شود:
```php
sessionToken: '<?php echo esc_js( $_COOKIE['wsm_session'] ?? '' ); ?>'
```
این کار کل حفاظت HttpOnly را خنثی می‌کند و در صورت وجود XSS در هر بخشی از سایت، توکن نشست قابل سرقت است.

**راه‌حل:**
- خط `sessionToken` را کامل از `window.wsmConfig` در `panel/layout.php` حذف کن.
- در `assets/js/wsm-panel.js`، منطق `wsmFetch` را اصلاح کن تا هدر `X-WSM-Token` را اضافه نکند و فقط به ارسال خودکار کوکی (`credentials: 'same-origin'` یا پیش‌فرض fetch) تکیه کند، چون کوکی با `SameSite=Strict` به‌صورت خودکار برای درخواست‌های هم‌مبدا ارسال می‌شود.
- در `includes/Auth/class-wsm-auth.php` و `includes/Router/class-wsm-rewrite.php`، منطق خواندن `X-WSM-Token` از هدر در `get_instance_token()` را می‌توانی نگه داری (برای استفاده‌های احتمالی API خارجی) اما دیگر نباید توسط فرانت‌اند پنل استفاده شود.

---

## ۲. بحرانی — غیرفعال بودن SSL Verify در آپدیتر گیت‌هاب (Critical, RCE Risk)

**فایل:** `includes/Core/class-wsm-github-updater.php`

**مشکل:**
```php
$request_args = [
    'headers'   => [ 'User-Agent' => 'KarasuWooPannel-Updater' ],
    'timeout'   => 15,
    'sslverify' => false,
];
```
غیرفعال بودن بررسی گواهی SSL در دانلود بسته‌ی آپدیت، حمله Man-in-the-Middle و تزریق بسته‌ی مخرب (RCE) را ممکن می‌کند.

**راه‌حل:**
- مقدار `'sslverify' => false` را کامل حذف کن (پیش‌فرض `wp_remote_get` یعنی `true` کافی است).
- یک مرحله‌ی اعتبارسنجی اضافه کن: بعد از دانلود اطلاعات ریلیز، اگر GitHub API یک فیلد checksum/hash منتشر نکرده، حداقل مطمئن شو دامنه‌ی دانلود همیشه `https://github.com` یا `https://api.github.com` است (هیچ‌گاه از URL دلخواه/ورودی کاربر).
- در تابع `rename_source`، قبل از جابه‌جایی پوشه، اطمینان حاصل کن مسیر نهایی همچنان داخل دایرکتوری موقت آپگریدر است (sanity check روی path).

---

## ۳. بالا — دور زدن Rate Limiting با جعل هدر IP (High)

**فایل:** `includes/Auth/class-wsm-rate-limiter.php`

**مشکل:**
```php
$ip_keys = [ 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' ];
```
این هدرها بدون اعتبارسنجی منبع، مستقیماً پذیرفته می‌شوند و مهاجم می‌تواند با تغییر آن‌ها در هر درخواست، قفل Brute-force را دور بزند.

**راه‌حل:**
- یک تنظیم جدید در صفحه‌ی ادمین اضافه کن: `wsm_trust_proxy_headers` (چک‌باکس، پیش‌فرض `false`، با توضیح "فقط در صورتی فعال کنید که سایت پشت Cloudflare یا یک Reverse Proxy معتبر است").
- در `get_client_ip()`: اگر `wsm_trust_proxy_headers` فعال نبود، فقط از `REMOTE_ADDR` استفاده کن. اگر فعال بود، همان منطق فعلی با اولویت `HTTP_CF_CONNECTING_IP` را اجرا کن.
- علاوه بر Rate Limit بر اساس IP، یک Rate Limit دوم بر اساس `username` ارسالی هم در `WSM_Auth_Controller::login()` اضافه کن تا حتی با IPهای متفاوت هم بروت‌فورس روی یک حساب خاص محدود شود (مثلاً حداکثر ۵ تلاش ناموفق در ۱۵ دقیقه برای هر username، در جدول `wsm_login_attempts` یک ستون `username` اضافه کن).

---

## ۴. بالا — بررسی ظاهری نوع فایل آپلودی (High)

**فایل:** `includes/Services/class-wsm-media-service.php`

**مشکل:**
```php
$type = sanitize_text_field( wp_unslash( $_FILES[ $file_key ]['type'] ?? '' ) );
if ( ! str_starts_with( $type, 'image/' ) ) { ... }
```
مقدار `type` کاملاً توسط کلاینت تعیین می‌شود و قابل جعل است.

**راه‌حل:**
- این بررسی سطحی را با اعتبارسنجی واقعی محتوای فایل جایگزین کن:
```php
require_once ABSPATH . 'wp-admin/includes/file.php';
$file_path = $_FILES[$file_key]['tmp_name'];
$file_name = $_FILES[$file_key]['name'];
$check = wp_check_filetype_and_ext( $file_path, $file_name );
$allowed_types = [ 'image/jpeg', 'image/png', 'image/gif', 'image/webp' ];
if ( empty( $check['type'] ) || ! in_array( $check['type'], $allowed_types, true ) ) {
    return new WP_Error( 'wsm_invalid_file_type', __( 'تنها بارگذاری فایل‌های تصویری مجاز است.', 'karasu-woo-pannel' ) );
}
// همچنین تایید کن getimagesize() روی فایل بدون خطا کار می‌کند:
if ( false === @getimagesize( $file_path ) ) {
    return new WP_Error( 'wsm_invalid_image', __( 'فایل ارسالی یک تصویر معتبر نیست.', 'karasu-woo-pannel' ) );
}
```
- محدودیت حجم فایل (مثلاً حداکثر ۵ مگابایت) هم اضافه کن.

---

## ۵. متوسط — ذخیره و نمایش متن‌ساده‌ی رمز عبور پیامک (Medium)

**فایل‌ها:** `includes/Admin/class-wsm-admin-settings.php`, `includes/Admin/class-wsm-admin-menu.php` (تابع `render_settings_page`)

**مشکل:** `wsm_sms_password` به‌صورت متن ساده در دیتابیس ذخیره و در HTML با `value="<?= esc_attr($value) ?>"` چاپ می‌شود (قابل مشاهده با View Source).

**راه‌حل:**
- فیلد رمز عبور را در فرم تنظیمات، همیشه خالی نمایش بده (placeholder: «برای تغییر رمز، مقدار جدید وارد کنید — برای حفظ رمز فعلی خالی بگذارید»).
- در `sanitize_callback` مربوط به `wsm_sms_password`، اگر فیلد ارسالی خالی بود، مقدار قبلی را از `get_option` بازگردان (یعنی تغییری اعمال نشود)؛ اگر مقدار جدید وارد شده بود، آن را ذخیره کن.
- برای ذخیره‌سازی، با استفاده از `openssl_encrypt` (کلید مبتنی بر `wp_salt('auth')`) رمز را در دیتابیس رمزنگاری کن و فقط هنگام استفاده‌ی واقعی در `WSM_Sms_Service::send_sms()` آن را `openssl_decrypt` کن.

---

## ۶. متوسط — نبود Capability اختصاصی برای مدیریت پیامک (Medium)

**فایل:** `includes/Api/class-wsm-sms-controller.php`, `includes/Auth/class-wsm-capabilities.php`, `includes/Auth/class-wsm-roles.php`

**مشکل:** کنترلر SMS فقط `wsm_access_panel` را چک می‌کند، یعنی هر کاربر پنل (حتی با دسترسی محدود) می‌تواند تنظیمات پیامک را تغییر دهد.

**راه‌حل:**
- یک Capability جدید `wsm_manage_sms` به `WSM_Capabilities::get_all()` و به `WSM_Roles` اضافه کن.
- در `WSM_Sms_Controller::check_permission()`، شرط را به `current_user_can('wsm_manage_sms') || current_user_can('manage_woocommerce') || current_user_can('manage_options')` تغییر بده.
- در تب «مدیریت دسترسی کاربران» در `class-wsm-admin-menu.php`، یک ستون جدید برای `wsm_manage_sms` به جدول کاربران اضافه کن.

---

## ۷. متوسط — حذف فایل‌های توسعه از پکیج توزیعی (Medium, Information Disclosure)

**مشکل:** پوشه‌های/فایل‌های `docs/` (`ARCHITECTURE.md`, `DEBUG_LOG.MD`, `PRD.md`, `ROADMAP.md`, `GUIDELINES.md`)، `package.json`, `package-lock.json`, `tailwind.config.js`, `INSTALL.md`, `README.md`، و پوشه‌ی خالی تکراری `karasu-woo-pannel/karasu-woo-pannel/` داخل zip توزیعی هستند و بعد از نصب، از طریق URL مستقیم (بدون احراز هویت) در دسترس عموم قرار می‌گیرند و معماری داخلی/دیتابیس را افشا می‌کنند.

**راه‌حل:**
- یک فایل `.distignore` در ریشه‌ی پروژه بساز که شامل این موارد باشد:
```
docs/
package.json
package-lock.json
tailwind.config.js
INSTALL.md
README.md
node_modules/
.git/
.gitignore
src/
karasu-woo-pannel/
```
- یک اسکریپت ساده‌ی build (مثلاً `build.sh` یا یک اسکریپت npm) بساز که قبل از ساخت zip نهایی، این فایل‌ها را از یک پوشه‌ی موقت کپی‌شده حذف کند و فقط نسخه‌ی پاک را zip کند.
- پوشه‌ی تکراری و اشتباه `karasu-woo-pannel/karasu-woo-pannel/` را از ساختار پروژه پاک کن.
- به‌عنوان لایه‌ی دفاعی دوم، در ریشه‌ی پلاگین و هر زیرپوشه‌ی غیر-PHP (در صورت باقی ماندن هرگونه فایل غیرضروری)، یک `index.php` خالی با محتوای `<?php // Silence is golden.` اضافه کن تا لیست شدن دایرکتوری (directory listing) مسدود شود.

---

## ۸. متوسط — پاکسازی خودکار جدول‌های لاگ (Medium, Performance + Data Hygiene)

**فایل‌ها:** `includes/Core/class-wsm-plugin.php`, یک کلاس جدید `includes/Core/class-wsm-cron.php`

**مشکل:** جدول‌های `wsm_sessions`، `wsm_login_attempts` و `wsm_sms_log` هیچ‌گاه پاکسازی نمی‌شوند و با گذشت زمان حجیم می‌شوند.

**راه‌حل:**
- یک کلاس `WSM_Cron` بساز که در فعال‌سازی (`WSM_Activator::activate`) یک رویداد روزانه با `wp_schedule_event` ثبت کند (مثلاً `wsm_daily_cleanup`).
- در `WSM_Deactivator::deactivate`، با `wp_clear_scheduled_hook('wsm_daily_cleanup')` این رویداد را پاک کن.
- در هندلر این Cron:
  - رکوردهای `wsm_sessions` با `expires_at` گذشته از ۳۰ روز قبل را حذف کن.
  - رکوردهای `wsm_login_attempts` قدیمی‌تر از ۳۰ روز را حذف کن.
  - رکوردهای `wsm_sms_log` قدیمی‌تر از ۱۸۰ روز را حذف کن (یا یک تنظیم قابل تغییر در صفحه‌ی ادمین برای این بازه‌ی زمانی اضافه کن: `wsm_log_retention_days`).

---

## ۹. کم — بهینه‌سازی کوئری‌های گزارش‌ها (Low/Medium, Performance)

**فایل:** `includes/Repositories/class-wsm-report-repository.php`

**مشکل:** توابع `low_stock_products()`, `top_products()`, `customer_report()`, `sales_by_date_range()` همه‌ی رکوردها را با `'limit' => -1` در حافظه‌ی PHP لود می‌کنند که در فروشگاه‌های بزرگ باعث Timeout/مصرف بالای حافظه می‌شود.

**راه‌حل:**
- در `low_stock_products()`: به‌جای لود همه‌ی محصولات و فیلتر در PHP، از `wc_get_products` با آرگومان‌های `meta_query` روی `_stock` و `_manage_stock` استفاده کن تا فیلتر در سطح دیتابیس انجام شود.
- روی همه‌ی این متدها، یک لایه‌ی Cache با `get_transient`/`set_transient` (مدت‌زمان مثلاً ۱۵ دقیقه) اضافه کن، با یک کلید نسخه‌دار مثل `wsm_reports_cache_version` که با هر تغییر سفارش/محصول افزایش یابد (همان الگویی که در `docs/DEBUG_LOG.MD` توضیح داده شده ولی در کد واقعی این Repository پیاده‌سازی نشده بود).
- برای بازه‌های زمانی بزرگ، Pagination یا محدودیت تعداد رکورد اضافه کن.

---

## ۱۰. کم — استانداردهای ساخت افزونه

**فایل‌ها:** `karasu-woo-pannel.php`, `includes/Core/class-wsm-plugin.php`, `panel/layout.php`

موارد زیر را اصلاح کن:

1. **i18n:** در `karasu-woo-pannel.php`، داخل تابع `wsm_run()` یا با هوک `init` با اولویت پایین، خط زیر را اضافه کن:
```php
load_plugin_textdomain( 'karasu-woo-pannel', false, dirname( WSM_PLUGIN_BASENAME ) . '/languages' );
```
   و یک پوشه‌ی خالی `languages/` با یک فایل `.pot` پایه (می‌توانی با `wp i18n make-pot` تولید کنی) ایجاد کن.

2. **Enqueue صحیح Asset ها:** در `panel/layout.php`، تمام تگ‌های `<link>` و `<script>` دستی را حذف کن و به‌جای آن در یک متد جدید (مثلاً `WSM_Router::enqueue_panel_assets`) با هوک مناسب (یا مستقیم در render) از `wp_enqueue_style` و `wp_enqueue_script` با نسخه‌ی `WSM_VERSION` استفاده کن تا Cache-busting خودکار صورت گیرد. خروجی نهایی را با `wp_head()`/`wp_footer()` یا معادل دستی (`wp_print_styles`, `wp_print_scripts`) رندر کن.

3. **حذف کد تکراری Permission Check:** یک متد `protected function check_capability_permission(WP_REST_Request $request, string $capability): bool|WP_Error` در `WSM_REST_Controller` پایه بساز که منطق مشترک (auth check + capability check + manage_woocommerce/manage_options fallback) را یک‌بار بنویسد، و در کنترلرهای Orders/Products/Coupons/Reports/Sms آن را صدا بزن (فقط نام Capability را پاس بده) تا کد تکراری حذف شود.

4. **هماهنگی Uninstall:** در `uninstall.php`، موارد زیر را هم اضافه کن:
   - حذف option `wsm_sms_templates` و `wsm_trust_proxy_headers` و `wsm_log_retention_days` و `wsm_manage_sms` از لیست پاکسازی.
   - حذف Capabilityهای اضافه‌شده (`wsm_access_panel`, ...، و `wsm_manage_sms` جدید) از نقش‌های `administrator` و `shop_manager` با `remove_cap`، نه فقط حذف نقش `shop_manager_custom`.

---

## ۱۱. کم — سئو (SEO)

**فایل:** `panel/layout.php`, `includes/Router/class-wsm-rewrite.php`

1. در `<head>` فایل `panel/layout.php`، این تگ را اضافه کن:
```html
<meta name="robots" content="noindex, nofollow, noarchive">
```
2. هدر HTTP معادل را هم در `WSM_Rewrite::handle_request()` قبل از رندر اضافه کن:
```php
header( 'X-Robots-Tag: noindex, nofollow', true );
```
3. با هوک `robots_txt`، مسیر اسلاگ پنل را به‌صورت داینامیک به robots.txt مجازی وردپرس اضافه کن:
```php
add_filter( 'robots_txt', function( $output, $public ) {
    $slug = get_option( 'wsm_panel_slug', 'store-admin' );
    $output .= "Disallow: /{$slug}/\n";
    return $output;
}, 10, 2 );
```

---

## ۱۲. کم — بهینه‌سازی بارگذاری منابع خارجی (Performance)

**فایل:** `panel/layout.php`

1. اسکریپت `jalalidatepicker.min.js` از `unpkg.com` را با ویژگی `defer` لود کن.
2. به تگ‌های `<link>` و `<script>` خارجی (فونت گوگل، jalalidatepicker) ویژگی `integrity` و `crossorigin="anonymous"` (Subresource Integrity) اضافه کن تا در صورت آلوده‌شدن CDN، مرورگر از اجرای فایل دستکاری‌شده خودداری کند. (هش‌های SRI را برای نسخه‌ی فعلی این کتابخانه‌ها از CDN دریافت و درج کن.)
3. در صورت امکان، یک نسخه‌ی Local از این کتابخانه‌ها را داخل `assets/vendor/` قرار بده تا وابستگی به CDN خارجی به‌کلی حذف شود (بهتر برای پایداری و GDPR/حریم خصوصی هم هست چون درخواست مستقیم به `unpkg.com` و `fonts.googleapis.com` ارسال می‌شود).

---

## چک‌لیست نهایی بعد از اعمال تغییرات

- [x] هیچ مقدار حساس (توکن، رمز عبور) در HTML/JS خام چاپ نشود.
- [x] هیچ `sslverify => false` در کل پروژه باقی نماند.
- [x] `composer.json`/`package.json`/`docs/` و فایل‌های توسعه در zip نهایی توزیع نشوند.
- [x] تمام Capability های جدید (`wsm_manage_sms`) در فعال‌سازی، غیرفعال‌سازی و Uninstall به‌درستی مدیریت شوند.
- [x] تست کامل لاگین، آپلود تصویر، ذخیره‌ی تنظیمات SMS، و گزارش‌ها بعد از تغییرات انجام شود.
- [x] نسخه‌ی افزونه و `readme.txt`/Changelog به‌روزرسانی شود.

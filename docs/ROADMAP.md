# ROADMAP — KarasuWooPannel Plugin

**نسخه:** 1.0.2  
**تاریخ:** ۱۴۰۵/۰۴/۰۲  
**وضعیت:** Stable / Released

---

## فهرست مطالب

1. [نمای کلی فازها](#۱-نمای-کلی-فازها)
2. [فاز ۱ — Setup و زیرساخت](#فاز-۱--setup-و-زیرساخت)
3. [فاز ۲ — هسته مدیریت فروشگاه](#فاز-۲--هسته-مدیریت-فروشگاه)
4. [فاز ۳ — قابلیت‌های پیشرفته](#فاز-۳--قابلیت‌های-پیشرفته)
5. [فاز ۴ — Elementor و بهینه‌سازی](#فاز-۴--elementor-و-بهینه‌سازی)
6. [وابستگی‌های بین Task ها](#۶-وابستگی‌های-بین-task-ها)
7. [تعریف Done برای هر Task](#۷-تعریف-done-برای-هر-task)

---

## ۱. نمای کلی فازها

```
فاز ۱          فاز ۲              فاز ۳           فاز ۴
Setup        هسته فروشگاه      قابلیت‌های        Elementor
─────────    ──────────────    پیشرفته           ─────────
ساختار       سفارش‌ها          تخفیف/کوپن        Widget ورود
Auth         محصولات          گزارش‌گیری         بهینه‌سازی
DB           دسته‌بندی‌ها       پیامک             تست جامع
Router       تصاویر           CSV Export        مستندات
Settings
```

| فاز | عنوان | تخمین زمان | خروجی اصلی |
|-----|-------|-----------|-----------|
| فاز ۱ | Setup و زیرساخت | ۲ هفته | افزونه فعال + ورود کارکردی |
| فاز ۲ | هسته مدیریت فروشگاه | ۴ هفته | مدیریت سفارش/محصول/دسته |
| فاز ۳ | قابلیت‌های پیشرفته | ۳ هفته | کوپن + گزارش + پیامک |
| فاز ۴ | Elementor و بهینه‌سازی | ۱ هفته | Widget + تست نهایی |

---

## فاز ۱ — Setup و زیرساخت

**هدف:** ایجاد ساختار کامل افزونه، سیستم احراز هویت و تنظیمات اولیه.  
**پیش‌نیاز:** وردپرس 6.0+ و ووکامرس 7.0+ نصب‌شده.

---

### Task 1.1 — ساختار اولیه افزونه

**اولویت:** Critical

- [x] ایجاد فایل اصلی `woostore-manager.php` با header استاندارد وردپرس
- [x] تعریف Constants: `WSM_VERSION`, `WSM_PLUGIN_DIR`, `WSM_PLUGIN_URL`, `WSM_PLUGIN_BASENAME`
- [x] پیاده‌سازی `class-wsm-autoloader.php` (PSR-4 Autoloading)
- [x] پیاده‌سازی `class-wsm-plugin.php` (Singleton + Loader)
- [x] پیاده‌سازی `class-wsm-loader.php` (Hook Registry)
- [x] ایجاد ساختار کامل پوشه‌بندی طبق ARCHITECTURE.md
- [x] ایجاد فایل `uninstall.php` با cleanup کامل
- [x] تست فعال/غیرفعال/حذف افزونه بدون خطا

**خروجی قابل تست:** افزونه در لیست افزونه‌های وردپرس ظاهر می‌شود و فعال/غیرفعال می‌شود.

---

### Task 1.2 — دیتابیس و Activation

**اولویت:** Critical  
**پیش‌نیاز:** Task 1.1

- [x] پیاده‌سازی `class-wsm-activator.php`
- [x] بررسی پیش‌نیاز بودن ووکامرس در هنگام فعال‌سازی و غیرفعال‌کردن خودکار افزونه در صورت عدم نصب ووکامرس
- [x] ایجاد جداول `wp_wsm_sms_log` ، `wp_wsm_login_attempts` و `wp_wsm_sessions` با `dbDelta()`
- [x] ذخیره `wsm_db_version` در Options (برای مدیریت Migration آینده)
- [x] پیاده‌سازی `class-wsm-deactivator.php` (Flush Rewrite Rules)
- [x] پیاده‌سازی cleanup کامل در `uninstall.php` (حذف جداول + Options)

**خروجی قابل تست:** جداول در phpMyAdmin پس از فعال‌سازی وجود دارند و افزونه بدون ووکامرس فعال نمی‌شود.

---

### Task 1.3 — نقش‌های کاربری و Capabilities

**اولویت:** Critical  
**پیش‌نیاز:** Task 1.2

- [x] پیاده‌سازی `class-wsm-roles.php`
- [x] ایجاد نقش `shop_manager_custom` هنگام فعال‌سازی
- [x] تعریف Capabilities اختصاصی: `wsm_access_panel`, `wsm_manage_orders`, `wsm_manage_products`, `wsm_manage_coupons`, `wsm_view_reports`
- [x] پیاده‌سازی `class-wsm-capabilities.php` (constants و helper methods)
- [x] بلاک دسترسی نقش به `/wp-admin` (hook: `admin_init`)
- [x] حذف نقش هنگام Uninstall

**خروجی قابل تست:** کاربر با نقش `shop_manager_custom` نمی‌تواند وارد `/wp-admin` شود.

---

### Task 1.4 — سیستم Routing و Custom Endpoint

**اولویت:** Critical  
**پیش‌نیاز:** Task 1.3

- [x] پیاده‌سازی `class-wsm-rewrite.php`
- [x] ثبت Rewrite Rule برای slug پنل
- [x] ثبت Query Vars: `wsm_panel`, `wsm_path`
- [x] پیاده‌سازی `class-wsm-router.php` با جدول مسیریابی کامل
- [x] پیاده‌سازی `panel/layout.php` (HTML پایه + بارگذاری TailwindCSS CDN)
- [x] Flush Rewrite Rules هنگام ذخیره تنظیمات slug
- [x] تست دسترسی به `/store-admin/` و ریدایرکت صحیح

**خروجی قابل تست:** آدرس `/store-admin/` به Template پنل می‌رود نه ۴۰۴.

---

### Task 1.5 — سیستم احراز هویت

**اولویت:** Critical  
**پیش‌نیاز:** Task 1.4

- [x] پیاده‌سازی `class-wsm-auth.php`
  - [x] متد `login( string $username, string $password ): bool`
  - [x] متد `logout(): void`
  - [x] متد `is_authenticated(): bool`
  - [x] متد `get_current_user(): ?WP_User`
  - [x] متد `verify_token( string $token ): bool`
- [x] پیاده‌سازی `class-wsm-rate-limiter.php` (طبق GUIDELINES.md)
- [x] ایجاد `panel/login.php` (فرم ورود با TailwindCSS + RTL)
- [x] پیاده‌سازی ریدایرکت‌ها:
  - کاربر غیرلاگین → صفحه ورود پنل
  - کاربر لاگین → Dashboard پنل
  - کاربر عادی → Home سایت
- [x] تست: ۵ بار تلاش ناموفق → بلاک ۳۰ دقیقه‌ای

**خروجی قابل تست:** ورود موفق با اعتبارنامه صحیح و بلاک پس از ۵ تلاش ناموفق.

---

### Task 1.6 — تنظیمات افزونه (WP Admin)

**اولویت:** High  
**پیش‌نیاز:** Task 1.5

- [x] پیاده‌سازی `class-wsm-admin-menu.php` (ثبت منو در WP Admin)
- [x] پیاده‌سازی `class-wsm-admin-settings.php`
- [x] صفحه تنظیمات با فیلدهای:
  - [x] آدرس URL پنل (slug)
  - [x] مدت اعتبار Session
  - [x] شماره موبایل مدیر
  - [x] آستانه هشدار موجودی
- [x] ذخیره تنظیمات با `register_setting()` + Nonce
- [x] پیاده‌سازی `wsm_get_setting( string $key, mixed $default = null ): mixed`
- [x] تست ذخیره و بازخوانی تنظیمات

**خروجی قابل تست:** صفحه تنظیمات در WP Admin > Settings > KarasuWooPannel نمایش داده می‌شود.

---

### Task 1.7 — REST API Base و Helper ها

**اولویت:** High  
**پیش‌نیاز:** Task 1.6

- [x] پیاده‌سازی `class-wsm-rest-controller.php` (abstract base)
- [x] پیاده‌سازی `class-wsm-response.php` (فرمت یکسان پاسخ)
- [x] پیاده‌سازی `class-wsm-sanitizer.php` (طبق GUIDELINES.md)
- [x] پیاده‌سازی `class-wsm-date-helper.php` (تبدیل جلالی ↔ گرگوری)
- [x] پیاده‌سازی `panel/layout.php` کامل با تزریق `wsmConfig` به JS
- [x] تست دسترسی به `/wp-json/wsm/v1/` بدون احراز هویت (باید ۴۰۱ برگرداند)

**خروجی قابل تست:** namespace `wsm/v1` در REST API Discovery ظاهر می‌شود.

---

## فاز ۲ — هسته مدیریت فروشگاه

**هدف:** پیاده‌سازی کامل مدیریت سفارش‌ها، محصولات و دسته‌بندی‌ها.  
**پیش‌نیاز:** تمام Task های فاز ۱ تکمیل‌شده.

---

### Task 2.1 — مدیریت سفارش‌ها (Backend)

**اولویت:** Critical

- [x] پیاده‌سازی `class-wsm-order-repository.php`
  - [x] `find_all( array $args ): array`
  - [x] `find_by_id( int $id ): ?WC_Order`
  - [x] `update_status( int $id, string $status ): bool`
  - [x] `add_note( int $id, string $note, bool $customer_note ): int`
- [x] پیاده‌سازی `class-wsm-order-service.php`
- [x] پیاده‌سازی `class-wsm-orders-controller.php` (REST Endpoints)
- [x] پیاده‌سازی Endpoints:
  - [x] `GET /wsm/v1/orders` (با فیلتر و Pagination)
  - [x] `GET /wsm/v1/orders/{id}`
  - [x] `PATCH /wsm/v1/orders/{id}/status`
  - [x] `POST /wsm/v1/orders/{id}/notes`

**خروجی قابل تست:** `GET /wp-json/wsm/v1/orders` لیست سفارش‌ها را با Token معتبر برمی‌گرداند.

---

### Task 2.2 — مدیریت سفارش‌ها (Frontend)

**اولویت:** Critical  
**پیش‌نیاز:** Task 2.1

- [x] ایجاد `panel/views/orders/list.php`
  - [x] جدول سفارش‌ها با TailwindCSS
  - [x] فیلتر وضعیت، بازه تاریخ، جستجو
  - [x] Pagination
  - [x] دکمه‌های سریع تغییر وضعیت (AJAX)
- [x] ایجاد `panel/views/orders/detail.php`
  - [x] نمایش کامل اطلاعات سفارش
  - [x] تاریخچه وضعیت (Timeline)
  - [x] فرم افزودن یادداشت
  - [x] Dropdown تغییر وضعیت
- [x] ایجاد `assets/js/wsm-orders.js`

**خروجی قابل تست:** لیست سفارش‌ها نمایش داده می‌شود و تغییر وضعیت بدون رفرش کار می‌کند.

---

### Task 2.3 — مدیریت محصولات (Backend)

**اولویت:** High  
**پیش‌نیاز:** Task 2.1

- [x] پیاده‌سازی `class-wsm-product-repository.php`
  - [x] `find_all( array $args ): array`
  - [x] `find_by_id( int $id ): ?WC_Product`
  - [x] `create( array $data ): int|WP_Error`
  - [x] `update( int $id, array $data ): bool|WP_Error`
  - [x] `delete( int $id ): bool`
  - [x] `toggle_stock( int $id, string $status ): bool`
- [x] پیاده‌سازی `class-wsm-product-service.php`
- [x] پیاده‌سازی `class-wsm-products-controller.php`
- [x] پیاده‌سازی `class-wsm-media-service.php` (آپلود تصویر)
- [x] پیاده‌سازی Endpoints:
  - [x] `GET /wsm/v1/products`
  - [x] `GET /wsm/v1/products/{id}`
  - [x] `POST /wsm/v1/products`
  - [x] `PUT /wsm/v1/products/{id}`
  - [x] `PATCH /wsm/v1/products/{id}/stock`
  - [x] `DELETE /wsm/v1/products/{id}`
  - [x] `GET /wsm/v1/products/search` (برای Upsell/Cross-sell AJAX)

**خروجی قابل تست:** ایجاد محصول ساده از طریق API و مشاهده آن در ووکامرس.

---

### Task 2.4 — مدیریت محصولات (Frontend — محصول ساده)

**اولویت:** High  
**پیش‌نیاز:** Task 2.3

- [x] ایجاد `panel/views/products/list.php`
  - [x] جدول محصولات با تصویر بندانگشتی
  - [x] فیلتر دسته‌بندی، وضعیت، موجودی
  - [x] Toggle ناموجود/موجود (AJAX)
  - [x] دکمه ایجاد محصول جدید
- [x] ایجاد `panel/views/products/edit.php` (برای محصول ساده)
  - [x] فرم اطلاعات اصلی (نام، توضیحات، توضیحات کوتاه)
  - [x] تب قیمت‌گذاری (قیمت عادی، حراج، تاریخ)
  - [x] تب موجودی
  - [x] آپلود تصویر اصلی و گالری
  - [x] انتخاب دسته‌بندی و برچسب
- [x] ایجاد `assets/js/wsm-products.js`

**خروجی قابل تست:** ایجاد و ویرایش محصول ساده از پنل.

---

### Task 2.5 — محصول متغیر (Variable Product)

**اولویت:** Medium  
**پیش‌نیاز:** Task 2.4

- [x] اضافه کردن تب Attributes به فرم محصول
- [x] اضافه کردن تب Variations
- [x] رابط Generate Variations (AJAX)
- [x] ویرایش هر Variation: قیمت، موجودی، SKU، تصویر
- [x] بروزرسانی Repository برای محصول متغیر

**خروجی قابل تست:** ایجاد محصول متغیر با ۳ رنگ مختلف.

---

### Task 2.6 — مدیریت دسته‌بندی‌ها

**اولویت:** Medium  
**پیش‌نیاز:** Task 2.4

- [x] پیاده‌سازی Category/Tag/Attribute در Repository و Service
- [x] Endpoint های REST برای Category/Tag/Attribute
- [x] ایجاد `panel/views/categories/list.php`
  - [x] نمایش درختی دسته‌بندی‌ها
  - [x] فرم ایجاد/ویرایش inline
  - [x] آپلود تصویر دسته
- [x] مدیریت برچسب‌ها
- [x] مدیریت Global Attributes

**خروجی قابل تست:** ایجاد دسته‌بندی والد/فرزند و اختصاص به محصول.

---

## فاز ۳ — قابلیت‌های پیشرفته

**هدف:** پیاده‌سازی کوپن، گزارش‌گیری، CSV Export و سیستم پیامک.  
**پیش‌نیاز:** تمام Task های فاز ۲ تکمیل‌شده.

---

### Task 3.1 — سیستم کوپن

**اولویت:** High

- [x] پیاده‌سازی `class-wsm-coupon-repository.php`
- [x] پیاده‌سازی `class-wsm-coupon-service.php`
- [x] پیاده‌سازی `class-wsm-coupons-controller.php`
- [x] ایجاد `panel/views/coupons/list.php`
- [x] ایجاد `panel/views/coupons/edit.php` (تمام فیلدهای ووکامرس)
- [x] دکمه تولید کد تصادفی

**خروجی قابل تست:** ایجاد کوپن ۲۰٪ تخفیف با تاریخ انقضا و اعمال آن در فروشگاه.

---

### Task 3.2 — گزارش‌گیری (Backend)

**اولویت:** High

- [x] پیاده‌سازی `class-wsm-report-repository.php`
  - [x] `sales_by_date_range( string $from, string $to ): array`
  - [x] `top_products( int $limit, string $from, string $to ): array`
  - [x] `low_stock_products( int $threshold ): array`
  - [x] `customer_report( string $type, string $from, string $to ): array`
- [x] پیاده‌سازی `class-wsm-report-service.php`
- [x] پیاده‌سازی `class-wsm-reports-controller.php`
- [x] پیاده‌سازی CSV Export (streaming برای داده‌های بزرگ)

**خروجی قابل تست:** API گزارش فروش ماه جاری را با جمع صحیح برمی‌گرداند.

---

### Task 3.3 — گزارش‌گیری (Frontend)

**اولویت:** High  
**پیش‌نیاز:** Task 3.2

- [x] ایجاد `panel/views/reports/dashboard.php`
  - [x] کارت‌های آماری (فروش امروز/هفته/ماه)
  - [x] نمودار خطی فروش (Chart.js) با DatePicker شمسی
  - [x] جدول Top 5 محصولات
- [x] ایجاد `panel/views/reports/sales.php` (گزارش تفصیلی + صادرات CSV)
- [x] ایجاد `panel/views/reports/products.php`
- [x] ایجاد `panel/views/reports/customers.php`
- [x] ایجاد `assets/js/wsm-reports.js`

**خروجی قابل تست:** نمودار فروش ۳۰ روز اخیر نمایش داده می‌شود.

---

### Task 3.4 — سیستم پیامک ملی‌پیامک (Backend)

**اولویت:** Medium

- [x] پیاده‌سازی `class-wsm-sms-service.php`
  - [x] `send( string $to, string $message ): bool`
  - [x] رویدادهای مدیر: `on_admin_new_order()`, `on_admin_low_stock()`
  - [x] رویدادهای مشتری: `on_customer_order_placed()`, `on_customer_order_status_changed()`
  - [x] `process_template( string $template, array $vars ): string`
- [x] اتصال به API ملی‌پیامک (REST)
- [x] ذخیره در `wp_wsm_sms_log`
- [x] ثبت Hook های ووکامرس (مانند `woocommerce_new_order` و `woocommerce_order_status_changed`)

**خروجی قابل تست:** ثبت سفارش آزمایشی → پیامک‌های مدیر و مشتری به صورت مجزا به شماره‌های تنظیم‌شده ارسال می‌شوند.

---

### Task 3.5 — تنظیمات پیامک و لاگ (Admin + Frontend)

**اولویت:** Medium  
**پیش‌نیاز:** Task 3.4

- [x] اضافه کردن تب پیامک به صفحه تنظیمات WP Admin
  - [x] فیلدهای username, password, از خط ملی‌پیامک
  - [x] دکمه تست اتصال (AJAX)
  - [x] فعال/غیرفعال هر رویداد (به صورت مجزا برای مشتری و مدیر)
  - [x] ویرایش قالب پیامک هر رویداد (به صورت مجزا برای مشتری و مدیر)
- [x] ایجاد `panel/views/reports/sms-log.php`
- [x] پیاده‌سازی `class-wsm-log-repository.php`

**خروجی قابل تست:** دکمه تست پیامک در تنظیمات کار می‌کند.

---

## فاز ۴ — Elementor و بهینه‌سازی

**هدف:** Widget Elementor، تست جامع و بهینه‌سازی.  
**پیش‌نیاز:** تمام فازهای قبلی تکمیل‌شده.

---

### Task 4.1 — Elementor Widget

**اولویت:** Low

- [x] پیاده‌سازی `class-wsm-elementor.php`
- [x] پیاده‌سازی `class-wsm-login-button-widget.php`
- [x] تنظیمات Widget در Elementor Editor
- [x] تست عدم تداخل TailwindCSS با صفحات Elementor
- [x] تست نمایش Widget فقط برای نقش مجاز

**خروجی قابل تست:** Widget در Elementor Editor ظاهر می‌شود و دکمه فقط برای `shop_manager_custom` نمایش داده می‌شود.

---

### Task 4.2 — بهینه‌سازی و Caching

**اولویت:** Medium

- [x] اضافه کردن Transient Cache به Query های پرتکرار گزارش‌ها
- [x] بررسی و بهینه‌سازی Index های جداول اختصاصی
- [x] Lazy Loading داده‌های صفحه (بارگذاری اولیه سریع + AJAX تکمیل)
- [x] بررسی Console Errors در صفحات Elementor
- [x] تست زمان بارگذاری (هدف: زیر ۲ ثانیه)

---

### Task 4.3 — تست جامع و امنیت

**اولویت:** Critical

- [x] تست کامل چک‌لیست GUIDELINES.md
- [x] تست نفوذ دستی:
  - [x] تلاش دسترسی به `/wp-admin` با نقش `shop_manager_custom`
  - [x] تلاش اجرای SQL Injection در فیلدهای فرم
  - [x] تلاش XSS در فیلدهای ورودی
  - [x] تلاش دسترسی به REST بدون Session Token
  - [x] بررسی عملکرد Rate Limiter
- [x] تست سازگاری با WooCommerce Themes متداول
- [x] تست با PHP 8.0 و PHP 8.2

---

### Task 4.4 — مستندات و انتشار

**اولویت:** Medium

- [x] تکمیل `readme.txt` (WordPress Plugin Directory format)
- [x] نوشتن راهنمای نصب و راه‌اندازی (`INSTALL.md`)
- [x] بروزرسانی `DEBUG_LOG.md` با مشکلات و راه‌حل‌های کشف‌شده
- [x] بررسی لایسنس تمام کتابخانه‌های استفاده‌شده
- [x] آماده‌سازی Package نهایی (بدون فایل‌های dev)

---

## ۶. وابستگی‌های بین Task ها

```
1.1 → 1.2 → 1.3 → 1.4 → 1.5 → 1.6 → 1.7
                                        │
                              ┌─────────┤
                              ▼         ▼
                            2.1       2.3
                              │         │
                              ▼         ▼
                            2.2       2.4 → 2.5
                                        │
                                        ▼
                                       2.6
                                        │
                              ┌─────────┤
                              ▼         ▼
                            3.1       3.2
                                        │
                                        ▼
                                       3.3
                                        │
                              ┌─────────┤
                              ▼         ▼
                            3.4       4.1
                              │
                              ▼
                            3.5 → 4.2 → 4.3 → 4.4
```

---

## ۷. تعریف Done برای هر Task

یک Task **Done** است اگر:

```
✅ کد نوشته‌شده رعایت تمام GUIDELINES.md را دارد
✅ تمام موارد چک‌لیست امنیتی GUIDELINES.md پاس شده‌اند
✅ API Endpoint های مربوطه با Token معتبر کار می‌کنند
✅ API Endpoint های مربوطه بدون Token یا با Token نامعتبر ۴۰۱ برمی‌گردانند
✅ خروجی HTML بدون JS Error در Browser Console است
✅ عملکرد در وردپرس 6.0+ و ووکامرس 7.0+ تأیید شده
✅ تداخل با Elementor وجود ندارد (اگر Elementor نصب باشد)
✅ DocBlock برای تمام متدهای public نوشته شده
✅ هر خطا/مشکل کشف‌شده در DEBUG_LOG.md ثبت شده
```

---

*برای هر Task جدیدی که به این Roadmap اضافه می‌شود، وابستگی‌ها و تعریف Done باید مشخص شوند.*

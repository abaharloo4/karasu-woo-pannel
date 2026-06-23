# ARCHITECTURE — KarasuWooPannel Plugin

**نسخه:** 1.0.7  
**تاریخ:** ۱۴۰۵/۰۴/۰۲  
**وضعیت:** Stable / Released  
**Prefix اختصاصی:** `wsm_`  
**Namespace پایه:** `WooStoreManager`

---

## فهرست مطالب

1. [نمای کلی معماری](#۱-نمای-کلی-معماری)
2. [ساختار پوشه‌بندی](#۲-ساختار-پوشه‌بندی)
3. [لایه‌بندی معماری](#۳-لایه‌بندی-معماری)
4. [کلاس‌های اصلی و مسئولیت‌ها](#۴-کلاس‌های-اصلی-و-مسئولیت‌ها)
5. [ساختار دیتابیس](#۵-ساختار-دیتابیس)
6. [REST API اختصاصی](#۶-rest-api-اختصاصی)
7. [سیستم Routing پنل](#۷-سیستم-routing-پنل)
8. [یکپارچگی با WooCommerce](#۸-یکپارچگی-با-woocommerce)
9. [یکپارچگی با Elementor](#۹-یکپارچگی-با-elementor)
10. [جریان داده — Data Flow](#۱۰-جریان-داده--data-flow)
11. [سیستم Asset بارگذاری](#۱۱-سیستم-asset-بارگذاری)
12. [Service Container و Dependency Injection](#۱۲-service-container-و-dependency-injection)

---

## ۱. نمای کلی معماری

```
┌─────────────────────────────────────────────────────────┐
│                    WordPress Core                        │
├──────────────────────────┬──────────────────────────────┤
│      WooCommerce         │        Elementor             │
├──────────────────────────┴──────────────────────────────┤
│                 KarasuWooPannel Plugin                   │
│  ┌─────────────┐  ┌──────────────┐  ┌───────────────┐  │
│  │  Auth Layer  │  │  Panel Layer │  │  Admin Layer  │  │
│  │  (Session,   │  │  (Router,    │  │  (WP Settings,│  │
│  │   Roles)     │  │   Templates) │  │   SMS Config) │  │
│  └──────┬───────┘  └──────┬───────┘  └───────┬───────┘  │
│         │                 │                   │          │
│  ┌──────▼─────────────────▼───────────────────▼───────┐  │
│  │               Service Layer                         │  │
│  │  Orders │ Products │ Coupons │ Reports │ SMS        │  │
│  └──────────────────────────┬────────────────────────┘  │
│                             │                            │
│  ┌──────────────────────────▼────────────────────────┐  │
│  │               Repository Layer                     │  │
│  │         (WooCommerce CRUD + wpdb)                  │  │
│  └────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────┘
```

**الگوی معماری:** MVC سبک با Service Layer و Repository Pattern  
**استاندارد کدنویسی:** WordPress Coding Standards (WPCS) + PSR-4 Autoloading  
**PHP Namespace:** `WooStoreManager\`

---

## ۲. ساختار پوشه‌بندی

```
woostore-manager/
│
├── woostore-manager.php              # فایل اصلی افزونه (bootstrap)
├── uninstall.php                     # پاک‌سازی هنگام حذف افزونه
├── readme.txt                        # توضیحات WordPress Plugin Directory
├── composer.json                     # Autoloading PSR-4
│
├── includes/                         # هسته اصلی — تمام PHP بدون خروجی HTML
│   │
│   ├── Core/
│   │   ├── class-wsm-plugin.php      # کلاس اصلی — نقطه ورود (Singleton)
│   │   ├── class-wsm-loader.php      # مدیریت ثبت Hook/Filter
│   │   ├── class-wsm-activator.php   # منطق فعال‌سازی (نصب جداول، نقش‌ها)
│   │   ├── class-wsm-deactivator.php # منطق غیرفعال‌سازی
│   │   └── class-wsm-autoloader.php  # PSR-4 Autoloader (fallback)
│   │
│   ├── Auth/
│   │   ├── class-wsm-auth.php        # مدیریت Session، Login، Logout
│   │   ├── class-wsm-roles.php       # تعریف و مدیریت نقش‌های اختصاصی
│   │   ├── class-wsm-rate-limiter.php # Rate Limiting برای فرم ورود
│   │   └── class-wsm-capabilities.php # تعریف Capabilities
│   │
│   ├── Router/
│   │   ├── class-wsm-router.php      # تشخیص URL پنل و ریدایرکت‌ها
│   │   └── class-wsm-rewrite.php     # ثبت Custom Rewrite Rules وردپرس
│   │
│   ├── Services/
│   │   ├── class-wsm-order-service.php    # منطق تجاری سفارش‌ها
│   │   ├── class-wsm-product-service.php  # منطق تجاری محصولات
│   │   ├── class-wsm-coupon-service.php   # منطق تجاری کوپن‌ها
│   │   ├── class-wsm-report-service.php   # منطق گزارش‌گیری
│   │   ├── class-wsm-sms-service.php      # ارسال پیامک ملی‌پیامک
│   │   └── class-wsm-media-service.php    # آپلود و مدیریت تصاویر
│   │
│   ├── Repositories/
│   │   ├── class-wsm-order-repository.php    # CRUD سفارش‌ها
│   │   ├── class-wsm-product-repository.php  # CRUD محصولات
│   │   ├── class-wsm-coupon-repository.php   # CRUD کوپن‌ها
│   │   ├── class-wsm-report-repository.php   # Query گزارش‌ها
│   │   └── class-wsm-log-repository.php      # CRUD جدول لاگ پیامک
│   │
│   ├── Api/
│   │   ├── class-wsm-rest-controller.php     # Base Controller (abstract)
│   │   ├── class-wsm-orders-controller.php   # REST: /wsm/v1/orders
│   │   ├── class-wsm-products-controller.php # REST: /wsm/v1/products
│   │   ├── class-wsm-coupons-controller.php  # REST: /wsm/v1/coupons
│   │   ├── class-wsm-reports-controller.php  # REST: /wsm/v1/reports
│   │   └── class-wsm-sms-controller.php      # REST: /wsm/v1/sms
│   │
│   ├── Admin/
│   │   ├── class-wsm-admin-menu.php          # ثبت منوی تنظیمات در WP Admin
│   │   └── class-wsm-admin-settings.php      # صفحه تنظیمات افزونه
│   │
│   ├── Elementor/
│   │   ├── class-wsm-elementor.php           # ثبت Widget در Elementor
│   │   └── widgets/
│   │       └── class-wsm-login-button-widget.php
│   │
│   └── Helpers/
│       ├── class-wsm-date-helper.php         # تبدیل تاریخ شمسی/میلادی
│       ├── class-wsm-sanitizer.php           # توابع Sanitize اختصاصی
│       └── class-wsm-response.php            # فرمت JSON Response یکسان
│
├── panel/                            # فایل‌های Template پنل اختصاصی
│   ├── layout.php                    # قالب پایه HTML پنل (doctype، head، body)
│   ├── login.php                     # صفحه ورود اختصاصی
│   │
│   └── views/                        # View های هر بخش
│       ├── dashboard.php
│       ├── orders/
│       │   ├── list.php
│       │   └── detail.php
│       ├── products/
│       │   ├── list.php
│       │   └── edit.php
│       ├── categories/
│       │   └── list.php
│       ├── coupons/
│       │   ├── list.php
│       │   └── edit.php
│       └── reports/
│           ├── dashboard.php
│           └── sms-log.php
│
├── assets/
│   ├── css/
│   │   └── wsm-panel.css             # استایل‌های اضافه (بر TailwindCSS)
│   ├── js/
│   │   ├── wsm-panel.js              # JS اصلی پنل (Vanilla JS / Alpine)
│   │   ├── wsm-orders.js
│   │   ├── wsm-products.js
│   │   └── wsm-reports.js
│   └── images/
│       └── wsm-logo.svg
│
└── languages/
    ├── woostore-manager.pot
    └── woostore-manager-fa_IR.po
```

---

## ۳. لایه‌بندی معماری

### لایه ۱ — Core Bootstrap

```php
// woostore-manager.php
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'WSM_VERSION', '1.0.7' );
define( 'WSM_PLUGIN_FILE', __FILE__ );
define( 'WSM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WSM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WSM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

function wsm_run(): void {
    $plugin = \WooStoreManager\Core\WSM_Plugin::get_instance();
    $plugin->run();
}
add_action( 'plugins_loaded', 'wsm_run' );
```

### لایه ۲ — Plugin Singleton

```php
// includes/Core/class-wsm-plugin.php
namespace WooStoreManager\Core;

final class WSM_Plugin {

    private static ?self $instance = null;
    private WSM_Loader $loader;

    public static function get_instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->loader = new WSM_Loader();
        $this->load_dependencies();
        $this->define_hooks();
    }

    private function load_dependencies(): void {
        // بارگذاری Autoloader یا Composer
    }

    private function define_hooks(): void {
        // ثبت Hook های Admin، Panel، API
    }

    public function run(): void {
        $this->loader->run();
    }
}
```

### لایه ۳ — Service Layer (نمونه)

```php
// includes/Services/class-wsm-order-service.php
namespace WooStoreManager\Services;

use WooStoreManager\Repositories\WSM_Order_Repository;

class WSM_Order_Service {

    public function __construct(
        private readonly WSM_Order_Repository $repository
    ) {}

    public function get_orders( array $args = [] ): array {
        // اعتبارسنجی و پردازش $args
        // تبدیل تاریخ شمسی به میلادی
        return $this->repository->find_all( $args );
    }

    public function update_status( int $order_id, string $status ): bool {
        // اعتبارسنجی وضعیت
        $allowed = wc_get_order_statuses();
        if ( ! array_key_exists( 'wc-' . $status, $allowed ) ) {
            return false;
        }
        return $this->repository->update_status( $order_id, $status );
    }
}
```

---

## ۴. کلاس‌های اصلی و مسئولیت‌ها

| کلاس | Namespace | مسئولیت |
|------|-----------|---------|
| `WSM_Plugin` | `Core` | Singleton اصلی، bootstrapping |
| `WSM_Loader` | `Core` | ثبت و اجرای تمام Hook/Filter |
| `WSM_Activator` | `Core` | ایجاد جداول، نقش‌ها، Flush Rewrite Rules |
| `WSM_Deactivator` | `Core` | پاک‌سازی Option ها و Transients |
| `WSM_Auth` | `Auth` | Login، Logout، Session، Cookie |
| `WSM_Roles` | `Auth` | تعریف `shop_manager_custom`، Capabilities |
| `WSM_Rate_Limiter` | `Auth` | جلوگیری از Brute Force فرم ورود |
| `WSM_Router` | `Router` | Routing درخواست‌ها به View مناسب |
| `WSM_Rewrite` | `Router` | ثبت Custom Endpoint در وردپرس |
| `WSM_Order_Service` | `Services` | منطق تجاری سفارش‌ها |
| `WSM_Product_Service` | `Services` | منطق تجاری محصولات |
| `WSM_Coupon_Service` | `Services` | منطق تجاری کوپن‌ها |
| `WSM_Report_Service` | `Services` | تجمیع و پردازش داده گزارش‌ها |
| `WSM_SMS_Service` | `Services` | ارسال پیامک از طریق ملی‌پیامک API |
| `WSM_Order_Repository` | `Repositories` | CRUD با `WC_Order` CRUD Classes |
| `WSM_Product_Repository` | `Repositories` | CRUD با `WC_Product_Factory` |
| `WSM_Rest_Controller` | `Api` | کلاس پایه Abstract برای REST |
| `WSM_Admin_Settings` | `Admin` | ثبت و ذخیره تنظیمات در WP Admin |
| `WSM_Date_Helper` | `Helpers` | تبدیل تاریخ جلالی ↔ گرگوری |
| `WSM_Response` | `Helpers` | فرمت یکسان پاسخ JSON |

---

## ۵. ساختار دیتابیس

### جداول اختصاصی

هنگام فعال‌سازی افزونه (`register_activation_hook`) جداول زیر ساخته می‌شوند:

#### جدول ۱ — لاگ پیامک

```sql
CREATE TABLE {$wpdb->prefix}wsm_sms_log (
    id          BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    event_type  VARCHAR(50)         NOT NULL,
    recipient   VARCHAR(20)         NOT NULL,
    message     TEXT                NOT NULL,
    status      TINYINT(1)          NOT NULL DEFAULT 0,  -- 0=fail, 1=success
    api_response VARCHAR(255)        DEFAULT NULL,
    related_id  BIGINT(20) UNSIGNED DEFAULT NULL,         -- order_id یا product_id
    sent_at     DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY event_type (event_type),
    KEY sent_at (sent_at)
) {$charset_collate};
```

#### جدول ۲ — Rate Limiting ورود

```sql
CREATE TABLE {$wpdb->prefix}wsm_login_attempts (
    id           BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    ip_address   VARCHAR(45)         NOT NULL,
    attempt_time DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    is_blocked   TINYINT(1)          NOT NULL DEFAULT 0,
    blocked_until DATETIME           DEFAULT NULL,
    PRIMARY KEY (id),
    KEY ip_address (ip_address),
    KEY attempt_time (attempt_time)
) {$charset_collate};
```

### استفاده از جداول ووکامرس

افزونه از جداول اصلی ووکامرس **بدون تغییر** استفاده می‌کند:

| جدول ووکامرس | نحوه دسترسی |
|-------------|------------|
| `wp_posts` (orders/products) | از طریق `WC_Order`, `WC_Product` CRUD Classes |
| `wp_postmeta` | از طریق CRUD Classes — هرگز مستقیم |
| `wp_woocommerce_order_items` | از طریق `WC_Order::get_items()` |
| `wp_woocommerce_order_itemmeta` | از طریق Order Item Object |
| `wp_terms`, `wp_term_taxonomy` | از طریق `wp_insert_term()`, `get_terms()` |

### نمونه Query گزارش (مستقیم wpdb)

```php
// فقط برای Query های پیچیده که WC CRUD پاسخگو نیست
global $wpdb;

$results = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT 
            DATE(p.post_date) as sale_date,
            COUNT(p.ID) as order_count,
            SUM(pm.meta_value) as total_sales
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} pm 
            ON p.ID = pm.post_id AND pm.meta_key = '_order_total'
        WHERE p.post_type = 'shop_order'
            AND p.post_status IN ('wc-completed', 'wc-processing')
            AND p.post_date BETWEEN %s AND %s
        GROUP BY DATE(p.post_date)
        ORDER BY sale_date ASC",
        $date_from,
        $date_to
    )
);
```

> **قانون:** استفاده مستقیم از `$wpdb` فقط برای Query های گزارشی پیچیده مجاز است.  
> تمام عملیات Create/Update/Delete از طریق WooCommerce CRUD Classes انجام می‌شود.

---

## ۶. REST API اختصاصی

### ثبت Namespace

```php
// در WSM_Rest_Controller (abstract)
namespace WooStoreManager\Api;

abstract class WSM_Rest_Controller extends \WP_REST_Controller {

    protected string $namespace = 'wsm/v1';

    public function register_routes(): void {
        // هر Controller متد خود را Override می‌کند
    }

    protected function wsm_check_permission( \WP_REST_Request $request ): bool|\WP_Error {
        if ( ! wsm_is_authenticated() ) {
            return new \WP_Error(
                'wsm_unauthorized',
                __( 'دسترسی غیرمجاز.', 'woostore-manager' ),
                [ 'status' => 401 ]
            );
        }
        return true;
    }
}
```

### Endpoint های تعریف‌شده

#### سفارش‌ها — `/wsm/v1/orders`

| Method | Endpoint | عملکرد | Permission |
|--------|----------|--------|-----------|
| GET | `/wsm/v1/orders` | لیست سفارش‌ها (+ فیلتر/صفحه‌بندی) | `wsm_manage_orders` |
| GET | `/wsm/v1/orders/{id}` | جزئیات یک سفارش | `wsm_manage_orders` |
| PATCH | `/wsm/v1/orders/{id}/status` | تغییر وضعیت سفارش | `wsm_manage_orders` |
| POST | `/wsm/v1/orders/{id}/notes` | افزودن یادداشت | `wsm_manage_orders` |

#### محصولات — `/wsm/v1/products`

| Method | Endpoint | عملکرد | Permission |
|--------|----------|--------|-----------|
| GET | `/wsm/v1/products` | لیست محصولات (+ فیلتر) | `wsm_manage_products` |
| GET | `/wsm/v1/products/{id}` | جزئیات محصول |`wsm_manage_products` |
| POST | `/wsm/v1/products` | ایجاد محصول جدید | `wsm_manage_products` |
| PUT | `/wsm/v1/products/{id}` | ویرایش کامل محصول | `wsm_manage_products` |
| PATCH | `/wsm/v1/products/{id}/stock` | تغییر وضعیت موجودی | `wsm_manage_products` |
| DELETE | `/wsm/v1/products/{id}` | حذف (Trash) محصول | `wsm_manage_products` |

#### کوپن‌ها — `/wsm/v1/coupons`

| Method | Endpoint | عملکرد | Permission |
|--------|----------|--------|-----------|
| GET | `/wsm/v1/coupons` | لیست کوپن‌ها | `wsm_manage_coupons` |
| POST | `/wsm/v1/coupons` | ایجاد کوپن | `wsm_manage_coupons` |
| PUT | `/wsm/v1/coupons/{id}` | ویرایش کوپن | `wsm_manage_coupons` |
| DELETE | `/wsm/v1/coupons/{id}` | حذف کوپن | `wsm_manage_coupons` |

#### گزارش‌ها — `/wsm/v1/reports`

| Method | Endpoint | عملکرد | Permission |
|--------|----------|--------|-----------|
| GET | `/wsm/v1/reports/sales` | گزارش فروش | `wsm_view_reports` |
| GET | `/wsm/v1/reports/products` | گزارش محصولات | `wsm_view_reports` |
| GET | `/wsm/v1/reports/customers` | گزارش مشتریان | `wsm_view_reports` |
| GET | `/wsm/v1/reports/export` | دانلود CSV | `wsm_view_reports` |

#### پیامک — `/wsm/v1/sms`

| Method | Endpoint | عملکرد | Permission |
|--------|----------|--------|-----------|
| GET | `/wsm/v1/sms/logs` | لاگ پیامک‌ها | `manage_options` |
| POST | `/wsm/v1/sms/test` | ارسال پیامک تست | `manage_options` |

### فرمت یکسان پاسخ

```php
// WSM_Response Helper
class WSM_Response {

    public static function success( mixed $data, string $message = '', int $code = 200 ): \WP_REST_Response {
        return new \WP_REST_Response([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $code );
    }

    public static function error( string $message, int $code = 400, array $errors = [] ): \WP_REST_Response {
        return new \WP_REST_Response([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
        ], $code );
    }
}
```

---

## ۷. سیستم Routing پنل

### ثبت Custom Endpoint

```php
// class-wsm-rewrite.php
class WSM_Rewrite {

    public function add_rewrite_rules(): void {
        $slug = get_option( 'wsm_panel_slug', 'store-admin' );
        add_rewrite_rule(
            '^' . $slug . '(/(.*))?/?$',
            'index.php?wsm_panel=1&wsm_path=$matches[2]',
            'top'
        );
    }

    public function add_query_vars( array $vars ): array {
        $vars[] = 'wsm_panel';
        $vars[] = 'wsm_path';
        return $vars;
    }

    public function handle_request(): void {
        if ( ! get_query_var( 'wsm_panel' ) ) return;

        // بارگذاری Template پنل به جای تم وردپرس
        $router = new WSM_Router();
        $router->dispatch( get_query_var( 'wsm_path', '' ) );
        exit;
    }
}
```

### جدول مسیریابی

```php
// class-wsm-router.php
class WSM_Router {

    private array $routes = [
        ''                  => 'dashboard',
        'login'             => 'login',
        'orders'            => 'orders/list',
        'orders/view'       => 'orders/detail',
        'products'          => 'products/list',
        'products/new'      => 'products/edit',
        'products/edit'     => 'products/edit',
        'categories'        => 'categories/list',
        'coupons'           => 'coupons/list',
        'coupons/new'       => 'coupons/edit',
        'coupons/edit'      => 'coupons/edit',
        'reports'           => 'reports/dashboard',
        'reports/sales'     => 'reports/sales',
        'reports/products'  => 'reports/products',
        'reports/customers' => 'reports/customers',
        'reports/sms-log'   => 'reports/sms-log',
    ];

    public function dispatch( string $path ): void {
        $path = trim( $path, '/' );

        // صفحه ورود — بدون نیاز به احراز هویت
        if ( $path === 'login' ) {
            $this->render( 'login' );
            return;
        }

        // بررسی احراز هویت
        if ( ! WSM_Auth::is_authenticated() ) {
            wp_redirect( wsm_login_url() );
            exit;
        }

        $view = $this->routes[ $path ] ?? null;
        if ( ! $view ) {
            $this->render( '404' );
            return;
        }

        $this->render( $view );
    }

    private function render( string $view ): void {
        $file = WSM_PLUGIN_DIR . 'panel/views/' . $view . '.php';
        require_once WSM_PLUGIN_DIR . 'panel/layout.php';
    }
}
```

---

## ۸. یکپارچگی با WooCommerce

### اصل اساسی

> **هرگز مستقیم با جداول پایگاه داده ووکامرس کار نکنید.**  
> همیشه از WooCommerce CRUD Classes و توابع رسمی استفاده کنید.

### نمونه — ایجاد محصول

```php
// در WSM_Product_Repository
public function create( array $data ): int|\WP_Error {
    $product = new \WC_Product_Simple();

    $product->set_name( $data['name'] );
    $product->set_status( $data['status'] ?? 'publish' );
    $product->set_regular_price( $data['regular_price'] );
    $product->set_sale_price( $data['sale_price'] ?? '' );
    $product->set_manage_stock( $data['manage_stock'] ?? false );
    $product->set_stock_quantity( $data['stock_quantity'] ?? null );
    $product->set_stock_status( $data['stock_status'] ?? 'instock' );
    $product->set_category_ids( $data['category_ids'] ?? [] );
    $product->set_description( $data['description'] ?? '' );
    $product->set_short_description( $data['short_description'] ?? '' );
    $product->set_sku( $data['sku'] ?? '' );

    $product_id = $product->save();
    return $product_id > 0 ? $product_id : new \WP_Error( 'wsm_create_failed', 'خطا در ایجاد محصول.' );
}
```

### نمونه — بروزرسانی وضعیت سفارش

```php
// در WSM_Order_Repository
public function update_status( int $order_id, string $status ): bool {
    $order = wc_get_order( $order_id );
    if ( ! $order ) return false;

    $order->update_status( $status, __( 'تغییر وضعیت از پنل مدیر فروشگاه.', 'woostore-manager' ) );
    return true;
}
```

### Hook های ووکامرس که افزونه به آن‌ها گوش می‌دهد

```php
// برای ارسال پیامک
add_action( 'woocommerce_new_order',            [ $sms_service, 'on_new_order' ] );
add_action( 'woocommerce_order_status_changed', [ $sms_service, 'on_status_changed' ], 10, 4 );
add_action( 'woocommerce_low_stock',            [ $sms_service, 'on_low_stock' ] );
```

---

## ۹. یکپارچگی با Elementor

### شرط بارگذاری

```php
// class-wsm-elementor.php
class WSM_Elementor {

    public function init(): void {
        if ( ! did_action( 'elementor/loaded' ) ) return;
        add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
    }

    public function register_widgets( \Elementor\Widgets_Manager $manager ): void {
        require_once WSM_PLUGIN_DIR . 'includes/Elementor/widgets/class-wsm-login-button-widget.php';
        $manager->register( new WSM_Login_Button_Widget() );
    }
}
```

### ساختار Widget

```php
// class-wsm-login-button-widget.php
class WSM_Login_Button_Widget extends \Elementor\Widget_Base {

    public function get_name(): string   { return 'wsm_login_button'; }
    public function get_title(): string  { return __( 'دکمه ورود مدیر فروشگاه', 'woostore-manager' ); }
    public function get_icon(): string   { return 'eicon-lock-user'; }
    public function get_categories(): array { return [ 'general' ]; }

    protected function register_controls(): void {
        $this->start_controls_section( 'content_section', [
            'label' => __( 'تنظیمات دکمه', 'woostore-manager' ),
        ]);

        $this->add_control( 'button_text', [
            'label'   => __( 'متن دکمه', 'woostore-manager' ),
            'type'    => \Elementor\Controls_Manager::TEXT,
            'default' => __( 'ورود مدیر فروشگاه', 'woostore-manager' ),
        ]);

        $this->end_controls_section();
    }

    protected function render(): void {
        // فقط برای کاربران با نقش shop_manager_custom نمایش داده می‌شود
        if ( ! current_user_can( 'wsm_access_panel' ) ) return;

        $settings = $this->get_settings_for_display();
        echo '<a href="' . esc_url( wsm_panel_url() ) . '" class="wsm-login-btn">'
           . esc_html( $settings['button_text'] )
           . '</a>';
    }
}
```

### جلوگیری از تداخل TailwindCSS با Elementor

```php
// بارگذاری TailwindCSS فقط در صفحه پنل اختصاصی — نه در صفحات سایت
add_action( 'wp_enqueue_scripts', function() {
    // TailwindCSS هرگز در frontend سایت بارگذاری نمی‌شود
});

// Tailwind فقط درون layout.php پنل به صورت inline بارگذاری می‌شود
// <script src="https://cdn.tailwindcss.com"></script>
// با prefix اختصاصی در tailwind.config برای جلوگیری از تداخل
```

---

## ۱۰. جریان داده — Data Flow

### جریان یک درخواست API (مثال: تغییر وضعیت سفارش)

```
Browser (پنل)
    │
    │  PATCH /wp-json/wsm/v1/orders/123/status
    │  Headers: X-WSM-Token: {session_token}
    ▼
WSM_Orders_Controller::update_status()
    │
    ├─► WSM_Auth::verify_token()          ← احراز هویت
    │       └── بررسی wsm_session کوکی
    │
    ├─► WSM_Sanitizer::sanitize_status()  ← پاک‌سازی ورودی
    │
    ├─► current_user_can('wsm_manage_orders') ← بررسی Permission
    │
    ├─► WSM_Order_Service::update_status()  ← منطق تجاری
    │       │
    │       └─► WSM_Order_Repository::update_status()  ← دسترسی داده
    │               │
    │               └─► wc_get_order()->update_status()  ← WC CRUD
    │                       │
    │                       └─► WooCommerce Hooks (woocommerce_order_status_changed)
    │                               │
    │                               └─► WSM_SMS_Service::on_status_changed()
    │                                       └─► ملی‌پیامک API
    │
    └─► WSM_Response::success(['order_id' => 123, 'new_status' => 'completed'])
```

---

## ۱۱. سیستم Asset بارگذاری

```php
// assets فقط در صفحه پنل اختصاصی بارگذاری می‌شوند
// نه از طریق wp_enqueue_scripts — بلکه مستقیم در layout.php

// panel/layout.php
<!DOCTYPE html>
<html dir="rtl" lang="fa-IR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html( get_bloginfo('name') ); ?> — پنل مدیر فروشگاه</title>

    <!-- TailwindCSS CDN با prefix اختصاصی -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            prefix: 'wsm-',   // جلوگیری از تداخل با تم
            corePlugins: { preflight: false }  // جلوگیری از Reset CSS سراسری
        }
    </script>

    <!-- Chart.js برای گزارش‌ها -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>

    <!-- CSS اضافی افزونه -->
    <link rel="stylesheet" href="<?php echo esc_url( WSM_PLUGIN_URL . 'assets/css/wsm-panel.css' ); ?>">

    <!-- Nonce برای AJAX/REST -->
    <script>
        const wsmConfig = {
            apiUrl: '<?php echo esc_url( rest_url('wsm/v1') ); ?>',
            nonce:  '<?php echo esc_js( wp_create_nonce('wp_rest') ); ?>',
            panelUrl: '<?php echo esc_url( wsm_panel_url() ); ?>'
        };
    </script>
</head>
```

---

## ۱۲. Service Container و Dependency Injection

```php
// includes/Core/class-wsm-plugin.php
private function load_dependencies(): void {

    // Repositories
    $order_repo   = new \WooStoreManager\Repositories\WSM_Order_Repository();
    $product_repo = new \WooStoreManager\Repositories\WSM_Product_Repository();
    $coupon_repo  = new \WooStoreManager\Repositories\WSM_Coupon_Repository();
    $report_repo  = new \WooStoreManager\Repositories\WSM_Report_Repository();
    $log_repo     = new \WooStoreManager\Repositories\WSM_Log_Repository();

    // Services (DI از طریق Constructor)
    $sms_service     = new \WooStoreManager\Services\WSM_SMS_Service( $log_repo );
    $order_service   = new \WooStoreManager\Services\WSM_Order_Service( $order_repo, $sms_service );
    $product_service = new \WooStoreManager\Services\WSM_Product_Service( $product_repo, $sms_service );
    $coupon_service  = new \WooStoreManager\Services\WSM_Coupon_Service( $coupon_repo );
    $report_service  = new \WooStoreManager\Services\WSM_Report_Service( $report_repo );

    // API Controllers
    $orders_ctrl   = new \WooStoreManager\Api\WSM_Orders_Controller( $order_service );
    $products_ctrl = new \WooStoreManager\Api\WSM_Products_Controller( $product_service );
    $coupons_ctrl  = new \WooStoreManager\Api\WSM_Coupons_Controller( $coupon_service );
    $reports_ctrl  = new \WooStoreManager\Api\WSM_Reports_Controller( $report_service );

    // ثبت REST Routes
    $this->loader->add_action( 'rest_api_init', $orders_ctrl,   'register_routes' );
    $this->loader->add_action( 'rest_api_init', $products_ctrl, 'register_routes' );
    $this->loader->add_action( 'rest_api_init', $coupons_ctrl,  'register_routes' );
    $this->loader->add_action( 'rest_api_init', $reports_ctrl,  'register_routes' );

    // Auth & Router
    $auth   = new \WooStoreManager\Auth\WSM_Auth();
    $rewrite = new \WooStoreManager\Router\WSM_Rewrite();

    $this->loader->add_action( 'init',          $rewrite, 'add_rewrite_rules' );
    $this->loader->add_filter( 'query_vars',    $rewrite, 'add_query_vars' );
    $this->loader->add_action( 'template_redirect', $rewrite, 'handle_request' );

    // WooCommerce Hooks برای پیامک
    $this->loader->add_action( 'woocommerce_new_order',            $sms_service, 'on_new_order' );
    $this->loader->add_action( 'woocommerce_order_status_changed', $sms_service, 'on_status_changed', 10, 4 );
    $this->loader->add_action( 'woocommerce_low_stock',            $sms_service, 'on_low_stock' );
}
```

---

*این سند مرجع فنی پروژه است. هر تغییر در ساختار باید اینجا ثبت شود.*  
*فایل بعدی: `GUIDELINES.md` — قوانین امنیت و استانداردهای کدنویسی*

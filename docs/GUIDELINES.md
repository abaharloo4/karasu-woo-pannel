# GUIDELINES — KarasuWooPannel Plugin

**نسخه:** 1.0.5  
**تاریخ:** ۱۴۰۵/۰۴/۰۲  
**وضعیت:** Stable / Released  
**Prefix اختصاصی:** `wsm_`

---

## فهرست مطالب

1. [اصول کلی](#۱-اصول-کلی)
2. [استانداردهای نام‌گذاری](#۲-استانداردهای-نام‌گذاری)
3. [امنیت — Security](#۳-امنیت--security)
4. [Sanitization و Validation](#۴-sanitization-و-validation)
5. [Escaping خروجی](#۵-escaping-خروجی)
6. [Nonce و CSRF Protection](#۶-nonce-و-csrf-protection)
7. [Rate Limiting](#۷-rate-limiting)
8. [استانداردهای کدنویسی PHP](#۸-استانداردهای-کدنویسی-php)
9. [استانداردهای JavaScript](#۹-استانداردهای-javascript)
10. [استانداردهای دیتابیس](#۱۰-استانداردهای-دیتابیس)
11. [مدیریت خطا و Logging](#۱۱-مدیریت-خطا-و-logging)
12. [قوانین کار با WooCommerce](#۱۲-قوانین-کار-با-woocommerce)
13. [چک‌لیست پیش از Commit](#۱۳-چک‌لیست-پیش-از-commit)

---

## ۱. اصول کلی

### قوانین سخت — هرگز نقض نشوند

```
❌ هرگز مستقیم با $wpdb جداول ووکامرس را CREATE/UPDATE/DELETE نکنید
❌ هرگز echo یا print بدون Escaping ندهید
❌ هرگز $_GET / $_POST را بدون Sanitization استفاده نکنید
❌ هرگز بدون بررسی Nonce، درخواست POST را پردازش نکنید
❌ هرگز اطلاعات حساس (پسورد، کلید API) را در لاگ‌های عمومی ذخیره نکنید
❌ هرگز از eval() استفاده نکنید
❌ هرگز از serialize() برای ذخیره داده کاربر در دیتابیس استفاده نکنید
❌ هرگز فایل PHP را مستقیم قابل دسترس نگذارید (ABSPATH check اجباری)
```

### قانون اول هر فایل PHP

```php
<?php
// این خط باید اولین خط هر فایل PHP باشد
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
```

---

## ۲. استانداردهای نام‌گذاری

### Prefix اجباری: `wsm_`

تمام identifier های عمومی باید با `wsm_` شروع شوند تا از تداخل با سایر افزونه‌ها جلوگیری شود.

### کلاس‌ها

```php
// فرمت: WSM_{Context}_{Name}
// کلمات: PascalCase بعد از prefix

class WSM_Order_Service {}        // ✅ درست
class WSM_Auth {}                 // ✅ درست
class WSM_Rate_Limiter {}         // ✅ درست

class OrderService {}             // ❌ بدون prefix
class wsm_order_service {}        // ❌ lowercase
class WsmOrderService {}          // ❌ فرمت نادرست
```

### متدها و توابع

```php
// متدها: snake_case
public function get_orders(): array {}           // ✅
public function update_order_status(): bool {}   // ✅
public function getOrders(): array {}            // ❌ camelCase (WP Standard نیست)

// توابع Helper سراسری: wsm_ prefix + snake_case
function wsm_panel_url(): string {}              // ✅
function wsm_is_authenticated(): bool {}         // ✅
function wsm_get_setting( string $key ): mixed {} // ✅

function panel_url(): string {}                  // ❌ بدون prefix
```

### متغیرها

```php
$order_id    = 123;        // ✅ snake_case
$panel_slug  = 'store-admin'; // ✅
$orderId     = 123;        // ❌ camelCase
$ORDER_ID    = 123;        // ❌ ALL_CAPS (فقط برای Constants)
```

### Constants

```php
define( 'WSM_VERSION',    '1.0.0' );     // ✅ WSM_ prefix + ALL_CAPS
define( 'WSM_PLUGIN_DIR', __DIR__ );     // ✅

define( 'VERSION', '1.0.0' );           // ❌ بدون prefix (خطر تداخل)
define( 'wsm_version', '1.0.0' );       // ❌ lowercase
```

### Option های وردپرس

```php
// فرمت: wsm_{option_name}
get_option( 'wsm_panel_slug' );         // ✅
get_option( 'wsm_sms_username' );       // ✅
get_option( 'wsm_session_lifetime' );   // ✅

get_option( 'panel_slug' );             // ❌ بدون prefix
```

### Hook ها

```php
// Action های اختصاصی: wsm_{context}_{event}
do_action( 'wsm_before_order_status_update', $order_id, $status );  // ✅
do_action( 'wsm_after_product_create', $product_id );                // ✅

// Filter های اختصاصی
apply_filters( 'wsm_order_list_args', $args );                       // ✅
apply_filters( 'wsm_sms_message', $message, $event_type );           // ✅
```

### جداول دیتابیس

```php
// فرمت: {$wpdb->prefix}wsm_{table_name}
$wpdb->prefix . 'wsm_sms_log'         // → wp_wsm_sms_log         ✅
$wpdb->prefix . 'wsm_login_attempts'  // → wp_wsm_login_attempts   ✅
```

### فایل‌ها

```
class-wsm-order-service.php     ✅ (class- prefix + kebab-case)
interface-wsm-repository.php    ✅ (interface- prefix)
trait-wsm-singleton.php         ✅ (trait- prefix)
wsm-panel.css                   ✅ (asset files: wsm- prefix + kebab-case)
wsm-panel.js                    ✅

OrderService.php                ❌
order_service.php               ❌
```

---

## ۳. امنیت — Security

### اصل Least Privilege

```php
// هر مدیر فروشگاه فقط Capability های مورد نیاز خود را دارد
// تعریف در WSM_Capabilities
const CAPABILITIES = [
    'wsm_access_panel',     // دسترسی به پنل
    'wsm_manage_orders',    // مدیریت سفارش‌ها
    'wsm_manage_products',  // مدیریت محصولات
    'wsm_manage_coupons',   // مدیریت کوپن‌ها
    'wsm_view_reports',     // مشاهده گزارش‌ها
];

// بررسی Permission قبل از هر عملیات
public function update_order_status( int $order_id, string $status ): bool|\WP_Error {
    if ( ! current_user_can( 'wsm_manage_orders' ) ) {
        return new \WP_Error( 'wsm_forbidden', 'دسترسی غیرمجاز.', [ 'status' => 403 ] );
    }
    // ادامه عملیات...
}
```

### محافظت در برابر دسترسی مستقیم

```php
// در REST Controller — بررسی احراز هویت پنل
protected function wsm_check_permission( \WP_REST_Request $request ): bool|\WP_Error {
    // ۱. بررسی Session Token از هدر یا کوکی
    $token = $request->get_header( 'X-WSM-Token' )
          ?? ( $_COOKIE['wsm_session'] ?? '' );

    if ( ! WSM_Auth::verify_token( sanitize_text_field( $token ) ) ) {
        return new \WP_Error( 'wsm_unauthorized', 'نشست منقضی شده است.', [ 'status' => 401 ] );
    }

    // ۲. بررسی Capability
    if ( ! current_user_can( $this->required_capability ) ) {
        return new \WP_Error( 'wsm_forbidden', 'دسترسی غیرمجاز.', [ 'status' => 403 ] );
    }

    return true;
}
```

### Session Security

```php
// تنظیمات کوکی امن
setcookie(
    'wsm_session',
    $token,
    [
        'expires'  => time() + ( get_option( 'wsm_session_lifetime', 8 ) * HOUR_IN_SECONDS ),
        'path'     => '/',
        'domain'   => '',
        'secure'   => is_ssl(),      // فقط روی HTTPS
        'httponly' => true,          // غیرقابل دسترس از JavaScript
        'samesite' => 'Strict',      // جلوگیری از CSRF
    ]
);

// تولید Token امن
private function generate_token(): string {
    return bin2hex( random_bytes( 32 ) ); // 64 کاراکتر hex
}

// Regenerate Token پس از Login
public function login( string $username, string $password ): bool {
    // ...احراز هویت...
    $token = $this->generate_token();
    // ذخیره Token هش‌شده در User Meta
    update_user_meta( $user->ID, 'wsm_session_token', wp_hash( $token ) );
    // تنظیم کوکی
    $this->set_cookie( $token );
    return true;
}
```

---

## ۴. Sanitization و Validation

### قانون اساسی

> **همیشه در لحظه ورود داده، Sanitize کنید. همیشه در لحظه خروج، Escape کنید.**

### توابع Sanitize اجباری

```php
// متن ساده (نام، عنوان)
$name = sanitize_text_field( $_POST['name'] ?? '' );

// ایمیل
$email = sanitize_email( $_POST['email'] ?? '' );

// URL
$url = esc_url_raw( $_POST['url'] ?? '' );

// عدد صحیح
$order_id = absint( $_GET['order_id'] ?? 0 );

// عدد اعشاری (قیمت)
$price = (float) wc_format_decimal( $_POST['price'] ?? 0 );

// HTML با تگ‌های مجاز (توضیحات محصول)
$description = wp_kses_post( $_POST['description'] ?? '' );

// متن چندخطی بدون HTML
$note = sanitize_textarea_field( $_POST['note'] ?? '' );

// کلید/Slug
$slug = sanitize_title( $_POST['slug'] ?? '' );

// وضعیت سفارش — Whitelist
$allowed_statuses = [ 'pending', 'processing', 'on-hold', 'completed', 'cancelled', 'refunded' ];
$status = in_array( $_POST['status'] ?? '', $allowed_statuses, true )
    ? $_POST['status']
    : 'pending';
```

### کلاس WSM_Sanitizer

```php
// includes/Helpers/class-wsm-sanitizer.php
namespace WooStoreManager\Helpers;

class WSM_Sanitizer {

    // Sanitize آرایه داده‌های محصول
    public static function product_data( array $data ): array {
        return [
            'name'              => sanitize_text_field( $data['name'] ?? '' ),
            'status'            => self::post_status( $data['status'] ?? 'publish' ),
            'regular_price'     => wc_format_decimal( $data['regular_price'] ?? 0 ),
            'sale_price'        => '' !== ( $data['sale_price'] ?? '' )
                                    ? wc_format_decimal( $data['sale_price'] )
                                    : '',
            'description'       => wp_kses_post( $data['description'] ?? '' ),
            'short_description' => wp_kses_post( $data['short_description'] ?? '' ),
            'sku'               => sanitize_text_field( $data['sku'] ?? '' ),
            'manage_stock'      => (bool) ( $data['manage_stock'] ?? false ),
            'stock_quantity'    => absint( $data['stock_quantity'] ?? 0 ),
            'stock_status'      => self::stock_status( $data['stock_status'] ?? 'instock' ),
            'category_ids'      => array_map( 'absint', (array) ( $data['category_ids'] ?? [] ) ),
            'slug'              => sanitize_title( $data['slug'] ?? '' ),
        ];
    }

    // Whitelist برای وضعیت‌های سفارش
    public static function order_status( string $status ): string {
        $allowed = [ 'pending', 'processing', 'on-hold', 'completed', 'cancelled', 'refunded', 'failed' ];
        return in_array( $status, $allowed, true ) ? $status : 'pending';
    }

    // Whitelist برای وضعیت‌های post
    public static function post_status( string $status ): string {
        $allowed = [ 'publish', 'draft', 'pending', 'private' ];
        return in_array( $status, $allowed, true ) ? $status : 'draft';
    }

    // Whitelist برای وضعیت موجودی
    public static function stock_status( string $status ): string {
        $allowed = [ 'instock', 'outofstock', 'onbackorder' ];
        return in_array( $status, $allowed, true ) ? $status : 'instock';
    }

    // Sanitize شماره موبایل ایرانی
    public static function phone_number( string $phone ): string {
        $phone = preg_replace( '/[^0-9+]/', '', $phone );
        // تبدیل فرمت‌های مختلف به 09xxxxxxxxx
        if ( str_starts_with( $phone, '+98' ) ) {
            $phone = '0' . substr( $phone, 3 );
        } elseif ( str_starts_with( $phone, '98' ) && strlen( $phone ) === 12 ) {
            $phone = '0' . substr( $phone, 2 );
        }
        return preg_match( '/^09[0-9]{9}$/', $phone ) ? $phone : '';
    }
}
```

---

## ۵. Escaping خروجی

### قانون اساسی

> **هر متغیری که در HTML چاپ می‌شود باید Escape شود، حتی اگر مطمئن باشید داده‌اش امن است.**

### توابع Escape اجباری

```php
// متن داخل HTML
echo esc_html( $product_name );

// متن داخل HTML Attribute
echo '<input value="' . esc_attr( $value ) . '">';

// URL در href یا src
echo '<a href="' . esc_url( $url ) . '">';

// داده در تگ <script>
echo '<script>var name = ' . wp_json_encode( $name ) . ';</script>';

// متن در JavaScript (درون رشته JS)
echo 'var msg = "' . esc_js( $message ) . '";';

// HTML کامل (وقتی داده از wp_kses_post عبور کرده)
echo wp_kses_post( $description );

// Textarea
echo esc_textarea( $content );
```

### ❌ اشتباهات رایج

```php
// ❌ NEVER — هرگز متغیر را مستقیم echo نکنید
echo $product_name;
echo $_GET['search'];
echo $order->get_billing_first_name();

// ✅ ALWAYS
echo esc_html( $product_name );
echo esc_html( sanitize_text_field( $_GET['search'] ?? '' ) );
echo esc_html( $order->get_billing_first_name() );
```

### Late Escaping در Templates

```php
// ❌ Escape زودهنگام (در Service یا Repository)
public function get_product_name(): string {
    return esc_html( $this->product->get_name() ); // اشتباه — ممکن است داده در جای دیگری استفاده شود
}

// ✅ Escape دیرهنگام (در Template/View)
// در Service:
public function get_product_name(): string {
    return $this->product->get_name(); // داده خام
}
// در View:
echo esc_html( $product_service->get_product_name() ); // ✅
```

---

## ۶. Nonce و CSRF Protection

### برای فرم‌های HTML

```php
// ایجاد Nonce در Template
<form method="post">
    <?php wp_nonce_field( 'wsm_update_order_' . $order_id, 'wsm_nonce' ); ?>
    <!-- فیلدهای فرم -->
</form>

// بررسی Nonce در Handler
public function handle_update_order(): void {
    if ( ! isset( $_POST['wsm_nonce'] ) ||
         ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wsm_nonce'] ) ), 'wsm_update_order_' . absint( $_POST['order_id'] ?? 0 ) ) ) {
        wp_die( esc_html__( 'خطای امنیتی. لطفاً دوباره تلاش کنید.', 'woostore-manager' ) );
    }
    // ادامه پردازش...
}
```

### برای AJAX Requests

```php
// ارسال Nonce از JavaScript
const response = await fetch( wsmConfig.apiUrl + '/orders/' + orderId + '/status', {
    method: 'PATCH',
    headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce':   wsmConfig.nonce,   // WordPress REST API Nonce
        'X-WSM-Token':  wsmConfig.sessionToken,
    },
    body: JSON.stringify({ status: newStatus }),
});

// بررسی خودکار در REST API (WordPress این کار را می‌کند)
// اما برای AJAX های غیر REST:
add_action( 'wp_ajax_wsm_update_order', function() {
    check_ajax_referer( 'wsm_ajax_nonce', 'nonce' );
    // ...
});
```

### قوانین Nonce

```
✅ هر Action باید Action String منحصربه‌فرد داشته باشد
✅ برای عملیات روی یک آیتم خاص، ID را در Action String بگنجانید
✅ Nonce را قبل از هر پردازش داده بررسی کنید
✅ از wp_verify_nonce() برای فرم‌ها و check_ajax_referer() برای AJAX استفاده کنید
✅ REST API به صورت خودکار X-WP-Nonce را بررسی می‌کند
```

---

## ۷. Rate Limiting

### پیاده‌سازی برای فرم ورود

```php
// includes/Auth/class-wsm-rate-limiter.php
namespace WooStoreManager\Auth;

class WSM_Rate_Limiter {

    private const MAX_ATTEMPTS    = 5;
    private const LOCKOUT_MINUTES = 30;
    private const WINDOW_MINUTES  = 15;

    public function is_blocked( string $ip ): bool {
        global $wpdb;
        $table = $wpdb->prefix . 'wsm_login_attempts';

        $result = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT is_blocked, blocked_until FROM {$table}
                 WHERE ip_address = %s
                 ORDER BY id DESC LIMIT 1",
                $ip
            )
        );

        if ( ! $result ) return false;

        if ( $result->is_blocked && strtotime( $result->blocked_until ) > time() ) {
            return true;
        }

        // اگر زمان بلاک تمام شده، ریست کن
        if ( $result->is_blocked ) {
            $this->reset( $ip );
        }

        return false;
    }

    public function record_attempt( string $ip ): void {
        global $wpdb;
        $table = $wpdb->prefix . 'wsm_login_attempts';

        $wpdb->insert( $table, [
            'ip_address'   => $ip,
            'attempt_time' => current_time( 'mysql' ),
            'is_blocked'   => 0,
        ]);

        // بررسی تعداد تلاش‌ها در پنجره زمانی
        $attempts = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$table}
             WHERE ip_address = %s
             AND attempt_time > DATE_SUB(NOW(), INTERVAL %d MINUTE)
             AND is_blocked = 0",
            $ip, self::WINDOW_MINUTES
        ));

        if ( (int) $attempts >= self::MAX_ATTEMPTS ) {
            $this->block( $ip );
        }
    }

    private function block( string $ip ): void {
        global $wpdb;
        $table = $wpdb->prefix . 'wsm_login_attempts';

        $blocked_until = gmdate( 'Y-m-d H:i:s', time() + ( self::LOCKOUT_MINUTES * MINUTE_IN_SECONDS ) );

        $wpdb->update(
            $table,
            [ 'is_blocked' => 1, 'blocked_until' => $blocked_until ],
            [ 'ip_address' => $ip ],
            [ '%d', '%s' ],
            [ '%s' ]
        );
    }

    public function get_remaining_lockout( string $ip ): int {
        global $wpdb;
        $table = $wpdb->prefix . 'wsm_login_attempts';

        $blocked_until = $wpdb->get_var( $wpdb->prepare(
            "SELECT blocked_until FROM {$table}
             WHERE ip_address = %s AND is_blocked = 1
             ORDER BY id DESC LIMIT 1",
            $ip
        ));

        if ( ! $blocked_until ) return 0;
        return max( 0, (int) ceil( ( strtotime( $blocked_until ) - time() ) / 60 ) );
    }

    private function reset( string $ip ): void {
        global $wpdb;
        $wpdb->delete( $wpdb->prefix . 'wsm_login_attempts', [ 'ip_address' => $ip ] );
    }

    // دریافت IP واقعی کاربر
    public static function get_client_ip(): string {
        $ip_keys = [ 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' ];
        foreach ( $ip_keys as $key ) {
            if ( ! empty( $_SERVER[ $key ] ) ) {
                $ip = explode( ',', sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) ) );
                return trim( $ip[0] );
            }
        }
        return '0.0.0.0';
    }
}
```

---

## ۸. استانداردهای کدنویسی PHP

### فرمت پایه — WordPress Coding Standards

```php
<?php
// ✅ Tab برای indent (نه Space)
if ( condition ) {          // فاصله بعد از if — قبل و بعد از پرانتز
    do_something();
} elseif ( other ) {        // elseif (نه else if)
    do_other();
} else {
    fallback();
}

// ✅ فاصله دور از عملگرها
$value = $a + $b;
$array = [ 'key' => 'value' ];   // Short Array Syntax

// ✅ Yoda Conditions برای مقایسه
if ( 'completed' === $status ) {}  // ✅ Yoda
if ( $status === 'completed' ) {}  // ❌ نه Yoda

// ✅ Type Hinting اجباری (PHP 8.0+)
public function get_order( int $id ): ?\WC_Order {}
public function create_product( array $data ): int|\WP_Error {}
public function is_valid(): bool {}
```

### DocBlocks اجباری

```php
/**
 * دریافت لیست سفارش‌ها با فیلتر.
 *
 * @since  1.0.0
 * @param  array{
 *     status?: string,
 *     date_from?: string,
 *     date_to?: string,
 *     per_page?: int,
 *     page?: int
 * } $args آرگومان‌های فیلتر.
 * @return array{
 *     orders: WC_Order[],
 *     total: int,
 *     pages: int
 * }
 */
public function get_orders( array $args = [] ): array {
    // ...
}
```

### مدیریت Return Early

```php
// ✅ Return Early — کاهش تورفتگی
public function process_order( int $order_id ): bool|\WP_Error {
    if ( $order_id <= 0 ) {
        return new \WP_Error( 'wsm_invalid_id', 'شناسه سفارش نامعتبر است.' );
    }

    $order = wc_get_order( $order_id );
    if ( ! $order ) {
        return new \WP_Error( 'wsm_not_found', 'سفارش یافت نشد.' );
    }

    if ( ! current_user_can( 'wsm_manage_orders' ) ) {
        return new \WP_Error( 'wsm_forbidden', 'دسترسی غیرمجاز.' );
    }

    // منطق اصلی بدون تورفتگی اضافه
    return $this->repository->process( $order );
}
```

---

## ۹. استانداردهای JavaScript

### ساختار کلی

```javascript
/**
 * KarasuWooPannel — Panel Scripts
 * @version 1.0.5
 */
( function() {
    'use strict';

    // wsmConfig از PHP تزریق شده (در layout.php)
    const { apiUrl, nonce, panelUrl, sessionToken } = window.wsmConfig ?? {};

    /**
     * ارسال درخواست به REST API پنل
     * @param {string} endpoint
     * @param {Object} options
     * @returns {Promise<Object>}
     */
    async function wsmFetch( endpoint, options = {} ) {
        const defaults = {
            headers: {
                'Content-Type':  'application/json',
                'X-WP-Nonce':    nonce,
                'X-WSM-Token':   sessionToken,
            },
        };

        try {
            const response = await fetch( apiUrl + endpoint, { ...defaults, ...options } );

            if ( response.status === 401 ) {
                // Session منقضی شده — ریدایرکت به ورود
                window.location.href = panelUrl + '/login';
                return;
            }

            const data = await response.json();

            if ( ! response.ok || ! data.success ) {
                throw new Error( data.message ?? 'خطای ناشناخته' );
            }

            return data;
        } catch ( error ) {
            wsmShowError( error.message );
            throw error;
        }
    }

    // Escape HTML برای جلوگیری از XSS در JavaScript
    function wsmEscHtml( str ) {
        const div = document.createElement( 'div' );
        div.appendChild( document.createTextNode( String( str ) ) );
        return div.innerHTML;
    }

    // نمایش خطا به کاربر
    function wsmShowError( message ) {
        const el = document.getElementById( 'wsm-error-toast' );
        if ( el ) {
            el.textContent = message; // textContent نه innerHTML
            el.classList.remove( 'wsm-hidden' );
            setTimeout( () => el.classList.add( 'wsm-hidden' ), 5000 );
        }
    }

    // Export برای استفاده در سایر فایل‌ها
    window.WSM = { fetch: wsmFetch, escHtml: wsmEscHtml, showError: wsmShowError };

} )();
```

### قوانین JavaScript

```
✅ همیشه 'use strict' در ابتدای IIFE
✅ هیچ‌گاه innerHTML = متغیر_کاربر نگذارید — از textContent یا wsmEscHtml استفاده کنید
✅ از wsmFetch() به جای fetch() مستقیم استفاده کنید (headers امنیتی دارد)
✅ خطاهای async را با try/catch مدیریت کنید
✅ Nonce را از wsmConfig بخوانید — هرگز هاردکد نکنید
✅ eventListener ها را در DOMContentLoaded ثبت کنید
```

---

## ۱۰. استانداردهای دیتابیس

### قانون Prepared Statements — هرگز نقض نشود

```php
// ❌ NEVER — آسیب‌پذیر به SQL Injection
$results = $wpdb->get_results(
    "SELECT * FROM {$wpdb->posts} WHERE post_status = '$status'"
);

// ✅ ALWAYS — Prepared Statement
$results = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM {$wpdb->posts} WHERE post_status = %s",
        $status
    )
);
```

### Placeholder های $wpdb->prepare()

```php
%s  → string
%d  → integer
%f  → float
%i  → identifier (table/column name) — PHP 8.2+

// نمونه چند placeholder
$wpdb->prepare(
    "SELECT * FROM %i WHERE meta_key = %s AND meta_value > %d",
    $wpdb->prefix . 'wsm_sms_log',
    $event_type,
    $min_id
);
```

### Cache کردن Query ها

```php
// برای Query های پرتکرار از Transient استفاده کنید
public function get_top_products( int $limit = 5 ): array {
    $cache_key = "wsm_top_products_{$limit}";
    $cached    = get_transient( $cache_key );

    if ( false !== $cached ) {
        return $cached;
    }

    $results = $this->query_top_products( $limit );

    set_transient( $cache_key, $results, HOUR_IN_SECONDS );
    return $results;
}

// پاک‌سازی Cache پس از تغییر داده
public function create_product( array $data ): int|\WP_Error {
    $product_id = $this->insert( $data );
    if ( ! is_wp_error( $product_id ) ) {
        delete_transient( 'wsm_top_products_5' );
    }
    return $product_id;
}
```

---

## ۱۱. مدیریت خطا و Logging

### استفاده از WP_Error

```php
// بازگشت WP_Error — نه throw Exception (WP Standard)
public function get_order( int $id ): \WC_Order|\WP_Error {
    if ( $id <= 0 ) {
        return new \WP_Error(
            'wsm_invalid_id',          // error code
            'شناسه نامعتبر است.',       // پیام برای کاربر
            [ 'id' => $id ]            // داده اضافی برای Debug
        );
    }
    $order = wc_get_order( $id );
    return $order ?: new \WP_Error( 'wsm_not_found', 'سفارش یافت نشد.' );
}

// بررسی WP_Error در فراخوان
$order = $order_service->get_order( $id );
if ( is_wp_error( $order ) ) {
    return WSM_Response::error( $order->get_error_message() );
}
```

### Logging امن

```php
// فقط در محیط Development
private function log( string $message, array $context = [] ): void {
    if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) return;
    if ( ! defined( 'WP_DEBUG_LOG' ) || ! WP_DEBUG_LOG ) return;

    // ❌ هرگز اطلاعات حساس را لاگ نکنید
    // unset( $context['password'], $context['sms_password'], $context['api_key'] );

    error_log( sprintf(
        '[WSM] %s | Context: %s',
        $message,
        wp_json_encode( $context )
    ));
}
```

---

## ۱۲. قوانین کار با WooCommerce

```
✅ برای ایجاد/ویرایش/حذف محصول: WC_Product CRUD Classes
✅ برای ایجاد/ویرایش/حذف سفارش: WC_Order CRUD Classes
✅ برای ایجاد/ویرایش/حذف کوپن: WC_Coupon CRUD Classes
✅ برای دریافت قیمت: $product->get_price() — نه مستقیم از postmeta
✅ برای تبدیل قیمت: wc_price(), wc_format_decimal()
✅ برای وضعیت سفارش: wc_get_order_statuses() برای لیست مجاز

❌ هرگز مستقیم در wp_postmeta برای سفارش/محصول INSERT/UPDATE نکنید
❌ هرگز از $order->post->post_status استفاده نکنید — از $order->get_status() استفاده کنید
❌ هرگز WC Tables را مستقیم DROP یا ALTER نکنید
```

---

## ۱۳. چک‌لیست پیش از Commit

قبل از هر Commit این موارد را بررسی کنید:

```
امنیت:
  [ ] تمام ورودی‌های $_GET/$_POST/$_COOKIE با توابع sanitize مناسب پاک‌سازی شده‌اند
  [ ] تمام خروجی‌های HTML با esc_html/esc_attr/esc_url escape شده‌اند
  [ ] تمام درخواست‌های POST دارای بررسی Nonce هستند
  [ ] تمام Endpoint های REST دارای بررسی Permission هستند
  [ ] هیچ اطلاعات حساسی در error_log ثبت نمی‌شود

کدنویسی:
  [ ] تمام فایل‌های PHP با if (!defined('ABSPATH')) exit; شروع می‌شوند
  [ ] تمام کلاس‌ها دارای prefix WSM_ هستند
  [ ] تمام توابع سراسری دارای prefix wsm_ هستند
  [ ] تمام Option های وردپرس دارای prefix wsm_ هستند
  [ ] DocBlock برای متدهای public نوشته شده

دیتابیس:
  [ ] تمام Query های مستقیم از $wpdb->prepare() استفاده می‌کنند
  [ ] هیچ Query ای روی جداول ووکامرس INSERT/UPDATE/DELETE مستقیم ندارد
  [ ] Transient ها پس از تغییر داده پاک می‌شوند

تست:
  [ ] عملکرد در WordPress 6.0+ تست شده
  [ ] عملکرد در WooCommerce 7.0+ تست شده
  [ ] تداخل با Elementor بررسی شده (صفحات Elementor بدون JS Error هستند)
  [ ] صفحه ورود پنل بدون اتصال به /wp-admin کار می‌کند
```

---

*این سند قانون اساسی توسعه است. هر استثنا باید با دلیل مستند شود.*  
*فایل بعدی: `ROADMAP.md` — فازبندی توسعه*

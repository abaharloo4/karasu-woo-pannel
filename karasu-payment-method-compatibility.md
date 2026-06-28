# راهنمای سازگاری افزونه کارت‌به‌کارت با پنل مدیریت کاراسو (KarasuWooPannel)

افزونه **پنل مدیریت کاراسو** به دو روش کاملاً پویا و خودکار می‌تواند رسیدهای پرداخت افزونه کارت‌به‌کارت را شناسایی کرده و در بخش جزئیات سفارش نمایش دهد:

---

## روش اول: ساختار اختصاصی و ایمن با متای `_kpm_receipt_files` (توصیه شده)

اگر افزونه کارت‌به‌کارت از کلید متای اختصاصی `_kpm_receipt_files` استفاده کند، پنل مدیریت فایل‌ها را شناسایی کرده و آن‌ها را به صورت امن و غیرقابل دسترسی عمومی (توسط فایل `.htaccess`) از طریق API پنل لود می‌کند.

### ساختار داده در پایگاه‌داده:
مقدار متای `_kpm_receipt_files` باید یک **آرایه از آرایه‌ها** باشد. هر عضو آرایه نشان‌دهنده یک فایل رسید آپلود شده با کلیدهای زیر است:
- `file_path`: مسیر نسبی فایل از پوشه `wp-content/uploads/` (مثال: `kpm-receipts/617/randomhash.png`).
- `file_name`: نام اصلی فایل آپلود شده توسط کاربر (مثال: `my-receipt.png`).
- `file_hash`: یک رشته رندوم یکتا که به عنوان شناسه فایل جهت لود امن استفاده می‌شود.
- `uploaded_at`: تایم‌استمپ زمان آپلود.

### نمونه کد ذخیره‌سازی در افزونه کارت‌به‌کارت:
```php
// آماده‌سازی آرایه اطلاعات رسید
$relative_path = 'kpm-receipts/' . $order_id . '/' . $new_filename;
$random_hash   = wp_generate_password( 16, false );

$receipt_data = [
    'file_path'   => $relative_path,
    'file_name'   => $original_filename,
    'uploaded_at' => current_time( 'timestamp' ),
    'file_hash'   => $random_hash,
];

// دریافت رسیدهای قبلی سفارش و افزودن رسید جدید
$existing_receipts = $order->get_meta( '_kpm_receipt_files', true );
if ( ! is_array( $existing_receipts ) ) {
    $existing_receipts = [];
}
$existing_receipts[] = $receipt_data;

// ذخیره در متای سفارش (سازگار با HPOS و ساختار پست قدیم)
$order->update_meta_data( '_kpm_receipt_files', $existing_receipts );
$order->save();
```

---

## روش دوم: ساختار عمومی (شناسایی هوشمند کلمات کلیدی)

اگر افزونه کارت‌به‌کارت مایل به استفاده از متای اختصاصی نباشد، پنل به صورت هوشمند تمامی متادیتاهای سفارش را اسکن کرده و در صورت پیدا کردن کدهای کلیدی، رسید را در پنل رندر می‌کند.

### نحوه شناسایی:
اگر نام کلید متای سفارش (Meta Key) شامل یکی از کلمات زیر باشد:
`receipt`, `card_to_card`, `card2card`, `payment_image`, `receipt_image`, `receipt_file`, `transaction_image`, `payment_receipt`, `fish`, `c2c`

و مقدار متادیتا (Meta Value) یکی از موارد زیر باشد:
1. **شناسه پیوست (Attachment ID):** مثلاً عدد `1234` که شناسه تصویر آپلود شده در رسانه‌های وردپرس است.
2. **آدرس لینک مستقیم (URL):** مثلاً آدرس مستقیم تصویر رسید `https://site.com/uploads/.../fish.jpg`.

پنل به صورت خودکار آن را شناسایی کرده و به عنوان تصویر رسید پرداخت در صفحه سفارش نمایش می‌دهد.

### نمونه کد برای متای عمومی:
```php
// ذخیره شناسه مدیا به عنوان رسید پرداخت
$order->update_meta_data( '_receipt_image_id', $attachment_id );
$order->save();
```
||
```php
// ذخیره لینک مستقیم تصویر رسید
$order->update_meta_data( '_kpm_payment_receipt_url', $file_url );
$order->save();
```

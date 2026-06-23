<?php
/**
 * SMS Configuration Panel View Template
 *
 * @package KarasuWooPannel
 * @version 1.0.4
 * @date 2026-06-23
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div class="wsm-space-y-6">
	<div class="wsm-flex wsm-items-center wsm-justify-between">
		<h1 class="wsm-text-2xl wsm-font-bold wsm-text-slate-100">تنظیمات پیامک (ملی‌پیامک)</h1>
	</div>

	<form id="wsm-sms-settings-form" class="wsm-grid wsm-grid-cols-1 lg:wsm-grid-cols-3 wsm-gap-6">
		<!-- Right Side: Templates Columns -->
		<div class="lg:wsm-col-span-2 wsm-space-y-6">
			<!-- Customer Templates Section -->
			<div class="wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-p-6 wsm-shadow-lg wsm-space-y-4">
				<h3 class="wsm-font-semibold wsm-text-slate-200">قالب‌های پیامکی خریدار</h3>
				<p class="wsm-text-xs wsm-text-slate-400">قالب‌های پیامک ارسالی به خریداران در وضعیت‌های مختلف سفارش را مدیریت کنید.</p>
				
				<div class="wsm-divide-y wsm-divide-slate-800/40 wsm-space-y-4">
					<!-- Pending Template -->
					<div class="wsm-pt-4 wsm-space-y-3">
						<div class="wsm-flex wsm-items-center wsm-justify-between">
							<span class="wsm-text-sm wsm-font-medium wsm-text-slate-300">در انتظار پرداخت (Pending)</span>
							<label class="wsm-flex wsm-items-center wsm-cursor-pointer">
								<input type="checkbox" id="sms-pending-enabled" class="wsm-ml-2">
								<span class="wsm-text-xs wsm-text-slate-400">فعال</span>
							</label>
						</div>
						<textarea id="sms-pending-text" rows="2" class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-p-3 wsm-text-xs wsm-text-slate-200 focus:wsm-outline-none"></textarea>
					</div>

					<!-- Processing Template -->
					<div class="wsm-pt-4 wsm-space-y-3">
						<div class="wsm-flex wsm-items-center wsm-justify-between">
							<span class="wsm-text-sm wsm-font-medium wsm-text-slate-300">در حال پردازش / ثبت سفارش (Processing)</span>
							<label class="wsm-flex wsm-items-center wsm-cursor-pointer">
								<input type="checkbox" id="sms-processing-enabled" class="wsm-ml-2">
								<span class="wsm-text-xs wsm-text-slate-400">فعال</span>
							</label>
						</div>
						<textarea id="sms-processing-text" rows="2" class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-p-3 wsm-text-xs wsm-text-slate-200 focus:wsm-outline-none"></textarea>
					</div>

					<!-- On Hold Template -->
					<div class="wsm-pt-4 wsm-space-y-3">
						<div class="wsm-flex wsm-items-center wsm-justify-between">
							<span class="wsm-text-sm wsm-font-medium wsm-text-slate-300">معلق (On Hold)</span>
							<label class="wsm-flex wsm-items-center wsm-cursor-pointer">
								<input type="checkbox" id="sms-on-hold-enabled" class="wsm-ml-2">
								<span class="wsm-text-xs wsm-text-slate-400">فعال</span>
							</label>
						</div>
						<textarea id="sms-on-hold-text" rows="2" class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-p-3 wsm-text-xs wsm-text-slate-200 focus:wsm-outline-none"></textarea>
					</div>

					<!-- Completed Template -->
					<div class="wsm-pt-4 wsm-space-y-3">
						<div class="wsm-flex wsm-items-center wsm-justify-between">
							<span class="wsm-text-sm wsm-font-medium wsm-text-slate-300">تکمیل شده / ارسال شده (Completed)</span>
							<label class="wsm-flex wsm-items-center wsm-cursor-pointer">
								<input type="checkbox" id="sms-completed-enabled" class="wsm-ml-2">
								<span class="wsm-text-xs wsm-text-slate-400">فعال</span>
							</label>
						</div>
						<textarea id="sms-completed-text" rows="2" class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-p-3 wsm-text-xs wsm-text-slate-200 focus:wsm-outline-none"></textarea>
					</div>

					<!-- Cancelled Template -->
					<div class="wsm-pt-4 wsm-space-y-3">
						<div class="wsm-flex wsm-items-center wsm-justify-between">
							<span class="wsm-text-sm wsm-font-medium wsm-text-slate-300">لغو شده (Cancelled)</span>
							<label class="wsm-flex wsm-items-center wsm-cursor-pointer">
								<input type="checkbox" id="sms-cancelled-enabled" class="wsm-ml-2">
								<span class="wsm-text-xs wsm-text-slate-400">فعال</span>
							</label>
						</div>
						<textarea id="sms-cancelled-text" rows="2" class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-p-3 wsm-text-xs wsm-text-slate-200 focus:wsm-outline-none"></textarea>
					</div>

					<!-- Refunded Template -->
					<div class="wsm-pt-4 wsm-space-y-3">
						<div class="wsm-flex wsm-items-center wsm-justify-between">
							<span class="wsm-text-sm wsm-font-medium wsm-text-slate-300">مسترد شده (Refunded)</span>
							<label class="wsm-flex wsm-items-center wsm-cursor-pointer">
								<input type="checkbox" id="sms-refunded-enabled" class="wsm-ml-2">
								<span class="wsm-text-xs wsm-text-slate-400">فعال</span>
							</label>
						</div>
						<textarea id="sms-refunded-text" rows="2" class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-p-3 wsm-text-xs wsm-text-slate-200 focus:wsm-outline-none"></textarea>
					</div>

					<!-- Failed Template -->
					<div class="wsm-pt-4 wsm-space-y-3">
						<div class="wsm-flex wsm-items-center wsm-justify-between">
							<span class="wsm-text-sm wsm-font-medium wsm-text-slate-300">پرداخت ناموفق (Failed)</span>
							<label class="wsm-flex wsm-items-center wsm-cursor-pointer">
								<input type="checkbox" id="sms-failed-enabled" class="wsm-ml-2">
								<span class="wsm-text-xs wsm-text-slate-400">فعال</span>
							</label>
						</div>
						<textarea id="sms-failed-text" rows="2" class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-p-3 wsm-text-xs wsm-text-slate-200 focus:wsm-outline-none"></textarea>
					</div>
				</div>
			</div>

			<!-- Admin Templates Section -->
			<div class="wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-p-6 wsm-shadow-lg wsm-space-y-4">
				<h3 class="wsm-font-semibold wsm-text-slate-200">قالب‌های پیامکی مدیر</h3>
				<p class="wsm-text-xs wsm-text-slate-400">قالب‌های پیامک ارسالی به مدیر سایت در زمان رویدادهای خاص را مدیریت کنید.</p>

				<div class="wsm-divide-y wsm-divide-slate-800/40 wsm-space-y-4">
					<!-- New Order Admin -->
					<div class="wsm-pt-4 wsm-space-y-3">
						<div class="wsm-flex wsm-items-center wsm-justify-between">
							<span class="wsm-text-sm wsm-font-medium wsm-text-slate-300">سفارش جدید (New Order)</span>
							<label class="wsm-flex wsm-items-center wsm-cursor-pointer">
								<input type="checkbox" id="sms-new-order-enabled" class="wsm-ml-2">
								<span class="wsm-text-xs wsm-text-slate-400">فعال</span>
							</label>
						</div>
						<textarea id="sms-new-order-text" rows="2" class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-p-3 wsm-text-xs wsm-text-slate-200 focus:wsm-outline-none"></textarea>
					</div>

					<!-- Low Stock Admin -->
					<div class="wsm-pt-4 wsm-space-y-3">
						<div class="wsm-flex wsm-items-center wsm-justify-between">
							<span class="wsm-text-sm wsm-font-medium wsm-text-slate-300">کاهش موجودی انبار (Low Stock Alert)</span>
							<label class="wsm-flex wsm-items-center wsm-cursor-pointer">
								<input type="checkbox" id="sms-low-stock-enabled" class="wsm-ml-2">
								<span class="wsm-text-xs wsm-text-slate-400">فعال</span>
							</label>
						</div>
						<textarea id="sms-low-stock-text" rows="2" class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-p-3 wsm-text-xs wsm-text-slate-200 focus:wsm-outline-none"></textarea>
					</div>
				</div>
			</div>
		</div>

		<!-- Left Side: Variables and Save Options -->
		<div class="wsm-space-y-6">
			<!-- Available Variables Card -->
			<div class="wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-p-6 wsm-shadow-lg wsm-space-y-4">
				<h3 class="wsm-font-semibold wsm-text-slate-200">متغیرهای پیامک</h3>
				<p class="wsm-text-xs wsm-text-slate-400">با کپی کردن این کدها در متن پیام، اطلاعات سفارش یا محصول به صورت هوشمند جایگزین می‌شوند:</p>
				
				<div class="wsm-space-y-2 wsm-pt-2">
					<div class="wsm-flex wsm-items-center wsm-justify-between wsm-bg-slate-950/40 wsm-px-3 wsm-py-1.5 wsm-rounded-xl wsm-border wsm-border-slate-800/40">
						<code class="wsm-text-xs wsm-text-indigo-400 wsm-font-mono">{order_id}</code>
						<span class="wsm-text-xs wsm-text-slate-400">شماره سفارش</span>
					</div>
					<div class="wsm-flex wsm-items-center wsm-justify-between wsm-bg-slate-950/40 wsm-px-3 wsm-py-1.5 wsm-rounded-xl wsm-border wsm-border-slate-800/40">
						<code class="wsm-text-xs wsm-text-indigo-400 wsm-font-mono">{order_total}</code>
						<span class="wsm-text-xs wsm-text-slate-400">مجموع سفارش (تومان)</span>
					</div>
					<div class="wsm-flex wsm-items-center wsm-justify-between wsm-bg-slate-950/40 wsm-px-3 wsm-py-1.5 wsm-rounded-xl wsm-border wsm-border-slate-800/40">
						<code class="wsm-text-xs wsm-text-indigo-400 wsm-font-mono">{customer_name}</code>
						<span class="wsm-text-xs wsm-text-slate-400">نام کامل خریدار</span>
					</div>
					<div class="wsm-flex wsm-items-center wsm-justify-between wsm-bg-slate-950/40 wsm-px-3 wsm-py-1.5 wsm-rounded-xl wsm-border wsm-border-slate-800/40">
						<code class="wsm-text-xs wsm-text-indigo-400 wsm-font-mono">{status_label}</code>
						<span class="wsm-text-xs wsm-text-slate-400">برچسب وضعیت فارسی</span>
					</div>
					<div class="wsm-flex wsm-items-center wsm-justify-between wsm-bg-slate-950/40 wsm-px-3 wsm-py-1.5 wsm-rounded-xl wsm-border wsm-border-slate-800/40">
						<code class="wsm-text-xs wsm-text-indigo-400 wsm-font-mono">{product_name}</code>
						<span class="wsm-text-xs wsm-text-slate-400">نام کالا (هشدار انبار)</span>
					</div>
					<div class="wsm-flex wsm-items-center wsm-justify-between wsm-bg-slate-950/40 wsm-px-3 wsm-py-1.5 wsm-rounded-xl wsm-border wsm-border-slate-800/40">
						<code class="wsm-text-xs wsm-text-indigo-400 wsm-font-mono">{stock_qty}</code>
						<span class="wsm-text-xs wsm-text-slate-400">موجودی کالا (هشدار انبار)</span>
					</div>
				</div>
			</div>

			<!-- Test SMS Form Card -->
			<div class="wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-p-6 wsm-shadow-lg wsm-space-y-4">
				<h3 class="wsm-font-semibold wsm-text-slate-200">تست ارسال پیامک</h3>
				<p class="wsm-text-xs wsm-text-slate-400">یک پیام تستی برای بررسی اتصال سامانه ارسال کنید.</p>
				<div>
					<label for="test-phone" class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-mb-2">شماره گیرنده</label>
					<input type="text" id="test-phone" placeholder="09xxxxxxxxx" class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-px-4 wsm-py-3 wsm-text-sm wsm-text-slate-200 focus:wsm-outline-none">
				</div>
				<div>
					<label for="test-message" class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-mb-2">متن پیام</label>
					<textarea id="test-message" rows="2" class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-p-3 wsm-text-xs wsm-text-slate-200 focus:wsm-outline-none">پیامک تست از پنل کاربری KarasuWooPannel</textarea>
				</div>
				<button type="button" id="wsm-send-test-sms-btn" class="wsm-w-full wsm-bg-emerald-600 hover:wsm-bg-emerald-500 wsm-text-white wsm-font-semibold wsm-rounded-2xl wsm-py-3.5 wsm-transition-colors wsm-text-sm">
					ارسال پیامک تست
				</button>
			</div>

			<!-- Save Settings Action Box -->
			<div class="wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-p-6 wsm-shadow-lg">
				<button type="submit" id="wsm-save-sms-btn" class="wsm-w-full wsm-bg-indigo-600 hover:wsm-bg-indigo-500 wsm-text-white wsm-font-semibold wsm-rounded-2xl wsm-py-4 wsm-shadow-lg wsm-shadow-indigo-500/20 wsm-transition-colors">
					ذخیره تنظیمات قالب‌ها
				</button>
			</div>
		</div>
	</form>
</div>

<script src="<?php echo esc_url( WSM_PLUGIN_URL . 'assets/js/wsm-sms.js' ); ?>"></script>

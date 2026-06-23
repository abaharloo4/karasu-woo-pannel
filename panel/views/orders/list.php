<?php
/**
 * Orders List Panel View Template
 *
 * @package KarasuWooPannel
 * @version 1.0.3
 * @date 2026-06-23
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div class="wsm-space-y-6">
	<div class="wsm-flex wsm-items-center wsm-justify-between">
		<h1 class="wsm-text-2xl wsm-font-bold wsm-text-slate-100">مدیریت سفارش‌ها</h1>
	</div>

	<!-- Filters Panel -->
	<div class="wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-p-6 wsm-shadow-lg">
		<div class="wsm-grid wsm-grid-cols-1 md:wsm-grid-cols-4 wsm-gap-4">
			<!-- Search input keyword -->
			<div>
				<label class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-mb-2">جستجو</label>
				<input type="text" id="order-search" class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-px-4 wsm-py-2.5 wsm-text-sm wsm-text-slate-200 focus:wsm-outline-none focus:wsm-border-indigo-500 wsm-transition-colors" placeholder="شماره سفارش، نام مشتری...">
			</div>
			<!-- Order status filter -->
			<div>
				<label class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-mb-2">وضعیت</label>
				<select id="order-status-filter" class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-px-4 wsm-py-2.5 wsm-text-sm wsm-text-slate-200 focus:wsm-outline-none focus:wsm-border-indigo-500 wsm-transition-colors">
					<option value="">همه وضعیت‌ها</option>
					<option value="pending">در انتظار پرداخت</option>
					<option value="processing">در حال انجام</option>
					<option value="on-hold">در انتظار بررسی</option>
					<option value="completed">تکمیل شده</option>
					<option value="cancelled">لغو شده</option>
					<option value="refunded">مسترد شده</option>
					<option value="failed">ناموفق</option>
				</select>
			</div>
			<!-- Date range from -->
			<div>
				<label class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-mb-2">از تاریخ (جلالی)</label>
				<input type="text" id="order-date-from" class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-px-4 wsm-py-2.5 wsm-text-sm wsm-text-slate-200 focus:wsm-outline-none focus:wsm-border-indigo-500 wsm-transition-colors" placeholder="۱۴۰۴/۰۱/۰۱">
			</div>
			<!-- Date range to -->
			<div>
				<label class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-mb-2">تا تاریخ (جلالی)</label>
				<input type="text" id="order-date-to" class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-px-4 wsm-py-2.5 wsm-text-sm wsm-text-slate-200 focus:wsm-outline-none focus:wsm-border-indigo-500 wsm-transition-colors" placeholder="۱۴۰۴/۱۲/۲۹">
			</div>
		</div>
		<div class="wsm-flex wsm-justify-end wsm-mt-4">
			<button id="clear-filters-btn" class="wsm-px-4 wsm-py-2 wsm-text-xs wsm-font-medium wsm-text-slate-400 hover:wsm-text-slate-200 wsm-transition-colors">پاک کردن فیلترها</button>
		</div>
	</div>

	<!-- Table Panel -->
	<div class="wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-overflow-hidden wsm-shadow-lg">
		<div class="wsm-overflow-x-auto">
			<table class="wsm-w-full wsm-text-right wsm-border-collapse">
				<thead>
					<tr class="wsm-border-b wsm-border-slate-800 wsm-bg-slate-950/20">
						<th class="wsm-px-6 wsm-py-4 wsm-text-xs wsm-font-semibold wsm-text-slate-400">شماره سفارش</th>
						<th class="wsm-px-6 wsm-py-4 wsm-text-xs wsm-font-semibold wsm-text-slate-400">مشتری</th>
						<th class="wsm-px-6 wsm-py-4 wsm-text-xs wsm-font-semibold wsm-text-slate-400">تاریخ ثبت</th>
						<th class="wsm-px-6 wsm-py-4 wsm-text-xs wsm-font-semibold wsm-text-slate-400">روش پرداخت</th>
						<th class="wsm-px-6 wsm-py-4 wsm-text-xs wsm-font-semibold wsm-text-slate-400">مبلغ کل</th>
						<th class="wsm-px-6 wsm-py-4 wsm-text-xs wsm-font-semibold wsm-text-slate-400">وضعیت</th>
						<th class="wsm-px-6 wsm-py-4 wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-text-center">عملیات</th>
					</tr>
				</thead>
				<tbody id="orders-table-body" class="wsm-divide-y wsm-divide-slate-800/50">
					<!-- Loading Skeleton Placeholder -->
					<tr>
						<td colspan="7" class="wsm-px-6 wsm-py-12 wsm-text-center wsm-text-slate-500">در حال بارگذاری اطلاعات سفارش‌ها...</td>
					</tr>
				</tbody>
			</table>
		</div>

		<!-- Pagination Footer -->
		<div class="wsm-px-6 wsm-py-4 wsm-border-t wsm-border-slate-800 wsm-flex wsm-items-center wsm-justify-between">
			<div class="wsm-text-xs wsm-text-slate-500">
				نمایش <span id="orders-count-start" class="wsm-font-semibold">0</span> تا <span id="orders-count-end" class="wsm-font-semibold">0</span> از <span id="orders-count-total" class="wsm-font-semibold">0</span> سفارش
			</div>
			<div class="wsm-flex wsm-items-center wsm-space-x-2 wsm-space-x-reverse" id="orders-pagination-controls">
				<!-- Buttons loaded via script -->
			</div>
		</div>
	</div>
</div>

<!-- Page script attachment -->
<script src="<?php echo esc_url( WSM_PLUGIN_URL . 'assets/js/wsm-orders.js' ); ?>"></script>

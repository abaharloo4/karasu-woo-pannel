<?php
/**
 * SMS Logs Panel View Template
 *
 * @package KarasuWooPannel
 * @version 1.0.1
 * @date 2026-06-23
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div class="wsm-space-y-6">
	<div class="wsm-flex wsm-items-center wsm-justify-between">
		<h1 class="wsm-text-2xl wsm-font-bold wsm-text-slate-100">تاریخچه پیامک‌های ارسالی</h1>
	</div>

	<!-- Logs Table Card -->
	<div class="wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-overflow-hidden wsm-shadow-lg">
		<div class="wsm-overflow-x-auto">
			<table class="wsm-w-full wsm-text-right wsm-border-collapse">
				<thead>
					<tr class="wsm-border-b wsm-border-slate-800 wsm-bg-slate-950/20">
						<th class="wsm-px-6 wsm-py-3 wsm-text-xs wsm-text-slate-500">شماره سفارش / شناسه</th>
						<th class="wsm-px-6 wsm-py-3 wsm-text-xs wsm-text-slate-500">رویداد</th>
						<th class="wsm-px-6 wsm-py-3 wsm-text-xs wsm-text-slate-500">شماره گیرنده</th>
						<th class="wsm-px-6 wsm-py-3 wsm-text-xs wsm-text-slate-500">متن پیام</th>
						<th class="wsm-px-6 wsm-py-3 wsm-text-xs wsm-text-slate-500">وضعیت</th>
						<th class="wsm-px-6 wsm-py-3 wsm-text-xs wsm-text-slate-500">پاسخ وب‌سرویس</th>
						<th class="wsm-px-6 wsm-py-3 wsm-text-xs wsm-text-slate-500">زمان ارسال</th>
					</tr>
				</thead>
				<tbody id="sms-logs-table-body" class="wsm-divide-y wsm-divide-slate-800/40">
					<tr>
						<td colspan="7" class="wsm-px-6 wsm-py-12 wsm-text-center wsm-text-slate-500">در حال دریافت لاگ‌ها...</td>
					</tr>
				</tbody>
			</table>
		</div>

		<!-- Pagination Footer -->
		<div class="wsm-px-6 wsm-py-4 wsm-border-t wsm-border-slate-800 wsm-flex wsm-items-center wsm-justify-between">
			<div class="wsm-text-xs wsm-text-slate-500">
				نمایش <span id="logs-count-start" class="wsm-font-semibold">۰</span> تا <span id="logs-count-end" class="wsm-font-semibold">۰</span> از <span id="logs-count-total" class="wsm-font-semibold">۰</span> لاگ پیامک
			</div>
			<div id="logs-pagination-controls" class="wsm-flex wsm-items-center wsm-space-x-2 wsm-space-x-reverse">
				<!-- Buttons loaded dynamically -->
			</div>
		</div>
	</div>
</div>

<script src="<?php echo esc_url( WSM_PLUGIN_URL . 'assets/js/wsm-sms.js' ); ?>"></script>

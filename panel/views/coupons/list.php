<?php
/**
 * Coupons List Panel View Template
 *
 * @package KarasuWooPannel
 * @version 1.0.8
 * @date 2026-06-23
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div class="wsm-space-y-6">
	<div class="wsm-flex wsm-items-center wsm-justify-between">
		<h1 class="wsm-text-2xl wsm-font-bold wsm-text-slate-100">مدیریت کوپن‌های تخفیف</h1>
		<a href="<?php echo esc_url( wsm_panel_url( 'coupons/new' ) ); ?>" class="wsm-bg-indigo-600 hover:wsm-bg-indigo-500 wsm-text-white wsm-font-semibold wsm-rounded-2xl wsm-px-5 wsm-py-3 wsm-text-sm wsm-shadow-lg wsm-shadow-indigo-500/20 wsm-transition-colors">
			ایجاد کوپن جدید
		</a>
	</div>

	<!-- Filters Row -->
	<div class="wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-p-4 wsm-shadow-lg wsm-flex wsm-flex-wrap wsm-gap-4 wsm-items-center">
		<div class="wsm-flex-1 wsm-min-w-[200px]">
			<input type="text" id="coupon-search" placeholder="جستجوی کد کوپن..." class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-px-4 wsm-py-2.5 wsm-text-sm wsm-text-slate-200 focus:wsm-outline-none focus:wsm-border-indigo-500">
		</div>
		<button id="clear-coupon-filters" class="wsm-px-4 wsm-py-2.5 wsm-text-xs wsm-bg-slate-950 wsm-border wsm-border-slate-850 hover:wsm-bg-slate-800 wsm-text-slate-400 wsm-rounded-2xl wsm-transition-colors">
			پاک کردن فیلترها
		</button>
	</div>

	<!-- Coupons Table Card -->
	<div class="wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-overflow-hidden wsm-shadow-lg">
		<div class="wsm-overflow-x-auto">
			<table class="wsm-w-full wsm-text-right wsm-border-collapse">
				<thead>
					<tr class="wsm-border-b wsm-border-slate-800 wsm-bg-slate-950/20">
						<th class="wsm-px-6 wsm-py-3 wsm-text-xs wsm-text-slate-500">کد کوپن</th>
						<th class="wsm-px-6 wsm-py-3 wsm-text-xs wsm-text-slate-500">نوع تخفیف</th>
						<th class="wsm-px-6 wsm-py-3 wsm-text-xs wsm-text-slate-500">میزان تخفیف</th>
						<th class="wsm-px-6 wsm-py-3 wsm-text-xs wsm-text-slate-500">تعداد استفاده</th>
						<th class="wsm-px-6 wsm-py-3 wsm-text-xs wsm-text-slate-500">تاریخ انقضا</th>
						<th class="wsm-px-6 wsm-py-3 wsm-text-xs wsm-text-slate-500 wsm-text-center">عملیات</th>
					</tr>
				</thead>
				<tbody id="coupons-table-body" class="wsm-divide-y wsm-divide-slate-800/40">
					<tr>
						<td colspan="6" class="wsm-px-6 wsm-py-12 wsm-text-center wsm-text-slate-500">در حال دریافت اطلاعات...</td>
					</tr>
				</tbody>
			</table>
		</div>

		<!-- Pagination Footer -->
		<div class="wsm-px-6 wsm-py-4 wsm-border-t wsm-border-slate-800 wsm-flex wsm-items-center wsm-justify-between">
			<div class="wsm-text-xs wsm-text-slate-500">
				نمایش <span id="coupons-count-start" class="wsm-font-semibold">۰</span> تا <span id="coupons-count-end" class="wsm-font-semibold">۰</span> از <span id="coupons-count-total" class="wsm-font-semibold">۰</span> کوپن تخفیف
			</div>
			<div id="coupons-pagination-controls" class="wsm-flex wsm-items-center wsm-space-x-2 wsm-space-x-reverse">
				<!-- Loaded dynamically -->
			</div>
		</div>
	</div>
</div>

<script src="<?php echo esc_url( WSM_PLUGIN_URL . 'assets/js/wsm-coupons.js' ); ?>"></script>

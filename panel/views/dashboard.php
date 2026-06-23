<?php
/**
 * Dashboard Landing Page Template View
 *
 * @package KarasuWooPannel
 * @version 1.0.10
 * @date 2026-06-23
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$current_user = wp_get_current_user();
?>
<div class="wsm-space-y-6">
	<!-- Greeting Header -->
	<div class="wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-p-6 wsm-shadow-lg wsm-flex wsm-flex-wrap wsm-justify-between wsm-items-center wsm-gap-4">
		<div>
			<h1 class="wsm-text-2xl wsm-font-bold wsm-bg-gradient-to-r wsm-from-indigo-400 wsm-to-cyan-400 wsm-bg-clip-text wsm-text-transparent">
				سلام، <?php echo esc_html( $current_user->display_name ); ?>!
			</h1>
			<p class="wsm-text-xs wsm-text-slate-400 wsm-mt-1">
				به پنل مدیریت اختصاصی فروشگاه خود خوش آمدید. خلاصه وضعیت امروز و عملکرد فروشگاه را در زیر مشاهده کنید.
			</p>
		</div>
		<div class="wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-px-4 wsm-py-2.5 wsm-text-xs wsm-font-mono wsm-text-slate-400">
			<span id="dash-live-clock">در حال دریافت ساعت...</span>
		</div>
	</div>

	<!-- Stats Grid -->
	<div class="wsm-grid wsm-grid-cols-1 md:wsm-grid-cols-4 wsm-gap-6">
		<!-- Today Sales -->
		<div class="wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-p-6 wsm-shadow-lg wsm-relative wsm-overflow-hidden">
			<span class="wsm-text-xs wsm-font-semibold wsm-text-slate-400">فروش امروز</span>
			<h2 id="dash-today-sales" class="wsm-text-2xl wsm-font-bold wsm-text-indigo-400 wsm-mt-2">در حال بارگذاری...</h2>
			<div class="wsm-absolute wsm-bottom-0 wsm-left-0 wsm-right-0 wsm-h-1 wsm-bg-gradient-to-r wsm-from-indigo-500 wsm-to-indigo-300 wsm-opacity-50"></div>
		</div>
		<!-- Today Orders -->
		<div class="wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-p-6 wsm-shadow-lg wsm-relative wsm-overflow-hidden">
			<span class="wsm-text-xs wsm-font-semibold wsm-text-slate-400">سفارش‌های امروز</span>
			<h2 id="dash-today-orders" class="wsm-text-2xl wsm-font-bold wsm-text-slate-200 wsm-mt-2">در حال بارگذاری...</h2>
			<div class="wsm-absolute wsm-bottom-0 wsm-left-0 wsm-right-0 wsm-h-1 wsm-bg-gradient-to-r wsm-from-emerald-500 wsm-to-emerald-300 wsm-opacity-50"></div>
		</div>
		<!-- Month Sales -->
		<div class="wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-p-6 wsm-shadow-lg wsm-relative wsm-overflow-hidden">
			<span class="wsm-text-xs wsm-font-semibold wsm-text-slate-400">فروش این ماه</span>
			<h2 id="dash-month-sales" class="wsm-text-2xl wsm-font-bold wsm-text-indigo-400 wsm-mt-2">در حال بارگذاری...</h2>
			<div class="wsm-absolute wsm-bottom-0 wsm-left-0 wsm-right-0 wsm-h-1 wsm-bg-gradient-to-r wsm-from-cyan-500 wsm-to-cyan-300 wsm-opacity-50"></div>
		</div>
		<!-- Month Orders -->
		<div class="wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-p-6 wsm-shadow-lg wsm-relative wsm-overflow-hidden">
			<span class="wsm-text-xs wsm-font-semibold wsm-text-slate-400">سفارش‌های این ماه</span>
			<h2 id="dash-month-orders" class="wsm-text-2xl wsm-font-bold wsm-text-slate-200 wsm-mt-2">در حال بارگذاری...</h2>
			<div class="wsm-absolute wsm-bottom-0 wsm-left-0 wsm-right-0 wsm-h-1 wsm-bg-gradient-to-r wsm-from-purple-500 wsm-to-purple-300 wsm-opacity-50"></div>
		</div>
	</div>

	<!-- Quick Actions -->
	<div class="wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-p-6 wsm-shadow-lg">
		<h3 class="wsm-font-semibold wsm-text-slate-200 wsm-mb-4">دسترسی سریع و عملیات‌ها</h3>
		<div class="wsm-grid wsm-grid-cols-2 md:wsm-grid-cols-4 wsm-gap-4">
			<a href="<?php echo esc_url( wsm_panel_url( 'orders' ) ); ?>" class="wsm-flex wsm-flex-col wsm-items-center wsm-justify-center wsm-p-4 wsm-bg-slate-950/60 hover:wsm-bg-slate-950 wsm-border wsm-border-slate-800 hover:wsm-border-indigo-500/50 wsm-rounded-2xl wsm-transition-all wsm-text-center wsm-group">
				<svg style="width: 24px; height: 24px;" class="wsm-w-6 wsm-h-6 wsm-mb-2 wsm-text-slate-400 wsm-group-hover:wsm-text-indigo-400 wsm-group-hover:wsm-scale-110 wsm-transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
				<span class="wsm-text-xs wsm-font-medium wsm-text-slate-300">لیست سفارش‌ها</span>
			</a>
			<a href="<?php echo esc_url( wsm_panel_url( 'products' ) ); ?>" class="wsm-flex wsm-flex-col wsm-items-center wsm-justify-center wsm-p-4 wsm-bg-slate-950/60 hover:wsm-bg-slate-950 wsm-border wsm-border-slate-800 hover:wsm-border-indigo-500/50 wsm-rounded-2xl wsm-transition-all wsm-text-center wsm-group">
				<svg style="width: 24px; height: 24px;" class="wsm-w-6 wsm-h-6 wsm-mb-2 wsm-text-slate-400 wsm-group-hover:wsm-text-indigo-400 wsm-group-hover:wsm-scale-110 wsm-transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
				<span class="wsm-text-xs wsm-font-medium wsm-text-slate-300">مدیریت محصولات</span>
			</a>
			<a href="<?php echo esc_url( wsm_panel_url( 'coupons/new' ) ); ?>" class="wsm-flex wsm-flex-col wsm-items-center wsm-justify-center wsm-p-4 wsm-bg-slate-950/60 hover:wsm-bg-slate-950 wsm-border wsm-border-slate-800 hover:wsm-border-indigo-500/50 wsm-rounded-2xl wsm-transition-all wsm-text-center wsm-group">
				<svg style="width: 24px; height: 24px;" class="wsm-w-6 wsm-h-6 wsm-mb-2 wsm-text-slate-400 wsm-group-hover:wsm-text-indigo-400 wsm-group-hover:wsm-scale-110 wsm-transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
				<span class="wsm-text-xs wsm-font-medium wsm-text-slate-300">کوپن تخفیف جدید</span>
			</a>
			<a href="<?php echo esc_url( wsm_panel_url( 'reports' ) ); ?>" class="wsm-flex wsm-flex-col wsm-items-center wsm-justify-center wsm-p-4 wsm-bg-slate-950/60 hover:wsm-bg-slate-950 wsm-border wsm-border-slate-800 hover:wsm-border-indigo-500/50 wsm-rounded-2xl wsm-transition-all wsm-text-center wsm-group">
				<svg style="width: 24px; height: 24px;" class="wsm-w-6 wsm-h-6 wsm-mb-2 wsm-text-slate-400 wsm-group-hover:wsm-text-indigo-400 wsm-group-hover:wsm-scale-110 wsm-transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
				<span class="wsm-text-xs wsm-font-medium wsm-text-slate-300">گزارش‌های فروشگاه</span>
			</a>
		</div>
	</div>

	<!-- Split Two Column Layout -->
	<div class="wsm-grid wsm-grid-cols-1 lg:wsm-grid-cols-2 wsm-gap-6">
		<!-- Column 1: Recent Orders -->
		<div class="wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-p-6 wsm-shadow-lg wsm-flex wsm-flex-col">
			<div class="wsm-flex wsm-items-center wsm-justify-between wsm-mb-4">
				<h3 class="wsm-font-semibold wsm-text-slate-200">آخرین سفارش‌ها</h3>
				<a href="<?php echo esc_url( wsm_panel_url( 'orders' ) ); ?>" class="wsm-text-xs wsm-text-indigo-400 hover:wsm-text-indigo-300">مشاهده همه</a>
			</div>
			<div class="wsm-flex-1 wsm-overflow-x-auto">
				<table class="wsm-w-full wsm-text-right wsm-border-collapse">
					<thead>
						<tr class="wsm-border-b wsm-border-slate-800/80">
							<th class="wsm-pb-2 wsm-text-xs wsm-text-slate-500">سفارش</th>
							<th class="wsm-pb-2 wsm-text-xs wsm-text-slate-500">خریدار</th>
							<th class="wsm-pb-2 wsm-text-xs wsm-text-slate-500">وضعیت</th>
							<th class="wsm-pb-2 wsm-text-xs wsm-text-slate-500">مجموع</th>
						</tr>
					</thead>
					<tbody id="dash-orders-table-body" class="wsm-divide-y wsm-divide-slate-800/40">
						<tr>
							<td colspan="4" class="wsm-py-4 wsm-text-center wsm-text-slate-500">در حال دریافت...</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

		<!-- Column 2: Low Stock Alerts -->
		<div class="wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-p-6 wsm-shadow-lg wsm-flex wsm-flex-col">
			<div class="wsm-flex wsm-items-center wsm-justify-between wsm-mb-4">
				<h3 class="wsm-font-semibold wsm-text-slate-200">هشدارهای انبار (موجودی رو به اتمام)</h3>
				<a href="<?php echo esc_url( wsm_panel_url( 'reports/products' ) ); ?>" class="wsm-text-xs wsm-text-indigo-400 hover:wsm-text-indigo-300">گزارش انبار</a>
			</div>
			<div class="wsm-flex-1 wsm-overflow-x-auto">
				<table class="wsm-w-full wsm-text-right wsm-border-collapse">
					<thead>
						<tr class="wsm-border-b wsm-border-slate-800/80">
							<th class="wsm-pb-2 wsm-text-xs wsm-text-slate-500">محصول</th>
							<th class="wsm-pb-2 wsm-text-xs wsm-text-slate-500">کد کالا (SKU)</th>
							<th class="wsm-pb-2 wsm-text-xs wsm-text-slate-500 wsm-text-center">موجودی فعلی</th>
						</tr>
					</thead>
					<tbody id="dash-inventory-table-body" class="wsm-divide-y wsm-divide-slate-800/40">
						<tr>
							<td colspan="3" class="wsm-py-4 wsm-text-center wsm-text-slate-500">در حال دریافت...</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<script src="<?php echo esc_url( WSM_PLUGIN_URL . 'assets/js/wsm-dashboard.js' ); ?>"></script>

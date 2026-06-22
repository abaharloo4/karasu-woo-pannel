<?php
/**
 * Jalali Sales Reports Dashboard Panel View Template
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
		<h1 class="wsm-text-2xl wsm-font-bold wsm-text-slate-100">گزارش‌های فروشگاه</h1>
		<button id="wsm-export-csv-btn" class="wsm-bg-emerald-600 hover:wsm-bg-emerald-500 wsm-text-white wsm-font-semibold wsm-rounded-2xl wsm-px-5 wsm-py-3 wsm-text-sm wsm-shadow-lg wsm-shadow-emerald-500/20 wsm-transition-colors">
			خروجی فایل CSV
		</button>
	</div>

	<!-- Reports Sub-tab Navigation -->
	<?php
	$current_tab = isset( $view ) ? str_replace( 'reports/', '', $view ) : 'dashboard';
	?>
	<div class="wsm-flex wsm-border-b wsm-border-slate-800 wsm-mb-6 wsm-gap-6">
		<a href="<?php echo esc_url( wsm_panel_url( 'reports' ) ); ?>" class="wsm-pb-3 wsm-text-sm wsm-font-semibold <?php echo 'dashboard' === $current_tab ? 'wsm-border-b-2 wsm-border-indigo-500 wsm-text-indigo-400' : 'wsm-text-slate-400 hover:wsm-text-slate-200'; ?>">داشبورد گزارش</a>
		<a href="<?php echo esc_url( wsm_panel_url( 'reports/sales' ) ); ?>" class="wsm-pb-3 wsm-text-sm wsm-font-semibold <?php echo 'sales' === $current_tab ? 'wsm-border-b-2 wsm-border-indigo-500 wsm-text-indigo-400' : 'wsm-text-slate-400 hover:wsm-text-slate-200'; ?>">گزارش تفصیلی فروش</a>
		<a href="<?php echo esc_url( wsm_panel_url( 'reports/products' ) ); ?>" class="wsm-pb-3 wsm-text-sm wsm-font-semibold <?php echo 'products' === $current_tab ? 'wsm-border-b-2 wsm-border-indigo-500 wsm-text-indigo-400' : 'wsm-text-slate-400 hover:wsm-text-slate-200'; ?>">انبار و موجودی کالا</a>
		<a href="<?php echo esc_url( wsm_panel_url( 'reports/customers' ) ); ?>" class="wsm-pb-3 wsm-text-sm wsm-font-semibold <?php echo 'customers' === $current_tab ? 'wsm-border-b-2 wsm-border-indigo-500 wsm-text-indigo-400' : 'wsm-text-slate-400 hover:wsm-text-slate-200'; ?>">گزارش مشتریان</a>
	</div>

	<!-- Date Picker Filters -->
	<div class="wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-p-4 wsm-shadow-lg wsm-flex wsm-flex-wrap wsm-gap-4 wsm-items-end">
		<div class="wsm-flex-1 wsm-min-w-[150px]">
			<label for="rep-start" class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-mb-2">تاریخ شروع (مثال: ۱۴۰۵/۰۱/۰۱)</label>
			<input type="text" id="rep-start" placeholder="YYYY/MM/DD" class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-px-4 wsm-py-2.5 wsm-text-sm wsm-text-slate-200 focus:wsm-outline-none">
		</div>
		<div class="wsm-flex-1 wsm-min-w-[150px]">
			<label for="rep-end" class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-mb-2">تاریخ پایان (مثال: ۱۴۰۵/۰۱/۳۱)</label>
			<input type="text" id="rep-end" placeholder="YYYY/MM/DD" class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-px-4 wsm-py-2.5 wsm-text-sm wsm-text-slate-200 focus:wsm-outline-none">
		</div>
		<button id="wsm-filter-reports-btn" class="wsm-px-6 wsm-py-3 wsm-text-sm wsm-bg-indigo-600 hover:wsm-bg-indigo-500 wsm-text-white wsm-font-semibold wsm-rounded-2xl wsm-transition-colors">
			فیلتر گزارش
		</button>
	</div>

	<!-- Stats Grid Widgets -->
	<div class="wsm-grid wsm-grid-cols-1 md:wsm-grid-cols-4 wsm-gap-6">
		<div class="wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-p-6 wsm-shadow-lg">
			<span class="wsm-text-xs wsm-font-semibold wsm-text-slate-400">فروش امروز</span>
			<h2 id="stat-today-sales" class="wsm-text-2xl wsm-font-bold wsm-text-indigo-400 wsm-mt-2">۰ تومان</h2>
		</div>
		<div class="wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-p-6 wsm-shadow-lg">
			<span class="wsm-text-xs wsm-font-semibold wsm-text-slate-400">سفارش‌های امروز</span>
			<h2 id="stat-today-orders" class="wsm-text-2xl wsm-font-bold wsm-text-slate-200 wsm-mt-2">۰ سفارش</h2>
		</div>
		<div class="wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-p-6 wsm-shadow-lg">
			<span class="wsm-text-xs wsm-font-semibold wsm-text-slate-400">فروش کل دوره فیلتر شده</span>
			<h2 id="stat-period-sales" class="wsm-text-2xl wsm-font-bold wsm-text-indigo-400 wsm-mt-2">۰ تومان</h2>
		</div>
		<div class="wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-p-6 wsm-shadow-lg">
			<span class="wsm-text-xs wsm-font-semibold wsm-text-slate-400">سفارش‌های دوره فیلتر شده</span>
			<h2 id="stat-period-orders" class="wsm-text-2xl wsm-font-bold wsm-text-slate-200 wsm-mt-2">۰ سفارش</h2>
		</div>
	</div>

	<!-- Chart Box and Top Products side-by-side -->
	<div class="wsm-grid wsm-grid-cols-1 lg:wsm-grid-cols-3 wsm-gap-6">
		<!-- Sales Line Chart -->
		<div class="lg:wsm-col-span-2 wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-p-6 wsm-shadow-lg wsm-flex wsm-flex-col wsm-h-[400px]">
			<h3 class="wsm-font-semibold wsm-text-slate-200 wsm-mb-4">نمودار خطی روند فروش</h3>
			<div class="wsm-flex-1 wsm-relative wsm-w-full wsm-h-full">
				<canvas id="sales-line-chart"></canvas>
			</div>
		</div>

		<!-- Best Selling Products -->
		<div class="wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-p-6 wsm-shadow-lg wsm-flex wsm-flex-col">
			<h3 class="wsm-font-semibold wsm-text-slate-200 wsm-mb-4">محصولات پرفروش</h3>
			<div class="wsm-flex-1 wsm-overflow-y-auto">
				<table class="wsm-w-full wsm-text-right wsm-border-collapse">
					<thead>
						<tr class="wsm-border-b wsm-border-slate-800/80">
							<th class="wsm-pb-2 wsm-text-xs wsm-text-slate-500">عنوان کالا</th>
							<th class="wsm-pb-2 wsm-text-xs wsm-text-slate-500 wsm-text-center">تعداد فروش</th>
						</tr>
					</thead>
					<tbody id="top-products-table-body" class="wsm-divide-y wsm-divide-slate-800/40">
						<tr>
							<td colspan="2" class="wsm-py-4 wsm-text-center wsm-text-slate-500">در حال بارگذاری...</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script src="<?php echo esc_url( WSM_PLUGIN_URL . 'assets/js/wsm-reports.js' ); ?>"></script>

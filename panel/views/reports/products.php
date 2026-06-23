<?php
/**
 * Products Inventory Report Template
 *
 * @package KarasuWooPannel
 * @version 1.0.9
 * @date 2026-06-23
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div id="wsm-products-report-page" class="wsm-space-y-6">
	<div class="wsm-flex wsm-items-center wsm-justify-between">
		<h1 class="wsm-text-2xl wsm-font-bold wsm-text-slate-100">انبار و موجودی کالا (کالاهای رو به اتمام)</h1>
		<button id="wsm-export-products-csv-btn" class="wsm-bg-emerald-600 hover:wsm-bg-emerald-500 wsm-text-white wsm-font-semibold wsm-rounded-2xl wsm-px-5 wsm-py-3 wsm-text-sm wsm-shadow-lg wsm-shadow-emerald-500/20 wsm-transition-colors">
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

	<!-- Products Inventory Table -->
	<div class="wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-p-6 wsm-shadow-lg wsm-overflow-hidden">
		<div class="wsm-overflow-x-auto">
			<table class="wsm-w-full wsm-text-right wsm-border-collapse">
				<thead>
					<tr class="wsm-border-b wsm-border-slate-800/80">
						<th class="wsm-pb-3 wsm-text-xs wsm-text-slate-500">شناسه کالا (ID)</th>
						<th class="wsm-pb-3 wsm-text-xs wsm-text-slate-500">نام محصول</th>
						<th class="wsm-pb-3 wsm-text-xs wsm-text-slate-500 wsm-text-center">موجودی فعلی</th>
						<th class="wsm-pb-3 wsm-text-xs wsm-text-slate-500 wsm-text-center">آستانه هشدار</th>
						<th class="wsm-pb-3 wsm-text-xs wsm-text-slate-500 wsm-text-center">وضعیت انبار</th>
					</tr>
				</thead>
				<tbody id="products-inventory-table-body" class="wsm-divide-y wsm-divide-slate-800/40">
					<tr>
						<td colspan="5" class="wsm-py-6 wsm-text-center wsm-text-slate-500">در حال بارگذاری...</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>

<script src="<?php echo esc_url( WSM_PLUGIN_URL . 'assets/js/wsm-reports.js' ); ?>"></script>

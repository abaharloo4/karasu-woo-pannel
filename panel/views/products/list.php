<?php
/**
 * Products List Panel View Template
 *
 * @package KarasuWooPannel
 * @version 1.0.0
 * @date 2026-06-23
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div class="wsm-space-y-6">
	<div class="wsm-flex wsm-items-center wsm-justify-between">
		<h1 class="wsm-text-2xl wsm-font-bold wsm-text-slate-100">مدیریت محصولات</h1>
		<a href="<?php echo esc_url( wsm_panel_url( 'products/new' ) ); ?>" class="wsm-px-5 wsm-py-3 wsm-bg-indigo-600 hover:wsm-bg-indigo-500 wsm-text-white wsm-text-sm wsm-font-semibold wsm-rounded-2xl wsm-shadow-lg wsm-shadow-indigo-500/20 wsm-transition-all">
			افزودن محصول جدید
		</a>
	</div>

	<!-- Filters Panel -->
	<div class="wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-p-6 wsm-shadow-lg">
		<div class="wsm-grid wsm-grid-cols-1 md:wsm-grid-cols-4 wsm-gap-4">
			<!-- Search query -->
			<div>
				<label class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-mb-2">جستجوی محصول</label>
				<input type="text" id="product-search" class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-px-4 wsm-py-2.5 wsm-text-sm wsm-text-slate-200 focus:wsm-outline-none focus:wsm-border-indigo-500 wsm-transition-colors" placeholder="نام یا شناسه (SKU) محصول...">
			</div>
			<!-- Category filter -->
			<div>
				<label class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-mb-2">دسته‌بندی</label>
				<select id="product-category-filter" class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-px-4 wsm-py-2.5 wsm-text-sm wsm-text-slate-200 focus:wsm-outline-none focus:wsm-border-indigo-500 wsm-transition-colors">
					<option value="">همه دسته‌بندی‌ها</option>
					<!-- Loaded dynamically via JavaScript API -->
				</select>
			</div>
			<!-- Stock status filter -->
			<div>
				<label class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-mb-2">وضعیت موجودی</label>
				<select id="product-stock-filter" class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-px-4 wsm-py-2.5 wsm-text-sm wsm-text-slate-200 focus:wsm-outline-none focus:wsm-border-indigo-500 wsm-transition-colors">
					<option value="">همه</option>
					<option value="instock">موجود</option>
					<option value="outofstock">ناموجود</option>
				</select>
			</div>
			<!-- Publish status filter -->
			<div>
				<label class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-mb-2">وضعیت انتشار</label>
				<select id="product-status-filter" class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-px-4 wsm-py-2.5 wsm-text-sm wsm-text-slate-200 focus:wsm-outline-none focus:wsm-border-indigo-500 wsm-transition-colors">
					<option value="">همه</option>
					<option value="publish">منتشر شده</option>
					<option value="draft">پیش‌نویس</option>
				</select>
			</div>
		</div>
		<div class="wsm-flex wsm-justify-end wsm-mt-4">
			<button id="clear-product-filters" class="wsm-px-4 wsm-py-2 wsm-text-xs wsm-font-medium wsm-text-slate-400 hover:wsm-text-slate-200 wsm-transition-colors">پاک کردن فیلترها</button>
		</div>
	</div>

	<!-- Table Panel -->
	<div class="wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-overflow-hidden wsm-shadow-lg">
		<div class="wsm-overflow-x-auto">
			<table class="wsm-w-full wsm-text-right wsm-border-collapse">
				<thead>
					<tr class="wsm-border-b wsm-border-slate-800 wsm-bg-slate-950/20">
						<th class="wsm-px-6 wsm-py-4 wsm-text-xs wsm-font-semibold wsm-text-slate-400">تصویر</th>
						<th class="wsm-px-6 wsm-py-4 wsm-text-xs wsm-font-semibold wsm-text-slate-400">نام محصول</th>
						<th class="wsm-px-6 wsm-py-4 wsm-text-xs wsm-font-semibold wsm-text-slate-400">شناسه (SKU)</th>
						<th class="wsm-px-6 wsm-py-4 wsm-text-xs wsm-font-semibold wsm-text-slate-400">دسته‌بندی</th>
						<th class="wsm-px-6 wsm-py-4 wsm-text-xs wsm-font-semibold wsm-text-slate-400">قیمت</th>
						<th class="wsm-px-6 wsm-py-4 wsm-text-xs wsm-font-semibold wsm-text-slate-400">موجودی</th>
						<th class="wsm-px-6 wsm-py-4 wsm-text-xs wsm-font-semibold wsm-text-slate-400">وضعیت</th>
						<th class="wsm-px-6 wsm-py-4 wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-text-center">عملیات</th>
					</tr>
				</thead>
				<tbody id="products-table-body" class="wsm-divide-y wsm-divide-slate-800/50">
					<!-- Loading Skeleton Placeholder -->
					<tr>
						<td colspan="8" class="wsm-px-6 wsm-py-12 wsm-text-center wsm-text-slate-500">در حال بارگذاری لیست محصولات...</td>
					</tr>
				</tbody>
			</table>
		</div>

		<!-- Pagination Footer -->
		<div class="wsm-px-6 wsm-py-4 wsm-border-t wsm-border-slate-800 wsm-flex wsm-items-center wsm-justify-between">
			<div class="wsm-text-xs wsm-text-slate-500">
				نمایش <span id="products-count-start" class="wsm-font-semibold">0</span> تا <span id="products-count-end" class="wsm-font-semibold">0</span> از <span id="products-count-total" class="wsm-font-semibold">0</span> محصول
			</div>
			<div class="wsm-flex wsm-items-center wsm-space-x-2 wsm-space-x-reverse" id="products-pagination-controls">
				<!-- Buttons loaded via script -->
			</div>
		</div>
	</div>
</div>

<!-- Page script attachment -->
<script src="<?php echo esc_url( WSM_PLUGIN_URL . 'assets/js/wsm-products.js' ); ?>"></script>

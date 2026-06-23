<?php
/**
 * Categories List Panel View Template
 *
 * @package KarasuWooPannel
 * @version 1.1.1
 * @date 2026-06-23
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div class="wsm-space-y-6">
	<div class="wsm-flex wsm-items-center wsm-justify-between">
		<h1 class="wsm-text-2xl wsm-font-bold wsm-text-slate-100"><?php echo esc_html( __( 'Manage Categories', 'karasu-woo-pannel' ) ); ?></h1>
	</div>

	<div class="wsm-grid wsm-grid-cols-1 lg:wsm-grid-cols-3 wsm-gap-6">
		<!-- Add Category Form -->
		<div class="wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-p-6 wsm-shadow-lg wsm-h-fit">
			<h3 class="wsm-font-semibold wsm-text-slate-200 wsm-mb-4"><?php echo esc_html( __( 'Add New Category', 'karasu-woo-pannel' ) ); ?></h3>
			<form id="wsm-add-category-form" class="wsm-space-y-4">
				<div>
					<label for="cat-name" class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-mb-2"><?php echo esc_html( __( 'Category Name', 'karasu-woo-pannel' ) ); ?></label>
					<input type="text" id="cat-name" required class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-px-4 wsm-py-2.5 wsm-text-sm wsm-text-slate-200 focus:wsm-outline-none focus:wsm-border-indigo-500 wsm-transition-colors">
				</div>

				<div>
					<label for="cat-slug" class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-mb-2"><?php echo esc_html( __( 'Slug', 'karasu-woo-pannel' ) ); ?></label>
					<input type="text" id="cat-slug" class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-px-4 wsm-py-2.5 wsm-text-sm wsm-text-slate-200 focus:wsm-outline-none focus:wsm-border-indigo-500 wsm-transition-colors">
				</div>

				<div>
					<label for="cat-parent" class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-mb-2"><?php echo esc_html( __( 'Parent Category', 'karasu-woo-pannel' ) ); ?></label>
					<select id="cat-parent" class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-px-4 wsm-py-2.5 wsm-text-sm focus:wsm-outline-none">
						<option value="0"><?php echo esc_html( __( 'No Parent', 'karasu-woo-pannel' ) ); ?></option>
						<!-- Loaded dynamically via JavaScript API -->
					</select>
				</div>

				<div>
					<label for="cat-desc" class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-mb-2"><?php echo esc_html( __( 'Description', 'karasu-woo-pannel' ) ); ?></label>
					<textarea id="cat-desc" rows="3" class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-p-3 wsm-text-sm wsm-text-slate-200 focus:wsm-outline-none focus:wsm-border-indigo-500 wsm-transition-colors"></textarea>
				</div>

				<div>
					<label class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-mb-2"><?php echo esc_html( __( 'Category Image', 'karasu-woo-pannel' ) ); ?></label>
					<div class="wsm-flex wsm-items-center wsm-space-x-3 wsm-space-x-reverse">
						<div id="cat-image-trigger" class="wsm-w-16 wsm-h-16 wsm-bg-slate-950/60 wsm-border-2 wsm-border-dashed wsm-border-slate-800 wsm-rounded-2xl wsm-overflow-hidden wsm-flex wsm-items-center wsm-justify-center wsm-cursor-pointer group">
							<img id="cat-image-preview" src="" class="wsm-w-full wsm-h-full wsm-object-cover wsm-hidden" alt="Preview">
							<span id="cat-image-placeholder" class="wsm-text-slate-600 group-hover:wsm-text-slate-400 wsm-transition-colors">+</span>
						</div>
						<input type="hidden" id="cat-image-id" value="">
						<input type="file" id="cat-image-file" class="wsm-hidden" accept="image/*">
						<span class="wsm-text-xs wsm-text-slate-500"><?php echo esc_html( __( 'Square dimensions are recommended', 'karasu-woo-pannel' ) ); ?></span>
					</div>
				</div>

				<button type="submit" class="wsm-w-full wsm-bg-indigo-600 hover:wsm-bg-indigo-500 wsm-text-white wsm-font-semibold wsm-rounded-2xl wsm-py-3 wsm-shadow-lg wsm-shadow-indigo-500/20 wsm-transition-colors">
					<?php echo esc_html( __( 'Add Category', 'karasu-woo-pannel' ) ); ?>
				</button>
			</form>
		</div>

		<!-- Categories Hierarchy Listing -->
		<div class="lg:wsm-col-span-2 wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-overflow-hidden wsm-shadow-lg">
			<div class="wsm-px-6 wsm-py-4 wsm-border-b wsm-border-slate-800 wsm-font-semibold"><?php echo esc_html( __( 'Categories List', 'karasu-woo-pannel' ) ); ?></div>
			<div class="wsm-overflow-x-auto">
				<table class="wsm-w-full wsm-text-right wsm-border-collapse">
					<thead>
						<tr class="wsm-border-b wsm-border-slate-800 wsm-bg-slate-950/20">
							<th class="wsm-px-6 wsm-py-3 wsm-text-xs wsm-text-slate-500"><?php echo esc_html( __( 'Image', 'karasu-woo-pannel' ) ); ?></th>
							<th class="wsm-px-6 wsm-py-3 wsm-text-xs wsm-text-slate-500"><?php echo esc_html( __( 'Category Name', 'karasu-woo-pannel' ) ); ?></th>
							<th class="wsm-px-6 wsm-py-3 wsm-text-xs wsm-text-slate-500"><?php echo esc_html( __( 'Slug', 'karasu-woo-pannel' ) ); ?></th>
							<th class="wsm-px-6 wsm-py-3 wsm-text-xs wsm-text-slate-500 wsm-text-center"><?php echo esc_html( __( 'Actions', 'karasu-woo-pannel' ) ); ?></th>
						</tr>
					</thead>
					<tbody id="categories-table-body" class="wsm-divide-y wsm-divide-slate-800/40">
						<tr>
							<td colspan="4" class="wsm-px-6 wsm-py-12 wsm-text-center wsm-text-slate-500"><?php echo esc_html( __( 'Loading categories...', 'karasu-woo-pannel' ) ); ?></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<!-- Page script attachment -->
<script src="<?php echo esc_url( WSM_PLUGIN_URL . 'assets/js/wsm-products.js' ); ?>"></script>

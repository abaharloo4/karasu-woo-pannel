<?php
/**
 * Attributes List Panel View Template
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
		<h1 class="wsm-text-2xl wsm-font-bold wsm-text-slate-100"><?php echo esc_html( __( 'Product Attributes', 'karasu-woo-pannel' ) ); ?></h1>
	</div>

	<div class="wsm-grid wsm-grid-cols-1 lg:wsm-grid-cols-3 wsm-gap-6">
		<!-- Add Attribute Form -->
		<div class="wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-p-6 wsm-shadow-lg wsm-h-fit font-theme-card">
			<h3 class="wsm-font-semibold wsm-text-slate-200 wsm-mb-4"><?php echo esc_html( __( 'Add New Attribute', 'karasu-woo-pannel' ) ); ?></h3>
			<form id="wsm-add-attribute-form" class="wsm-space-y-4">
				<div>
					<label for="attr-name" class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-mb-2"><?php echo esc_html( __( 'Name', 'karasu-woo-pannel' ) ); ?></label>
					<input type="text" id="attr-name" required class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-px-4 wsm-py-2.5 wsm-text-sm wsm-text-slate-200 focus:wsm-outline-none focus:wsm-border-indigo-500 wsm-transition-colors">
				</div>

				<div>
					<label for="attr-slug" class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-mb-2"><?php echo esc_html( __( 'Slug', 'karasu-woo-pannel' ) ); ?></label>
					<input type="text" id="attr-slug" class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-px-4 wsm-py-2.5 wsm-text-sm wsm-text-slate-200 focus:wsm-outline-none focus:wsm-border-indigo-500 wsm-transition-colors">
					<p class="wsm-text-[10px] wsm-text-slate-500 wsm-mt-1"><?php echo esc_html( __( 'Unique identifier (slug). Max 28 characters.', 'karasu-woo-pannel' ) ); ?></p>
				</div>

				<button type="submit" class="wsm-w-full wsm-bg-indigo-600 hover:wsm-bg-indigo-500 wsm-text-white wsm-font-semibold wsm-rounded-2xl wsm-py-3 wsm-shadow-lg wsm-shadow-indigo-500/20 wsm-transition-colors">
					<?php echo esc_html( __( 'Add Attribute', 'karasu-woo-pannel' ) ); ?>
				</button>
			</form>
		</div>

		<!-- Attributes Listing -->
		<div class="lg:wsm-col-span-2 wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-overflow-hidden wsm-shadow-lg">
			<div class="wsm-px-6 wsm-py-4 wsm-border-b wsm-border-slate-800 wsm-font-semibold"><?php echo esc_html( __( 'Attributes List', 'karasu-woo-pannel' ) ); ?></div>
			<div class="wsm-overflow-x-auto">
				<table class="wsm-w-full wsm-text-right wsm-border-collapse">
					<thead>
						<tr class="wsm-border-b wsm-border-slate-800 wsm-bg-slate-950/20">
							<th class="wsm-px-6 wsm-py-3 wsm-text-xs wsm-text-slate-500"><?php echo esc_html( __( 'Name', 'karasu-woo-pannel' ) ); ?></th>
							<th class="wsm-px-6 wsm-py-3 wsm-text-xs wsm-text-slate-500"><?php echo esc_html( __( 'Slug', 'karasu-woo-pannel' ) ); ?></th>
							<th class="wsm-px-6 wsm-py-3 wsm-text-xs wsm-text-slate-500 wsm-text-center"><?php echo esc_html( __( 'Actions', 'karasu-woo-pannel' ) ); ?></th>
						</tr>
					</thead>
					<tbody id="attributes-table-body" class="wsm-divide-y wsm-divide-slate-800/40">
						<tr>
							<td colspan="3" class="wsm-px-6 wsm-py-12 wsm-text-center wsm-text-slate-500"><?php echo esc_html( __( 'Loading attributes...', 'karasu-woo-pannel' ) ); ?></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

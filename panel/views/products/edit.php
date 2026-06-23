<?php
/**
 * Product Create/Edit Form Template
 *
 * @package KarasuWooPannel
 * @version 1.0.10
 * @date 2026-06-23
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$product_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
?>
<div class="wsm-space-y-6" id="product-edit-container" data-product-id="<?php echo esc_attr( $product_id ); ?>">
	<!-- Loading Skeleton -->
	<div class="wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-p-12 wsm-text-center wsm-text-slate-500 wsm-animate-pulse">
		در حال بارگذاری اطلاعات فرم...
	</div>
</div>

<!-- Page script attachment -->
<script src="<?php echo esc_url( WSM_PLUGIN_URL . 'assets/js/wsm-products.js' ); ?>"></script>

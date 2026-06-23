<?php
/**
 * Order Detail Panel View Template
 *
 * @package KarasuWooPannel
 * @version 1.0.4
 * @date 2026-06-23
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$order_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
?>
<div class="wsm-space-y-6" id="order-detail-container" data-order-id="<?php echo esc_attr( $order_id ); ?>">
	<!-- Loading Skeleton -->
	<div class="wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-p-12 wsm-text-center wsm-text-slate-500 wsm-animate-pulse">
		در حال بارگذاری جزئیات سفارش...
	</div>
</div>

<!-- Page script attachment -->
<script src="<?php echo esc_url( WSM_PLUGIN_URL . 'assets/js/wsm-orders.js' ); ?>"></script>

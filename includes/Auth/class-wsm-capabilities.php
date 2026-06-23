<?php
/**
 * Custom Capabilities Constants
 *
 * @package KarasuWooPannel
 * @version 1.1.1
 * @date 2026-06-23
 */

namespace WooStoreManager\Auth;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class WSM_Capabilities
 */
class WSM_Capabilities {

	/**
	 * Capability to access the store admin panel.
	 */
	const ACCESS_PANEL = 'wsm_access_panel';

	/**
	 * Capability to manage WooCommerce orders.
	 */
	const MANAGE_ORDERS = 'wsm_manage_orders';

	/**
	 * Capability to manage WooCommerce products.
	 */
	const MANAGE_PRODUCTS = 'wsm_manage_products';

	/**
	 * Capability to manage WooCommerce coupons.
	 */
	const MANAGE_COUPONS = 'wsm_manage_coupons';

	/**
	 * Capability to view sales reports.
	 */
	const VIEW_REPORTS = 'wsm_view_reports';

	/**
	 * Capability to manage SMS gateway settings.
	 */
	const MANAGE_SMS = 'wsm_manage_sms';

	/**
	 * Get list of all custom capabilities.
	 *
	 * @return string[] Capabilities list.
	 */
	public static function get_all(): array {
		return [
			self::ACCESS_PANEL,
			self::MANAGE_ORDERS,
			self::MANAGE_PRODUCTS,
			self::MANAGE_COUPONS,
			self::VIEW_REPORTS,
			self::MANAGE_SMS,
		];
	}

	/**
	 * Wrapper to check capability for current user.
	 *
	 * @param string $capability Capability name.
	 * @return bool True if permitted.
	 */
	public static function check( string $capability ): bool {
		return current_user_can( $capability );
	}
}

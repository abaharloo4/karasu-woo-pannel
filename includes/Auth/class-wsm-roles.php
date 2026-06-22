<?php
/**
 * Role and Capability Management
 *
 * @package KarasuWooPannel
 * @version 1.0.1
 * @date 2026-06-23
 */

namespace WooStoreManager\Auth;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class WSM_Roles
 */
class WSM_Roles {

	/**
	 * Create custom user role with designated capabilities.
	 */
	public static function create_role(): void {
		add_role(
			'shop_manager_custom',
			__( 'مدیر فروشگاه (اختصاصی)', 'karasu-woo-pannel' ),
			[
				'read'                => true,
				'upload_files'        => true, // Essential for uploading product media.
				'wsm_access_panel'    => true,
				'wsm_manage_orders'   => true,
				'wsm_manage_products' => true,
				'wsm_manage_coupons'  => true,
				'wsm_view_reports'    => true,
			]
		);
	}

	/**
	 * Block access to wp-admin for the custom shop manager role.
	 */
	public function block_admin_access(): void {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		if ( ! is_user_logged_in() ) {
			return;
		}

		$user = wp_get_current_user();
		if ( in_array( 'shop_manager_custom', (array) $user->roles, true ) ) {
			wp_safe_redirect( wsm_panel_url() );
			exit;
		}
	}
}

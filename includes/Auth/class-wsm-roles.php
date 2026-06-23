<?php
/**
 * Role and Capability Management
 *
 * @package KarasuWooPannel
 * @version 1.0.10
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

		self::add_caps_to_builtins();
	}

	/**
	 * Automatically add custom capabilities to administrator and standard shop_manager roles.
	 */
	public static function add_caps_to_builtins(): void {
		$caps = [
			'wsm_access_panel',
			'wsm_manage_orders',
			'wsm_manage_products',
			'wsm_manage_coupons',
			'wsm_view_reports',
		];

		$roles_to_update = [ 'administrator', 'shop_manager' ];

		foreach ( $roles_to_update as $role_name ) {
			$role = get_role( $role_name );
			if ( $role ) {
				foreach ( $caps as $cap ) {
					if ( ! $role->has_cap( $cap ) ) {
						$role->add_cap( $cap );
					}
				}
			}
		}
	}

	/**
	 * Block access to wp-admin for the custom shop manager role.
	 */
	public function block_admin_access(): void {
		// Dynamically ensure administrator and shop_manager roles have necessary access capabilities.
		self::add_caps_to_builtins();

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

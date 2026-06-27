<?php
/**
 * Abstract Base REST Controller
 *
 * @package KarasuWooPannel
 * @version 1.1.1
 * @date 2026-06-23
 */

namespace WooStoreManager\Api;

use WP_REST_Controller;
use WP_REST_Request;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class WSM_REST_Controller
 */
abstract class WSM_REST_Controller extends WP_REST_Controller {

	/**
	 * Route namespace prefix.
	 *
	 * @var string
	 */
	protected $namespace = 'wsm/v1';



	/**
	 * Default permission check callback for protected endpoints.
	 *
	 * @param WP_REST_Request $request API request parameters.
	 * @return bool|WP_Error True if permitted, WP_Error if unauthorized.
	 */
	protected function wsm_check_permission( WP_REST_Request $request ): bool|WP_Error {
		if ( ! wsm_is_authenticated() ) {
			return new WP_Error(
				'wsm_unauthorized',
				__( 'نشست شما منقضی شده است. لطفا مجددا وارد شوید.', 'karasu-woo-pannel' ),
				[ 'status' => 401 ]
			);
		}
		return true;
	}

	/**
	 * Shared helper to check authorization and specific capabilities with fallback.
	 *
	 * @param WP_REST_Request $request Request.
	 * @param string          $capability Capability name.
	 * @param string          $error_message Custom error message.
	 * @return bool|WP_Error True if permitted, WP_Error if unauthorized.
	 */
	protected function check_capability_permission( WP_REST_Request $request, string $capability, string $error_message = '' ): bool|WP_Error {
		$auth_check = $this->wsm_check_permission( $request );
		if ( is_wp_error( $auth_check ) ) {
			return $auth_check;
		}

		// Check if the corresponding section is globally enabled
		$cap_to_section = [
			'wsm_access_panel'    => 'dashboard',
			'wsm_manage_orders'   => 'orders',
			'wsm_manage_products' => 'products',
			'wsm_manage_coupons'  => 'coupons',
			'wsm_view_reports'    => 'reports',
			'wsm_manage_sms'      => 'sms',
		];

		if ( isset( $cap_to_section[ $capability ] ) ) {
			$sec = $cap_to_section[ $capability ];
			if ( get_option( 'wsm_enable_' . $sec, 'yes' ) !== 'yes' ) {
				return new WP_Error(
					'wsm_forbidden',
					__( 'این بخش از پنل غیرفعال شده است.', 'karasu-woo-pannel' ),
					[ 'status' => 403 ]
				);
			}
		}

		if ( ! current_user_can( $capability ) ) {
			if ( empty( $error_message ) ) {
				$error_message = __( 'دسترسی غیرمجاز.', 'karasu-woo-pannel' );
			}
			return new WP_Error(
				'wsm_forbidden',
				$error_message,
				[ 'status' => 403 ]
			);
		}

		return true;
	}
}

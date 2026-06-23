<?php
/**
 * Abstract Base REST Controller
 *
 * @package KarasuWooPannel
 * @version 1.1.0
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

		if ( ! current_user_can( $capability ) && ! current_user_can( 'manage_woocommerce' ) && ! current_user_can( 'manage_options' ) ) {
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

<?php
/**
 * Abstract Base REST Controller
 *
 * @package KarasuWooPannel
 * @version 1.0.7
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
}

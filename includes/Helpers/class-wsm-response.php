<?php
/**
 * Standardized JSON API Response Helper
 *
 * @package KarasuWooPannel
 * @version 1.0.6
 * @date 2026-06-23
 */

namespace WooStoreManager\Helpers;

use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class WSM_Response
 */
class WSM_Response {

	/**
	 * Return a successful JSON response.
	 *
	 * @param mixed  $data    Response payload data.
	 * @param string $message User friendly feedback message.
	 * @param int    $code    HTTP status code. Default 200.
	 * @return WP_REST_Response Standardized response object.
	 */
	public static function success( mixed $data, string $message = '', int $code = 200 ): WP_REST_Response {
		return new WP_REST_Response(
			[
				'success' => true,
				'message' => $message,
				'data'    => $data,
			],
			$code
		);
	}

	/**
	 * Return an error JSON response.
	 *
	 * @param string $message Main error message feedback.
	 * @param int    $code    HTTP status code. Default 400.
	 * @param array  $errors  Optional list of specific field validation errors.
	 * @return WP_REST_Response Standardized response object.
	 */
	public static function error( string $message, int $code = 400, array $errors = [] ): WP_REST_Response {
		return new WP_REST_Response(
			[
				'success' => false,
				'message' => $message,
				'errors'  => $errors,
			],
			$code
		);
	}
}

<?php
/**
 * REST Controller for WooCommerce Coupons
 *
 * @package KarasuWooPannel
 * @version 1.0.8
 * @date 2026-06-23
 */

namespace WooStoreManager\Api;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use WooStoreManager\Services\WSM_Coupon_Service;
use WooStoreManager\Helpers\WSM_Response;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class WSM_Coupons_Controller
 */
class WSM_Coupons_Controller extends WSM_REST_Controller {

	/**
	 * Coupon service.
	 *
	 * @var WSM_Coupon_Service
	 */
	private WSM_Coupon_Service $service;

	/**
	 * WSM_Coupons_Controller constructor.
	 *
	 * @param WSM_Coupon_Service $service Target service.
	 */
	public function __construct( WSM_Coupon_Service $service ) {
		$this->service = $service;
	}

	/**
	 * Register REST routes.
	 */
	public function register_routes(): void {
		register_rest_route(
			$this->namespace,
			'/coupons',
			[
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'get_coupons' ],
					'permission_callback' => [ $this, 'check_permission' ],
				],
				[
					'methods'             => 'POST',
					'callback'            => [ $this, 'create_coupon' ],
					'permission_callback' => [ $this, 'check_permission' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/coupons/(?P<id>\d+)',
			[
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'get_coupon_detail' ],
					'permission_callback' => [ $this, 'check_permission' ],
				],
				[
					'methods'             => 'PUT',
					'callback'            => [ $this, 'update_coupon' ],
					'permission_callback' => [ $this, 'check_permission' ],
				],
				[
					'methods'             => 'DELETE',
					'callback'            => [ $this, 'delete_coupon' ],
					'permission_callback' => [ $this, 'check_permission' ],
				],
			]
		);
	}

	/**
	 * Verify permissions.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return bool|WP_Error True if authorized, else WP_Error.
	 */
	public function check_permission( WP_REST_Request $request ): bool|WP_Error {
		$auth_check = $this->wsm_check_permission( $request );
		if ( is_wp_error( $auth_check ) ) {
			return $auth_check;
		}

		if ( ! current_user_can( 'wsm_manage_coupons' ) && ! current_user_can( 'manage_woocommerce' ) && ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'wsm_forbidden',
				__( 'دسترسی غیرمجاز. شما مجوز مدیریت کوپن‌ها را ندارید.', 'karasu-woo-pannel' ),
				[ 'status' => 403 ]
			);
		}

		return true;
	}

	/**
	 * Query coupons list.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response REST API Response.
	 */
	public function get_coupons( WP_REST_Request $request ): WP_REST_Response {
		$args = [
			'page'     => $request->get_param( 'page' ),
			'per_page' => $request->get_param( 'per_page' ),
			'search'   => $request->get_param( 'search' ),
		];

		$results = $this->service->get_coupons( array_filter( $args ) );
		return WSM_Response::success( $results, __( 'لیست کوپن‌ها با موفقیت بارگذاری شد.', 'karasu-woo-pannel' ) );
	}

	/**
	 * Get details of single coupon.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error Response or error.
	 */
	public function get_coupon_detail( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$id     = (int) $request->get_param( 'id' );
		$result = $this->service->get_coupon_detail( $id );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return WSM_Response::success( $result, __( 'جزییات کوپن با موفقیت بارگذاری شد.', 'karasu-woo-pannel' ) );
	}

	/**
	 * Create a coupon.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error Response or error.
	 */
	public function create_coupon( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$body   = json_decode( $request->get_body(), true );
		$result = $this->service->create_coupon( (array) $body );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return WSM_Response::success( [ 'coupon_id' => $result ], __( 'کوپن با موفقیت ایجاد شد.', 'karasu-woo-pannel' ) );
	}

	/**
	 * Update coupon details.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error Response or error.
	 */
	public function update_coupon( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$id     = (int) $request->get_param( 'id' );
		$body   = json_decode( $request->get_body(), true );
		$result = $this->service->update_coupon( $id, (array) $body );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return WSM_Response::success( [ 'id' => $id ], __( 'کوپن با موفقیت ویرایش شد.', 'karasu-woo-pannel' ) );
	}

	/**
	 * Delete a coupon.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error Response or error.
	 */
	public function delete_coupon( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$id     = (int) $request->get_param( 'id' );
		$result = $this->service->delete_coupon( $id );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return WSM_Response::success( [ 'id' => $id ], __( 'کوپن با موفقیت حذف شد.', 'karasu-woo-pannel' ) );
	}
}

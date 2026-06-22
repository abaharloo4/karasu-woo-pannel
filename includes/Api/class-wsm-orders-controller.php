<?php
/**
 * REST Controller for WooCommerce Orders
 *
 * @package KarasuWooPannel
 * @version 1.0.1
 * @date 2026-06-23
 */

namespace WooStoreManager\Api;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use WooStoreManager\Services\WSM_Order_Service;
use WooStoreManager\Helpers\WSM_Response;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class WSM_Orders_Controller
 */
class WSM_Orders_Controller extends WSM_REST_Controller {

	/**
	 * Order service.
	 *
	 * @var WSM_Order_Service
	 */
	private WSM_Order_Service $service;

	/**
	 * WSM_Orders_Controller constructor.
	 *
	 * @param WSM_Order_Service $service Target service.
	 */
	public function __construct( WSM_Order_Service $service ) {
		$this->service = $service;
	}

	/**
	 * Register REST routes.
	 */
	public function register_routes(): void {
		register_rest_route(
			$this->namespace,
			'/orders',
			[
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'get_orders' ],
					'permission_callback' => [ $this, 'check_permission' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/orders/(?P<id>\d+)',
			[
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'get_order_detail' ],
					'permission_callback' => [ $this, 'check_permission' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/orders/(?P<id>\d+)/status',
			[
				[
					'methods'             => 'PATCH',
					'callback'            => [ $this, 'update_status' ],
					'permission_callback' => [ $this, 'check_permission' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/orders/(?P<id>\d+)/notes',
			[
				[
					'methods'             => 'POST',
					'callback'            => [ $this, 'add_note' ],
					'permission_callback' => [ $this, 'check_permission' ],
				],
			]
		);
	}

	/**
	 * Check capability permissions for orders management.
	 *
	 * @param WP_REST_Request $request Request properties.
	 * @return bool|WP_Error True if authorized, else WP_Error.
	 */
	public function check_permission( WP_REST_Request $request ): bool|WP_Error {
		$auth_check = $this->wsm_check_permission( $request );
		if ( is_wp_error( $auth_check ) ) {
			return $auth_check;
		}

		if ( ! current_user_can( 'wsm_manage_orders' ) ) {
			return new WP_Error(
				'wsm_forbidden',
				__( 'دسترسی غیرمجاز. شما مجوز مدیریت سفارش‌ها را ندارید.', 'karasu-woo-pannel' ),
				[ 'status' => 403 ]
			);
		}

		return true;
	}

	/**
	 * Query list of orders.
	 *
	 * @param WP_REST_Request $request Request filters parameters.
	 * @return WP_REST_Response REST API Response.
	 */
	public function get_orders( WP_REST_Request $request ): WP_REST_Response {
		$args = [
			'page'      => $request->get_param( 'page' ),
			'per_page'  => $request->get_param( 'per_page' ),
			'status'    => $request->get_param( 'status' ),
			'search'    => $request->get_param( 'search' ),
			'date_from' => $request->get_param( 'date_from' ),
			'date_to'   => $request->get_param( 'date_to' ),
		];

		$results = $this->service->get_orders( array_filter( $args ) );
		return WSM_Response::success( $results, __( 'لیست سفارش‌ها با موفقیت بارگذاری شد.', 'karasu-woo-pannel' ) );
	}

	/**
	 * Retrieve a detailed single order.
	 *
	 * @param WP_REST_Request $request Request params.
	 * @return WP_REST_Response|WP_Error Detailed response or error.
	 */
	public function get_order_detail( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$id     = (int) $request->get_param( 'id' );
		$result = $this->service->get_order_detail( $id );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return WSM_Response::success( $result, __( 'جزییات سفارش با موفقیت بارگذاری شد.', 'karasu-woo-pannel' ) );
	}

	/**
	 * Update status code of a single order.
	 *
	 * @param WP_REST_Request $request Request parameters containing status inside body.
	 * @return WP_REST_Response|WP_Error Response or error.
	 */
	public function update_status( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$id     = (int) $request->get_param( 'id' );
		$body   = json_decode( $request->get_body(), true );
		$status = sanitize_text_field( $body['status'] ?? '' );

		$result = $this->service->update_status( $id, $status );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return WSM_Response::success(
			[
				'id'     => $id,
				'status' => $status,
			],
			__( 'وضعیت سفارش با موفقیت تغییر کرد.', 'karasu-woo-pannel' )
		);
	}

	/**
	 * Append note comment to a single order.
	 *
	 * @param WP_REST_Request $request Request details.
	 * @return WP_REST_Response|WP_Error Response details or error.
	 */
	public function add_note( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$id            = (int) $request->get_param( 'id' );
		$body          = json_decode( $request->get_body(), true );
		$note          = sanitize_textarea_field( $body['note'] ?? '' );
		$customer_note = isset( $body['customer_note'] ) ? (bool) $body['customer_note'] : false;

		$result = $this->service->add_note( $id, $note, $customer_note );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return WSM_Response::success( [ 'note_id' => $result ], __( 'یادداشت با موفقیت ثبت شد.', 'karasu-woo-pannel' ) );
	}
}

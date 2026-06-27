<?php
/**
 * REST Controller for WooCommerce Orders
 *
 * @package KarasuWooPannel
 * @version 1.1.1
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
				[
					'methods'             => 'DELETE',
					'callback'            => [ $this, 'delete_order' ],
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

		register_rest_route(
			$this->namespace,
			'/orders/(?P<id>\d+)/receipts/(?P<hash>[a-zA-Z0-9]+)',
			[
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'download_receipt' ],
					'permission_callback' => [ $this, 'check_receipt_permission' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/orders/bulk',
			[
				[
					'methods'             => 'POST',
					'callback'            => [ $this, 'bulk_action' ],
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
		return $this->check_capability_permission( $request, 'wsm_manage_orders', __( 'دسترسی غیرمجاز. شما مجوز مدیریت سفارش‌ها را ندارید.', 'karasu-woo-pannel' ) );
	}

	/**
	 * Check receipt file access permission.
	 * Accepts both panel session auth AND standard WordPress admin cookies.
	 *
	 * @param WP_REST_Request $request Request properties.
	 * @return bool|WP_Error True if authorized, else WP_Error.
	 */
	public function check_receipt_permission( WP_REST_Request $request ): bool|WP_Error {
		// First try standard panel session auth
		if ( wsm_is_authenticated() && current_user_can( 'wsm_manage_orders' ) ) {
			return true;
		}

		// Fallback: allow WordPress logged-in admin users with WooCommerce management capability
		if ( is_user_logged_in() && current_user_can( 'manage_woocommerce' ) ) {
			return true;
		}

		return new WP_Error(
			'wsm_unauthorized',
			__( 'دسترسی غیرمجاز. برای مشاهده رسید باید وارد پنل شده باشید.', 'karasu-woo-pannel' ),
			[ 'status' => 401 ]
		);
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

	/**
	 * Delete/trash a single WooCommerce order.
	 *
	 * @param WP_REST_Request $request Request details.
	 * @return WP_REST_Response|WP_Error Response or error details.
	 */
	public function delete_order( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$id = (int) $request->get_param( 'id' );
		$result = $this->service->delete_order( $id, false ); // default to move to trash

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return WSM_Response::success( [ 'id' => $id ], __( 'سفارش با موفقیت به زباله‌دان منتقل شد.', 'karasu-woo-pannel' ) );
	}

	/**
	 * Perform bulk operations (status updates / deletions) on multiple orders.
	 *
	 * @param WP_REST_Request $request Request body with ids, action, and optional status.
	 * @return WP_REST_Response|WP_Error Response or error.
	 */
	public function bulk_action( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$body = json_decode( $request->get_body(), true );
		$ids = isset( $body['ids'] ) ? array_map( 'absint', (array) $body['ids'] ) : [];
		$action = sanitize_text_field( $body['action'] ?? '' );
		$status = sanitize_text_field( $body['status'] ?? '' );

		if ( empty( $ids ) ) {
			return new WP_Error( 'wsm_missing_ids', __( 'هیچ سفارشی انتخاب نشده است.', 'karasu-woo-pannel' ), [ 'status' => 400 ] );
		}

		if ( ! in_array( $action, [ 'status', 'delete' ], true ) ) {
			return new WP_Error( 'wsm_invalid_action', __( 'عملیات دسته جمعی نامعتبر است.', 'karasu-woo-pannel' ), [ 'status' => 400 ] );
		}

		$success_count = 0;
		if ( 'status' === $action ) {
			$allowed_statuses = [ 'pending', 'processing', 'on-hold', 'completed', 'cancelled', 'refunded', 'failed' ];
			if ( ! in_array( $status, $allowed_statuses, true ) ) {
				return new WP_Error( 'wsm_invalid_status', __( 'وضعیت سفارش نامعتبر است.', 'karasu-woo-pannel' ), [ 'status' => 400 ] );
			}
			foreach ( $ids as $id ) {
				$result = $this->service->update_status( $id, $status );
				if ( ! is_wp_error( $result ) ) {
					$success_count++;
				}
			}
		} elseif ( 'delete' === $action ) {
			foreach ( $ids as $id ) {
				$result = $this->service->delete_order( $id, false ); // Move to trash
				if ( ! is_wp_error( $result ) ) {
					$success_count++;
				}
			}
		}

		return WSM_Response::success(
			[
				'success_count' => $success_count,
				'total_count'   => count( $ids ),
			],
			sprintf( __( 'عملیات دسته جمعی روی %d سفارش با موفقیت اعمال شد.', 'karasu-woo-pannel' ), $success_count )
		);
	}

	/**
	 * Serve a card-to-card receipt file securely for panel managers.
	 *
	 * @param WP_REST_Request $request REST request properties.
	 */
	public function download_receipt( \WP_REST_Request $request ): void {
		$order_id  = (int) $request->get_param( 'id' );
		$file_hash = sanitize_text_field( $request->get_param( 'hash' ) );

		// Retrieve receipt from metadata of Karasu Payment Method
		// Meta key: _kpm_receipt_files
		$receipts = get_post_meta( $order_id, '_kpm_receipt_files', true );
		if ( ! is_array( $receipts ) ) {
			$receipts = [];
		}

		$found_receipt = null;
		foreach ( $receipts as $receipt ) {
			if ( isset( $receipt['file_hash'] ) && $receipt['file_hash'] === $file_hash ) {
				$found_receipt = $receipt;
				break;
			}
		}

		if ( ! $found_receipt ) {
			status_header( 404 );
			echo 'File not found';
			exit;
		}

		$wp_upload = wp_upload_dir();
		$basedir   = trailingslashit( $wp_upload['basedir'] );
		$full_path = $basedir . $found_receipt['file_path'];

		if ( ! file_exists( $full_path ) ) {
			status_header( 404 );
			echo 'File not found on disk';
			exit;
		}

		$orig_name = $found_receipt['file_name'] ?? basename( $full_path );
		$finfo     = wp_check_filetype( $full_path );
		$mime_type = ! empty( $finfo['type'] ) ? $finfo['type'] : 'application/octet-stream';

		$action = $request->get_param( 'action' );
		$disposition = ( 'download' === $action ) ? 'attachment' : 'inline';

		nocache_headers();
		header( 'Content-Type: ' . $mime_type );
		header( 'Content-Disposition: ' . $disposition . '; filename="' . sanitize_file_name( $orig_name ) . '"' );
		header( 'Content-Length: ' . filesize( $full_path ) );
		header( 'Content-Transfer-Encoding: binary' );

		readfile( $full_path );
		exit;
	}
}

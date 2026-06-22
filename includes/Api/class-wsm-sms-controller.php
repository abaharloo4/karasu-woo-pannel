<?php
/**
 * REST Controller for SMS Settings & Logs
 *
 * @package KarasuWooPannel
 * @version 1.0.1
 * @date 2026-06-23
 */

namespace WooStoreManager\Api;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use WooStoreManager\Services\WSM_Sms_Service;
use WooStoreManager\Helpers\WSM_Response;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class WSM_Sms_Controller
 */
class WSM_Sms_Controller extends WSM_REST_Controller {

	/**
	 * SMS service.
	 *
	 * @var WSM_Sms_Service
	 */
	private WSM_Sms_Service $service;

	/**
	 * WSM_Sms_Controller constructor.
	 */
	public function __construct() {
		$this->service = new WSM_Sms_Service();
	}

	/**
	 * Register SMS REST routes.
	 */
	public function register_routes(): void {
		register_rest_route(
			$this->namespace,
			'/sms/templates',
			[
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'get_templates' ],
					'permission_callback' => [ $this, 'check_permission' ],
				],
				[
					'methods'             => 'POST',
					'callback'            => [ $this, 'update_templates' ],
					'permission_callback' => [ $this, 'check_permission' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/sms/logs',
			[
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'get_logs' ],
					'permission_callback' => [ $this, 'check_permission' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/sms/test',
			[
				[
					'methods'             => 'POST',
					'callback'            => [ $this, 'send_test_sms' ],
					'permission_callback' => [ $this, 'check_permission' ],
				],
			]
		);
	}

	/**
	 * Check capabilities.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return bool|WP_Error True if user authorized, else WP_Error.
	 */
	public function check_permission( WP_REST_Request $request ): bool|WP_Error {
		$auth_check = $this->wsm_check_permission( $request );
		if ( is_wp_error( $auth_check ) ) {
			return $auth_check;
		}

		if ( ! current_user_can( 'wsm_access_panel' ) ) {
			return new WP_Error(
				'wsm_forbidden',
				__( 'دسترسی غیرمجاز.', 'karasu-woo-pannel' ),
				[ 'status' => 403 ]
			);
		}

		return true;
	}

	/**
	 * Get SMS templates.
	 *
	 * @return WP_REST_Response REST API Response.
	 */
	public function get_templates(): WP_REST_Response {
		$templates = WSM_Sms_Service::get_templates();
		return WSM_Response::success( $templates, __( 'قالب‌های پیامک با موفقیت دریافت شدند.', 'karasu-woo-pannel' ) );
	}

	/**
	 * Save updated SMS templates.
	 *
	 * @param WP_REST_Request $request Request body.
	 * @return WP_REST_Response REST API Response.
	 */
	public function update_templates( WP_REST_Request $request ): WP_REST_Response {
		$body = json_decode( $request->get_body(), true );
		WSM_Sms_Service::update_templates( (array) $body );
		return WSM_Response::success( null, __( 'تنظیمات قالب‌های پیامک با موفقیت ذخیره شدند.', 'karasu-woo-pannel' ) );
	}

	/**
	 * Get SMS dispatches logs list.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response REST API Response.
	 */
	public function get_logs( WP_REST_Request $request ): WP_REST_Response {
		global $wpdb;
		$page     = max( 1, (int) $request->get_param( 'page' ) );
		$per_page = max( 1, (int) $request->get_param( 'per_page' ) );
		if ( 0 === $per_page ) {
			$per_page = 20;
		}
		$offset   = ( $page - 1 ) * $per_page;

		$table_name = $wpdb->prefix . 'wsm_sms_log';
		$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );
		$logs = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $table_name ORDER BY id DESC LIMIT %d OFFSET %d",
				$per_page,
				$offset
			),
			ARRAY_A
		);

		$formatted = [];
		foreach ( $logs as $log ) {
			$log['sent_at_jalali'] = \WooStoreManager\Helpers\WSM_Date_Helper::to_jalali_string( $log['sent_at'] );
			$formatted[] = $log;
		}

		return WSM_Response::success(
			[
				'logs'  => $formatted,
				'total' => $total,
				'pages' => ceil( $total / $per_page ),
			],
			__( 'لاگ‌های پیامک با موفقیت دریافت شدند.', 'karasu-woo-pannel' )
		);
	}

	/**
	 * Send a test SMS message.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error REST Response or error.
	 */
	public function send_test_sms( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$body = json_decode( $request->get_body(), true );
		$phone = sanitize_text_field( $body['phone'] ?? '' );
		$message = sanitize_text_field( $body['message'] ?? '' );

		if ( empty( $phone ) || empty( $message ) ) {
			return WSM_Response::error( __( 'شماره موبایل و پیام الزامی هستند.', 'karasu-woo-pannel' ) );
		}

		$success = $this->service->send_sms( $phone, $message, 'test_message' );

		if ( $success ) {
			return WSM_Response::success( null, __( 'پیامک تست با موفقیت ارسال شد.', 'karasu-woo-pannel' ) );
		} else {
			return WSM_Response::error( __( 'ارسال پیامک تست ناموفق بود. لاگ‌ها را بررسی کنید.', 'karasu-woo-pannel' ) );
		}
	}
}

<?php
/**
 * REST Controller for Solar Jalali Sales Reports
 *
 * @package KarasuWooPannel
 * @version 1.0.3
 * @date 2026-06-23
 */

namespace WooStoreManager\Api;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use WooStoreManager\Repositories\WSM_Report_Repository;
use WooStoreManager\Helpers\WSM_Date_Helper;
use WooStoreManager\Helpers\WSM_Response;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class WSM_Reports_Controller
 */
class WSM_Reports_Controller extends WSM_REST_Controller {

	/**
	 * Report repository.
	 *
	 * @var WSM_Report_Repository
	 */
	private WSM_Report_Repository $repository;

	/**
	 * Report service.
	 *
	 * @var \WooStoreManager\Services\WSM_Report_Service
	 */
	private \WooStoreManager\Services\WSM_Report_Service $service;

	/**
	 * WSM_Reports_Controller constructor.
	 */
	public function __construct() {
		$this->repository = new WSM_Report_Repository();
		$this->service    = new \WooStoreManager\Services\WSM_Report_Service();
	}

	/**
	 * Register REST routes.
	 */
	public function register_routes(): void {
		register_rest_route(
			$this->namespace,
			'/reports/sales',
			[
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'get_sales_report' ],
					'permission_callback' => [ $this, 'check_permission' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/reports/dashboard-stats',
			[
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'get_dashboard_stats' ],
					'permission_callback' => [ $this, 'check_permission' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/reports/sales-detailed',
			[
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'get_sales_detailed' ],
					'permission_callback' => [ $this, 'check_permission' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/reports/products-inventory',
			[
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'get_products_inventory' ],
					'permission_callback' => [ $this, 'check_permission' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/reports/customers',
			[
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'get_customers_report' ],
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

		if ( ! current_user_can( 'wsm_view_reports' ) ) {
			return new WP_Error(
				'wsm_forbidden',
				__( 'دسترسی غیرمجاز. شما مجوز مشاهده گزارش‌ها را ندارید.', 'karasu-woo-pannel' ),
				[ 'status' => 403 ]
			);
		}

		return true;
	}

	/**
	 * Clean reports transient cache by incrementing the cache version option.
	 */
	public static function clean_reports_cache(): void {
		update_option( 'wsm_reports_cache_version', time() );
	}

	/**
	 * Fetch sales report based on Jalali date inputs.
	 *
	 * @param WP_REST_Request $request Request parameters.
	 * @return WP_REST_Response REST API Response.
	 */
	public function get_sales_report( WP_REST_Request $request ): WP_REST_Response {
		$start_jalali = sanitize_text_field( $request->get_param( 'start_date' ) );
		$end_jalali   = sanitize_text_field( $request->get_param( 'end_date' ) );

		if ( empty( $start_jalali ) || empty( $end_jalali ) ) {
			$today_ts = time();
			$g_start  = date( 'Y-m-d', strtotime( '-30 days', $today_ts ) );
			$g_end    = date( 'Y-m-d', $today_ts );
		} else {
			$g_start = $this->convert_jalali_to_gregorian_str( $start_jalali );
			$g_end   = $this->convert_jalali_to_gregorian_str( $end_jalali );
		}

		$version = get_option( 'wsm_reports_cache_version', '1' );
		$cache_key = 'wsm_report_' . $version . '_' . md5( $g_start . '_' . $g_end );
		$stats = get_transient( $cache_key );

		if ( false === $stats ) {
			$stats = $this->repository->get_sales_stats( $g_start, $g_end );

			foreach ( $stats['daily'] as &$day ) {
				$jalali = WSM_Date_Helper::to_jalali_string( $day['date'] . ' 00:00:00' );
				$parts = explode( ' ', $jalali );
				$day['date_jalali'] = $parts[0] ?? $day['date'];
			}

			$stats['top_products'] = $this->repository->get_top_selling_products( 5 );

			set_transient( $cache_key, $stats, HOUR_IN_SECONDS );
		}

		return WSM_Response::success( $stats, __( 'گزارش فروش با موفقیت محاسبه شد.', 'karasu-woo-pannel' ) );
	}

	/**
	 * Load quick stats widgets values for the landing dashboard.
	 *
	 * @return WP_REST_Response REST API Response.
	 */
	public function get_dashboard_stats(): WP_REST_Response {
		$version = get_option( 'wsm_reports_cache_version', '1' );
		$cache_key = 'wsm_dashboard_stats_' . $version;
		$results = get_transient( $cache_key );

		if ( false === $results ) {
			$today_g = date( 'Y-m-d' );
			$month_start_g = date( 'Y-m-01' );
			$month_end_g   = date( 'Y-m-t' );

			$today_stats = $this->repository->get_sales_stats( $today_g, $today_g );
			$month_stats = $this->repository->get_sales_stats( $month_start_g, $month_end_g );

			$results = [
				'today_sales'    => $today_stats['total_sales'],
				'today_orders'   => $today_stats['total_orders'],
				'month_sales'    => $month_stats['total_sales'],
				'month_orders'   => $month_stats['total_orders'],
				'top_products'   => $this->repository->get_top_selling_products( 5 ),
			];

			set_transient( $cache_key, $results, HOUR_IN_SECONDS );
		}

		return WSM_Response::success( $results, __( 'آمار پیش‌خوان دریافت شد.', 'karasu-woo-pannel' ) );
	}

	/**
	 * Convert Jalali string (YYYY/MM/DD) to Gregorian.
	 *
	 * @param string $jalali_str Jalali date.
	 * @return string Gregorian date (YYYY-MM-DD).
	 */
	/**
	 * Get detailed sales log report.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response REST API Response.
	 */
	public function get_sales_detailed( WP_REST_Request $request ): WP_REST_Response {
		$start_jalali = sanitize_text_field( $request->get_param( 'start_date' ) );
		$end_jalali   = sanitize_text_field( $request->get_param( 'end_date' ) );

		if ( empty( $start_jalali ) || empty( $end_jalali ) ) {
			$today_ts = time();
			$g_start  = date( 'Y-m-d', strtotime( '-30 days', $today_ts ) );
			$g_end    = date( 'Y-m-d', $today_ts );
		} else {
			$g_start = $this->convert_jalali_to_gregorian_str( $start_jalali );
			$g_end   = $this->convert_jalali_to_gregorian_str( $end_jalali );
		}

		$version = get_option( 'wsm_reports_cache_version', '1' );
		$cache_key = 'wsm_report_detailed_' . $version . '_' . md5( $g_start . '_' . $g_end );
		$data = get_transient( $cache_key );

		if ( false === $data ) {
			$data = $this->service->get_detailed_sales_report( $g_start, $g_end );
			foreach ( $data as &$item ) {
				$item['date_jalali'] = WSM_Date_Helper::to_jalali_string( $item['date'] );
			}
			set_transient( $cache_key, $data, HOUR_IN_SECONDS );
		}

		return WSM_Response::success( $data, __( 'گزارش تفصیلی فروش دریافت شد.', 'karasu-woo-pannel' ) );
	}

	/**
	 * Get products stock inventory report.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response REST API Response.
	 */
	public function get_products_inventory( WP_REST_Request $request ): WP_REST_Response {
		$version = get_option( 'wsm_reports_cache_version', '1' );
		$cache_key = 'wsm_report_inv_' . $version;
		$data = get_transient( $cache_key );

		if ( false === $data ) {
			$data = $this->service->get_low_stock_report();
			set_transient( $cache_key, $data, HOUR_IN_SECONDS );
		}

		return WSM_Response::success( $data, __( 'گزارش وضعیت انبار دریافت شد.', 'karasu-woo-pannel' ) );
	}

	/**
	 * Get customers aggregation report.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response REST API Response.
	 */
	public function get_customers_report( WP_REST_Request $request ): WP_REST_Response {
		$type         = sanitize_text_field( $request->get_param( 'type' ) ?: 'top' );
		$start_jalali = sanitize_text_field( $request->get_param( 'start_date' ) );
		$end_jalali   = sanitize_text_field( $request->get_param( 'end_date' ) );

		if ( empty( $start_jalali ) || empty( $end_jalali ) ) {
			$today_ts = time();
			$g_start  = date( 'Y-m-d', strtotime( '-30 days', $today_ts ) );
			$g_end    = date( 'Y-m-d', $today_ts );
		} else {
			$g_start = $this->convert_jalali_to_gregorian_str( $start_jalali );
			$g_end   = $this->convert_jalali_to_gregorian_str( $end_jalali );
		}

		$version = get_option( 'wsm_reports_cache_version', '1' );
		$cache_key = 'wsm_report_cust_' . $version . '_' . $type . '_' . md5( $g_start . '_' . $g_end );
		$data = get_transient( $cache_key );

		if ( false === $data ) {
			$data = $this->service->get_customers_report( $type, $g_start, $g_end );
			if ( 'new' === $type ) {
				foreach ( $data as &$item ) {
					$item['registered_jalali'] = WSM_Date_Helper::to_jalali_string( $item['registered'] );
				}
			}
			set_transient( $cache_key, $data, HOUR_IN_SECONDS );
		}

		return WSM_Response::success( $data, __( 'گزارش مشتریان دریافت شد.', 'karasu-woo-pannel' ) );
	}

	private function convert_jalali_to_gregorian_str( string $jalali_str ): string {
		$parts = explode( '/', str_replace( '-', '/', $jalali_str ) );
		if ( 3 !== count( $parts ) ) {
			return date( 'Y-m-d' );
		}

		list( $gy, $gm, $gd ) = WSM_Date_Helper::jalali_to_gregorian( (int) $parts[0], (int) $parts[1], (int) $parts[2] );
		return sprintf( '%04d-%02d-%02d', $gy, $gm, $gd );
	}
}

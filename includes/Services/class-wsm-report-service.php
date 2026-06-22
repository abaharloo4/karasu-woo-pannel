<?php
/**
 * Reports Service Layer
 *
 * @package KarasuWooPannel
 * @version 1.0.0
 * @date 2026-06-23
 */

namespace WooStoreManager\Services;

use WooStoreManager\Repositories\WSM_Report_Repository;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class WSM_Report_Service
 */
class WSM_Report_Service {

	/**
	 * Report repository.
	 *
	 * @var WSM_Report_Repository
	 */
	private WSM_Report_Repository $repository;

	/**
	 * WSM_Report_Service constructor.
	 */
	public function __construct() {
		$this->repository = new WSM_Report_Repository();
	}

	/**
	 * Get detailed sales report.
	 *
	 * @param string $start_date Start date (YYYY-MM-DD).
	 * @param string $end_date   End date (YYYY-MM-DD).
	 * @return array Detailed orders list.
	 */
	public function get_detailed_sales_report( string $start_date, string $end_date ): array {
		return $this->repository->sales_by_date_range( $start_date, $end_date );
	}

	/**
	 * Get top selling products in period.
	 *
	 * @param int    $limit      Max count.
	 * @param string $start_date Start date.
	 * @param string $end_date   End date.
	 * @return array Top products list.
	 */
	public function get_top_products_report( int $limit, string $start_date, string $end_date ): array {
		return $this->repository->top_products( $limit, $start_date, $end_date );
	}

	/**
	 * Get low stock inventory report.
	 *
	 * @return array Low stock products.
	 */
	public function get_low_stock_report(): array {
		// Use default threshold from options, fallback to 5.
		$threshold = (int) get_option( 'wsm_low_stock_threshold', 5 );
		return $this->repository->low_stock_products( $threshold );
	}

	/**
	 * Get customers report.
	 *
	 * @param string $type       'new' or 'top'.
	 * @param string $start_date Start date.
	 * @param string $end_date   End date.
	 * @return array Customers data.
	 */
	public function get_customers_report( string $type, string $start_date, string $end_date ): array {
		return $this->repository->customer_report( $type, $start_date, $end_date );
	}
}

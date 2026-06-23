<?php
/**
 * Business Service layer for WooCommerce Coupons
 *
 * @package KarasuWooPannel
 * @version 1.0.7
 * @date 2026-06-23
 */

namespace WooStoreManager\Services;

use WooStoreManager\Repositories\WSM_Coupon_Repository;
use WooStoreManager\Helpers\WSM_Date_Helper;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class WSM_Coupon_Service
 */
class WSM_Coupon_Service {

	/**
	 * Coupon repository.
	 *
	 * @var WSM_Coupon_Repository
	 */
	private WSM_Coupon_Repository $repository;

	/**
	 * WSM_Coupon_Service constructor.
	 *
	 * @param WSM_Coupon_Repository $repository Target repository.
	 */
	public function __construct( WSM_Coupon_Repository $repository ) {
		$this->repository = $repository;
	}

	/**
	 * Get list of coupons.
	 *
	 * @param array $args Query filters.
	 * @return array Formatted coupons payload.
	 */
	public function get_coupons( array $args = [] ): array {
		$results = $this->repository->find_all( $args );

		$formatted = [];
		foreach ( $results['coupons'] as $coupon ) {
			$formatted[] = $this->format_coupon_summary( $coupon );
		}

		return [
			'coupons' => $formatted,
			'total'   => $results['total'],
			'pages'   => $results['pages'],
		];
	}

	/**
	 * Get details of single coupon.
	 *
	 * @param int $id Coupon ID.
	 * @return array|WP_Error Formatted details or error.
	 */
	public function get_coupon_detail( int $id ): array|WP_Error {
		$coupon = $this->repository->find_by_id( $id );
		if ( ! $coupon ) {
			return new WP_Error( 'wsm_coupon_not_found', __( 'کوپن تخفیف یافت نشد.', 'karasu-woo-pannel' ) );
		}

		return $this->format_coupon_detail( $coupon );
	}

	/**
	 * Create a coupon.
	 *
	 * @param array $data Raw parameters.
	 * @return int|WP_Error Created ID or WP_Error.
	 */
	public function create_coupon( array $data ): int|WP_Error {
		$data = $this->sanitize_and_prepare( $data );
		if ( empty( $data['code'] ) ) {
			return new WP_Error( 'wsm_invalid_code', __( 'کد تخفیف الزامی است.', 'karasu-woo-pannel' ) );
		}
		return $this->repository->create( $data );
	}

	/**
	 * Update coupon fields.
	 *
	 * @param int   $id   Coupon ID.
	 * @param array $data Raw parameters.
	 * @return bool|WP_Error True if success, else WP_Error.
	 */
	public function update_coupon( int $id, array $data ): bool|WP_Error {
		$data = $this->sanitize_and_prepare( $data );
		return $this->repository->update( $id, $data );
	}

	/**
	 * Delete a coupon.
	 *
	 * @param int $id Coupon ID.
	 * @return bool|WP_Error True if success, else WP_Error.
	 */
	public function delete_coupon( int $id ): bool|WP_Error {
		$success = $this->repository->delete( $id );
		if ( ! $success ) {
			return new WP_Error( 'wsm_delete_failed', __( 'حذف کوپن ناموفق بود.', 'karasu-woo-pannel' ) );
		}
		return true;
	}

	/**
	 * Sanitize and convert Jalali dates.
	 *
	 * @param array $data Raw input.
	 * @return array Sanitized prepared output.
	 */
	private function sanitize_and_prepare( array $data ): array {
		if ( ! empty( $data['date_expires'] ) ) {
			$parts = explode( '/', sanitize_text_field( $data['date_expires'] ) );
			if ( 3 === count( $parts ) ) {
				list( $gy, $gm, $gd ) = WSM_Date_Helper::jalali_to_gregorian( (int) $parts[0], (int) $parts[1], (int) $parts[2] );
				$data['date_expires'] = sprintf( '%04d-%02d-%02d', $gy, $gm, $gd );
			}
		}

		return $data;
	}

	/**
	 * Format coupon summary properties.
	 *
	 * @param \WC_Coupon $coupon WooCommerce Coupon.
	 * @return array Formatted summary.
	 */
	private function format_coupon_summary( $coupon ): array {
		$types = [
			'percent'       => __( 'درصدی', 'karasu-woo-pannel' ),
			'fixed_cart'    => __( 'تخفیف ثابت سبد خرید', 'karasu-woo-pannel' ),
			'fixed_product' => __( 'تخفیف ثابت محصول', 'karasu-woo-pannel' ),
		];

		$date_expires = $coupon->get_date_expires();
		$expiry_jalali = '';
		if ( $date_expires ) {
			$g_date = $date_expires->date( 'Y-m-d H:i:s' );
			$expiry_jalali = WSM_Date_Helper::to_jalali_string( $g_date );
			$parts = explode( ' ', $expiry_jalali );
			$expiry_jalali = $parts[0] ?? '';
		}

		return [
			'id'                  => $coupon->get_id(),
			'code'                => $coupon->get_code(),
			'amount'              => (float) $coupon->get_amount(),
			'discount_type'       => $coupon->get_discount_type(),
			'discount_type_label' => $types[ $coupon->get_discount_type() ] ?? $coupon->get_discount_type(),
			'usage_count'         => $coupon->get_usage_count(),
			'usage_limit'         => $coupon->get_usage_limit(),
			'date_expires_jalali' => $expiry_jalali ? $expiry_jalali : __( 'بدون تاریخ', 'karasu-woo-pannel' ),
		];
	}

	/**
	 * Format complete coupon specifications detail.
	 *
	 * @param \WC_Coupon $coupon WooCommerce Coupon.
	 * @return array Formatted details.
	 */
	private function format_coupon_detail( $coupon ): array {
		$date_expires = $coupon->get_date_expires();
		$expiry_jalali = '';
		if ( $date_expires ) {
			$g_date = $date_expires->date( 'Y-m-d H:i:s' );
			$expiry_jalali = WSM_Date_Helper::to_jalali_string( $g_date );
			$parts = explode( ' ', $expiry_jalali );
			$expiry_jalali = $parts[0] ?? '';
		}

		return [
			'id'                   => $coupon->get_id(),
			'code'                 => $coupon->get_code(),
			'amount'               => (float) $coupon->get_amount(),
			'discount_type'        => $coupon->get_discount_type(),
			'description'          => $coupon->get_description(),
			'date_expires'         => $expiry_jalali, // Send Jalali to populate date picker input field
			'usage_limit'          => $coupon->get_usage_limit() ? $coupon->get_usage_limit() : '',
			'usage_limit_per_user' => $coupon->get_usage_limit_per_user() ? $coupon->get_usage_limit_per_user() : '',
			'free_shipping'        => $coupon->get_free_shipping(),
			'minimum_amount'       => $coupon->get_minimum_amount() ? (float) $coupon->get_minimum_amount() : '',
			'maximum_amount'       => $coupon->get_maximum_amount() ? (float) $coupon->get_maximum_amount() : '',
			'individual_use'       => $coupon->get_individual_use(),
			'exclude_sale_items'   => $coupon->get_exclude_sale_items(),
		];
	}
}

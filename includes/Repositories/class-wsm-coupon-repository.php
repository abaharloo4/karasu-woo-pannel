<?php
/**
 * WooCommerce Coupon CRUD Repository
 *
 * @package KarasuWooPannel
 * @version 1.0.10
 * @date 2026-06-23
 */

namespace WooStoreManager\Repositories;

use WC_Coupon;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class WSM_Coupon_Repository
 */
class WSM_Coupon_Repository {

	/**
	 * Find coupons matching arguments.
	 *
	 * @param array $args Query filters.
	 * @return array Array containing coupons list, total count, and pages.
	 */
	public function find_all( array $args ): array {
		$limit  = isset( $args['per_page'] ) ? absint( $args['per_page'] ) : 20;
		$page   = isset( $args['page'] ) ? absint( $args['page'] ) : 1;
		$offset = ( $page - 1 ) * $limit;

		$query_args = [
			'post_type'      => 'shop_coupon',
			'post_status'    => 'any',
			'posts_per_page' => $limit,
			'offset'         => $offset,
			'orderby'        => 'date',
			'order'          => 'DESC',
		];

		if ( ! empty( $args['search'] ) ) {
			$query_args['s'] = sanitize_text_field( $args['search'] );
		}

		$query = new \WP_Query( $query_args );

		$coupons = [];
		foreach ( $query->posts as $post ) {
			$coupons[] = new \WC_Coupon( $post->ID );
		}

		return [
			'coupons' => $coupons,
			'total'   => (int) $query->found_posts,
			'pages'   => (int) $query->max_num_pages,
		];
	}

	/**
	 * Find coupon by ID.
	 *
	 * @param int $id Coupon ID.
	 * @return WC_Coupon|null Coupon object if found, else null.
	 */
	public function find_by_id( int $id ): ?WC_Coupon {
		$coupon = new WC_Coupon( $id );
		return $coupon->get_id() > 0 ? $coupon : null;
	}

	/**
	 * Create a new coupon.
	 *
	 * @param array $data Coupon parameters.
	 * @return int|WP_Error Created coupon ID or WP_Error.
	 */
	public function create( array $data ): int|WP_Error {
		$coupon = new WC_Coupon();
		$coupon = $this->set_coupon_properties( $coupon, $data );
		$coupon_id = $coupon->save();

		return $coupon_id > 0 ? $coupon_id : new WP_Error( 'wsm_coupon_create_failed', __( 'خطا در ایجاد کوپن تخفیف.', 'karasu-woo-pannel' ) );
	}

	/**
	 * Update an existing coupon.
	 *
	 * @param int   $id   Coupon ID.
	 * @param array $data New parameters.
	 * @return bool|WP_Error True if success, else WP_Error.
	 */
	public function update( int $id, array $data ): bool|WP_Error {
		$coupon = $this->find_by_id( $id );
		if ( ! $coupon ) {
			return new WP_Error( 'wsm_coupon_not_found', __( 'کوپن تخفیف یافت نشد.', 'karasu-woo-pannel' ) );
		}

		$coupon = $this->set_coupon_properties( $coupon, $data );
		$coupon->save();

		return true;
	}

	/**
	 * Delete a coupon.
	 *
	 * @param int $id Coupon ID.
	 * @return bool True if deleted successfully.
	 */
	public function delete( int $id ): bool {
		$coupon = $this->find_by_id( $id );
		if ( ! $coupon ) {
			return false;
		}

		return (bool) $coupon->delete( true );
	}

	/**
	 * Map data properties to WC_Coupon object.
	 *
	 * @param WC_Coupon $coupon WC_Coupon instance.
	 * @param array     $data   Coupon data array.
	 * @return WC_Coupon Modified coupon instance.
	 */
	private function set_coupon_properties( WC_Coupon $coupon, array $data ): WC_Coupon {
		if ( isset( $data['code'] ) ) {
			$coupon->set_code( sanitize_text_field( $data['code'] ) );
		}
		if ( isset( $data['amount'] ) ) {
			$coupon->set_amount( wc_format_decimal( $data['amount'] ) );
		}
		if ( isset( $data['discount_type'] ) ) {
			$coupon->set_discount_type( sanitize_text_field( $data['discount_type'] ) );
		}
		if ( isset( $data['description'] ) ) {
			$coupon->set_description( sanitize_textarea_field( $data['description'] ) );
		}
		if ( isset( $data['date_expires'] ) ) {
			$coupon->set_date_expires( ! empty( $data['date_expires'] ) ? sanitize_text_field( $data['date_expires'] ) : '' );
		}
		if ( isset( $data['usage_limit'] ) ) {
			$coupon->set_usage_limit( '' !== $data['usage_limit'] ? absint( $data['usage_limit'] ) : '' );
		}
		if ( isset( $data['usage_limit_per_user'] ) ) {
			$coupon->set_usage_limit_per_user( '' !== $data['usage_limit_per_user'] ? absint( $data['usage_limit_per_user'] ) : '' );
		}
		if ( isset( $data['free_shipping'] ) ) {
			$coupon->set_free_shipping( (bool) $data['free_shipping'] );
		}
		if ( isset( $data['minimum_amount'] ) ) {
			$coupon->set_minimum_amount( '' !== $data['minimum_amount'] ? wc_format_decimal( $data['minimum_amount'] ) : '' );
		}
		if ( isset( $data['maximum_amount'] ) ) {
			$coupon->set_maximum_amount( '' !== $data['maximum_amount'] ? wc_format_decimal( $data['maximum_amount'] ) : '' );
		}
		if ( isset( $data['individual_use'] ) ) {
			$coupon->set_individual_use( (bool) $data['individual_use'] );
		}
		if ( isset( $data['exclude_sale_items'] ) ) {
			$coupon->set_exclude_sale_items( (bool) $data['exclude_sale_items'] );
		}

		return $coupon;
	}
}

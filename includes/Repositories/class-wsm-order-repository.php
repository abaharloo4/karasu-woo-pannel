<?php
/**
 * WooCommerce Order CRUD Repository
 *
 * @package KarasuWooPannel
 * @version 1.0.2
 * @date 2026-06-23
 */

namespace WooStoreManager\Repositories;

use WC_Order;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class WSM_Order_Repository
 */
class WSM_Order_Repository {

	/**
	 * Find orders matching filter parameters.
	 *
	 * @param array $args Filter arguments.
	 * @return array Array containing orders, total orders, and total pages count.
	 */
	public function find_all( array $args ): array {
		$limit = isset( $args['per_page'] ) ? absint( $args['per_page'] ) : 20;
		$page  = isset( $args['page'] ) ? absint( $args['page'] ) : 1;

		$query_args = [
			'limit'    => $limit,
			'page'     => $page,
			'paginate' => true,
			'orderby'  => 'date',
			'order'    => 'DESC',
		];

		if ( ! empty( $args['status'] ) ) {
			$query_args['status'] = $args['status'];
		}

		if ( ! empty( $args['search'] ) ) {
			$query_args['search'] = sanitize_text_field( $args['search'] );
		}

		// Handle Date Created queries (must be in Gregorian format)
		if ( ! empty( $args['date_from'] ) || ! empty( $args['date_to'] ) ) {
			$date_query = [];
			if ( ! empty( $args['date_from'] ) ) {
				$date_query['after'] = sanitize_text_field( $args['date_from'] ) . ' 00:00:00';
			}
			if ( ! empty( $args['date_to'] ) ) {
				$date_query['before'] = sanitize_text_field( $args['date_to'] ) . ' 23:59:59';
			}
			$query_args['date_created'] = $date_query;
		}

		$results = wc_get_orders( $query_args );

		return [
			'orders' => $results->orders,
			'total'  => $results->total,
			'pages'  => $results->max_num_pages,
		];
	}

	/**
	 * Retrieve a detailed WooCommerce order object.
	 *
	 * @param int $id Order ID.
	 * @return WC_Order|null Order object if found, else null.
	 */
	public function find_by_id( int $id ): ?WC_Order {
		$order = wc_get_order( $id );
		return $order ? $order : null;
	}

	/**
	 * Update standard order status.
	 *
	 * @param int    $id     Order ID.
	 * @param string $status New status code.
	 * @return bool True if updated successfully.
	 */
	public function update_status( int $id, string $status ): bool {
		$order = wc_get_order( $id );
		if ( ! $order ) {
			return false;
		}

		$order->update_status( $status, __( 'تغییر وضعیت از پنل مدیریت اختصاصی KarasuWooPannel.', 'karasu-woo-pannel' ) );
		return true;
	}

	/**
	 * Add custom internal or customer note comment to an order.
	 *
	 * @param int    $id            Order ID.
	 * @param string $note          Note text content.
	 * @param bool   $customer_note True if note should be visible to customer.
	 * @return int Created note ID, or 0 on failure.
	 */
	public function add_note( int $id, string $note, bool $customer_note ): int {
		$order = wc_get_order( $id );
		if ( ! $order ) {
			return 0;
		}

		return (int) $order->add_order_note( $note, $customer_note ? 1 : 0 );
	}
}

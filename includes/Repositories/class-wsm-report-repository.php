<?php
/**
 * WooCommerce Reports Aggregation Repository
 *
 * @package KarasuWooPannel
 * @version 1.0.7
 * @date 2026-06-23
 */

namespace WooStoreManager\Repositories;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class WSM_Report_Repository
 */
class WSM_Report_Repository {

	/**
	 * Aggregate sales statistics for a specific Gregorian date range.
	 *
	 * @param string $start_date Start date (YYYY-MM-DD).
	 * @param string $end_date   End date (YYYY-MM-DD).
	 * @return array Sales aggregation totals and daily breakdown.
	 */
	public function get_sales_stats( string $start_date, string $end_date ): array {
		$orders = wc_get_orders( [
			'limit'        => -1,
			'status'       => [ 'processing', 'completed' ],
			'date_created' => $start_date . '...' . $end_date,
		] );

		$total_sales    = 0;
		$total_orders   = count( $orders );
		$total_items    = 0;
		$total_shipping = 0;
		$daily_data     = [];

		foreach ( $orders as $order ) {
			$date_str = $order->get_date_created()->date( 'Y-m-d' );
			$total    = (float) $order->get_total();
			$shipping = (float) $order->get_shipping_total();
			$items    = (int) $order->get_item_count();

			$total_sales    += $total;
			$total_shipping += $shipping;
			$total_items    += $items;

			if ( ! isset( $daily_data[ $date_str ] ) ) {
				$daily_data[ $date_str ] = [
					'date'     => $date_str,
					'sales'    => 0,
					'orders'   => 0,
					'items'    => 0,
					'shipping' => 0,
				];
			}

			$daily_data[ $date_str ]['sales']    += $total;
			$daily_data[ $date_str ]['orders']   += 1;
			$daily_data[ $date_str ]['items']    += $items;
			$daily_data[ $date_str ]['shipping'] += $shipping;
		}

		ksort( $daily_data );

		return [
			'total_sales'    => $total_sales,
			'total_orders'   => $total_orders,
			'total_items'    => $total_items,
			'total_shipping' => $total_shipping,
			'daily'          => array_values( $daily_data ),
		];
	}

	/**
	 * Query best selling products based on total_sales metadata.
	 *
	 * @param int $limit Max items to return.
	 * @return array Top products list.
	 */
	public function get_top_selling_products( int $limit = 5 ): array {
		$products = wc_get_products( [
			'limit'    => $limit,
			'orderby'  => 'meta_value_num',
			'meta_key' => 'total_sales',
			'order'    => 'DESC',
		] );

		$formatted = [];
		foreach ( $products as $product ) {
			$formatted[] = [
				'id'          => $product->get_id(),
				'name'        => $product->get_name(),
				'total_sales' => (int) $product->get_meta( 'total_sales' ),
				'price'       => (float) $product->get_price(),
				'stock'       => $product->get_stock_quantity(),
			];
		}
		return $formatted;
	}

	/**
	 * Get detailed list of orders in a date range.
	 *
	 * @param string $start_date Start date (YYYY-MM-DD).
	 * @param string $end_date   End date (YYYY-MM-DD).
	 * @return array Detailed orders list.
	 */
	public function sales_by_date_range( string $start_date, string $end_date ): array {
		$orders = wc_get_orders( [
			'limit'        => -1,
			'status'       => [ 'processing', 'completed' ],
			'date_created' => $start_date . '...' . $end_date,
		] );

		$formatted = [];
		foreach ( $orders as $order ) {
			$items_desc = [];
			foreach ( $order->get_items() as $item ) {
				$items_desc[] = $item->get_name() . ' (x' . $item->get_quantity() . ')';
			}

			$formatted[] = [
				'id'         => $order->get_id(),
				'date'       => $order->get_date_created()->date( 'Y-m-d H:i:s' ),
				'customer'   => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
				'items_desc' => implode( ', ', $items_desc ),
				'tax'        => (float) $order->get_cart_tax() + (float) $order->get_shipping_tax(),
				'shipping'   => (float) $order->get_shipping_total(),
				'total'      => (float) $order->get_total(),
				'status'     => $order->get_status(),
			];
		}
		return $formatted;
	}

	/**
	 * Get top selling products in a specific date range.
	 *
	 * @param int    $limit      Limit.
	 * @param string $start_date Start date.
	 * @param string $end_date   End date.
	 * @return array Products list.
	 */
	public function top_products( int $limit, string $start_date, string $end_date ): array {
		$orders = wc_get_orders( [
			'limit'        => -1,
			'status'       => [ 'processing', 'completed' ],
			'date_created' => $start_date . '...' . $end_date,
		] );

		$product_counts = [];
		foreach ( $orders as $order ) {
			foreach ( $order->get_items() as $item ) {
				$product_id = $item->get_product_id();
				if ( ! $product_id ) {
					continue;
				}
				$qty = $item->get_quantity();
				if ( ! isset( $product_counts[ $product_id ] ) ) {
					$product_counts[ $product_id ] = [
						'id'    => $product_id,
						'name'  => $item->get_name(),
						'qty'   => 0,
						'sales' => 0,
					];
				}
				$product_counts[ $product_id ]['qty']   += $qty;
				$product_counts[ $product_id ]['sales'] += (float) $item->get_total();
			}
		}

		usort(
			$product_counts,
			function ( $a, $b ) {
				return $b['qty'] <=> $a['qty'];
			}
		);

		return array_slice( $product_counts, 0, $limit );
	}

	/**
	 * Get low stock products.
	 *
	 * @param int $threshold Stock threshold.
	 * @return array Low stock products.
	 */
	public function low_stock_products( int $threshold ): array {
		$products = wc_get_products( [
			'limit'  => -1,
			'status' => 'publish',
		] );

		$low_stock = [];
		foreach ( $products as $product ) {
			if ( $product->managing_stock() ) {
				$qty = $product->get_stock_quantity();
				if ( null !== $qty && $qty <= $threshold ) {
					$low_stock[] = [
						'id'        => $product->get_id(),
						'name'      => $product->get_name(),
						'sku'       => $product->get_sku() ?: '—',
						'stock'     => $qty,
						'threshold' => $threshold,
					];
				}
			}
		}
		return $low_stock;
	}

	/**
	 * Get customer report.
	 *
	 * @param string $type       'new' or 'top'.
	 * @param string $start_date Start date.
	 * @param string $end_date   End date.
	 * @return array Customers data.
	 */
	public function customer_report( string $type, string $start_date, string $end_date ): array {
		if ( 'new' === $type ) {
			$users = get_users( [
				'role'       => 'customer',
				'date_query' => [
					[
						'after'     => $start_date . ' 00:00:00',
						'before'    => $end_date . ' 23:59:59',
						'inclusive' => true,
					],
				],
			] );

			$formatted = [];
			foreach ( $users as $user ) {
				$formatted[] = [
					'id'         => $user->ID,
					'name'       => $user->display_name,
					'email'      => $user->user_email,
					'registered' => $user->user_registered,
				];
			}
			return $formatted;
		}

		// Top buying customers
		$orders = wc_get_orders( [
			'limit'        => -1,
			'status'       => [ 'processing', 'completed' ],
			'date_created' => $start_date . '...' . $end_date,
		] );

		$customers = [];
		foreach ( $orders as $order ) {
			$customer_id = $order->get_customer_id();
			$email       = $order->get_billing_email();
			$name        = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
			if ( ! $customer_id && ! $email ) {
				continue;
			}
			$key = $customer_id ? 'id_' . $customer_id : 'email_' . md5( $email );
			if ( ! isset( $customers[ $key ] ) ) {
				$customers[ $key ] = [
					'id'           => $customer_id,
					'name'         => trim( $name ) ?: ( $customer_id ? get_userdata( $customer_id )->display_name : $email ),
					'email'        => $email,
					'orders_count' => 0,
					'total_spent'  => 0,
				];
			}
			$customers[ $key ]['orders_count'] += 1;
			$customers[ $key ]['total_spent']  += (float) $order->get_total();
		}

		usort(
			$customers,
			function ( $a, $b ) {
				return $b['total_spent'] <=> $a['total_spent'];
			}
		);

		return array_slice( $customers, 0, 10 );
	}
}

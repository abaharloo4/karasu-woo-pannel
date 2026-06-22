<?php
/**
 * Business Service layer for WooCommerce Orders
 *
 * @package KarasuWooPannel
 * @version 1.0.1
 * @date 2026-06-23
 */

namespace WooStoreManager\Services;

use WooStoreManager\Repositories\WSM_Order_Repository;
use WooStoreManager\Helpers\WSM_Date_Helper;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class WSM_Order_Service
 */
class WSM_Order_Service {

	/**
	 * Order repository.
	 *
	 * @var WSM_Order_Repository
	 */
	private WSM_Order_Repository $repository;

	/**
	 * WSM_Order_Service constructor.
	 *
	 * @param WSM_Order_Repository $repository Target repository.
	 */
	public function __construct( WSM_Order_Repository $repository ) {
		$this->repository = $repository;
	}

	/**
	 * Get list of filtered orders.
	 *
	 * @param array $args Filter arguments.
	 * @return array Formatted order summaries list, count, pages.
	 */
	public function get_orders( array $args = [] ): array {
		// Convert Jalali date boundaries into Gregorian strings if set.
		if ( ! empty( $args['date_from'] ) ) {
			$args['date_from'] = $this->convert_shamsi_to_gregorian( $args['date_from'] );
		}
		if ( ! empty( $args['date_to'] ) ) {
			$args['date_to'] = $this->convert_shamsi_to_gregorian( $args['date_to'] );
		}

		$results = $this->repository->find_all( $args );

		$formatted_orders = [];
		foreach ( $results['orders'] as $order ) {
			$formatted_orders[] = $this->format_order_summary( $order );
		}

		return [
			'orders' => $formatted_orders,
			'total'  => $results['total'],
			'pages'  => $results['pages'],
		];
	}

	/**
	 * Retrieve and shape order details.
	 *
	 * @param int $id Order ID.
	 * @return array|WP_Error Formatted array or error object.
	 */
	public function get_order_detail( int $id ): array|WP_Error {
		$order = $this->repository->find_by_id( $id );
		if ( ! $order ) {
			return new WP_Error( 'wsm_order_not_found', __( 'سفارش یافت نشد.', 'karasu-woo-pannel' ) );
		}

		return $this->format_order_detail( $order );
	}

	/**
	 * Update an order's status.
	 *
	 * @param int    $id     Order ID.
	 * @param string $status New status code.
	 * @return bool|WP_Error True if success, else error object.
	 */
	public function update_status( int $id, string $status ): bool|WP_Error {
		$allowed = [ 'pending', 'processing', 'on-hold', 'completed', 'cancelled', 'refunded', 'failed' ];
		if ( ! in_array( $status, $allowed, true ) ) {
			return new WP_Error( 'wsm_invalid_status', __( 'وضعیت سفارش نامعتبر است.', 'karasu-woo-pannel' ) );
		}

		$updated = $this->repository->update_status( $id, $status );
		if ( ! $updated ) {
			return new WP_Error( 'wsm_update_failed', __( 'بروزرسانی وضعیت سفارش ناموفق بود.', 'karasu-woo-pannel' ) );
		}

		return true;
	}

	/**
	 * Add order note.
	 *
	 * @param int    $id            Order ID.
	 * @param string $note          Note content.
	 * @param bool   $customer_note True if visible to customer.
	 * @return int|WP_Error Created note ID or error object.
	 */
	public function add_note( int $id, string $note, bool $customer_note ): int|WP_Error {
		if ( empty( trim( $note ) ) ) {
			return new WP_Error( 'wsm_empty_note', __( 'متن یادداشت نمی‌تواند خالی باشد.', 'karasu-woo-pannel' ) );
		}

		$note_id = $this->repository->add_note( $id, $note, $customer_note );
		if ( 0 === $note_id ) {
			return new WP_Error( 'wsm_note_failed', __( 'ثبت یادداشت ناموفق بود.', 'karasu-woo-pannel' ) );
		}

		return $note_id;
	}

	/**
	 * Convert Shamsi date string to YYYY-MM-DD Gregorian.
	 *
	 * @param string $shamsi_date Format: YYYY/MM/DD
	 * @return string Gregorian date.
	 */
	private function convert_shamsi_to_gregorian( string $shamsi_date ): string {
		$parts = explode( '/', $shamsi_date );
		if ( 3 !== count( $parts ) ) {
			return $shamsi_date;
		}

		$jy = (int) $parts[0];
		$jm = (int) $parts[1];
		$jd = (int) $parts[2];

		$g_date = WSM_Date_Helper::jalali_to_gregorian( $jy, $jm, $jd );
		return sprintf( '%04d-%02d-%02d', $g_date[0], $g_date[1], $g_date[2] );
	}

	/**
	 * Format order summary records.
	 *
	 * @param \WC_Order $order Order instance.
	 * @return array Shaped fields.
	 */
	private function format_order_summary( $order ): array {
		return [
			'id'             => $order->get_id(),
			'customer_name'  => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
			'date'           => WSM_Date_Helper::to_jalali_string( $order->get_date_created()->date( 'Y-m-d H:i:s' ) ),
			'total'          => (float) $order->get_total(),
			'payment_method' => $order->get_payment_method_title(),
			'status'         => $order->get_status(),
			'status_label'   => wc_get_order_status_name( $order->get_status() ),
		];
	}

	/**
	 * Shape full order details for view render.
	 *
	 * @param \WC_Order $order Order instance.
	 * @return array Shaped detailed properties.
	 */
	private function format_order_detail( $order ): array {
		$items = [];
		foreach ( $order->get_items() as $item_id => $item ) {
			$product = $item->get_product();
			$items[] = [
				'id'         => $item_id,
				'product_id' => $item->get_product_id(),
				'name'       => $item->get_name(),
				'quantity'   => $item->get_quantity(),
				'subtotal'   => (float) $item->get_subtotal(),
				'total'      => (float) $item->get_total(),
				'sku'        => $product ? $product->get_sku() : '',
				'image'      => $product ? wp_get_attachment_image_url( $product->get_image_id(), 'thumbnail' ) : '',
			];
		}

		$notes      = [];
		$notes_data = wc_get_order_notes( [ 'order_id' => $order->get_id() ] );
		foreach ( $notes_data as $note ) {
			$notes[] = [
				'id'            => $note->id,
				'content'       => $note->content,
				'date'          => WSM_Date_Helper::to_jalali_string( $note->date_created->date( 'Y-m-d H:i:s' ) ),
				'added_by'      => $note->added_by,
				'customer_note' => (bool) $note->customer_note,
			];
		}

		return [
			'id'             => $order->get_id(),
			'status'         => $order->get_status(),
			'status_label'   => wc_get_order_status_name( $order->get_status() ),
			'date'           => WSM_Date_Helper::to_jalali_string( $order->get_date_created()->date( 'Y-m-d H:i:s' ) ),
			'total'          => (float) $order->get_total(),
			'subtotal'       => (float) $order->get_subtotal(),
			'discount'       => (float) $order->get_discount_total(),
			'shipping'       => (float) $order->get_shipping_total(),
			'payment_method' => $order->get_payment_method_title(),
			'billing'        => [
				'name'    => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
				'email'   => $order->get_billing_email(),
				'phone'   => $order->get_billing_phone(),
				'address' => $order->get_billing_address_1() . ' ' . $order->get_billing_address_2(),
				'city'    => $order->get_billing_city(),
				'state'   => $order->get_billing_state(),
			],
			'shipping_info'  => [
				'name'    => $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name(),
				'address' => $order->get_shipping_address_1() . ' ' . $order->get_shipping_address_2(),
				'city'    => $order->get_shipping_city(),
				'state'   => $order->get_shipping_state(),
			],
			'items'          => $items,
			'notes'          => $notes,
		];
	}
}

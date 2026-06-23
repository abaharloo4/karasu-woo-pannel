<?php
/**
 * Custom Input Sanitizer helpers
 *
 * @package KarasuWooPannel
 * @version 1.0.2
 * @date 2026-06-23
 */

namespace WooStoreManager\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class WSM_Sanitizer
 */
class WSM_Sanitizer {

	/**
	 * Sanitize product data arrays.
	 *
	 * @param array $data Raw input array.
	 * @return array Sanitized array.
	 */
	public static function product_data( array $data ): array {
		return [
			'name'              => sanitize_text_field( $data['name'] ?? '' ),
			'status'            => self::post_status( $data['status'] ?? 'publish' ),
			'regular_price'     => wc_format_decimal( $data['regular_price'] ?? 0 ),
			'sale_price'        => '' !== ( $data['sale_price'] ?? '' ) ? wc_format_decimal( $data['sale_price'] ) : '',
			'description'       => wp_kses_post( $data['description'] ?? '' ),
			'short_description' => wp_kses_post( $data['short_description'] ?? '' ),
			'sku'               => sanitize_text_field( $data['sku'] ?? '' ),
			'manage_stock'      => (bool) ( $data['manage_stock'] ?? false ),
			'stock_quantity'    => '' !== ( $data['stock_quantity'] ?? '' ) ? absint( $data['stock_quantity'] ) : null,
			'stock_status'      => self::stock_status( $data['stock_status'] ?? 'instock' ),
			'category_ids'      => array_map( 'absint', (array) ( $data['category_ids'] ?? [] ) ),
			'slug'              => sanitize_title( $data['slug'] ?? '' ),
			'image_id'          => isset( $data['image_id'] ) ? absint( $data['image_id'] ) : '',
			'gallery_image_ids' => isset( $data['gallery_image_ids'] ) ? array_map( 'absint', (array) $data['gallery_image_ids'] ) : [],
			'weight'            => sanitize_text_field( $data['weight'] ?? '' ),
			'length'            => sanitize_text_field( $data['length'] ?? '' ),
			'width'             => sanitize_text_field( $data['width'] ?? '' ),
			'height'            => sanitize_text_field( $data['height'] ?? '' ),
			'attributes'        => isset( $data['attributes'] ) ? (array) $data['attributes'] : [],
			'variations'        => isset( $data['variations'] ) ? (array) $data['variations'] : [],
		];
	}

	/**
	 * Sanitize and whitelist WooCommerce order status.
	 *
	 * @param string $status Target status.
	 * @return string Whitelisted status.
	 */
	public static function order_status( string $status ): string {
		$allowed = [ 'pending', 'processing', 'on-hold', 'completed', 'cancelled', 'refunded', 'failed' ];
		return in_array( $status, $allowed, true ) ? $status : 'pending';
	}

	/**
	 * Sanitize and whitelist WordPress post status.
	 *
	 * @param string $status Target status.
	 * @return string Whitelisted status.
	 */
	public static function post_status( string $status ): string {
		$allowed = [ 'publish', 'draft', 'pending', 'private' ];
		return in_array( $status, $allowed, true ) ? $status : 'draft';
	}

	/**
	 * Sanitize and whitelist WooCommerce stock status.
	 *
	 * @param string $status Target status.
	 * @return string Whitelisted status.
	 */
	public static function stock_status( string $status ): string {
		$allowed = [ 'instock', 'outofstock', 'onbackorder' ];
		return in_array( $status, $allowed, true ) ? $status : 'instock';
	}

	/**
	 * Sanitize Iranian mobile phone numbers.
	 *
	 * @param string $phone Raw phone string.
	 * @return string Formatted mobile number, or empty string if invalid.
	 */
	public static function phone_number( string $phone ): string {
		$phone = preg_replace( '/[^0-9+]/', '', $phone );
		if ( str_starts_with( $phone, '+98' ) ) {
			$phone = '0' . substr( $phone, 3 );
		} elseif ( str_starts_with( $phone, '98' ) && 12 === strlen( $phone ) ) {
			$phone = '0' . substr( $phone, 2 );
		}
		return preg_match( '/^09[0-9]{9}$/', $phone ) ? $phone : '';
	}
}

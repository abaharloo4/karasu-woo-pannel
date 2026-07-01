<?php
/**
 * Custom Input Sanitizer helpers
 *
 * @package KarasuWooPannel
 * @version 1.0.10
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
	 * @param array $data      Raw input array.
	 * @param bool  $is_update Optional. True if this is an update action (only present fields will be returned).
	 * @return array Sanitized array.
	 */
	public static function product_data( array $data, bool $is_update = false ): array {
		if ( ! $is_update ) {
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
				'brand_ids'         => isset( $data['brand_ids'] ) ? array_map( 'absint', (array) $data['brand_ids'] ) : [],
				'virtual'           => (bool) ( $data['virtual'] ?? false ),
				'downloadable'      => (bool) ( $data['downloadable'] ?? false ),
				'downloads'         => isset( $data['downloads'] ) ? (array) $data['downloads'] : [],
				'download_limit'    => isset( $data['download_limit'] ) ? intval( $data['download_limit'] ) : -1,
				'download_expiry'   => isset( $data['download_expiry'] ) ? intval( $data['download_expiry'] ) : -1,
				'children'          => isset( $data['children'] ) ? array_map( 'absint', (array) $data['children'] ) : [],
				'product_url'       => isset( $data['product_url'] ) ? esc_url_raw( $data['product_url'] ) : '',
				'button_text'       => isset( $data['button_text'] ) ? sanitize_text_field( $data['button_text'] ) : '',
			];
		}

		$sanitized = [];

		if ( array_key_exists( 'name', $data ) ) {
			$sanitized['name'] = sanitize_text_field( $data['name'] );
		}
		if ( array_key_exists( 'status', $data ) ) {
			$sanitized['status'] = self::post_status( $data['status'] );
		}
		if ( array_key_exists( 'regular_price', $data ) ) {
			$sanitized['regular_price'] = wc_format_decimal( $data['regular_price'] );
		}
		if ( array_key_exists( 'sale_price', $data ) ) {
			$sanitized['sale_price'] = '' !== $data['sale_price'] ? wc_format_decimal( $data['sale_price'] ) : '';
		}
		if ( array_key_exists( 'description', $data ) ) {
			$sanitized['description'] = wp_kses_post( $data['description'] );
		}
		if ( array_key_exists( 'short_description', $data ) ) {
			$sanitized['short_description'] = wp_kses_post( $data['short_description'] );
		}
		if ( array_key_exists( 'sku', $data ) ) {
			$sanitized['sku'] = sanitize_text_field( $data['sku'] );
		}
		if ( array_key_exists( 'manage_stock', $data ) ) {
			$sanitized['manage_stock'] = (bool) $data['manage_stock'];
		}
		if ( array_key_exists( 'stock_quantity', $data ) ) {
			$sanitized['stock_quantity'] = '' !== $data['stock_quantity'] && null !== $data['stock_quantity'] ? absint( $data['stock_quantity'] ) : null;
		}
		if ( array_key_exists( 'stock_status', $data ) ) {
			$sanitized['stock_status'] = self::stock_status( $data['stock_status'] );
		}
		if ( array_key_exists( 'category_ids', $data ) ) {
			$sanitized['category_ids'] = array_map( 'absint', (array) $data['category_ids'] );
		}
		if ( array_key_exists( 'slug', $data ) ) {
			$sanitized['slug'] = sanitize_title( $data['slug'] );
		}
		if ( array_key_exists( 'image_id', $data ) ) {
			$sanitized['image_id'] = isset( $data['image_id'] ) ? absint( $data['image_id'] ) : '';
		}
		if ( array_key_exists( 'gallery_image_ids', $data ) ) {
			$sanitized['gallery_image_ids'] = isset( $data['gallery_image_ids'] ) ? array_map( 'absint', (array) $data['gallery_image_ids'] ) : [];
		}
		if ( array_key_exists( 'weight', $data ) ) {
			$sanitized['weight'] = sanitize_text_field( $data['weight'] );
		}
		if ( array_key_exists( 'length', $data ) ) {
			$sanitized['length'] = sanitize_text_field( $data['length'] );
		}
		if ( array_key_exists( 'width', $data ) ) {
			$sanitized['width'] = sanitize_text_field( $data['width'] );
		}
		if ( array_key_exists( 'height', $data ) ) {
			$sanitized['height'] = sanitize_text_field( $data['height'] );
		}
		if ( array_key_exists( 'attributes', $data ) ) {
			$sanitized['attributes'] = (array) $data['attributes'];
		}
		if ( array_key_exists( 'variations', $data ) ) {
			$sanitized['variations'] = (array) $data['variations'];
		}
		if ( array_key_exists( 'brand_ids', $data ) ) {
			$sanitized['brand_ids'] = array_map( 'absint', (array) $data['brand_ids'] );
		}
		if ( array_key_exists( 'virtual', $data ) ) {
			$sanitized['virtual'] = (bool) $data['virtual'];
		}
		if ( array_key_exists( 'downloadable', $data ) ) {
			$sanitized['downloadable'] = (bool) $data['downloadable'];
		}
		if ( array_key_exists( 'downloads', $data ) ) {
			$sanitized['downloads'] = (array) $data['downloads'];
		}
		if ( array_key_exists( 'download_limit', $data ) ) {
			$sanitized['download_limit'] = intval( $data['download_limit'] );
		}
		if ( array_key_exists( 'download_expiry', $data ) ) {
			$sanitized['download_expiry'] = intval( $data['download_expiry'] );
		}
		if ( array_key_exists( 'children', $data ) ) {
			$sanitized['children'] = array_map( 'absint', (array) $data['children'] );
		}
		if ( array_key_exists( 'product_url', $data ) ) {
			$sanitized['product_url'] = esc_url_raw( $data['product_url'] );
		}
		if ( array_key_exists( 'button_text', $data ) ) {
			$sanitized['button_text'] = sanitize_text_field( $data['button_text'] );
		}

		return $sanitized;
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

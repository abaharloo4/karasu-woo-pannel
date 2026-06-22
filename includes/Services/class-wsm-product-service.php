<?php
/**
 * Business Service layer for WooCommerce Products
 *
 * @package KarasuWooPannel
 * @version 1.0.0
 * @date 2026-06-23
 */

namespace WooStoreManager\Services;

use WooStoreManager\Repositories\WSM_Product_Repository;
use WooStoreManager\Helpers\WSM_Sanitizer;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class WSM_Product_Service
 */
class WSM_Product_Service {

	/**
	 * Product repository.
	 *
	 * @var WSM_Product_Repository
	 */
	private WSM_Product_Repository $repository;

	/**
	 * WSM_Product_Service constructor.
	 *
	 * @param WSM_Product_Repository $repository Target repository.
	 */
	public function __construct( WSM_Product_Repository $repository ) {
		$this->repository = $repository;
	}

	/**
	 * Retrieve a list of products.
	 *
	 * @param array $args Query filters.
	 * @return array Formatted products, total, total pages.
	 */
	public function get_products( array $args = [] ): array {
		$results = $this->repository->find_all( $args );

		$formatted = [];
		foreach ( $results['products'] as $product ) {
			$formatted[] = $this->format_product_summary( $product );
		}

		return [
			'products' => $formatted,
			'total'    => $results['total'],
			'pages'    => $results['pages'],
		];
	}

	/**
	 * Load product detailed specifications.
	 *
	 * @param int $id Product ID.
	 * @return array|WP_Error Details array or WP_Error.
	 */
	public function get_product_detail( int $id ): array|WP_Error {
		$product = $this->repository->find_by_id( $id );
		if ( ! $product ) {
			return new WP_Error( 'wsm_product_not_found', __( 'محصول یافت نشد.', 'karasu-woo-pannel' ) );
		}

		return $this->format_product_detail( $product );
	}

	/**
	 * Create a product.
	 *
	 * @param array $data Raw values.
	 * @return int|WP_Error Created product ID or WP_Error.
	 */
	public function create_product( array $data ): int|WP_Error {
		$sanitized         = WSM_Sanitizer::product_data( $data );
		$sanitized['type'] = isset( $data['type'] ) ? sanitize_text_field( $data['type'] ) : 'simple';

		return $this->repository->create( $sanitized );
	}

	/**
	 * Update product properties.
	 *
	 * @param int   $id   Product ID.
	 * @param array $data New values.
	 * @return bool|WP_Error True if success, else WP_Error.
	 */
	public function update_product( int $id, array $data ): bool|WP_Error {
		$sanitized = WSM_Sanitizer::product_data( $data );
		return $this->repository->update( $id, $sanitized );
	}

	/**
	 * Delete product.
	 *
	 * @param int $id Product ID.
	 * @return bool|WP_Error True if deleted, else WP_Error.
	 */
	public function delete_product( int $id ): bool|WP_Error {
		$deleted = $this->repository->delete( $id );
		if ( ! $deleted ) {
			return new WP_Error( 'wsm_delete_failed', __( 'حذف محصول ناموفق بود.', 'karasu-woo-pannel' ) );
		}
		return true;
	}

	/**
	 * Format simple summary properties.
	 *
	 * @param \WC_Product $product WooCommerce product.
	 * @return array Formatted summary.
	 */
	private function format_product_summary( $product ): array {
		$categories = [];
		foreach ( $product->get_category_ids() as $cat_id ) {
			$term = get_term( $cat_id, 'product_cat' );
			if ( $term && ! is_wp_error( $term ) ) {
				$categories[] = $term->name;
			}
		}

		return [
			'id'            => $product->get_id(),
			'name'          => $product->get_name(),
			'sku'           => $product->get_sku(),
			'price'         => (float) $product->get_price(),
			'regular_price' => (float) $product->get_regular_price(),
			'sale_price'    => (float) $product->get_sale_price(),
			'stock'         => $product->get_stock_quantity(),
			'stock_status'  => $product->get_stock_status(),
			'stock_label'   => $product->is_in_stock() ? __( 'موجود', 'karasu-woo-pannel' ) : __( 'ناموجود', 'karasu-woo-pannel' ),
			'image'         => wp_get_attachment_image_url( $product->get_image_id(), 'thumbnail' ),
			'status'        => $product->get_status(),
			'status_label'  => 'publish' === $product->get_status() ? __( 'منتشر شده', 'karasu-woo-pannel' ) : __( 'پیش‌نویس', 'karasu-woo-pannel' ),
			'categories'    => implode( '، ', $categories ),
		];
	}

	/**
	 * Format complete specifications detail.
	 *
	 * @param \WC_Product $product WooCommerce product.
	 * @return array Formatted details.
	 */
	private function format_product_detail( $product ): array {
		$gallery = [];
		foreach ( $product->get_gallery_image_ids() as $img_id ) {
			$gallery[] = [
				'id'  => $img_id,
				'url' => wp_get_attachment_image_url( $img_id, 'large' ),
			];
		}

		$type = $product->get_type();
		$attributes = [];
		$variations = [];

		if ( 'variable' === $type ) {
			$product_attributes = $product->get_attributes();
			foreach ( $product_attributes as $attr ) {
				$attributes[] = [
					'name'         => $attr->get_name(),
					'options'      => is_array( $attr->get_options() ) ? implode( ' | ', $attr->get_options() ) : $attr->get_options(),
					'position'     => $attr->get_position(),
					'is_visible'   => $attr->get_visible(),
					'is_variation' => $attr->get_variation(),
				];
			}

			$variation_ids = $product->get_children();
			foreach ( $variation_ids as $var_id ) {
				$variation = wc_get_product( $var_id );
				if ( $variation ) {
					$variations[] = [
						'id'             => $variation->get_id(),
						'sku'            => $variation->get_sku(),
						'regular_price'  => $variation->get_regular_price(),
						'sale_price'     => $variation->get_sale_price(),
						'manage_stock'   => $variation->get_manage_stock(),
						'stock_quantity' => $variation->get_stock_quantity(),
						'stock_status'   => $variation->get_stock_status(),
						'image_id'       => $variation->get_image_id(),
						'image_url'      => wp_get_attachment_image_url( $variation->get_image_id(), 'thumbnail' ),
						'attributes'     => $variation->get_attributes(),
					];
				}
			}
		}

		return [
			'id'                => $product->get_id(),
			'name'              => $product->get_name(),
			'type'              => $type,
			'sku'               => $product->get_sku(),
			'status'            => $product->get_status(),
			'price'             => (float) $product->get_price(),
			'regular_price'     => (float) $product->get_regular_price(),
			'sale_price'        => (float) $product->get_sale_price(),
			'description'       => $product->get_description(),
			'short_description' => $product->get_short_description(),
			'manage_stock'      => $product->get_manage_stock(),
			'stock_quantity'    => $product->get_stock_quantity(),
			'stock_status'      => $product->get_stock_status(),
			'category_ids'      => $product->get_category_ids(),
			'image'             => [
				'id'  => $product->get_image_id(),
				'url' => wp_get_attachment_image_url( $product->get_image_id(), 'large' ),
			],
			'gallery'           => $gallery,
			'weight'            => $product->get_weight(),
			'length'            => $product->get_length(),
			'width'             => $product->get_width(),
			'height'            => $product->get_height(),
			'attributes'        => $attributes,
			'variations'        => $variations,
		];
	}
}

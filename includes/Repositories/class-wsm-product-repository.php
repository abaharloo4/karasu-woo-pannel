<?php
/**
 * WooCommerce Product CRUD Repository
 *
 * @package KarasuWooPannel
 * @version 1.0.10
 * @date 2026-06-23
 */

namespace WooStoreManager\Repositories;

use WC_Product;
use WC_Product_Simple;
use WC_Product_Variable;
use WC_Product_Grouped;
use WC_Product_External;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class WSM_Product_Repository
 */
class WSM_Product_Repository {

	/**
	 * Find products matching filter parameters.
	 *
	 * @param array $args Filter arguments.
	 * @return array Array containing products list, total count, and total pages.
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

		if ( ! empty( $args['category'] ) ) {
			$query_args['category'] = (array) $args['category'];
		}

		if ( ! empty( $args['status'] ) ) {
			$query_args['status'] = $args['status'];
		}

		if ( ! empty( $args['stock_status'] ) ) {
			$query_args['stock_status'] = $args['stock_status'];
		}

		if ( ! empty( $args['search'] ) ) {
			$query_args['search'] = sanitize_text_field( $args['search'] );
		}

		$results = wc_get_products( $query_args );

		return [
			'products' => $results->products,
			'total'    => $results->total,
			'pages'    => $results->max_num_pages,
		];
	}

	/**
	 * Retrieve a detailed WooCommerce product object.
	 *
	 * @param int $id Product ID.
	 * @return WC_Product|null Product object if found, else null.
	 */
	public function find_by_id( int $id ): ?WC_Product {
		$product = wc_get_product( $id );
		return $product ? $product : null;
	}

	/**
	 * Create a new product.
	 *
	 * @param array $data Raw product parameters.
	 * @return int|WP_Error Created product ID or WP_Error.
	 */
	public function create( array $data ): int|WP_Error {
		$type = isset( $data['type'] ) ? sanitize_text_field( $data['type'] ) : 'simple';

		if ( 'variable' === $type ) {
			$product = new WC_Product_Variable();
		} elseif ( 'grouped' === $type ) {
			$product = new WC_Product_Grouped();
		} elseif ( 'external' === $type ) {
			$product = new WC_Product_External();
		} else {
			$product = new WC_Product_Simple();
		}

		$product = $this->set_product_properties( $product, $data );
		$product_id = $product->save();

		if ( $product_id > 0 ) {
			$this->save_product_brands( $product_id, $data );
			if ( 'variable' === $type ) {
				$this->save_variable_data( $product, $data );
			}
		}

		return $product_id > 0 ? $product_id : new WP_Error( 'wsm_create_failed', __( 'خطا در ایجاد محصول.', 'karasu-woo-pannel' ) );
	}

	/**
	 * Update an existing product.
	 *
	 * @param int   $id   Product ID.
	 * @param array $data Raw product parameters.
	 * @return bool|WP_Error True if success, else WP_Error.
	 */
	public function update( int $id, array $data ): bool|WP_Error {
		$product = wc_get_product( $id );
		if ( ! $product ) {
			return new WP_Error( 'wsm_not_found', __( 'محصول یافت نشد.', 'karasu-woo-pannel' ) );
		}

		$product = $this->set_product_properties( $product, $data );
		$product->save();

		$this->save_product_brands( $product->get_id(), $data );

		if ( $product->is_type( 'variable' ) ) {
			$this->save_variable_data( $product, $data );
		}

		return true;
	}

	/**
	 * Delete a product (moves to trash).
	 *
	 * @param int $id Product ID.
	 * @return bool True if deleted successfully.
	 */
	public function delete( int $id ): bool {
		$product = wc_get_product( $id );
		if ( ! $product ) {
			return false;
		}

		// Delete with false parameters moves it to WordPress Trash.
		return (bool) $product->delete( false );
	}

	/**
	 * Set or update product properties from data array.
	 *
	 * @param WC_Product $product WooCommerce product object.
	 * @param array      $data    Product data.
	 * @return WC_Product Modified product object.
	 */
	private function set_product_properties( WC_Product $product, array $data ): WC_Product {
		if ( isset( $data['name'] ) ) {
			$product->set_name( sanitize_text_field( $data['name'] ) );
		}
		if ( isset( $data['status'] ) ) {
			$product->set_status( sanitize_text_field( $data['status'] ) );
		}
		if ( isset( $data['regular_price'] ) ) {
			$product->set_regular_price( wc_format_decimal( $data['regular_price'] ) );
		}
		if ( isset( $data['sale_price'] ) ) {
			$product->set_sale_price( '' !== $data['sale_price'] ? wc_format_decimal( $data['sale_price'] ) : '' );
		}
		if ( isset( $data['sku'] ) ) {
			$product->set_sku( sanitize_text_field( $data['sku'] ) );
		}
		if ( isset( $data['manage_stock'] ) ) {
			$product->set_manage_stock( (bool) $data['manage_stock'] );
		}
		if ( isset( $data['stock_quantity'] ) ) {
			$product->set_stock_quantity( '' !== $data['stock_quantity'] ? absint( $data['stock_quantity'] ) : null );
		}
		if ( isset( $data['stock_status'] ) ) {
			$product->set_stock_status( sanitize_text_field( $data['stock_status'] ) );
		}
		if ( isset( $data['description'] ) ) {
			$product->set_description( wp_kses_post( $data['description'] ) );
		}
		if ( isset( $data['short_description'] ) ) {
			$product->set_short_description( wp_kses_post( $data['short_description'] ) );
		}
		if ( isset( $data['category_ids'] ) ) {
			$product->set_category_ids( array_map( 'absint', (array) $data['category_ids'] ) );
		}
		if ( isset( $data['image_id'] ) ) {
			$product->set_image_id( absint( $data['image_id'] ) );
		}
		if ( isset( $data['gallery_image_ids'] ) ) {
			$product->set_gallery_image_ids( array_map( 'absint', (array) $data['gallery_image_ids'] ) );
		}

		// Dims
		if ( isset( $data['weight'] ) ) {
			$product->set_weight( sanitize_text_field( $data['weight'] ) );
		}
		if ( isset( $data['length'] ) ) {
			$product->set_length( sanitize_text_field( $data['length'] ) );
		}
		if ( isset( $data['width'] ) ) {
			$product->set_width( sanitize_text_field( $data['width'] ) );
		}
		if ( isset( $data['height'] ) ) {
			$product->set_height( sanitize_text_field( $data['height'] ) );
		}

		// Virtual & Downloadable Flags
		if ( isset( $data['virtual'] ) ) {
			$product->set_virtual( (bool) $data['virtual'] );
		}
		if ( isset( $data['downloadable'] ) ) {
			$product->set_downloadable( (bool) $data['downloadable'] );
		}

		if ( $product->get_downloadable() ) {
			$this->save_downloadable_data( $product, $data );
		}

		if ( $product->is_type( 'grouped' ) ) {
			$this->save_grouped_data( $product, $data );
		}

		if ( $product->is_type( 'external' ) ) {
			$this->save_external_data( $product, $data );
		}

		return $product;
	}

	/**
	 * Save variable product attributes and variations.
	 *
	 * @param WC_Product_Variable $product Variable product object.
	 * @param array               $data    Product data array.
	 */
	private function save_variable_data( WC_Product_Variable $product, array $data ): void {
		if ( isset( $data['attributes'] ) ) {
			$attributes = [];
			foreach ( $data['attributes'] as $attr_data ) {
				$attribute = new \WC_Product_Attribute();
				$attribute->set_id( 0 );
				$attribute->set_name( sanitize_text_field( $attr_data['name'] ) );
				$options = is_array( $attr_data['options'] ) ? $attr_data['options'] : explode( '|', $attr_data['options'] );
				$attribute->set_options( array_map( 'sanitize_text_field', $options ) );
				$attribute->set_position( isset( $attr_data['position'] ) ? absint( $attr_data['position'] ) : 0 );
				$attribute->set_visible( isset( $attr_data['is_visible'] ) ? (bool) $attr_data['is_visible'] : true );
				$attribute->set_variation( isset( $attr_data['is_variation'] ) ? (bool) $attr_data['is_variation'] : true );
				$attributes[] = $attribute;
			}
			$product->set_attributes( $attributes );
			$product->save();
		}

		if ( isset( $data['variations'] ) ) {
			$incoming_ids = [];
			foreach ( $data['variations'] as $var_data ) {
				$var_id = isset( $var_data['id'] ) ? absint( $var_data['id'] ) : 0;
				if ( $var_id > 0 ) {
					$incoming_ids[] = $var_id;
				}
			}

			$existing_children = $product->get_children();
			foreach ( $existing_children as $child_id ) {
				if ( ! in_array( $child_id, $incoming_ids, true ) ) {
					$variation = wc_get_product( $child_id );
					if ( $variation ) {
						$variation->delete( true );
					}
				}
			}

			foreach ( $data['variations'] as $var_data ) {
				$variation_id = isset( $var_data['id'] ) ? absint( $var_data['id'] ) : 0;
				if ( $variation_id > 0 ) {
					$variation = new \WC_Product_Variation( $variation_id );
				} else {
					$variation = new \WC_Product_Variation();
					$variation->set_parent_id( $product->get_id() );
				}

				if ( isset( $var_data['sku'] ) ) {
					$variation->set_sku( sanitize_text_field( $var_data['sku'] ) );
				}
				if ( isset( $var_data['regular_price'] ) ) {
					$variation->set_regular_price( wc_format_decimal( $var_data['regular_price'] ) );
				}
				if ( isset( $var_data['sale_price'] ) ) {
					$variation->set_sale_price( '' !== $var_data['sale_price'] ? wc_format_decimal( $var_data['sale_price'] ) : '' );
				}
				if ( isset( $var_data['manage_stock'] ) ) {
					$variation->set_manage_stock( (bool) $var_data['manage_stock'] );
				}
				if ( isset( $var_data['stock_quantity'] ) ) {
					$variation->set_stock_quantity( '' !== $var_data['stock_quantity'] ? absint( $var_data['stock_quantity'] ) : null );
				}
				if ( isset( $var_data['stock_status'] ) ) {
					$variation->set_stock_status( sanitize_text_field( $var_data['stock_status'] ) );
				}
				if ( isset( $var_data['image_id'] ) ) {
					$variation->set_image_id( absint( $var_data['image_id'] ) );
				}
				if ( isset( $var_data['attributes'] ) ) {
					$variation->set_attributes( (array) $var_data['attributes'] );
				}

				$variation->save();
			}
		}
	}

	/**
	 * Save grouped product children.
	 *
	 * @param \WC_Product_Grouped $product Grouped product.
	 * @param array               $data    Product data array.
	 */
	private function save_grouped_data( $product, array $data ): void {
		if ( ! isset( $data['children'] ) ) {
			return;
		}

		$children_ids = array_map( 'absint', (array) $data['children'] );
		$valid_children = [];

		foreach ( $children_ids as $child_id ) {
			$child = wc_get_product( $child_id );
			if ( $child && ! $child->is_type( 'grouped' ) && $child_id !== $product->get_id() ) {
				$valid_children[] = $child_id;
			}
		}

		$product->set_children( $valid_children );
	}

	/**
	 * Save external product URL and button text.
	 *
	 * @param \WC_Product_External $product External product.
	 * @param array                $data    Product data array.
	 */
	private function save_external_data( $product, array $data ): void {
		if ( isset( $data['product_url'] ) ) {
			$product->set_product_url( esc_url_raw( $data['product_url'] ) );
		}
		if ( isset( $data['button_text'] ) ) {
			$product->set_button_text( sanitize_text_field( $data['button_text'] ) );
		}
	}

	/**
	 * Save downloadable files and limit/expiry settings.
	 *
	 * @param \WC_Product $product WooCommerce product.
	 * @param array       $data    Product data array.
	 */
	private function save_downloadable_data( $product, array $data ): void {
		if ( isset( $data['download_limit'] ) ) {
			$product->set_download_limit( '' !== $data['download_limit'] ? intval( $data['download_limit'] ) : -1 );
		}
		if ( isset( $data['download_expiry'] ) ) {
			$product->set_download_expiry( '' !== $data['download_expiry'] ? intval( $data['download_expiry'] ) : -1 );
		}

		if ( isset( $data['downloads'] ) && is_array( $data['downloads'] ) ) {
			$downloads = [];
			$upload_dir = wp_upload_dir();
			$uploads_url = $upload_dir['baseurl'];
			$uploads_path = realpath( $upload_dir['basedir'] );

			foreach ( $data['downloads'] as $download ) {
				if ( empty( $download['file'] ) ) {
					continue;
				}

				$file = trim( $download['file'] );
				$is_valid = false;

				if ( str_starts_with( $file, $uploads_url ) ) {
					$is_valid = true;
				} elseif ( filter_var( $file, FILTER_VALIDATE_URL ) ) {
					$file_host = wp_parse_url( $file, PHP_URL_HOST );
					$site_host = wp_parse_url( home_url(), PHP_URL_HOST );
					if ( $file_host === $site_host ) {
						$is_valid = true;
					} else {
						$allowed_domains = apply_filters( 'wsm_allowed_download_domains', [ $site_host ] );
						if ( in_array( $file_host, $allowed_domains, true ) ) {
							$is_valid = true;
						}
					}
				} else {
					$real_file = realpath( $file );
					if ( $real_file && $uploads_path && str_starts_with( $real_file, $uploads_path ) ) {
						$is_valid = true;
					}
				}

				if ( $is_valid ) {
					$download_obj = new \WC_Product_Download();
					$download_obj->set_name( sanitize_text_field( $download['name'] ?? '' ) );
					$download_obj->set_file( $file );
					$download_obj->set_id( ! empty( $download['id'] ) ? sanitize_key( $download['id'] ) : wp_generate_uuid4() );
					$downloads[] = $download_obj;
				}
			}
			$product->set_downloads( $downloads );
		}
	}

	/**
	 * Save product brands taxonomy.
	 *
	 * @param int   $product_id Product ID.
	 * @param array $data       Product data.
	 */
	private function save_product_brands( int $product_id, array $data ): void {
		if ( isset( $data['brand_ids'] ) ) {
			wp_set_post_terms( $product_id, array_map( 'absint', (array) $data['brand_ids'] ), 'product_brand' );
		}
	}
}

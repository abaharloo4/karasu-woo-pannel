<?php
/**
 * REST Controller for WooCommerce Products
 *
 * @package KarasuWooPannel
 * @version 1.0.10
 * @date 2026-06-23
 */

namespace WooStoreManager\Api;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use WooStoreManager\Services\WSM_Product_Service;
use WooStoreManager\Services\WSM_Media_Service;
use WooStoreManager\Helpers\WSM_Response;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class WSM_Products_Controller
 */
class WSM_Products_Controller extends WSM_REST_Controller {

	/**
	 * Product service.
	 *
	 * @var WSM_Product_Service
	 */
	private WSM_Product_Service $service;

	/**
	 * Media upload service.
	 *
	 * @var WSM_Media_Service
	 */
	private WSM_Media_Service $media_service;

	/**
	 * WSM_Products_Controller constructor.
	 *
	 * @param WSM_Product_Service $service Target service.
	 */
	public function __construct( WSM_Product_Service $service ) {
		$this->service       = $service;
		$this->media_service = new WSM_Media_Service();
	}

	/**
	 * Register REST routes.
	 */
	public function register_routes(): void {
		register_rest_route(
			$this->namespace,
			'/products',
			[
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'get_products' ],
					'permission_callback' => [ $this, 'check_permission' ],
				],
				[
					'methods'             => 'POST',
					'callback'            => [ $this, 'create_product' ],
					'permission_callback' => [ $this, 'check_permission' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/products/(?P<id>\d+)',
			[
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'get_product_detail' ],
					'permission_callback' => [ $this, 'check_permission' ],
				],
				[
					'methods'             => 'PUT',
					'callback'            => [ $this, 'update_product' ],
					'permission_callback' => [ $this, 'check_permission' ],
				],
				[
					'methods'             => 'DELETE',
					'callback'            => [ $this, 'delete_product' ],
					'permission_callback' => [ $this, 'check_permission' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/products/bulk',
			[
				[
					'methods'             => 'POST',
					'callback'            => [ $this, 'bulk_action' ],
					'permission_callback' => [ $this, 'check_permission' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/products/media',
			[
				[
					'methods'             => 'POST',
					'callback'            => [ $this, 'upload_media' ],
					'permission_callback' => [ $this, 'check_permission' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/categories',
			[
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'get_categories' ],
					'permission_callback' => [ $this, 'check_permission' ],
				],
				[
					'methods'             => 'POST',
					'callback'            => [ $this, 'create_category' ],
					'permission_callback' => [ $this, 'check_permission' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/categories/(?P<id>\d+)',
			[
				[
					'methods'             => 'DELETE',
					'callback'            => [ $this, 'delete_category' ],
					'permission_callback' => [ $this, 'check_permission' ],
				],
			]
		);
	}

	/**
	 * Verify product permissions check.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return bool|WP_Error True if authorized, else WP_Error.
	 */
	public function check_permission( WP_REST_Request $request ): bool|WP_Error {
		$auth_check = $this->wsm_check_permission( $request );
		if ( is_wp_error( $auth_check ) ) {
			return $auth_check;
		}

		if ( ! current_user_can( 'wsm_manage_products' ) && ! current_user_can( 'manage_woocommerce' ) && ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'wsm_forbidden',
				__( 'دسترسی غیرمجاز. شما مجوز مدیریت محصولات را ندارید.', 'karasu-woo-pannel' ),
				[ 'status' => 403 ]
			);
		}

		return true;
	}

	/**
	 * Query products list.
	 *
	 * @param WP_REST_Request $request Request params.
	 * @return WP_REST_Response REST API Response.
	 */
	public function get_products( WP_REST_Request $request ): WP_REST_Response {
		$args = [
			'page'         => $request->get_param( 'page' ),
			'per_page'     => $request->get_param( 'per_page' ),
			'category'     => $request->get_param( 'category' ),
			'status'       => $request->get_param( 'status' ),
			'stock_status' => $request->get_param( 'stock_status' ),
			'search'       => $request->get_param( 'search' ),
		];

		$results = $this->service->get_products( array_filter( $args ) );
		return WSM_Response::success( $results, __( 'لیست محصولات با موفقیت بارگذاری شد.', 'karasu-woo-pannel' ) );
	}

	/**
	 * Get details of single product.
	 *
	 * @param WP_REST_Request $request Request ID.
	 * @return WP_REST_Response|WP_Error Detailed response or error.
	 */
	public function get_product_detail( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$id     = (int) $request->get_param( 'id' );
		$result = $this->service->get_product_detail( $id );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return WSM_Response::success( $result, __( 'جزییات محصول با موفقیت بارگذاری شد.', 'karasu-woo-pannel' ) );
	}

	/**
	 * Create simple/variable product.
	 *
	 * @param WP_REST_Request $request Request body data.
	 * @return WP_REST_Response|WP_Error Response or error.
	 */
	public function create_product( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$body   = json_decode( $request->get_body(), true );
		$result = $this->service->create_product( (array) $body );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return WSM_Response::success( [ 'product_id' => $result ], __( 'محصول با موفقیت ایجاد شد.', 'karasu-woo-pannel' ) );
	}

	/**
	 * Update product fields.
	 *
	 * @param WP_REST_Request $request Request parameter and body.
	 * @return WP_REST_Response|WP_Error Response details or error.
	 */
	public function update_product( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$id     = (int) $request->get_param( 'id' );
		$body   = json_decode( $request->get_body(), true );
		$result = $this->service->update_product( $id, (array) $body );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return WSM_Response::success( [ 'id' => $id ], __( 'محصول با موفقیت ویرایش شد.', 'karasu-woo-pannel' ) );
	}

	/**
	 * Delete a product.
	 *
	 * @param WP_REST_Request $request Request ID.
	 * @return WP_REST_Response|WP_Error Response or error.
	 */
	public function delete_product( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$id     = (int) $request->get_param( 'id' );
		$result = $this->service->delete_product( $id );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return WSM_Response::success( [ 'id' => $id ], __( 'محصول با موفقیت حذف شد.', 'karasu-woo-pannel' ) );
	}

	/**
	 * Handle image file upload request.
	 *
	 * @param WP_REST_Request $request Upload request containing files.
	 * @return WP_REST_Response|WP_Error Attachment details or error.
	 */
	public function upload_media( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$result = $this->media_service->upload_image( 'file' );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return WSM_Response::success( $result, __( 'تصویر با موفقیت آپلود شد.', 'karasu-woo-pannel' ) );
	}

	/**
	 * Get product categories list.
	 *
	 * @return WP_REST_Response List of categories.
	 */
	public function get_categories(): WP_REST_Response {
		$terms = get_terms(
			[
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
			]
		);

		$formatted = [];
		foreach ( $terms as $term ) {
			$thumbnail_id = get_term_meta( $term->term_id, 'thumbnail_id', true );
			$image_url    = $thumbnail_id ? wp_get_attachment_image_url( $thumbnail_id, 'thumbnail' ) : '';
			$formatted[]  = [
				'id'     => $term->term_id,
				'name'   => $term->name,
				'slug'   => $term->slug,
				'parent' => $term->parent,
				'image'  => $image_url ? [ 'id' => (int) $thumbnail_id, 'url' => $image_url ] : null,
			];
		}

		return WSM_Response::success( $formatted, __( 'دسته‌بندی‌ها با موفقیت دریافت شدند.', 'karasu-woo-pannel' ) );
	}

	/**
	 * Create a new product category term.
	 *
	 * @param WP_REST_Request $request REST Request parameters.
	 * @return WP_REST_Response|WP_Error Response or error.
	 */
	public function create_category( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$body        = json_decode( $request->get_body(), true );
		$name        = sanitize_text_field( $body['name'] ?? '' );
		$slug        = sanitize_title( $body['slug'] ?? '' );
		$parent      = isset( $body['parent'] ) ? absint( $body['parent'] ) : 0;
		$description = sanitize_textarea_field( $body['description'] ?? '' );

		if ( empty( $name ) ) {
			return WSM_Response::error( __( 'نام دسته‌بندی الزامی است.', 'karasu-woo-pannel' ) );
		}

		$args = [
			'slug'        => $slug,
			'parent'      => $parent,
			'description' => $description,
		];

		$result = wp_insert_term( $name, 'product_cat', $args );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		if ( isset( $body['image_id'] ) && ! empty( $body['image_id'] ) ) {
			update_term_meta( $result['term_id'], 'thumbnail_id', absint( $body['image_id'] ) );
		}

		return WSM_Response::success( [ 'term_id' => $result['term_id'] ], __( 'دسته‌بندی با موفقیت ایجاد شد.', 'karasu-woo-pannel' ) );
	}

	/**
	 * Delete a product category term.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error Response or error.
	 */
	public function delete_category( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$id = (int) $request->get_param( 'id' );

		// WooCommerce default product category cannot be deleted.
		$default_cat_id = get_option( 'default_product_cat' );
		if ( $id === (int) $default_cat_id ) {
			return WSM_Response::error( __( 'دسته‌بندی پیش‌فرض ووکامرس قابل حذف نیست.', 'karasu-woo-pannel' ) );
		}

		$result = wp_delete_term( $id, 'product_cat' );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		if ( ! $result ) {
			return WSM_Response::error( __( 'حذف دسته‌بندی ناموفق بود.', 'karasu-woo-pannel' ) );
		}

		return WSM_Response::success( [ 'id' => $id ], __( 'دسته‌بندی با موفقیت حذف شد.', 'karasu-woo-pannel' ) );
	}

	/**
	 * Perform bulk operations (status updates, stock status updates, deletions) on multiple products.
	 *
	 * @param WP_REST_Request $request Request properties.
	 * @return WP_REST_Response|WP_Error Response or error.
	 */
	public function bulk_action( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$body   = json_decode( $request->get_body(), true );
		$ids    = isset( $body['ids'] ) ? array_map( 'absint', (array) $body['ids'] ) : [];
		$action = sanitize_text_field( $body['action'] ?? '' );

		if ( empty( $ids ) ) {
			return new WP_Error( 'wsm_missing_ids', __( 'هیچ محصولی انتخاب نشده است.', 'karasu-woo-pannel' ), [ 'status' => 400 ] );
		}

		if ( ! in_array( $action, [ 'status', 'stock_status', 'delete' ], true ) ) {
			return new WP_Error( 'wsm_invalid_action', __( 'عملیات دسته جمعی نامعتبر است.', 'karasu-woo-pannel' ), [ 'status' => 400 ] );
		}

		$success_count = 0;
		if ( 'status' === $action ) {
			$status = sanitize_text_field( $body['status'] ?? '' );
			if ( ! in_array( $status, [ 'publish', 'draft' ], true ) ) {
				return new WP_Error( 'wsm_invalid_status', __( 'وضعیت نامعتبر است.', 'karasu-woo-pannel' ), [ 'status' => 400 ] );
			}
			foreach ( $ids as $id ) {
				$product = wc_get_product( $id );
				if ( $product ) {
					$product->set_status( $status );
					$product->save();
					$success_count++;
				}
			}
		} elseif ( 'stock_status' === $action ) {
			$stock_status = sanitize_text_field( $body['stock_status'] ?? '' );
			if ( ! in_array( $stock_status, [ 'instock', 'outofstock' ], true ) ) {
				return new WP_Error( 'wsm_invalid_stock', __( 'وضعیت موجودی نامعتبر است.', 'karasu-woo-pannel' ), [ 'status' => 400 ] );
			}
			foreach ( $ids as $id ) {
				$product = wc_get_product( $id );
				if ( $product ) {
					$product->set_stock_status( $stock_status );
					$product->save();
					$success_count++;
				}
			}
		} elseif ( 'delete' === $action ) {
			foreach ( $ids as $id ) {
				$product = wc_get_product( $id );
				if ( $product ) {
					$product->delete( false ); // Move to trash
					$success_count++;
				}
			}
		}

		return WSM_Response::success(
			[
				'success_count' => $success_count,
				'total_count'   => count( $ids ),
			],
			sprintf( __( 'عملیات دسته جمعی روی %d محصول با موفقیت اعمال شد.', 'karasu-woo-pannel' ), $success_count )
		);
	}
}

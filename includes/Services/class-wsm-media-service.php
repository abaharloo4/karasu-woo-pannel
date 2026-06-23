<?php
/**
 * Media Upload Services
 *
 * @package KarasuWooPannel
 * @version 1.1.0
 * @date 2026-06-23
 */

namespace WooStoreManager\Services;

use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class WSM_Media_Service
 */
class WSM_Media_Service {

	/**
	 * Upload file to WordPress media library.
	 *
	 * @param string $file_key Key of the file inside $_FILES array.
	 * @return array|WP_Error Details of created attachment [id, url], or WP_Error.
	 */
	public function upload_image( string $file_key ): array|WP_Error {
		// Include essential core files.
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		if ( empty( $_FILES[ $file_key ] ) || empty( $_FILES[ $file_key ]['tmp_name'] ) ) {
			return new WP_Error( 'wsm_no_file_uploaded', __( 'فایلی برای آپلود یافت نشد.', 'karasu-woo-pannel' ) );
		}

		$file_path = $_FILES[ $file_key ]['tmp_name'];
		$file_name = $_FILES[ $file_key ]['name'];
		$file_size = $_FILES[ $file_key ]['size'];

		// Enforce size limit (5MB).
		$max_size = 5 * 1024 * 1024;
		if ( $file_size > $max_size ) {
			return new WP_Error( 'wsm_file_too_large', __( 'حجم فایل ارسالی بیشتر از حد مجاز (۵ مگابایت) است.', 'karasu-woo-pannel' ) );
		}

		// Verify file type and extension securely.
		$check         = wp_check_filetype_and_ext( $file_path, $file_name );
		$allowed_types = [ 'image/jpeg', 'image/png', 'image/gif', 'image/webp' ];
		if ( empty( $check['type'] ) || ! in_array( $check['type'], $allowed_types, true ) ) {
			return new WP_Error( 'wsm_invalid_file_type', __( 'تنها بارگذاری فایل‌های تصویری مجاز است.', 'karasu-woo-pannel' ) );
		}

		// Verify image integrity.
		if ( false === @getimagesize( $file_path ) ) {
			return new WP_Error( 'wsm_invalid_image', __( 'فایل ارسالی یک تصویر معتبر نیست.', 'karasu-woo-pannel' ) );
		}

		// Handle file upload and register attachment.
		$attachment_id = media_handle_upload( $file_key, 0 );

		if ( is_wp_error( $attachment_id ) ) {
			return $attachment_id;
		}

		return [
			'id'  => $attachment_id,
			'url' => wp_get_attachment_image_url( $attachment_id, 'large' ),
		];
	}
}

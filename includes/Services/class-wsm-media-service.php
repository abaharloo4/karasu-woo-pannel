<?php
/**
 * Media Upload Services
 *
 * @package KarasuWooPannel
 * @version 1.0.8
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

		if ( empty( $_FILES[ $file_key ] ) ) {
			return new WP_Error( 'wsm_no_file_uploaded', __( 'فایلی برای آپلود یافت نشد.', 'karasu-woo-pannel' ) );
		}

		// Verify file is an image.
		$type = sanitize_text_field( wp_unslash( $_FILES[ $file_key ]['type'] ?? '' ) );
		if ( ! str_starts_with( $type, 'image/' ) ) {
			return new WP_Error( 'wsm_invalid_file_type', __( 'تنها بارگذاری فایل‌های تصویری مجاز است.', 'karasu-woo-pannel' ) );
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

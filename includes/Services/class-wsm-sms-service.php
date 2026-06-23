<?php
/**
 * SMS Dispatcher and Notification Service
 *
 * @package KarasuWooPannel
 * @version 1.0.3
 * @date 2026-06-23
 */

namespace WooStoreManager\Services;

use WC_Order;
use WC_Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class WSM_Sms_Service
 */
class WSM_Sms_Service {

	/**
	 * Send SMS message.
	 *
	 * @param string $to          Recipient phone number.
	 * @param string $message     SMS message text.
	 * @param string $event_type  Event label (e.g. processing, completed, new_order).
	 * @param int    $related_id  Related Order or Product ID.
	 * @return bool True if successfully dispatched, else false.
	 */
	public function send_sms( string $to, string $message, string $event_type, int $related_id = 0 ): bool {
		if ( empty( $to ) || empty( $message ) ) {
			return false;
		}

		$username = get_option( 'wsm_sms_username' );
		$password = get_option( 'wsm_sms_password' );
		$from     = get_option( 'wsm_sms_from_line' );

		// Check if credentials are not configured, fallback to mock logs.
		if ( empty( $username ) || empty( $password ) ) {
			$this->log_sms( $event_type, $to, $message, 1, 'MOCKED_SUCCESS', $related_id );
			return true;
		}

		$body = [
			'username' => $username,
			'password' => $password,
			'to'       => $to,
			'from'     => $from,
			'text'     => $message,
		];

		$response = wp_remote_post(
			'https://rest.payamak-panel.com/api/SendSMS/SendSMS',
			[
				'headers' => [ 'Content-Type' => 'application/json' ],
				'body'    => json_encode( $body ),
				'timeout' => 15,
			]
		);

		if ( is_wp_error( $response ) ) {
			$error_msg = $response->get_error_message();
			$this->log_sms( $event_type, $to, $message, 0, $error_msg, $related_id );
			return false;
		}

		$res_body = wp_remote_retrieve_body( $response );
		$res_data = json_decode( $res_body, true );

		$retval = isset( $res_data['RetVal'] ) ? (int) $res_data['RetVal'] : -999;
		// A positive value greater than 100 is typically a successful message ID returned by MeliPayamak.
		$status = $retval > 100 ? 1 : 0;
		$api_msg = $status ? 'Message ID: ' . $retval : 'Error Code: ' . $retval;

		$this->log_sms( $event_type, $to, $message, $status, $api_msg, $related_id );

		return (bool) $status;
	}

	/**
	 * Parse variables placeholder keys with actual values.
	 *
	 * @param string $text   Template text.
	 * @param mixed  $object WC_Order or WC_Product object.
	 * @return string Formatted text.
	 */
	public function parse_variables( string $text, $object ): string {
		if ( $object instanceof WC_Order ) {
			$status_slug = $object->get_status();
			$statuses    = wc_get_order_statuses();
			$status_lbl  = $statuses[ 'wc-' . $status_slug ] ?? $status_slug;

			$placeholders = [
				'{order_id}'      => $object->get_id(),
				'{order_total}'   => number_format( (float) $object->get_total() ),
				'{customer_name}' => trim( $object->get_billing_first_name() . ' ' . $object->get_billing_last_name() ),
				'{status_label}'  => $status_lbl,
				'{billing_phone}' => $object->get_billing_phone(),
			];

			return strtr( $text, $placeholders );
		}

		if ( $object instanceof WC_Product ) {
			$placeholders = [
				'{product_id}'   => $object->get_id(),
				'{product_name}' => $object->get_name(),
				'{sku}'          => $object->get_sku(),
				'{stock_qty}'    => $object->get_stock_quantity() ?? 0,
			];

			return strtr( $text, $placeholders );
		}

		return $text;
	}

	/**
	 * Log outgoing SMS transactions to custom DB table.
	 *
	 * @param string $event_type   Event label.
	 * @param string $recipient    Phone number.
	 * @param string $message      SMS content.
	 * @param int    $status       Success status (1/0).
	 * @param string $api_response Response string or error.
	 * @param int    $related_id   Order or product reference ID.
	 */
	private function log_sms( string $event_type, string $recipient, string $message, int $status, string $api_response, int $related_id ): void {
		global $wpdb;

		$table_name = $wpdb->prefix . 'wsm_sms_log';
		$wpdb->insert(
			$table_name,
			[
				'event_type'   => sanitize_text_field( $event_type ),
				'recipient'    => sanitize_text_field( $recipient ),
				'message'      => $message,
				'status'       => $status,
				'api_response' => sanitize_text_field( $api_response ),
				'related_id'   => $related_id > 0 ? absint( $related_id ) : null,
				'sent_at'      => current_time( 'mysql' ),
			],
			[ '%s', '%s', '%s', '%d', '%s', '%d', '%s' ]
		);
	}

	/**
	 * Get all registered SMS notification templates.
	 *
	 * @return array Multi-dimensional templates settings array.
	 */
	public static function get_templates(): array {
		$defaults = [
			// Customer templates.
			'pending'    => [
				'enabled' => false,
				'text'    => 'مشتری گرامی {customer_name}، سفارش #{order_id} ثبت شد و در انتظار پرداخت است.',
			],
			'processing' => [
				'enabled' => false,
				'text'    => 'مشتری گرامی {customer_name}، سفارش #{order_id} با موفقیت ثبت شد و در حال پردازش است.',
			],
			'on-hold'    => [
				'enabled' => false,
				'text'    => 'مشتری گرامی {customer_name}، سفارش #{order_id} در وضعیت معلق قرار گرفت.',
			],
			'completed'  => [
				'enabled' => false,
				'text'    => 'مشتری گرامی {customer_name}، سفارش #{order_id} تکمیل شد و فرآیند ارسال آغاز گردید. با تشکر.',
			],
			'cancelled'  => [
				'enabled' => false,
				'text'    => 'مشتری گرامی، سفارش #{order_id} لغو شد.',
			],
			'refunded'   => [
				'enabled' => false,
				'text'    => 'مشتری گرامی، سفارش #{order_id} مرجوع گردید و مبلغ آن مسترد شد.',
			],
			'failed'     => [
				'enabled' => false,
				'text'    => 'پرداخت سفارش #{order_id} ناموفق بود و لغو گردید.',
			],
			// Admin templates.
			'new_order'  => [
				'enabled' => false,
				'text'    => 'سفارش جدید #{order_id} به مبلغ {order_total} تومان ثبت شد.',
			],
			'low_stock'  => [
				'enabled' => false,
				'text'    => 'هشدار کمبود موجودی: محصول {product_name} به تعداد {stock_qty} رسیده است.',
			],
		];

		$saved = get_option( 'wsm_sms_templates', [] );
		return array_replace_recursive( $defaults, (array) $saved );
	}

	/**
	 * Save updated SMS templates.
	 *
	 * @param array $templates New templates settings.
	 * @return bool True if option value changed, else false.
	 */
	public static function update_templates( array $templates ): bool {
		$sanitized = [];
		$default_keys = [ 'pending', 'processing', 'on-hold', 'completed', 'cancelled', 'refunded', 'failed', 'new_order', 'low_stock' ];

		foreach ( $default_keys as $key ) {
			if ( isset( $templates[ $key ] ) ) {
				$sanitized[ $key ] = [
					'enabled' => (bool) ( $templates[ $key ]['enabled'] ?? false ),
					'text'    => sanitize_textarea_field( $templates[ $key ]['text'] ?? '' ),
				];
			}
		}

		return update_option( 'wsm_sms_templates', $sanitized );
	}
}

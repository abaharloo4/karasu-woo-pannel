<?php
/**
 * SMS Dispatcher and Notification Service
 *
 * @package KarasuWooPannel
 * @version 1.1.1
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
		$password = wsm_decrypt_password( get_option( 'wsm_sms_password' ) );
		$from     = get_option( 'wsm_sms_from_line' );
		$token    = get_option( 'wsm_sms_token' );

		// Check if credentials are not configured, fallback to mock logs.
		if ( empty( $token ) && ( empty( $username ) || empty( $password ) ) ) {
			$this->log_sms( $event_type, $to, $message, 1, 'MOCKED_SUCCESS', $related_id );
			return true;
		}

		$templates = self::get_templates();
		$tmpl = $templates[ $event_type ] ?? null;
		$sent = false;
		$api_msg = '';

		// 1. Try sending via Melipayamak shared pattern web service if body_id is configured
		if ( $tmpl && ! empty( $tmpl['body_id'] ) && $related_id > 0 ) {
			$body_id  = (int) $tmpl['body_id'];
			$args_str = $tmpl['args'] ?? '';

			// Load appropriate WooCommerce object
			$object = null;
			if ( 'admin_low_stock' === $event_type ) {
				$object = wc_get_product( $related_id );
			} else {
				$object = wc_get_order( $related_id );
			}

			if ( $object ) {
				$pattern_text = $this->get_pattern_text_values( $args_str, $object );
				
				if ( ! empty( $token ) ) {
					// Use REST console API (Token Auth)
					$pattern_args = explode( ';', $pattern_text );
					$pattern_args = array_map( 'trim', $pattern_args );

					$body = [
						'to'     => $to,
						'bodyId' => $body_id,
						'args'   => $pattern_args,
					];

					$response = wp_remote_post(
						'https://console.melipayamak.com/api/send/shared/' . $token,
						[
							'headers' => [ 'Content-Type' => 'application/json' ],
							'body'    => json_encode( $body ),
							'timeout' => 15,
						]
					);

					if ( ! is_wp_error( $response ) ) {
						$res_body = wp_remote_retrieve_body( $response );
						$res_data = json_decode( $res_body, true );
						
						$val = $res_data['recId'] ?? $res_data['value'] ?? $res_data['Value'] ?? '';
						if ( ! empty( $val ) && ( ! isset( $res_data['status'] ) || $res_data['status'] !== 'error' ) ) {
							$sent = true;
							$api_msg = 'Pattern Success (ID: ' . $val . ')';
							$this->log_sms( $event_type, $to, 'Pattern ID: ' . $body_id . ' | Args: ' . $pattern_text, 1, $api_msg, $related_id );
						} else {
							$err_code = $res_data['code'] ?? $res_data['RetStatus'] ?? '';
							$api_msg = 'Pattern Failed' . ( ! empty( $err_code ) ? ' (Code: ' . $err_code . ')' : '' );
							wsm_log_error( sprintf( 'Melipayamak API Token pattern sending failed (Event: %s, To: %s, BodyId: %d, Response: %s)', $event_type, $to, $body_id, $res_body ) );
						}
					} else {
						$api_msg = 'Pattern Request Error: ' . $response->get_error_message();
						wsm_log_error( sprintf( 'Melipayamak API Token pattern sending request error (Event: %s, To: %s, BodyId: %d, Error: %s)', $event_type, $to, $body_id, $api_msg ) );
					}
				} else {
					// Use legacy SOAP/REST API (Username/Password Auth)
					$body = [
						'username' => $username,
						'password' => $password,
						'to'       => $to,
						'bodyId'   => $body_id,
						'text'     => $pattern_text,
					];

					$response = wp_remote_post(
						'https://rest.payamak-panel.com/api/SendSMS/BaseServiceNumberShared',
						[
							'headers' => [ 'Content-Type' => 'application/json' ],
							'body'    => json_encode( $body ),
							'timeout' => 15,
						]
					);

					if ( ! is_wp_error( $response ) ) {
						$res_body = wp_remote_retrieve_body( $response );
						$res_data = json_decode( $res_body, true );
						$retval   = isset( $res_data['RetVal'] ) ? (int) $res_data['RetVal'] : -999;
						
						if ( $retval > 100 ) {
							$sent = true;
							$api_msg = 'Pattern Success (ID: ' . $retval . ')';
							$this->log_sms( $event_type, $to, 'Pattern ID: ' . $body_id . ' | Args: ' . $pattern_text, 1, $api_msg, $related_id );
						} else {
							$api_msg = 'Pattern Failed (Code: ' . $retval . ')';
							wsm_log_error( sprintf( 'Melipayamak pattern sending failed (Event: %s, To: %s, BodyId: %d, Response: %s)', $event_type, $to, $body_id, $res_body ) );
						}
					} else {
						$api_msg = 'Pattern Error: ' . $response->get_error_message();
						wsm_log_error( sprintf( 'Melipayamak pattern sending request error (Event: %s, To: %s, BodyId: %d, Error: %s)', $event_type, $to, $body_id, $api_msg ) );
					}
				}
			}
		}

		// 2. Fallback to standard dedicated line SendSMS if pattern wasn't configured or failed
		if ( ! $sent ) {
			if ( ! empty( $token ) ) {
				// Use REST console API fallback
				$body = [
					'to'   => $to,
					'from' => $from,
					'text' => $message,
				];

				$response = wp_remote_post(
					'https://console.melipayamak.com/api/send/simple/' . $token,
					[
						'headers' => [ 'Content-Type' => 'application/json' ],
						'body'    => json_encode( $body ),
						'timeout' => 15,
					]
				);

				if ( is_wp_error( $response ) ) {
					$error_msg = $response->get_error_message();
					$this->log_sms( $event_type, $to, $message, 0, $error_msg . ( ! empty( $api_msg ) ? ' (Fallback from: ' . $api_msg . ')' : '' ), $related_id );
					wsm_log_error( sprintf( 'Melipayamak API Token fallback send request error (Event: %s, To: %s, Error: %s)', $event_type, $to, $error_msg ) );
					return false;
				}

				$res_body = wp_remote_retrieve_body( $response );
				$res_data = json_decode( $res_body, true );

				$val    = $res_data['recId'] ?? $res_data['value'] ?? $res_data['Value'] ?? '';
				$status = ( ! empty( $val ) && ( ! isset( $res_data['status'] ) || $res_data['status'] !== 'error' ) ) ? 1 : 0;
				
				$api_msg_full = '';
				if ( $status ) {
					$api_msg_full = 'Message ID: ' . $val;
				} else {
					$err_code = $res_data['code'] ?? $res_data['RetStatus'] ?? 'Unknown';
					$api_msg_full = 'Error Code: ' . $err_code;
				}

				if ( ! empty( $api_msg ) ) {
					$api_msg_full .= ' (Fallback from: ' . $api_msg . ')';
				}

				if ( ! $status ) {
					wsm_log_error( sprintf( 'Melipayamak API Token fallback sending failed (Event: %s, To: %s, Response: %s)', $event_type, $to, $res_body ) );
				}

				$this->log_sms( $event_type, $to, $message, $status, $api_msg_full, $related_id );
				return (bool) $status;
			} else {
				// Use legacy SOAP/REST API fallback
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
					$this->log_sms( $event_type, $to, $message, 0, $error_msg . ( ! empty( $api_msg ) ? ' (Fallback from: ' . $api_msg . ')' : '' ), $related_id );
					wsm_log_error( sprintf( 'Melipayamak fallback send request error (Event: %s, To: %s, Error: %s)', $event_type, $to, $error_msg ) );
					return false;
				}

				$res_body = wp_remote_retrieve_body( $response );
				$res_data = json_decode( $res_body, true );

				$retval       = isset( $res_data['RetVal'] ) ? (int) $res_data['RetVal'] : -999;
				$status       = $retval > 100 ? 1 : 0;
				$api_msg_full = $status ? 'Message ID: ' . $retval : 'Error Code: ' . $retval;
				if ( ! empty( $api_msg ) ) {
					$api_msg_full .= ' (Fallback from: ' . $api_msg . ')';
				}

				if ( ! $status ) {
					wsm_log_error( sprintf( 'Melipayamak fallback sending failed (Event: %s, To: %s, Response: %s)', $event_type, $to, $res_body ) );
				}

				$this->log_sms( $event_type, $to, $message, $status, $api_msg_full, $related_id );
				return (bool) $status;
		}

		return true;
	}

	/**
	 * Parse variables placeholder keys with actual values and return them as joined string.
	 *
	 * @param string $args_str  Comma-separated variables string (e.g. "{customer_name},{order_id}").
	 * @param mixed  $object    WC_Order or WC_Product object.
	 * @return string Joined variables string (separated by semicolon).
	 */
	public function get_pattern_text_values( string $args_str, $object ): string {
		if ( empty( $args_str ) ) {
			return '';
		}

		$variables = explode( ',', $args_str );
		$values    = [];

		foreach ( $variables as $var ) {
			$var = trim( $var );
			$parsed = $this->parse_variables( $var, $object );
			$values[] = $parsed;
		}

		return implode( ';', $values );
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
			'pending'          => [
				'enabled' => false,
				'text'    => 'مشتری گرامی {customer_name}، سفارش #{order_id} ثبت شد و در انتظار پرداخت است.',
				'body_id' => '',
				'args'    => '',
			],
			'processing'       => [
				'enabled' => false,
				'text'    => 'مشتری گرامی {customer_name}، سفارش #{order_id} با موفقیت ثبت شد و در حال پردازش است.',
				'body_id' => '',
				'args'    => '',
			],
			'on-hold'          => [
				'enabled' => false,
				'text'    => 'مشتری گرامی {customer_name}، سفارش #{order_id} در وضعیت معلق قرار گرفت.',
				'body_id' => '',
				'args'    => '',
			],
			'completed'        => [
				'enabled' => false,
				'text'    => 'مشتری گرامی {customer_name}، سفارش #{order_id} تکمیل شد و فرآیند ارسال آغاز گردید. با تشکر.',
				'body_id' => '',
				'args'    => '',
			],
			'cancelled'        => [
				'enabled' => false,
				'text'    => 'مشتری گرامی، سفارش #{order_id} لغو شد.',
				'body_id' => '',
				'args'    => '',
			],
			'refunded'         => [
				'enabled' => false,
				'text'    => 'مشتری گرامی، سفارش #{order_id} مرجوع گردید و مبلغ آن مسترد شد.',
				'body_id' => '',
				'args'    => '',
			],
			'failed'           => [
				'enabled' => false,
				'text'    => 'پرداخت سفارش #{order_id} ناموفق بود و لغو گردید.',
				'body_id' => '',
				'args'    => '',
			],
			// Admin templates.
			'admin_pending'    => [
				'enabled' => false,
				'text'    => 'سفارش #{order_id} ثبت شد و در انتظار پرداخت است.',
				'body_id' => '',
				'args'    => '',
			],
			'admin_processing' => [
				'enabled' => false,
				'text'    => 'سفارش #{order_id} پرداخت شد و در حال پردازش است.',
				'body_id' => '',
				'args'    => '',
			],
			'admin_on-hold'    => [
				'enabled' => false,
				'text'    => 'سفارش #{order_id} به وضعیت معلق تغییر یافت.',
				'body_id' => '',
				'args'    => '',
			],
			'admin_completed'  => [
				'enabled' => false,
				'text'    => 'سفارش #{order_id} تکمیل و ارسال شد.',
				'body_id' => '',
				'args'    => '',
			],
			'admin_cancelled'  => [
				'enabled' => false,
				'text'    => 'سفارش #{order_id} لغو شد.',
				'body_id' => '',
				'args'    => '',
			],
			'admin_refunded'   => [
				'enabled' => false,
				'text'    => 'سفارش #{order_id} مرجوع شد.',
				'body_id' => '',
				'args'    => '',
			],
			'admin_failed'     => [
				'enabled' => false,
				'text'    => 'سفارش #{order_id} پرداخت ناموفق داشت.',
				'body_id' => '',
				'args'    => '',
			],
			'admin_new_order'  => [
				'enabled' => false,
				'text'    => 'سفارش جدید #{order_id} به مبلغ {order_total} تومان ثبت شد.',
				'body_id' => '',
				'args'    => '',
			],
			'admin_low_stock'  => [
				'enabled' => false,
				'text'    => 'هشدار کمبود موجودی: محصول {product_name} به تعداد {stock_qty} رسیده است.',
				'body_id' => '',
				'args'    => '',
			],
		];

		$saved = get_option( 'wsm_sms_templates', [] );
		$merged = array_replace_recursive( $defaults, (array) $saved );

		// Backward compatibility fallback for new_order and low_stock
		if ( isset( $saved['new_order'] ) && ! isset( $saved['admin_new_order'] ) ) {
			$merged['admin_new_order'] = $saved['new_order'];
		}
		if ( isset( $saved['low_stock'] ) && ! isset( $saved['admin_low_stock'] ) ) {
			$merged['admin_low_stock'] = $saved['low_stock'];
		}

		return $merged;
	}

	/**
	 * Save updated SMS templates.
	 *
	 * @param array $templates New templates settings.
	 * @return bool True if option value changed, else false.
	 */
	public static function update_templates( array $templates ): bool {
		$existing = get_option( 'wsm_sms_templates', [] );
		$sanitized = is_array( $existing ) ? $existing : [];

		$default_keys = [
			'pending', 'processing', 'on-hold', 'completed', 'cancelled', 'refunded', 'failed',
			'admin_pending', 'admin_processing', 'admin_on-hold', 'admin_completed', 'admin_cancelled', 'admin_refunded', 'admin_failed',
			'admin_new_order', 'admin_low_stock'
		];

		foreach ( $default_keys as $key ) {
			if ( isset( $templates[ $key ] ) ) {
				$sanitized[ $key ] = [
					'enabled' => (bool) ( $templates[ $key ]['enabled'] ?? false ),
					'text'    => sanitize_textarea_field( $templates[ $key ]['text'] ?? '' ),
					'body_id' => sanitize_text_field( $templates[ $key ]['body_id'] ?? '' ),
					'args'    => sanitize_text_field( $templates[ $key ]['args'] ?? '' ),
				];
			}
		}

		// Also preserve original new_order/low_stock keys for compatibility
		if ( isset( $sanitized['admin_new_order'] ) ) {
			$sanitized['new_order'] = $sanitized['admin_new_order'];
		}
		if ( isset( $sanitized['admin_low_stock'] ) ) {
			$sanitized['low_stock'] = $sanitized['admin_low_stock'];
		}

		return update_option( 'wsm_sms_templates', $sanitized );
	}
}

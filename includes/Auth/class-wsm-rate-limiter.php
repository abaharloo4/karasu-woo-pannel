<?php
/**
 * Rate Limiter for custom login page
 *
 * @package KarasuWooPannel
 * @version 1.0.6
 * @date 2026-06-23
 */

namespace WooStoreManager\Auth;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class WSM_Rate_Limiter
 */
class WSM_Rate_Limiter {

	/**
	 * Maximum allowed failed attempts before lockout.
	 */
	private const MAX_ATTEMPTS = 5;

	/**
	 * Lockout period duration in minutes.
	 */
	private const LOCKOUT_MINUTES = 30;

	/**
	 * Time window to monitor attempts.
	 */
	private const WINDOW_MINUTES = 15;

	/**
	 * Check if the given IP address is currently blocked.
	 *
	 * @param string $ip Client IP.
	 * @return bool True if blocked.
	 */
	public function is_blocked( string $ip ): bool {
		global $wpdb;
		$table = $wpdb->prefix . 'wsm_login_attempts';

		$result = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT is_blocked, blocked_until FROM {$table}
				 WHERE ip_address = %s
				 ORDER BY id DESC LIMIT 1",
				$ip
			)
		);

		if ( ! $result ) {
			return false;
		}

		if ( $result->is_blocked && strtotime( $result->blocked_until . ' UTC' ) > time() ) {
			return true;
		}

		// If block duration has expired, reset attempt history.
		if ( $result->is_blocked ) {
			$this->reset( $ip );
		}

		return false;
	}

	/**
	 * Record a failed login attempt for the given IP.
	 *
	 * @param string $ip Client IP.
	 */
	public function record_attempt( string $ip ): void {
		global $wpdb;
		$table = $wpdb->prefix . 'wsm_login_attempts';

		$wpdb->insert(
			$table,
			[
				'ip_address'   => $ip,
				'attempt_time' => current_time( 'mysql', true ), // Store as UTC
				'is_blocked'   => 0,
			],
			[ '%s', '%s', '%d' ]
		);

		// Count failed attempts in the window.
		$attempts = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table}
				 WHERE ip_address = %s
				 AND attempt_time > DATE_SUB(UTC_TIMESTAMP(), INTERVAL %d MINUTE)
				 AND is_blocked = 0",
				$ip,
				self::WINDOW_MINUTES
			)
		);

		if ( (int) $attempts >= self::MAX_ATTEMPTS ) {
			$this->block( $ip );
		}
	}

	/**
	 * Lock out the IP.
	 *
	 * @param string $ip Client IP.
	 */
	private function block( string $ip ): void {
		global $wpdb;
		$table = $wpdb->prefix . 'wsm_login_attempts';

		// Block expiration in UTC GMT time.
		$blocked_until = gmdate( 'Y-m-d H:i:s', time() + ( self::LOCKOUT_MINUTES * MINUTE_IN_SECONDS ) );

		$wpdb->update(
			$table,
			[ 'is_blocked' => 1, 'blocked_until' => $blocked_until ],
			[ 'ip_address' => $ip ],
			[ '%d', '%s' ],
			[ '%s' ]
		);
	}

	/**
	 * Get the remaining lockout period in minutes for blocked IP.
	 *
	 * @param string $ip Client IP.
	 * @return int Minutes remaining, or 0.
	 */
	public function get_remaining_lockout( string $ip ): int {
		global $wpdb;
		$table = $wpdb->prefix . 'wsm_login_attempts';

		$blocked_until = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT blocked_until FROM {$table}
				 WHERE ip_address = %s AND is_blocked = 1
				 ORDER BY id DESC LIMIT 1",
				$ip
			)
		);

		if ( ! $blocked_until ) {
			return 0;
		}

		$diff = strtotime( $blocked_until . ' UTC' ) - time();
		return max( 0, (int) ceil( $diff / 60 ) );
	}

	/**
	 * Clear login attempt history for the IP.
	 *
	 * @param string $ip Client IP.
	 */
	public function reset( string $ip ): void {
		global $wpdb;
		$wpdb->delete( $wpdb->prefix . 'wsm_login_attempts', [ 'ip_address' => $ip ], [ '%s' ] );
	}

	/**
	 * Get the real client IP, considering proxies and Cloudflare.
	 *
	 * @return string Client IP.
	 */
	public static function get_client_ip(): string {
		$ip_keys = [ 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' ];
		foreach ( $ip_keys as $key ) {
			if ( ! empty( $_SERVER[ $key ] ) ) {
				$ips = explode( ',', sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) ) );
				return trim( $ips[0] );
			}
		}
		return '0.0.0.0';
	}
}

<?php
/**
 * Rate Limiter for custom login page
 *
 * @package KarasuWooPannel
 * @version 1.1.1
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
	 * Check if the given IP address or username is currently blocked.
	 *
	 * @param string $ip Client IP.
	 * @param string $username Client username.
	 * @return bool True if blocked.
	 */
	public function is_blocked( string $ip, string $username = '' ): bool {
		global $wpdb;
		$table = $wpdb->prefix . 'wsm_login_attempts';

		// 1. Check if IP is blocked.
		$result_ip = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT is_blocked, blocked_until FROM {$table}
				 WHERE ip_address = %s
				 ORDER BY id DESC LIMIT 1",
				$ip
			)
		);

		if ( $result_ip && $result_ip->is_blocked && strtotime( $result_ip->blocked_until . ' UTC' ) > time() ) {
			return true;
		}

		if ( $result_ip && $result_ip->is_blocked ) {
			$this->reset( $ip, '' );
		}

		// 2. Check if username is blocked.
		if ( ! empty( $username ) ) {
			$result_user = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT is_blocked, blocked_until FROM {$table}
					 WHERE username = %s
					 ORDER BY id DESC LIMIT 1",
					$username
				)
			);

			if ( $result_user && $result_user->is_blocked && strtotime( $result_user->blocked_until . ' UTC' ) > time() ) {
				return true;
			}

			if ( $result_user && $result_user->is_blocked ) {
				$this->reset( '', $username );
			}
		}

		return false;
	}

	/**
	 * Record a failed login attempt.
	 *
	 * @param string $ip Client IP.
	 * @param string $username Client username.
	 */
	public function record_attempt( string $ip, string $username = '' ): void {
		global $wpdb;
		$table = $wpdb->prefix . 'wsm_login_attempts';

		$wpdb->insert(
			$table,
			[
				'ip_address'   => $ip,
				'username'     => ! empty( $username ) ? $username : null,
				'attempt_time' => current_time( 'mysql', true ), // Store as UTC
				'is_blocked'   => 0,
			],
			[ '%s', '%s', '%s', '%d' ]
		);

		// Count failed attempts for IP in the window.
		$attempts_ip = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table}
				 WHERE ip_address = %s
				 AND attempt_time > DATE_SUB(UTC_TIMESTAMP(), INTERVAL %d MINUTE)
				 AND is_blocked = 0",
				$ip,
				self::WINDOW_MINUTES
			)
		);

		if ( $attempts_ip >= self::MAX_ATTEMPTS ) {
			$this->block( $ip, '' );
		}

		// Count failed attempts for username in the window.
		if ( ! empty( $username ) ) {
			$attempts_user = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$table}
					 WHERE username = %s
					 AND attempt_time > DATE_SUB(UTC_TIMESTAMP(), INTERVAL %d MINUTE)
					 AND is_blocked = 0",
					$username,
					self::WINDOW_MINUTES
				)
			);

			if ( $attempts_user >= self::MAX_ATTEMPTS ) {
				$this->block( '', $username );
			}
		}
	}

	/**
	 * Lock out the IP or username.
	 *
	 * @param string $ip Client IP.
	 * @param string $username Client username.
	 */
	private function block( string $ip, string $username = '' ): void {
		global $wpdb;
		$table = $wpdb->prefix . 'wsm_login_attempts';

		// Block expiration in UTC GMT time.
		$blocked_until = gmdate( 'Y-m-d H:i:s', time() + ( self::LOCKOUT_MINUTES * MINUTE_IN_SECONDS ) );

		if ( ! empty( $ip ) ) {
			$wpdb->update(
				$table,
				[ 'is_blocked' => 1, 'blocked_until' => $blocked_until ],
				[ 'ip_address' => $ip ],
				[ '%d', '%s' ],
				[ '%s' ]
			);
		}

		if ( ! empty( $username ) ) {
			$wpdb->update(
				$table,
				[ 'is_blocked' => 1, 'blocked_until' => $blocked_until ],
				[ 'username' => $username ],
				[ '%d', '%s' ],
				[ '%s' ]
			);
		}
	}

	/**
	 * Get the remaining lockout period in minutes for blocked IP or username.
	 *
	 * @param string $ip Client IP.
	 * @param string $username Client username.
	 * @return int Minutes remaining, or 0.
	 */
	public function get_remaining_lockout( string $ip, string $username = '' ): int {
		global $wpdb;
		$table = $wpdb->prefix . 'wsm_login_attempts';

		$blocked_until = null;

		if ( ! empty( $ip ) ) {
			$blocked_until = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT blocked_until FROM {$table}
					 WHERE ip_address = %s AND is_blocked = 1
					 ORDER BY id DESC LIMIT 1",
					$ip
				)
			);
		}

		if ( ! $blocked_until && ! empty( $username ) ) {
			$blocked_until = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT blocked_until FROM {$table}
					 WHERE username = %s AND is_blocked = 1
					 ORDER BY id DESC LIMIT 1",
					$username
				)
			);
		}

		if ( ! $blocked_until ) {
			return 0;
		}

		$diff = strtotime( $blocked_until . ' UTC' ) - time();
		return max( 0, (int) ceil( $diff / 60 ) );
	}

	/**
	 * Clear login attempt history for the IP or username.
	 *
	 * @param string $ip Client IP.
	 * @param string $username Client username.
	 */
	public function reset( string $ip, string $username = '' ): void {
		global $wpdb;
		$table = $wpdb->prefix . 'wsm_login_attempts';

		if ( ! empty( $ip ) ) {
			$wpdb->delete( $table, [ 'ip_address' => $ip ], [ '%s' ] );
		}
		if ( ! empty( $username ) ) {
			$wpdb->delete( $table, [ 'username' => $username ], [ '%s' ] );
		}
	}

	/**
	 * Get the real client IP, considering proxies and Cloudflare.
	 *
	 * @return string Client IP.
	 */
	public static function get_client_ip(): string {
		if ( ! get_option( 'wsm_trust_proxy_headers' ) ) {
			return sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0' ) );
		}

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

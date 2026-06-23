<?php
/**
 * Daily Cleanup Cron Job handler
 *
 * @package KarasuWooPannel
 * @version 1.1.0
 * @date 2026-06-23
 */

namespace WooStoreManager\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class WSM_Cron
 */
class WSM_Cron {

	/**
	 * Execute daily cleanup queries.
	 */
	public function daily_cleanup(): void {
		global $wpdb;

		// 1. Clear sessions older than 30 days.
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->prefix}wsm_sessions WHERE expires_at < %s",
				current_time( 'mysql', true ) // UTC mysql date
			)
		);

		// 2. Clear login attempts older than 30 days.
		$wpdb->query(
			"DELETE FROM {$wpdb->prefix}wsm_login_attempts WHERE attempt_time < DATE_SUB(UTC_TIMESTAMP(), INTERVAL 30 DAY)"
		);

		// 3. Clear SMS logs older than retention days (default 180 days).
		$retention_days = (int) get_option( 'wsm_log_retention_days', 180 );
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->prefix}wsm_sms_log WHERE sent_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
				$retention_days
			)
		);
	}
}

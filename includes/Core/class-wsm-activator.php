<?php
/**
 * Plugin Activation Logic
 *
 * @package KarasuWooPannel
 * @version 1.0.6
 * @date 2026-06-23
 */

namespace WooStoreManager\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class WSM_Activator
 */
class WSM_Activator {

	/**
	 * Run on plugin activation.
	 */
	public static function activate(): void {
		// 1. Check WooCommerce dependency.
		if ( ! class_exists( 'WooCommerce' ) ) {
			deactivate_plugins( plugin_basename( WSM_PLUGIN_FILE ) );
			wp_die(
				esc_html__( 'برای فعال‌سازی این افزونه، ابتدا باید افزونه ووکامرس را نصب و فعال کنید.', 'karasu-woo-pannel' ),
				__( 'خطا در فعال‌سازی افزونه', 'karasu-woo-pannel' ),
				[ 'back_link' => true ]
			);
		}

		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		// SQL for SMS log table.
		$sql_sms_log = "CREATE TABLE {$wpdb->prefix}wsm_sms_log (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			event_type varchar(50) NOT NULL,
			recipient varchar(20) NOT NULL,
			message text NOT NULL,
			status tinyint(1) NOT NULL DEFAULT 0,
			api_response varchar(255) DEFAULT NULL,
			related_id bigint(20) unsigned DEFAULT NULL,
			sent_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			KEY event_type (event_type),
			KEY sent_at (sent_at)
		) $charset_collate;";

		// SQL for Rate Limiting login attempts table.
		$sql_login_attempts = "CREATE TABLE {$wpdb->prefix}wsm_login_attempts (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			ip_address varchar(45) NOT NULL,
			attempt_time datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			is_blocked tinyint(1) NOT NULL DEFAULT 0,
			blocked_until datetime DEFAULT NULL,
			PRIMARY KEY  (id),
			KEY ip_address (ip_address),
			KEY attempt_time (attempt_time)
		) $charset_collate;";

		// SQL for custom sessions table.
		$sql_sessions = "CREATE TABLE {$wpdb->prefix}wsm_sessions (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			session_id varchar(64) NOT NULL,
			user_id bigint(20) unsigned NOT NULL,
			created_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			expires_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			ip_address varchar(45) NOT NULL,
			user_agent varchar(255) NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY session_id (session_id),
			KEY user_id (user_id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql_sms_log );
		dbDelta( $sql_login_attempts );
		dbDelta( $sql_sessions );

		// Store database schema version.
		update_option( 'wsm_db_version', WSM_VERSION );

		// Register Custom User Role.
		\WooStoreManager\Auth\WSM_Roles::create_role();

		// Register Rewrite Rules.
		$rewrite = new \WooStoreManager\Router\WSM_Rewrite();
		$rewrite->add_rewrite_rules();

		// Flush rewrite rules.
		flush_rewrite_rules();
	}
}

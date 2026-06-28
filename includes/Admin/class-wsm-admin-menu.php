<?php
/**
 * WordPress Admin Menu Registry
 *
 * @package KarasuWooPannel
 * @version 1.1.1
 * @date 2026-06-23
 */

namespace WooStoreManager\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class WSM_Admin_Menu
 */
class WSM_Admin_Menu {

	/**
	 * Register submenu options page.
	 */
	/**
	 * Register submenu options page.
	 */
	public function add_admin_menu(): void {
		add_menu_page(
			__( 'تنظیمات پنل مدیریت کاراسو', 'karasu-woo-pannel' ),
			__( 'پنل مدیریت کاراسو', 'karasu-woo-pannel' ),
			'manage_options',
			'wsm_settings',
			[ $this, 'render_settings_page' ],
			'dashicons-store',
			56
		);
	}

	/**
	 * Process custom POST requests for user capabilities and SMS templates.
	 */
	public function handle_post_actions(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// 1. Save User Capabilities
		if ( isset( $_POST['wsm_save_user_caps'] ) ) {
			check_admin_referer( 'wsm_save_user_caps_action', 'wsm_save_user_caps_nonce' );

			// Handle adding a new user to the managers list
			if ( ! empty( $_POST['wsm_add_user_id'] ) ) {
				$new_user_id = absint( $_POST['wsm_add_user_id'] );
				$new_user = get_userdata( $new_user_id );
				if ( $new_user ) {
					$new_user->set_role( 'shop_manager_custom' );
				}
			}

			// Handle updating existing users
			if ( ! empty( $_POST['wsm_users'] ) && is_array( $_POST['wsm_users'] ) ) {
				foreach ( $_POST['wsm_users'] as $u_id => $data ) {
					$u_id = absint( $u_id );
					$user_obj = get_userdata( $u_id );
					if ( ! $user_obj ) {
						continue;
					}

					// Update Role
					if ( isset( $data['role'] ) ) {
						$new_role = sanitize_text_field( $data['role'] );
						if ( in_array( $new_role, [ 'administrator', 'shop_manager', 'shop_manager_custom', 'subscriber', 'contributor', 'author', 'editor' ], true ) ) {
							// Avoid demoting the current logged-in user if they are an administrator
							if ( get_current_user_id() !== $u_id || 'administrator' === $new_role ) {
								$user_obj->set_role( $new_role );
							}
						}
					}

					// Update Capabilities
					$caps = [
						'wsm_access_panel',
						'wsm_manage_orders',
						'wsm_manage_products',
						'wsm_manage_coupons',
						'wsm_view_reports',
						'wsm_manage_sms',
					];

					foreach ( $caps as $cap ) {
						if ( ! empty( $data['caps'][ $cap ] ) ) {
							$user_obj->add_cap( $cap );
						} else {
							// Do not remove access_panel from current user if they are admin
							if ( get_current_user_id() === $u_id && 'wsm_access_panel' === $cap ) {
								continue;
							}
							$user_obj->remove_cap( $cap );
						}
					}
				}
			}

			// Redirect to prevent form resubmission
			wp_safe_redirect( add_query_arg( [ 'page' => 'wsm_settings', 'settings-updated' => 'true', 'tab' => 'wsm-tab-users' ], admin_url( 'admin.php' ) ) );
			exit;
		}

		// 2. Save SMS Templates
		if ( isset( $_POST['wsm_save_sms_templates'] ) ) {
			check_admin_referer( 'wsm_save_sms_templates_action', 'wsm_save_sms_templates_nonce' );

			if ( isset( $_POST['wsm_templates'] ) && is_array( $_POST['wsm_templates'] ) ) {
				if ( class_exists( '\WooStoreManager\Services\WSM_Sms_Service' ) ) {
					\WooStoreManager\Services\WSM_Sms_Service::update_templates( $_POST['wsm_templates'] );
				}
			}

			// Redirect to prevent form resubmission
			$redirect_tab = isset( $_POST['wsm_redirect_tab'] ) ? sanitize_key( $_POST['wsm_redirect_tab'] ) : 'wsm-tab-templates-customer';
			wp_safe_redirect( add_query_arg( [ 'page' => 'wsm_settings', 'settings-updated' => 'true', 'tab' => $redirect_tab ], admin_url( 'admin.php' ) ) );
			exit;
		}

		// 3. Clear Error Logs
		if ( isset( $_POST['wsm_clear_error_logs'] ) ) {
			check_admin_referer( 'wsm_clear_error_logs_action', 'wsm_clear_error_logs_nonce' );
			wsm_clear_error_logs();
			wp_safe_redirect( add_query_arg( [ 'page' => 'wsm_settings', 'settings-updated' => 'true', 'tab' => 'wsm-tab-logs' ], admin_url( 'admin.php' ) ) );
			exit;
		}

		// 4. Clear SMS Logs
		if ( isset( $_POST['wsm_clear_sms_logs'] ) ) {
			check_admin_referer( 'wsm_clear_sms_logs_action', 'wsm_clear_sms_logs_nonce' );
			global $wpdb;
			$sms_logs_table = $wpdb->prefix . 'wsm_sms_log';
			if ( $wpdb->get_var( "SHOW TABLES LIKE '$sms_logs_table'" ) === $sms_logs_table ) {
				$wpdb->query( "DELETE FROM $sms_logs_table" );
			}
			wp_safe_redirect( add_query_arg( [ 'page' => 'wsm_settings', 'settings-updated' => 'true', 'tab' => 'wsm-tab-logs' ], admin_url( 'admin.php' ) ) );
			exit;
		}
	}

	/**
	 * Render options page HTML form with premium aesthetics and tabbed layout.
	 */
	public function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$panel_slug     = get_option( 'wsm_panel_slug', 'store-admin' );
		$session_life   = get_option( 'wsm_session_lifetime', 8 );
		$stock_thresh   = get_option( 'wsm_low_stock_threshold', 5 );
		
		$admin_mobile   = get_option( 'wsm_admin_mobile', '' );
		$sms_username   = get_option( 'wsm_sms_username', '' );
		$sms_password   = ''; // Always display SMS password empty in the form for security.
		$sms_from_line  = get_option( 'wsm_sms_from_line', '' );

		$trust_proxies  = get_option( 'wsm_trust_proxy_headers' ) ? 'checked' : '';
		$log_retention  = (int) get_option( 'wsm_log_retention_days', 180 );

		$evt_new_order    = get_option( 'wsm_sms_evt_new_order' ) ? 'checked' : '';
		$evt_order_status = get_option( 'wsm_sms_evt_order_status' ) ? 'checked' : '';
		$evt_low_stock    = get_option( 'wsm_sms_evt_low_stock' ) ? 'checked' : '';

		$api_url = rest_url( 'wsm/v1' );
		$nonce   = wp_create_nonce( 'wp_rest' );

		$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'wsm-tab-status';
		?>
		<style>
			.wsm-settings-wrap {
				font-family: 'Vazirmatn', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
				direction: rtl;
				max-width: 900px;
				margin: 30px auto;
				background: #0f172a;
				color: #cbd5e1;
				border-radius: 24px;
				border: 1px solid #1e293b;
				box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
				overflow: hidden;
			}
			.wsm-settings-header {
				background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 100%);
				padding: 30px;
				border-bottom: 1px solid #1e293b;
				display: flex;
				justify-content: space-between;
				align-items: center;
				flex-wrap: wrap;
				gap: 15px;
			}
			.wsm-title-area h1 {
				color: #ffffff;
				font-size: 24px;
				font-weight: 800;
				margin: 0 0 5px 0;
				background: linear-gradient(to right, #818cf8, #22d3ee);
				-webkit-background-clip: text;
				-webkit-text-transparent: true;
				color: transparent;
			}
			.wsm-title-area p {
				margin: 0;
				color: #94a3b8;
				font-size: 13px;
			}
			.wsm-launch-btn {
				background: linear-gradient(to right, #4f46e5, #6366f1);
				color: #ffffff !important;
				text-decoration: none !important;
				padding: 12px 24px;
				border-radius: 14px;
				font-weight: 700;
				font-size: 13px;
				transition: all 0.2s ease;
				box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
			}
			.wsm-launch-btn:hover {
				transform: translateY(-2px);
				box-shadow: 0 6px 16px rgba(79, 70, 229, 0.4);
				background: linear-gradient(to right, #6366f1, #818cf8);
			}
			.wsm-settings-container {
				display: flex;
				flex-direction: row;
				min-height: 550px;
				background: #0f172a;
			}
			@media(max-width: 768px) {
				.wsm-settings-container {
					flex-direction: column;
				}
				.wsm-settings-sidebar {
					width: 100% !important;
					border-left: none !important;
					border-bottom: 1px solid #1e293b;
				}
			}
			.wsm-settings-sidebar {
				width: 260px;
				background: #0a0f1d;
				border-left: 1px solid #1e293b;
				display: flex;
				flex-direction: column;
				padding: 15px 0;
			}
			.wsm-tabs-nav {
				display: flex;
				flex-direction: column;
				background: transparent;
				padding: 0;
			}
			.wsm-tab-link {
				color: #94a3b8;
				padding: 14px 20px;
				cursor: pointer;
				font-weight: 700;
				font-size: 13px;
				border-right: 3px solid transparent;
				transition: all 0.2s ease;
				display: flex;
				align-items: center;
				gap: 12px;
				text-align: right;
			}
			.wsm-tab-link:hover {
				color: #cbd5e1;
				background: rgba(30, 41, 59, 0.3);
			}
			.wsm-tab-link.active {
				color: #818cf8;
				background: rgba(99, 102, 241, 0.05);
				border-right-color: #818cf8;
			}
			.wsm-tab-link span {
				font-size: 16px;
			}
			.wsm-settings-content {
				flex: 1;
				padding: 35px;
				background: #0f172a;
			}
			.wsm-tab-content {
				display: none;
			}
			.wsm-tab-content.active {
				display: block;
			}
			.wsm-form-grid {
				display: grid;
				grid-template-columns: 1fr;
				gap: 25px;
			}
			@media(min-width: 600px) {
				.wsm-form-grid {
					grid-template-columns: 1fr 1fr;
				}
				.wsm-full-width {
					grid-column: span 2;
				}
			}
			.wsm-field-group {
				display: flex;
				flex-direction: column;
				gap: 8px;
			}
			.wsm-field-group label {
				font-weight: 700;
				font-size: 13px;
				color: #94a3b8;
				text-transform: uppercase;
				letter-spacing: 0.05em;
			}
			.wsm-input-text {
				background: #020617;
				border: 1px solid #1e293b;
				color: #f1f5f9;
				padding: 12px 16px;
				border-radius: 14px;
				font-size: 14px;
				transition: all 0.2s ease;
			}
			.wsm-input-text:focus {
				border-color: #6366f1;
				box-shadow: 0 0 0 1px #6366f1;
				outline: none;
			}
			.wsm-field-desc {
				margin: 0;
				font-size: 12px;
				color: #64748b;
			}
			.wsm-card {
				background: #1e293b/30;
				border: 1px solid #1e293b;
				border-radius: 18px;
				padding: 24px;
				margin-bottom: 25px;
			}
			.wsm-card h3 {
				margin: 0 0 15px 0;
				color: #f1f5f9;
				font-size: 15px;
				font-weight: 700;
			}
			.wsm-checkbox-label {
				display: flex;
				align-items: center;
				gap: 10px;
				font-size: 14px;
				color: #cbd5e1;
				cursor: pointer;
				padding: 8px 0;
			}
			.wsm-checkbox-label input {
				margin: 0;
				width: 18px;
				height: 18px;
				border-radius: 6px;
				border: 1px solid #1e293b;
				background: #020617;
				cursor: pointer;
			}
			.wsm-submit-area {
				border-top: 1px solid #1e293b;
				padding: 30px 40px;
				display: flex;
				justify-content: flex-end;
				background: #0b1329;
			}
			.wsm-save-btn {
				background: #4f46e5;
				color: #fff;
				border: none;
				padding: 14px 35px;
				font-weight: 700;
				font-size: 14px;
				border-radius: 14px;
				cursor: pointer;
				transition: all 0.2s ease;
			}
			.wsm-save-btn:hover {
				background: #6366f1;
			}
			/* Test SMS Section */
			.wsm-test-area {
				border-top: 1px solid #1e293b;
				margin-top: 30px;
				padding-top: 25px;
			}
			.wsm-test-row {
				display: flex;
				gap: 10px;
				margin-top: 15px;
			}
			.wsm-test-btn {
				background: #10b981;
				color: #fff;
				border: none;
				padding: 10px 20px;
				border-radius: 12px;
				font-weight: 700;
				font-size: 13px;
				cursor: pointer;
				transition: all 0.2s ease;
			}
			.wsm-test-btn:hover {
				background: #059669;
			}
			.wsm-alert {
				margin-top: 15px;
				padding: 12px 16px;
				border-radius: 12px;
				font-size: 13px;
				display: none;
			}
			.wsm-alert-success {
				background: rgba(16, 185, 129, 0.1);
				border: 1px solid rgba(16, 185, 129, 0.2);
				color: #34d399;
			}
			.wsm-alert-error {
				background: rgba(239, 68, 68, 0.1);
				border: 1px solid rgba(239, 68, 68, 0.2);
				color: #f87171;
			}
			.wsm-var-badge {
				background: #020617;
				border: 1px solid #1e293b;
				color: #818cf8;
				padding: 4px 8px;
				border-radius: 8px;
				font-size: 11px;
				cursor: pointer;
				transition: all 0.2s ease;
				font-family: inherit;
				font-weight: 600;
			}
			.wsm-var-badge:hover {
				background: #1e293b;
				color: #22d3ee;
				border-color: #3b82f6;
			}
		</style>

		<div class="wsm-settings-wrap">
			<div class="wsm-settings-header">
				<div class="wsm-title-area">
					<h1>تنظیمات افزونه پنل مدیریت کاراسو</h1>
					<p>پیکربندی پنل مدیریت اختصاصی، درگاه پیامک و سطح دسترسی کاربران</p>
				</div>
				<a href="<?php echo esc_url( home_url( '/' . $panel_slug ) ); ?>" class="wsm-launch-btn" target="_blank">
					ورود به پنل اختصاصی فروشگاه
				</a>
			</div>

			<div class="wsm-settings-container">
				<!-- Right Sidebar -->
				<div class="wsm-settings-sidebar">
					<div class="wsm-tabs-nav">
						<div class="wsm-tab-link <?php echo 'wsm-tab-status' === $active_tab ? 'active' : ''; ?>" onclick="wsmSwitchTab(event, 'wsm-tab-status')">
							<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px; display: inline-block; vertical-align: middle;"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> وضعیت و معرفی افزونه
						</div>
						<div class="wsm-tab-link <?php echo 'wsm-tab-general' === $active_tab ? 'active' : ''; ?>" onclick="wsmSwitchTab(event, 'wsm-tab-general')">
							<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px; display: inline-block; vertical-align: middle;"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg> تنظیمات عمومی پنل
						</div>
						<div class="wsm-tab-link <?php echo 'wsm-tab-sms' === $active_tab ? 'active' : ''; ?>" onclick="wsmSwitchTab(event, 'wsm-tab-sms')">
							<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px; display: inline-block; vertical-align: middle;"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg> درگاه پیامک (ملی‌پیامک)
						</div>
						<div class="wsm-tab-link <?php echo 'wsm-tab-templates-customer' === $active_tab ? 'active' : ''; ?>" onclick="wsmSwitchTab(event, 'wsm-tab-templates-customer')">
							<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px; display: inline-block; vertical-align: middle;"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg> قالب‌های پیامک خریدار
						</div>
						<div class="wsm-tab-link <?php echo 'wsm-tab-templates-admin' === $active_tab ? 'active' : ''; ?>" onclick="wsmSwitchTab(event, 'wsm-tab-templates-admin')">
							<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px; display: inline-block; vertical-align: middle;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg> قالب‌های پیامک مدیر
						</div>
						<div class="wsm-tab-link <?php echo 'wsm-tab-pages' === $active_tab ? 'active' : ''; ?>" onclick="wsmSwitchTab(event, 'wsm-tab-pages')">
							<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px; display: inline-block; vertical-align: middle;"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg> سفارشی‌سازی صفحات پنل
						</div>
						<div class="wsm-tab-link <?php echo 'wsm-tab-users' === $active_tab ? 'active' : ''; ?>" onclick="wsmSwitchTab(event, 'wsm-tab-users')">
							<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px; display: inline-block; vertical-align: middle;"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg> مدیریت دسترسی کاربران
						</div>
						<div class="wsm-tab-link <?php echo 'wsm-tab-logs' === $active_tab ? 'active' : ''; ?>" onclick="wsmSwitchTab(event, 'wsm-tab-logs')">
							<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px; display: inline-block; vertical-align: middle;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg> مشاهده لاگ‌ها
						</div>
					</div>
				</div>

				<!-- Left Content Area -->
				<div class="wsm-settings-content">
				<!-- Tab 1 & Tab 2: Options.php standard form -->
				<form action="options.php" method="post" id="wsm-options-form" style="<?php echo in_array( $active_tab, [ 'wsm-tab-general', 'wsm-tab-sms', 'wsm-tab-pages' ], true ) ? '' : 'display: none;'; ?>">
					<?php settings_fields( 'wsm_settings_group' ); ?>
					
					<!-- Tab 1: General Settings -->
					<div id="wsm-tab-general" class="wsm-tab-content <?php echo 'wsm-tab-general' === $active_tab ? 'active' : ''; ?>">
						<div class="wsm-form-grid">
							<div class="wsm-field-group">
								<label for="wsm_panel_slug">آدرس URL پنل (Slug)</label>
								<input type="text" id="wsm_panel_slug" name="wsm_panel_slug" value="<?php echo esc_attr( $panel_slug ); ?>" class="wsm-input-text">
								<p class="wsm-field-desc">نشانی اختصاصی برای ورود به پنل (مثال: store-admin). پس از ذخیره، قوانین ریرایت مجددا لود می‌شوند.</p>
							</div>

							<div class="wsm-field-group">
								<label for="wsm_session_lifetime">مدت اعتبار نشست (ساعت)</label>
								<input type="number" id="wsm_session_lifetime" name="wsm_session_lifetime" value="<?php echo esc_attr( $session_life ); ?>" min="1" max="168" class="wsm-input-text">
								<p class="wsm-field-desc">مدت زمان اعتبار کوکی مدیریت فروشگاه (حداکثر ۱۶۸ ساعت معادل ۱ هفته).</p>
							</div>

							<div class="wsm-field-group">
								<label for="wsm_low_stock_threshold">آستانه هشدار موجودی کم انبار</label>
								<input type="number" id="wsm_low_stock_threshold" name="wsm_low_stock_threshold" value="<?php echo esc_attr( $stock_thresh ); ?>" min="0" class="wsm-input-text">
								<p class="wsm-field-desc">زمانی که موجودی محصولی از این عدد کمتر شود، هشدار کمبود موجودی انبار صادر می‌گردد.</p>
							</div>

							<div class="wsm-field-group">
								<label for="wsm_log_retention_days">نگهداری لاگ‌های پیامک (روز)</label>
								<input type="number" id="wsm_log_retention_days" name="wsm_log_retention_days" value="<?php echo esc_attr( $log_retention ); ?>" min="1" class="wsm-input-text">
								<p class="wsm-field-desc">تعداد روزهای نگهداری لاگ‌های پیامک قبل از حذف خودکار (پیش‌فرض ۱۸۰ روز).</p>
							</div>
							<div class="wsm-field-group wsm-full-width">
								<label class="wsm-checkbox-label">
									<input type="checkbox" name="wsm_trust_proxy_headers" value="1" <?php echo $trust_proxies; ?>>
									<span>اعتماد به هدرهای پروکسی (Trust Proxy Headers)</span>
								</label>
								<p class="wsm-field-desc">فقط در صورتی فعال کنید که سایت پشت Cloudflare یا یک Reverse Proxy معتبر است.</p>
							</div>

							<div class="wsm-field-group wsm-full-width" style="margin-top: 20px; border-top: 1px solid #1e293b; padding-top: 20px;">
								<label style="font-weight: bold; margin-bottom: 10px; display: block;">فعال‌سازی بخش‌های پنل (منوی کناری)</label>
								<p class="wsm-field-desc" style="margin-bottom: 15px;">بخش‌هایی که مایلید در پنل نمایش داده شوند و کاربران مجاز بتوانند به آن‌ها دسترسی داشته باشند را مشخص کنید.</p>
								
								<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px;">
									<?php
									$sections = [
										'dashboard'  => 'داشبورد (Dashboard)',
										'orders'     => 'سفارش‌ها (Orders)',
										'products'   => 'محصولات (Products)',
										'categories' => 'دسته‌بندی‌ها (Categories)',
										'attributes' => 'ویژگی‌ها (Attributes)',
										'brands'     => 'برندها (Brands)',
										'coupons'    => 'کدهای تخفیف (Discounts)',
										'reports'    => 'گزارش‌ها (Reports)',
										'sms'        => 'تنظیمات پیامک (SMS Settings)',
									];
									foreach ( $sections as $sec_key => $sec_label ) {
										$is_enabled = get_option( 'wsm_enable_' . $sec_key, 'yes' ) === 'yes' ? 'checked' : '';
										?>
										<label class="wsm-checkbox-label" style="display: flex; align-items: center; gap: 8px; cursor: pointer; color: #cbd5e1;">
											<input type="checkbox" name="wsm_enable_<?php echo esc_attr( $sec_key ); ?>" value="yes" <?php echo $is_enabled; ?> style="cursor: pointer;">
											<span><?php echo esc_html( $sec_label ); ?></span>
										</label>
										<?php
									}
									?>
								</div>
							</div>

							<div class="wsm-field-group wsm-full-width" style="margin-top: 20px; border-top: 1px solid #1e293b; padding-top: 20px;">
								<label style="font-weight: bold; margin-bottom: 10px; display: block;">تنظیمات رنگ‌بندی اختصاصی پنل</label>
								<p class="wsm-field-desc" style="margin-bottom: 15px;">رنگ‌های مورد نظر خود را برای استایل فرانت‌اند پنل مدیریت انتخاب کنید.</p>
								
								<div style="display: flex; gap: 40px; flex-wrap: wrap;">
									<div class="wsm-field-group" style="flex: 0 1 auto; min-width: 200px; display: flex; align-items: center; gap: 15px;">
										<div>
											<label for="wsm_primary_color" style="font-weight: 600; display: block; color: #94a3b8;">رنگ اصلی (Primary Color)</label>
											<p class="wsm-field-desc" style="margin-top: 2px;">پیش‌فرض: #6366f1 (بنفش/نیلی)</p>
										</div>
										<input type="color" id="wsm_primary_color" name="wsm_primary_color" value="<?php echo esc_attr( get_option( 'wsm_primary_color', '#6366f1' ) ); ?>" style="width: 50px; height: 35px; padding: 0; border: 1px solid #334155; border-radius: 6px; cursor: pointer; background: transparent;">
									</div>
									<div class="wsm-field-group" style="flex: 0 1 auto; min-width: 200px; display: flex; align-items: center; gap: 15px;">
										<div>
											<label for="wsm_accent_color" style="font-weight: 600; display: block; color: #94a3b8;">رنگ فرعی/آکاردئونی (Accent Color)</label>
											<p class="wsm-field-desc" style="margin-top: 2px;">پیش‌فرض: #06b6d4 (آبی/فیروزه‌ای)</p>
										</div>
										<input type="color" id="wsm_accent_color" name="wsm_accent_color" value="<?php echo esc_attr( get_option( 'wsm_accent_color', '#06b6d4' ) ); ?>" style="width: 50px; height: 35px; padding: 0; border: 1px solid #334155; border-radius: 6px; cursor: pointer; background: transparent;">
									</div>
								</div>
							</div>
						</div>
					</div>

					<!-- Tab 2: SMS Gateway Settings -->
					<div id="wsm-tab-sms" class="wsm-tab-content <?php echo 'wsm-tab-sms' === $active_tab ? 'active' : ''; ?>">
						<div class="wsm-card">
							<h3>اعتبارنامه درگاه ملی‌پیامک</h3>
							<div class="wsm-form-grid">
								<div class="wsm-field-group" style="grid-column: span 2;">
									<label for="wsm_sms_token">کلید API یا توکن ملی‌پیامک (Auth Token - توصیه شده)</label>
									<input type="text" id="wsm_sms_token" name="wsm_sms_token" value="<?php echo esc_attr( get_option( 'wsm_sms_token', '' ) ); ?>" class="wsm-input-text" placeholder="توکن دریافتی از کنسول جدید ملی‌پیامک">
									<p class="wsm-field-desc">اگر از سیستم کنسول جدید ملی‌پیامک استفاده می‌کنید، توکن خود را در اینجا وارد کنید تا نیازی به ورود نام کاربری و رمز عبور نباشد.</p>
								</div>

								<div class="wsm-field-group">
									<label for="wsm_sms_username">نام کاربری ملی‌پیامک (اختیاری در صورت استفاده از توکن)</label>
									<input type="text" id="wsm_sms_username" name="wsm_sms_username" value="<?php echo esc_attr( $sms_username ); ?>" class="wsm-input-text">
								</div>

								<div class="wsm-field-group">
									<label for="wsm_sms_password">رمز عبور ملی‌پیامک</label>
									<input type="password" id="wsm_sms_password" name="wsm_sms_password" value="" class="wsm-input-text" placeholder="برای تغییر رمز، مقدار جدید وارد کنید — برای حفظ رمز فعلی خالی بگذارید">
								</div>

								<div class="wsm-field-group">
									<label for="wsm_sms_from_line">شماره فرستنده پیامک (خط اختصاصی)</label>
									<input type="text" id="wsm_sms_from_line" name="wsm_sms_from_line" value="<?php echo esc_attr( $sms_from_line ); ?>" class="wsm-input-text" placeholder="5000xxxx">
								</div>

								<div class="wsm-field-group">
									<label for="wsm_admin_mobile">شماره موبایل مدیر (جهت هشدارهای مدیریتی)</label>
									<input type="text" id="wsm_admin_mobile" name="wsm_admin_mobile" value="<?php echo esc_attr( $admin_mobile ); ?>" class="wsm-input-text" placeholder="09xxxxxxxxx">
								</div>
							</div>
						</div>

						<div class="wsm-card">
							<h3>رویدادهای ارسال پیامک</h3>
							<p class="wsm-field-desc" style="margin-bottom: 15px;">مشخص کنید پیامک در کدام رویدادها ارسال شود.</p>
							
							<div style="display: flex; flex-direction: column; gap: 5px;">
								<label class="wsm-checkbox-label">
									<input type="checkbox" name="wsm_sms_evt_new_order" value="1" <?php echo $evt_new_order; ?>>
									<span>ارسال هشدار پیامکی سفارش جدید به شماره مدیر</span>
								</label>

								<label class="wsm-checkbox-label">
									<input type="checkbox" name="wsm_sms_evt_order_status" value="1" <?php echo $evt_order_status; ?>>
									<span>ارسال پیامک تغییر وضعیت سفارش به خریدار</span>
								</label>

								<label class="wsm-checkbox-label">
									<input type="checkbox" name="wsm_sms_evt_low_stock" value="1" <?php echo $evt_low_stock; ?>>
									<span>ارسال هشدار پیامکی اتمام موجودی انبار به شماره مدیر</span>
								</label>
							</div>
						</div>

						<!-- Test SMS validation area -->
						<div class="wsm-card wsm-test-area">
							<h3>اعتبارسنجی اتصال درگاه (تست پیامک)</h3>
							<p class="wsm-field-desc">یک شماره موبایل وارد کنید تا وضعیت اتصال به درگاه ملی‌پیامک بررسی شود.</p>
							
							<div class="wsm-test-row">
								<input type="text" id="wsm_test_phone" class="wsm-input-text" style="flex: 1; max-width: 250px;" placeholder="09xxxxxxxxx">
								<button type="button" id="wsm_send_test_btn" class="wsm-test-btn" onclick="wsmSendTestSms()">ارسال پیامک تست</button>
							</div>
							
							<div id="wsm_test_alert" class="wsm-alert"></div>
						</div>
					</div>

					<!-- Tab: Page Customizations -->
					<div id="wsm-tab-pages" class="wsm-tab-content <?php echo 'wsm-tab-pages' === $active_tab ? 'active' : ''; ?>">
						<div class="wsm-card">
							<h3>سفارشی‌سازی و ویرایش صفحات پنل مدیریت</h3>
							<p class="wsm-field-desc">در این بخش می‌توانید کدهای HTML، CSS و JS اختصاصی خود را برای هر یک از صفحات پنل مدیریت تنظیم کنید. کدهای HTML در بالای صفحه نمایش داده خواهند شد و استایل‌ها/اسکریپت‌ها به آن صفحه اعمال می‌شوند.</p>
							
							<div class="wsm-form-grid" style="grid-template-columns: 1fr; gap: 20px; margin-top: 20px;">
								<?php
								$page_fields = [
									'dashboard'  => 'صفحه داشبورد (Dashboard)',
									'orders'     => 'صفحه سفارش‌ها (Orders)',
									'products'   => 'صفحه محصولات (Products)',
									'categories' => 'صفحه دسته‌بندی‌ها (Categories)',
									'attributes' => 'صفحه ویژگی‌ها (Attributes)',
									'brands'     => 'صفحه برندها (Brands)',
									'coupons'    => 'صفحه کدهای تخفیف (Discounts)',
									'reports'    => 'صفحه گزارش‌ها (Reports)',
									'sms'        => 'صفحه تنظیمات پیامک (SMS Settings)',
									'login'      => 'صفحه ورود (Login Page)',
								];
								foreach ( $page_fields as $page_key => $page_label ) {
									$html_val = get_option( 'wsm_custom_html_' . $page_key, '' );
									$css_val  = get_option( 'wsm_custom_css_' . $page_key, '' );
									$js_val   = get_option( 'wsm_custom_js_' . $page_key, '' );
									?>
									<div class="wsm-card wsm-page-collapsible" style="border: 1px solid #1e293b; border-radius: 12px; overflow: hidden; margin-bottom: 5px; background: #0f172a; padding: 0;">
										<div class="wsm-page-header" style="padding: 15px; background: #1e293b; color: #f8fafc; font-weight: bold; cursor: pointer; display: flex; justify-content: space-between; align-items: center;" onclick="wsmTogglePageCode(this)">
											<span><?php echo esc_html( $page_label ); ?></span>
											<span class="wsm-arrow" style="transition: transform 0.2s;">▼</span>
										</div>
										<div class="wsm-page-body" style="padding: 20px; display: none; flex-direction: column; gap: 15px; border-top: 1px solid #1e293b;">
											<div class="wsm-field-group">
												<label style="font-weight: 600; margin-bottom: 6px; display: block; color: #94a3b8;">کد HTML سفارشی</label>
												<textarea name="wsm_custom_html_<?php echo esc_attr( $page_key ); ?>" rows="4" class="wsm-input-text" style="font-family: monospace; font-size: 13px; width: 100%; border-radius: 8px; border: 1px solid #334155; padding: 10px; background: #020617; color: #cbd5e1;" placeholder="<div>کدهای HTML یا متن دلخواه...</div>"><?php echo esc_textarea( $html_val ); ?></textarea>
											</div>
											<div class="wsm-field-group">
												<label style="font-weight: 600; margin-bottom: 6px; display: block; color: #94a3b8;">کد CSS سفارشی (بدون نیاز به تگ style)</label>
												<textarea name="wsm_custom_css_<?php echo esc_attr( $page_key ); ?>" rows="4" class="wsm-input-text" style="font-family: monospace; font-size: 13px; width: 100%; border-radius: 8px; border: 1px solid #334155; padding: 10px; background: #020617; color: #cbd5e1;" placeholder=".selector { color: red; }"><?php echo esc_textarea( $css_val ); ?></textarea>
											</div>
											<div class="wsm-field-group">
												<label style="font-weight: 600; margin-bottom: 6px; display: block; color: #94a3b8;">کد JS سفارشی (بدون نیاز به تگ script)</label>
												<textarea name="wsm_custom_js_<?php echo esc_attr( $page_key ); ?>" rows="4" class="wsm-input-text" style="font-family: monospace; font-size: 13px; width: 100%; border-radius: 8px; border: 1px solid #334155; padding: 10px; background: #020617; color: #cbd5e1;" placeholder="console.log('Hello');"><?php echo esc_textarea( $js_val ); ?></textarea>
											</div>
										</div>
									</div>
									<?php
								}
								?>
							</div>
						</div>
					</div>

					<div class="wsm-submit-area" style="padding: 20px 0; background: transparent; border-top: none;">
						<button type="submit" class="wsm-save-btn">ذخیره تنظیمات اصلی</button>
					</div>
				</form>

				<!-- Tab 3: SMS Customer Templates Tab -->
				<div id="wsm-tab-templates-customer" class="wsm-tab-content <?php echo 'wsm-tab-templates-customer' === $active_tab ? 'active' : ''; ?>">
					<form action="" method="post">
						<?php wp_nonce_field( 'wsm_save_sms_templates_action', 'wsm_save_sms_templates_nonce' ); ?>
						<input type="hidden" name="wsm_save_sms_templates" value="1">
						<input type="hidden" name="wsm_redirect_tab" value="wsm-tab-templates-customer">
						
						<?php
						if ( class_exists( '\WooStoreManager\Services\WSM_Sms_Service' ) ) {
							$templates = \WooStoreManager\Services\WSM_Sms_Service::get_templates();
							$customer_keys = [ 'pending', 'processing', 'on-hold', 'completed', 'cancelled', 'refunded', 'failed' ];
							$labels = [
								'pending'          => 'در انتظار پرداخت (Pending)',
								'processing'       => 'در حال پردازش / ثبت سفارش (Processing)',
								'on-hold'          => 'معلق (On Hold)',
								'completed'        => 'تکمیل شده / ارسال شده (Completed)',
								'cancelled'        => 'لغو شده (Cancelled)',
								'refunded'         => 'مسترد شده (Refunded)',
								'failed'           => 'پرداخت ناموفق (Failed)',
							];
							?>
							
							<!-- Customer Templates Card -->
							<div class="wsm-card">
								<h3>قالب‌های پیامک ارسالی به خریدار (کاربر)</h3>
								<p class="wsm-field-desc" style="margin-bottom: 20px;">متن پیامک‌های ارسالی به خریداران در زمان تغییر وضعیت سفارش را در این بخش مدیریت کنید.</p>
								
								<?php
								foreach ( $customer_keys as $key ) {
									if ( isset( $templates[ $key ] ) ) {
										$tmpl = $templates[ $key ];
										$label = $labels[ $key ] ?? $key;
										$enabled_checked = ! empty( $tmpl['enabled'] ) ? 'checked' : '';
										?>
										<div style="border-bottom: 1px solid #1e293b; padding: 20px 0; margin-bottom: 15px;">
											<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
												<span style="font-weight: 700; font-size: 14px; color: #cbd5e1;"><?php echo esc_html( $label ); ?></span>
												<label class="wsm-checkbox-label" style="padding: 0;">
													<input type="checkbox" name="wsm_templates[<?php echo esc_attr( $key ); ?>][enabled]" value="1" <?php echo $enabled_checked; ?>>
													<span style="font-size: 13px; color: #94a3b8;">فعال</span>
												</label>
											</div>
											
											<div style="margin-bottom: 8px;">
												<label style="font-size: 12px; color: #94a3b8; display: block; margin-bottom: 4px;">متن پیامک پیش‌فرض (فالبک)</label>
												<textarea name="wsm_templates[<?php echo esc_attr( $key ); ?>][text]" id="wsm-textarea-<?php echo esc_attr( $key ); ?>" rows="2" class="wsm-input-text" style="width: 100%; box-sizing: border-box; font-family: inherit; font-size: 13px;" placeholder="متن پیامک..."><?php echo esc_textarea( $tmpl['text'] ); ?></textarea>
											</div>

											<div style="margin-bottom: 10px; display: grid; grid-template-columns: 1fr 2fr; gap: 15px;">
												<div>
													<label style="font-size: 12px; color: #94a3b8; display: block; margin-bottom: 4px;">کد الگوی خدماتی (Body ID)</label>
													<input type="text" name="wsm_templates[<?php echo esc_attr( $key ); ?>][body_id]" class="wsm-input-text" style="width: 100%; font-size: 12px; padding: 6px 10px;" value="<?php echo esc_attr( $tmpl['body_id'] ?? '' ); ?>" placeholder="مثال: 12345">
												</div>
												<div>
													<label style="font-size: 12px; color: #94a3b8; display: block; margin-bottom: 4px;">متغیرهای الگو (به ترتیب با کاما جدا شوند)</label>
													<input type="text" name="wsm_templates[<?php echo esc_attr( $key ); ?>][args]" class="wsm-input-text" style="width: 100%; font-size: 12px; padding: 6px 10px; direction: ltr;" value="<?php echo esc_attr( $tmpl['args'] ?? '' ); ?>" placeholder="مثال: {customer_name},{order_id}">
												</div>
											</div>

											<div class="wsm-template-vars" style="margin-top: 8px; display: flex; gap: 6px; flex-wrap: wrap; align-items: center;">
												<span style="font-size: 11px; color: #64748b; margin-left: 4px;">متغیرها (کلیک برای درج):</span>
												<button type="button" class="wsm-var-badge" data-target="wsm-textarea-<?php echo esc_attr( $key ); ?>" data-val="{order_id}">{order_id} (شناسه)</button>
												<button type="button" class="wsm-var-badge" data-target="wsm-textarea-<?php echo esc_attr( $key ); ?>" data-val="{order_total}">{order_total} (مبلغ)</button>
												<button type="button" class="wsm-var-badge" data-target="wsm-textarea-<?php echo esc_attr( $key ); ?>" data-val="{customer_name}">{customer_name} (خریدار)</button>
												<button type="button" class="wsm-var-badge" data-target="wsm-textarea-<?php echo esc_attr( $key ); ?>" data-val="{status_label}">{status_label} (وضعیت)</button>
												<button type="button" class="wsm-var-badge" data-target="wsm-textarea-<?php echo esc_attr( $key ); ?>" data-val="{billing_phone}">{billing_phone} (موبایل)</button>
											</div>
										</div>
										<?php
									}
								}
								?>
							</div>
							<?php
						}
						?>
						
						<div class="wsm-submit-area" style="padding: 20px 0; background: transparent; border-top: none;">
							<button type="submit" class="wsm-save-btn">ذخیره قالب‌های خریدار</button>
						</div>
					</form>
				</div>

				<!-- Tab 4: SMS Admin Templates Tab -->
				<div id="wsm-tab-templates-admin" class="wsm-tab-content <?php echo 'wsm-tab-templates-admin' === $active_tab ? 'active' : ''; ?>">
					<form action="" method="post">
						<?php wp_nonce_field( 'wsm_save_sms_templates_action', 'wsm_save_sms_templates_nonce' ); ?>
						<input type="hidden" name="wsm_save_sms_templates" value="1">
						<input type="hidden" name="wsm_redirect_tab" value="wsm-tab-templates-admin">
						
						<?php
						if ( class_exists( '\WooStoreManager\Services\WSM_Sms_Service' ) ) {
							$templates = \WooStoreManager\Services\WSM_Sms_Service::get_templates();
							$admin_keys = [
								'admin_new_order',
								'admin_low_stock',
								'admin_pending',
								'admin_processing',
								'admin_on-hold',
								'admin_completed',
								'admin_cancelled',
								'admin_refunded',
								'admin_failed',
							];
							$labels = [
								'admin_new_order'  => 'سفارش جدید (New Order)',
								'admin_low_stock'  => 'کاهش موجودی انبار (Low Stock Alert)',
								'admin_pending'    => 'در انتظار پرداخت (Pending) - مدیر',
								'admin_processing' => 'در حال پردازش / ثبت سفارش (Processing) - مدیر',
								'admin_on-hold'    => 'معلق (On Hold) - مدیر',
								'admin_completed'  => 'تکمیل شده / ارسال شده (Completed) - مدیر',
								'admin_cancelled'  => 'لغو شده (Cancelled) - مدیر',
								'admin_refunded'   => 'مسترد شده (Refunded) - مدیر',
								'admin_failed'     => 'پرداخت ناموفق (Failed) - مدیر',
							];
							?>
							
							<!-- Admin Templates Card -->
							<div class="wsm-card">
								<h3>قالب‌های پیامک ارسالی به مدیر</h3>
								<p class="wsm-field-desc" style="margin-bottom: 20px;">متن پیامک‌های ارسالی به مدیر سایت در زمان رویدادهای ویژه را در این بخش مدیریت کنید.</p>
								
								<?php
								foreach ( $admin_keys as $key ) {
									if ( isset( $templates[ $key ] ) ) {
										$tmpl = $templates[ $key ];
										$label = $labels[ $key ] ?? $key;
										$enabled_checked = ! empty( $tmpl['enabled'] ) ? 'checked' : '';
										$is_low_stock = ( 'admin_low_stock' === $key );
										?>
										<div style="border-bottom: 1px solid #1e293b; padding: 20px 0; margin-bottom: 15px;">
											<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
												<span style="font-weight: 700; font-size: 14px; color: #cbd5e1;"><?php echo esc_html( $label ); ?></span>
												<label class="wsm-checkbox-label" style="padding: 0;">
													<input type="checkbox" name="wsm_templates[<?php echo esc_attr( $key ); ?>][enabled]" value="1" <?php echo $enabled_checked; ?>>
													<span style="font-size: 13px; color: #94a3b8;">فعال</span>
												</label>
											</div>
											
											<div style="margin-bottom: 8px;">
												<label style="font-size: 12px; color: #94a3b8; display: block; margin-bottom: 4px;">متن پیامک پیش‌فرض (فالبک)</label>
												<textarea name="wsm_templates[<?php echo esc_attr( $key ); ?>][text]" id="wsm-textarea-<?php echo esc_attr( $key ); ?>" rows="2" class="wsm-input-text" style="width: 100%; box-sizing: border-box; font-family: inherit; font-size: 13px;" placeholder="متن پیامک..."><?php echo esc_textarea( $tmpl['text'] ); ?></textarea>
											</div>

											<div style="margin-bottom: 10px; display: grid; grid-template-columns: 1fr 2fr; gap: 15px;">
												<div>
													<label style="font-size: 12px; color: #94a3b8; display: block; margin-bottom: 4px;">کد الگوی خدماتی (Body ID)</label>
													<input type="text" name="wsm_templates[<?php echo esc_attr( $key ); ?>][body_id]" class="wsm-input-text" style="width: 100%; font-size: 12px; padding: 6px 10px;" value="<?php echo esc_attr( $tmpl['body_id'] ?? '' ); ?>" placeholder="مثال: 12345">
												</div>
												<div>
													<label style="font-size: 12px; color: #94a3b8; display: block; margin-bottom: 4px;">متغیرهای الگو (به ترتیب با کاما جدا شوند)</label>
													<input type="text" name="wsm_templates[<?php echo esc_attr( $key ); ?>][args]" class="wsm-input-text" style="width: 100%; font-size: 12px; padding: 6px 10px; direction: ltr;" value="<?php echo esc_attr( $tmpl['args'] ?? '' ); ?>" placeholder="مثال: {customer_name},{order_id}">
												</div>
											</div>

											<?php if ( $is_low_stock ) : ?>
												<div class="wsm-template-vars" style="margin-top: 8px; display: flex; gap: 6px; flex-wrap: wrap; align-items: center;">
													<span style="font-size: 11px; color: #64748b; margin-left: 4px;">متغیرها (کلیک برای درج):</span>
													<button type="button" class="wsm-var-badge" data-target="wsm-textarea-<?php echo esc_attr( $key ); ?>" data-val="{product_id}">{product_id} (شناسه)</button>
													<button type="button" class="wsm-var-badge" data-target="wsm-textarea-<?php echo esc_attr( $key ); ?>" data-val="{product_name}">{product_name} (نام محصول)</button>
													<button type="button" class="wsm-var-badge" data-target="wsm-textarea-<?php echo esc_attr( $key ); ?>" data-val="{sku}">{sku} (کد محصول)</button>
													<button type="button" class="wsm-var-badge" data-target="wsm-textarea-<?php echo esc_attr( $key ); ?>" data-val="{stock_qty}">{stock_qty} (موجودی)</button>
												</div>
											<?php else : ?>
												<div class="wsm-template-vars" style="margin-top: 8px; display: flex; gap: 6px; flex-wrap: wrap; align-items: center;">
													<span style="font-size: 11px; color: #64748b; margin-left: 4px;">متغیرها (کلیک برای درج):</span>
													<button type="button" class="wsm-var-badge" data-target="wsm-textarea-<?php echo esc_attr( $key ); ?>" data-val="{order_id}">{order_id} (شناسه)</button>
													<button type="button" class="wsm-var-badge" data-target="wsm-textarea-<?php echo esc_attr( $key ); ?>" data-val="{order_total}">{order_total} (مبلغ)</button>
													<button type="button" class="wsm-var-badge" data-target="wsm-textarea-<?php echo esc_attr( $key ); ?>" data-val="{customer_name}">{customer_name} (خریدار)</button>
													<button type="button" class="wsm-var-badge" data-target="wsm-textarea-<?php echo esc_attr( $key ); ?>" data-val="{status_label}">{status_label} (وضعیت)</button>
													<button type="button" class="wsm-var-badge" data-target="wsm-textarea-<?php echo esc_attr( $key ); ?>" data-val="{billing_phone}">{billing_phone} (موبایل)</button>
												</div>
											<?php endif; ?>
										</div>
										<?php
									}
								}
								?>
							</div>
							<?php
						}
						?>
						
						<div class="wsm-submit-area" style="padding: 20px 0; background: transparent; border-top: none;">
							<button type="submit" class="wsm-save-btn">ذخیره قالب‌های مدیر</button>
						</div>
					</form>
				</div>

				<!-- Tab 4: User Access Control Settings Form -->
				<div id="wsm-tab-users" class="wsm-tab-content <?php echo 'wsm-tab-users' === $active_tab ? 'active' : ''; ?>">
					<form action="" method="post">
						<?php wp_nonce_field( 'wsm_save_user_caps_action', 'wsm_save_user_caps_nonce' ); ?>
						<input type="hidden" name="wsm_save_user_caps" value="1">

						<div class="wsm-card">
							<h3>افزودن مدیر فروشگاه جدید</h3>
							<p class="wsm-field-desc" style="margin-bottom: 15px;">یکی از کاربران موجود در سایت را انتخاب کنید تا به لیست مدیران فروشگاه اضافه شده و نقش مدیر اختصاصی را دریافت کند.</p>
							
							<div style="display: flex; gap: 15px; align-items: center; max-width: 500px;">
								<select name="wsm_add_user_id" class="wsm-input-text" style="flex: 1; min-width: 200px; height: 46px; background-color: #020617; border-color: #1e293b; color: #f1f5f9; border-radius: 14px;">
									<option value="">-- انتخاب کاربر برای ارتقا به مدیر فروشگاه --</option>
									<?php
									// Fetch subscribers or other roles that are not admins or managers
									$other_users = get_users( [
										'role__not_in' => [ 'administrator', 'shop_manager', 'shop_manager_custom' ],
										'number'       => 200,
									] );
									foreach ( $other_users as $ou ) {
										?>
										<option value="<?php echo esc_attr( $ou->ID ); ?>">
											<?php echo esc_html( $ou->display_name . ' (' . $ou->user_login . ' - ' . $ou->user_email . ')' ); ?>
										</option>
										<?php
									}
									?>
								</select>
							</div>
						</div>

						<div class="wsm-card">
							<h3>لیست مدیران و سطح دسترسی‌ها</h3>
							<p class="wsm-field-desc" style="margin-bottom: 20px;">نقش و سطح دسترسی هر کدام از مدیران فروشگاه به بخش‌های مختلف پنل را به طور دقیق پیکربندی کنید.</p>
							
							<style>
								.wsm-users-table {
									width: 100%;
									border-collapse: collapse;
									font-size: 13px;
									color: #cbd5e1;
								}
								.wsm-users-table th {
									background: #020617;
									color: #94a3b8;
									font-weight: 700;
									padding: 15px 10px;
									text-align: right;
									border-bottom: 2px solid #1e293b;
								}
								.wsm-users-table td {
									padding: 15px 10px;
									border-bottom: 1px solid #1e293b;
									vertical-align: middle;
								}
								.wsm-users-table tr:hover {
									background: rgba(2, 6, 23, 0.2);
								}
								.wsm-cap-check {
									display: inline-flex;
									flex-direction: column;
									align-items: center;
									gap: 4px;
									cursor: pointer;
								}
								.wsm-cap-check input {
									margin: 0 !important;
									width: 16px;
									height: 16px;
								}
								.wsm-cap-check span {
									font-size: 10px;
									color: #64748b;
								}
							</style>

							<div style="overflow-x: auto;">
								<table class="wsm-users-table">
									<thead>
										<tr>
											<th>نام کاربر</th>
											<th>نقش فعلی</th>
											<th style="text-align: center;">ورود به پنل</th>
											<th style="text-align: center;">سفارش‌ها</th>
											<th style="text-align: center;">محصولات</th>
											<th style="text-align: center;">تخفیف‌ها</th>
											<th style="text-align: center;">گزارش‌ها</th>
											<th style="text-align: center;">مدیریت پیامک</th>
										</tr>
									</thead>
									<tbody>
										<?php
										$managers = get_users( [
											'role__in' => [ 'administrator', 'shop_manager', 'shop_manager_custom' ],
										] );

										$caps_to_check = [
											'wsm_access_panel'    => 'ورود',
											'wsm_manage_orders'   => 'سفارش',
											'wsm_manage_products' => 'محصول',
											'wsm_manage_coupons'  => 'تخفیف',
											'wsm_view_reports'    => 'گزارش',
											'wsm_manage_sms'      => 'پیامک',
										];

										foreach ( $managers as $m_user ) {
											$current_role = reset( $m_user->roles );
											?>
											<tr>
												<td>
													<strong style="color: #fff;"><?php echo esc_html( $m_user->display_name ); ?></strong><br>
													<span style="color: #64748b; font-size: 11px;"><?php echo esc_html( $m_user->user_email ); ?></span>
												</td>
												<td>
													<select name="wsm_users[<?php echo esc_attr( $m_user->ID ); ?>][role]" style="background: #020617; border: 1px solid #1e293b; color: #cbd5e1; border-radius: 8px; padding: 5px 8px; font-size: 12px; font-family: inherit;">
														<option value="administrator" <?php selected( $current_role, 'administrator' ); ?>>مدیر کل (Admin)</option>
														<option value="shop_manager" <?php selected( $current_role, 'shop_manager' ); ?>>مدیر فروشگاه (WC)</option>
														<option value="shop_manager_custom" <?php selected( $current_role, 'shop_manager_custom' ); ?>>مدیر اختصاصی (Custom)</option>
														<option value="subscriber" <?php selected( $current_role, 'subscriber' ); ?>>مشترک عادی (Demote)</option>
													</select>
												</td>
												<?php
												foreach ( $caps_to_check as $cap => $label ) {
													$has_cap = user_can( $m_user, $cap ) ? 'checked' : '';
													?>
													<td style="text-align: center;">
														<label class="wsm-cap-check">
															<input type="checkbox" name="wsm_users[<?php echo esc_attr( $m_user->ID ); ?>][caps][<?php echo esc_attr( $cap ); ?>]" value="1" <?php echo $has_cap; ?>>
															<span><?php echo esc_html( $label ); ?></span>
														</label>
													</td>
													<?php
												}
												?>
											</tr>
											<?php
										}
										?>
									</tbody>
								</table>
							</div>
						</div>

						<div class="wsm-submit-area" style="padding: 20px 0; background: transparent; border-top: none;">
							<button type="submit" class="wsm-save-btn">ذخیره دسترسی کاربران</button>
						</div>
					</form>
				</div>

				<!-- Tab 7: Logs Viewer Tab -->
				<div id="wsm-tab-logs" class="wsm-tab-content <?php echo 'wsm-tab-logs' === $active_tab ? 'active' : ''; ?>">
					<div class="wsm-card">
						<h3>لاگ خطاهای افزونه (Debug & Error Logs)</h3>
						<p class="wsm-field-desc" style="margin-bottom: 15px;">خطاها، مشکلات ارتباطی با درگاه پیامک و سایر اشکالات فنی افزونه در این بخش ثبت و نمایش داده می‌شوند.</p>
						
						<form action="" method="post" style="margin-bottom: 20px;">
							<?php wp_nonce_field( 'wsm_clear_error_logs_action', 'wsm_clear_error_logs_nonce' ); ?>
							<input type="hidden" name="wsm_clear_error_logs" value="1">
							
							<textarea readonly rows="12" class="wsm-input-text" style="font-family: monospace; font-size: 13px; width: 100%; border-radius: 12px; border: 1px solid #334155; padding: 15px; background: #020617; color: #f1f5f9; line-height: 1.6; direction: ltr; text-align: left;" placeholder="No errors logged yet."><?php echo esc_textarea( wsm_get_error_logs() ); ?></textarea>
							
							<div style="margin-top: 15px; display: flex; justify-content: flex-end;">
								<button type="submit" class="wsm-test-btn" style="background: #e11d48; border-color: #f43f5e;" onclick="return confirm('آیا از پاک کردن لاگ خطاهای افزونه مطمئن هستید؟');">پاک کردن لاگ خطاها</button>
							</div>
						</form>
					</div>

					<div class="wsm-card">
						<h3>لاگ پیامک‌های ارسالی (SMS logs)</h3>
						<p class="wsm-field-desc" style="margin-bottom: 20px;">فهرست آخرین ۱۰۰ پیامک ارسال شده توسط افزونه به خریداران و مدیران فروشگاه.</p>
						
						<div style="overflow-x: auto; border: 1px solid #1e293b; border-radius: 12px; background: #0f172a;">
							<table style="width: 100%; border-collapse: collapse; font-size: 13px; text-align: right; color: #cbd5e1;">
								<thead>
									<tr style="background: #1e293b; border-bottom: 1px solid #334155; color: #f8fafc;">
										<th style="padding: 12px; text-align: center;">وضعیت</th>
										<th style="padding: 12px;">گیرنده</th>
										<th style="padding: 12px;">رویداد</th>
										<th style="padding: 12px; width: 40%;">متن پیامک</th>
										<th style="padding: 12px;">پاسخ درگاه</th>
										<th style="padding: 12px; text-align: center;">تاریخ ارسال</th>
									</tr>
								</thead>
								<tbody>
									<?php
									global $wpdb;
									$sms_logs_table = $wpdb->prefix . 'wsm_sms_log';
									$sms_logs = [];
									if ( $wpdb->get_var( "SHOW TABLES LIKE '$sms_logs_table'" ) === $sms_logs_table ) {
										$sms_logs = $wpdb->get_results( "SELECT * FROM $sms_logs_table ORDER BY sent_at DESC LIMIT 100" );
									}

									if ( empty( $sms_logs ) ) {
										echo '<tr><td colspan="6" style="padding: 25px; text-align: center; color: #64748b;">هیچ پیامکی ارسال یا لاگ نشده است.</td></tr>';
									} else {
										foreach ( $sms_logs as $log ) {
											$status_badge = $log->status 
												? '<span style="background: #065f46; color: #34d399; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: bold;">موفق</span>' 
												: '<span style="background: #991b1b; color: #fca5a5; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: bold;">ناموفق</span>';
											?>
											<tr style="border-bottom: 1px solid #1e293b; background: #0b0f19;">
												<td style="padding: 12px; text-align: center; vertical-align: middle;"><?php echo $status_badge; ?></td>
												<td style="padding: 12px; font-weight: 600; direction: ltr; text-align: right;"><?php echo esc_html( $log->recipient ); ?></td>
												<td style="padding: 12px; color: #38bdf8;"><?php echo esc_html( $log->event_type ); ?></td>
												<td style="padding: 12px; line-height: 1.6;"><?php echo esc_html( $log->message ); ?></td>
												<td style="padding: 12px; font-family: monospace; font-size: 12px; color: #94a3b8;"><?php echo esc_html( $log->api_response ); ?></td>
												<td style="padding: 12px; text-align: center; color: #64748b; font-size: 12px;"><?php echo esc_html( $log->sent_at ); ?></td>
											</tr>
											<?php
										}
									}
									?>
								</tbody>
							</table>
						</div>

						<form action="" method="post" style="margin-top: 15px; display: flex; justify-content: flex-end;">
							<?php wp_nonce_field( 'wsm_clear_sms_logs_action', 'wsm_clear_sms_logs_nonce' ); ?>
							<input type="hidden" name="wsm_clear_sms_logs" value="1">
							<button type="submit" class="wsm-test-btn" style="background: #e11d48; border-color: #f43f5e;" onclick="return confirm('آیا از پاک کردن لاگ پیامک‌های ارسالی مطمئن هستید؟');">پاک کردن لاگ پیامک‌ها</button>
						</form>
					</div>

					<div class="wsm-card" style="margin-top: 20px;">
						<h3 style="cursor: pointer; display: flex; justify-content: space-between; align-items: center; margin: 0;" onclick="wsmToggleGuide(this)">
							<span>راهنمای کدهای پاسخ و خطاهای درگاه ملی‌پیامک</span>
							<span class="wsm-guide-arrow" style="transition: transform 0.2s; font-size: 14px;">▼</span>
						</h3>
						<div class="wsm-guide-body" style="display: none; margin-top: 15px; border-top: 1px solid #1e293b; padding-top: 15px; font-size: 13px; line-height: 1.8; color: #cbd5e1;">
							<p style="margin-bottom: 15px; color: #94a3b8;">در زمان ارسال پیامک، درگاه ملی‌پیامک یک کد پاسخ (RetVal یا RetStatus) برمی‌گرداند. مقادیر بالای ۱۰۰ نشان‌دهنده موفقیت (شناسه پیامک) و مقادیر دیگر نشان‌دهنده خطا هستند:</p>
							<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; font-family: inherit;">
								<div>
									<strong style="color: #fca5a5;">کد ۳۵ / InvalidData:</strong>
									<p style="margin: 3px 0 10px 0; color: #94a3b8;">شماره موبایل گیرنده در <strong>لیست سیاه (Blacklist) مخابرات</strong> قرار دارد و دریافت پیامک‌های تبلیغاتی را مسدود کرده است. برای ارسال به این خطوط، حتماً باید از الگو در «وب‌سرویس خدماتی» استفاده کنید.</p>
									
									<strong style="color: #fca5a5;">کد ۰ یا ۱:</strong>
									<p style="margin: 3px 0 10px 0; color: #94a3b8;">نام کاربری، رمز عبور یا توکن API وارد شده اشتباه یا نامعتبر است.</p>
									
									<strong style="color: #fca5a5;">کد ۲:</strong>
									<p style="margin: 3px 0 10px 0; color: #94a3b8;">پنل کاربری ملی‌پیامک شما غیرفعال یا مسدود شده است.</p>
								</div>
								<div>
									<strong style="color: #fca5a5;">کد ۳:</strong>
									<p style="margin: 3px 0 10px 0; color: #94a3b8;">اعتبار مالی (شارژ) پنل کاربری شما کافی نیست.</p>
									
									<strong style="color: #fca5a5;">کد ۴ یا ۵:</strong>
									<p style="margin: 3px 0 10px 0; color: #94a3b8;">شماره فرستنده انتخابی (Sender Line) معتبر نیست یا به درستی تنظیم نشده است.</p>
									
									<strong style="color: #fca5a5;">کد ۶:</strong>
									<p style="margin: 3px 0 10px 0; color: #94a3b8;">فرمت شماره گیرنده نامعتبر است (باید با ۰۹ شروع شود).</p>
								</div>
							</div>
							<p style="margin-top: 15px; border-top: 1px solid #1e293b; padding-top: 10px; color: #64748b; font-size: 11px;">توجه: دکمه تست پیامک به علت ارسال پیام متنی خام، به صورت مستقیم از شماره اختصاصی شما (Promotional Line) ارسال می‌شود و در صورت بلک‌لیست بودن شماره با خطای ۳۵ مواجه می‌شوید، اما ارسال‌های خدماتی سفارش با الگو بدون مشکل ارسال خواهند شد.</p>
						</div>
					</div>
				</div>

				<!-- Tab 6: Status & About Info Tab -->
				<div id="wsm-tab-status" class="wsm-tab-content <?php echo 'wsm-tab-status' === $active_tab ? 'active' : ''; ?>">
					<div class="wsm-card">
						<h3>معرفی افزونه پنل مدیریت کاراسو</h3>
						<p class="wsm-field-desc" style="line-height: 1.8; color: #cbd5e1; font-size: 13.5px; margin-bottom: 20px;">
							افزونه <strong>پنل مدیریت کاراسو</strong> یک ابزار پیشرفته و مدرن است که مدیریت فروشگاه ووکامرس شما را متحول می‌کند. این افزونه با راه‌اندازی یک پنل مدیریت اختصاصی و سریع با زیبایی بصری مدرن، امکان مدیریت محصولات ساده و متغیر، دسته‌بندی‌ها، کوپن‌ها، بررسی دقیق سفارش‌ها و مشاهده گزارش‌های پیشرفته با تقویم شمسی جلالی را به ارمغان می‌آورد. همچنین سیستم هوشمند اطلاع‌رسانی پیامکی ملی‌پیامک خریداران و مدیران را از آخرین رویدادهای خرید آگاه می‌سازد.
						</p>
					</div>

					<div class="wsm-card">
						<h3>وضعیت سیستم و مشخصات فنی</h3>
						<style>
							.wsm-status-table {
								width: 100%;
								border-collapse: collapse;
								margin-top: 10px;
								font-size: 13px;
							}
							.wsm-status-table td {
								padding: 12px 10px;
								border-bottom: 1px solid #1e293b;
							}
							.wsm-status-table td:first-child {
								font-weight: 700;
								color: #94a3b8;
								width: 200px;
							}
							.wsm-status-badge {
								display: inline-block;
								padding: 3px 10px;
								border-radius: 6px;
								font-size: 11px;
								font-weight: bold;
							}
						</style>
						<table class="wsm-status-table">
							<tr>
								<td>نسخه افزونه</td>
								<td><span class="wsm-status-badge" style="background: rgba(99,102,241,0.15); color: #818cf8;"><?php echo esc_html( WSM_VERSION ); ?></span></td>
							</tr>
							<tr>
								<td>آدرس پنل مدیریت اختصاصی</td>
								<td><a href="<?php echo esc_url( home_url( '/' . $panel_slug ) ); ?>" target="_blank" style="color: #6366f1; text-decoration: none; font-weight: 700;"><?php echo esc_html( home_url( '/' . $panel_slug ) ); ?></a></td>
							</tr>
							<tr>
								<td>وضعیت ووکامرس (WooCommerce)</td>
								<td>
									<?php if ( class_exists( 'WooCommerce' ) ) : ?>
										<span class="wsm-status-badge" style="background: rgba(16,185,129,0.15); color: #34d399;">فعال (نسخه <?php echo esc_html( WC()->version ); ?>)</span>
									<?php else : ?>
										<span class="wsm-status-badge" style="background: rgba(239,68,68,0.15); color: #f87171;">غیرفعال / نصب نشده</span>
									<?php endif; ?>
								</td>
							</tr>
							<tr>
								<td>نسخه وردپرس</td>
								<td><?php echo esc_html( get_bloginfo( 'version' ) ); ?></td>
							</tr>
							<tr>
								<td>نسخه PHP سرور</td>
								<td><?php echo esc_html( PHP_VERSION ); ?></td>
							</tr>
							<tr>
								<td>اتصال درگاه ملی‌پیامک</td>
								<td>
									<?php
									$sms_token = get_option( 'wsm_sms_token', '' );
									$has_db_password = ! empty( get_option( 'wsm_sms_password', '' ) );
									if ( ! empty( $sms_token ) ) :
										?>
										<span class="wsm-status-badge" style="background: rgba(16,185,129,0.15); color: #34d399;">پیکربندی شده (با کلید API)</span>
									<?php elseif ( ! empty( $sms_username ) && $has_db_password ) : ?>
										<span class="wsm-status-badge" style="background: rgba(16,185,129,0.15); color: #34d399;">پیکربندی شده (نام کاربری: <?php echo esc_html( $sms_username ); ?>)</span>
									<?php else : ?>
										<span class="wsm-status-badge" style="background: rgba(239,68,68,0.15); color: #f87171;">پیکربندی نشده</span>
									<?php endif; ?>
								</td>
							</tr>
							<tr>
								<td>عضویت مدیران فروشگاه (Custom Roles)</td>
								<td>
									<?php
									$custom_managers = get_users( [ 'role' => 'shop_manager_custom' ] );
									echo esc_html( count( $custom_managers ) ) . ' کاربر با نقش مدیر اختصاصی';
									?>
								</td>
							</tr>
						</table>
					</div>
				</div>
			</div>
		</div>

		<script>
			function wsmSwitchTab(evt, tabId) {
				const contents = document.querySelectorAll('.wsm-tab-content');
				contents.forEach(c => c.classList.remove('active'));
				
				const links = document.querySelectorAll('.wsm-tab-link');
				links.forEach(l => l.classList.remove('active'));
				
				document.getElementById(tabId).classList.add('active');
				evt.currentTarget.classList.add('active');

				const optionsForm = document.getElementById('wsm-options-form');
				const templatesForm = document.getElementById('wsm-templates-form');
				
				if (tabId === 'wsm-tab-general' || tabId === 'wsm-tab-sms' || tabId === 'wsm-tab-pages') {
					if (optionsForm) optionsForm.style.display = 'block';
					if (templatesForm) templatesForm.style.display = 'none';
				} else if (tabId === 'wsm-tab-templates-customer' || tabId === 'wsm-tab-templates-admin') {
					if (optionsForm) optionsForm.style.display = 'none';
					if (templatesForm) templatesForm.style.display = 'block';
				} else {
					if (optionsForm) optionsForm.style.display = 'none';
					if (templatesForm) templatesForm.style.display = 'none';
				}
			}

			function wsmSendTestSms() {
				const phoneInput = document.getElementById('wsm_test_phone');
				const alertBox = document.getElementById('wsm_test_alert');
				const btn = document.getElementById('wsm_send_test_btn');
				
				const phone = phoneInput.value.trim();
				if (!phone) {
					wsmShowAlert('لطفا شماره موبایل را وارد نمایید.', 'error');
					return;
				}

				btn.disabled = true;
				btn.innerText = 'در حال ارسال...';
				wsmShowAlert('در حال برقراری ارتباط با درگاه ملی‌پیامک...', 'success');

				fetch('<?php echo $api_url; ?>/sms/test', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-WP-Nonce': '<?php echo $nonce; ?>'
					},
					body: JSON.stringify({
						phone: phone,
						message: 'این یک پیامک تست از بخش تنظیمات افزونه KarasuWooPannel می‌باشد.'
					})
				})
				.then(res => res.json())
				.then(data => {
					btn.disabled = false;
					btn.innerText = 'ارسال پیامک تست';
					if (data.success) {
						wsmShowAlert('پیامک تست با موفقیت ارسال گردید. اتصال برقرار است.', 'success');
					} else {
						wsmShowAlert('خطا در ارسال پیامک: ' + (data.message || 'مشکل در درگاه ارتباطی.'), 'error');
					}
				})
				.catch(err => {
					btn.disabled = false;
					btn.innerText = 'ارسال پیامک تست';
					wsmShowAlert('خطای سرور در ارسال درخواست تست.', 'error');
				});
			}

			function wsmShowAlert(msg, type) {
				const alertBox = document.getElementById('wsm_test_alert');
				alertBox.style.display = 'block';
				alertBox.innerText = msg;
				alertBox.className = 'wsm-alert wsm-alert-' + type;
			}

			document.addEventListener('DOMContentLoaded', () => {
				document.querySelectorAll('.wsm-var-badge').forEach(badge => {
					badge.addEventListener('click', (e) => {
						e.preventDefault();
						const targetId = badge.getAttribute('data-target');
						const val = badge.getAttribute('data-val');
						const textarea = document.getElementById(targetId);
						if (textarea) {
							wsmInsertAtCursor(textarea, val);
						}
					});
				});
			});

			function wsmInsertAtCursor(textarea, text) {
				if (textarea.selectionStart || textarea.selectionStart === 0) {
					var startPos = textarea.selectionStart;
					var endPos = textarea.selectionEnd;
					textarea.value = textarea.value.substring(0, startPos) + text + textarea.value.substring(endPos, textarea.value.length);
					textarea.selectionStart = startPos + text.length;
					textarea.selectionEnd = startPos + text.length;
				} else {
					textarea.value += text;
				}
				textarea.focus();
			}
			function wsmTogglePageCode(header) {
				const body = header.nextElementSibling;
				const arrow = header.querySelector('.wsm-arrow');
				if (body.style.display === 'none' || !body.style.display) {
					body.style.display = 'flex';
					arrow.style.transform = 'rotate(180deg)';
				} else {
					body.style.display = 'none';
					arrow.style.transform = 'rotate(0deg)';
				}
			}
			function wsmToggleGuide(header) {
				const body = header.nextElementSibling;
				const arrow = header.querySelector('.wsm-guide-arrow');
				if (body.style.display === 'none' || !body.style.display) {
					body.style.display = 'block';
					arrow.style.transform = 'rotate(180deg)';
				} else {
					body.style.display = 'none';
					arrow.style.transform = 'rotate(0deg)';
				}
			}
		</script>
		<?php
	}
}

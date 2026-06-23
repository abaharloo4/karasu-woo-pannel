<?php
/**
 * WordPress Admin Menu Registry
 *
 * @package KarasuWooPannel
 * @version 1.0.2
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
	public function add_admin_menu(): void {
		add_submenu_page(
			'woocommerce',
			__( 'تنظیمات KarasuWooPannel', 'karasu-woo-pannel' ),
			__( 'KarasuWooPannel', 'karasu-woo-pannel' ),
			'manage_options',
			'wsm_settings',
			[ $this, 'render_settings_page' ]
		);
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
		$sms_password   = get_option( 'wsm_sms_password', '' );
		$sms_from_line  = get_option( 'wsm_sms_from_line', '' );

		$evt_new_order    = get_option( 'wsm_sms_evt_new_order' ) ? 'checked' : '';
		$evt_order_status = get_option( 'wsm_sms_evt_order_status' ) ? 'checked' : '';
		$evt_low_stock    = get_option( 'wsm_sms_evt_low_stock' ) ? 'checked' : '';

		$api_url = rest_url( 'wsm/v1' );
		$nonce   = wp_create_nonce( 'wp_rest' );
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
			.wsm-tabs-nav {
				display: flex;
				background: #020617;
				padding: 10px 20px 0 20px;
				border-bottom: 1px solid #1e293b;
			}
			.wsm-tab-link {
				color: #94a3b8;
				padding: 15px 25px;
				cursor: pointer;
				font-weight: 700;
				font-size: 14px;
				border-bottom: 3px solid transparent;
				transition: all 0.2s ease;
			}
			.wsm-tab-link:hover {
				color: #cbd5e1;
			}
			.wsm-tab-link.active {
				color: #818cf8;
				border-bottom-color: #818cf8;
			}
			.wsm-settings-body {
				padding: 40px;
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
		</style>

		<div class="wsm-settings-wrap">
			<div class="wsm-settings-header">
				<div class="wsm-title-area">
					<h1>تنظیمات افزونه KarasuWooPannel</h1>
					<p>پیکربندی پنل مدیریت اختصاصی و یکپارچه‌سازی سامانه پیامکی ملی‌پیامک</p>
				</div>
				<a href="<?php echo esc_url( home_url( '/' . $panel_slug ) ); ?>" class="wsm-launch-btn" target="_blank">
					ورود به پنل اختصاصی فروشگاه
				</a>
			</div>

			<div class="wsm-tabs-nav">
				<div class="wsm-tab-link active" onclick="wsmSwitchTab(event, 'wsm-tab-general')">تنظیمات عمومی پنل</div>
				<div class="wsm-tab-link" onclick="wsmSwitchTab(event, 'wsm-tab-sms')">سامانه اطلاع‌رسانی پیامکی</div>
			</div>

			<form action="options.php" method="post">
				<?php settings_fields( 'wsm_settings_group' ); ?>
				
				<div class="wsm-settings-body">
					<!-- Tab 1: General Settings -->
					<div id="wsm-tab-general" class="wsm-tab-content active">
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

							<div class="wsm-field-group wsm-full-width">
								<label for="wsm_low_stock_threshold">آستانه هشدار موجودی کم انبار</label>
								<input type="number" id="wsm_low_stock_threshold" name="wsm_low_stock_threshold" value="<?php echo esc_attr( $stock_thresh ); ?>" min="0" class="wsm-input-text" style="max-width: 200px;">
								<p class="wsm-field-desc">زمانی که موجودی محصولی از این عدد کمتر شود، هشدار کمبود موجودی انبار صادر می‌گردد.</p>
							</div>
						</div>
					</div>

					<!-- Tab 2: SMS Gateway Settings -->
					<div id="wsm-tab-sms" class="wsm-tab-content">
						<div class="wsm-card">
							<h3>اعتبارنامه درگاه ملی‌پیامک</h3>
							<div class="wsm-form-grid">
								<div class="wsm-field-group">
									<label for="wsm_sms_username">نام کاربری ملی‌پیامک</label>
									<input type="text" id="wsm_sms_username" name="wsm_sms_username" value="<?php echo esc_attr( $sms_username ); ?>" class="wsm-input-text">
								</div>

								<div class="wsm-field-group">
									<label for="wsm_sms_password">رمز عبور ملی‌پیامک</label>
									<input type="password" id="wsm_sms_password" name="wsm_sms_password" value="<?php echo esc_attr( $sms_password ); ?>" class="wsm-input-text">
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
							
							<p class="wsm-field-desc" style="margin-top: 15px; border-top: 1px dashed #1e293b; padding-top: 15px;">
								<strong>نکته:</strong> برای شخصی‌سازی متون و قالب‌های پیامکی، لطفا به بخش تنظیمات پیامک در <a href="<?php echo esc_url( home_url( '/' . $panel_slug . '/sms-settings' ) ); ?>" target="_blank" style="color: #818cf8; text-decoration: none;">پنل اختصاصی فروشگاه</a> مراجعه نمایید.
							</p>
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
				</div>

				<div class="wsm-submit-area">
					<button type="submit" class="wsm-save-btn">ذخیره تغییرات</button>
				</div>
			</form>
		</div>

		<script>
			function wsmSwitchTab(evt, tabId) {
				const contents = document.querySelectorAll('.wsm-tab-content');
				contents.forEach(c => c.classList.remove('active'));
				
				const links = document.querySelectorAll('.wsm-tab-link');
				links.forEach(l => l.classList.remove('active'));
				
				document.getElementById(tabId).classList.add('active');
				evt.currentTarget.classList.add('active');
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
		</script>
		<?php
	}
}

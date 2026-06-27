<?php
/**
 * Settings Registry and Fields Registration
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
 * Class WSM_Admin_Settings
 */
class WSM_Admin_Settings {

	/**
	 * Register settings groups, sections, and input fields.
	 */
	public function register_settings(): void {
		// 1. Register option keys.
		register_setting(
			'wsm_settings_group',
			'wsm_panel_slug',
			[
				'type'              => 'string',
				'sanitize_callback' => [ $this, 'sanitize_slug' ],
				'default'           => 'store-admin',
			]
		);

		register_setting(
			'wsm_settings_group',
			'wsm_session_lifetime',
			[
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'default'           => 8,
			]
		);

		register_setting(
			'wsm_settings_group',
			'wsm_admin_mobile',
			[
				'type'              => 'string',
				'sanitize_callback' => [ $this, 'sanitize_mobile' ],
			]
		);

		register_setting(
			'wsm_settings_group',
			'wsm_sms_username',
			[
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		register_setting(
			'wsm_settings_group',
			'wsm_sms_password',
			[
				'type'              => 'string',
				'sanitize_callback' => [ $this, 'sanitize_sms_password' ],
			]
		);

		register_setting(
			'wsm_settings_group',
			'wsm_sms_token',
			[
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		register_setting(
			'wsm_settings_group',
			'wsm_sms_from_line',
			[
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		register_setting(
			'wsm_settings_group',
			'wsm_sms_evt_new_order',
			[
				'type'    => 'boolean',
				'default' => false,
			]
		);

		register_setting(
			'wsm_settings_group',
			'wsm_sms_evt_order_status',
			[
				'type'    => 'boolean',
				'default' => false,
			]
		);

		register_setting(
			'wsm_settings_group',
			'wsm_sms_evt_low_stock',
			[
				'type'    => 'boolean',
				'default' => false,
			]
		);

		register_setting(
			'wsm_settings_group',
			'wsm_low_stock_threshold',
			[
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'default'           => 5,
			]
		);

		register_setting(
			'wsm_settings_group',
			'wsm_trust_proxy_headers',
			[
				'type'    => 'boolean',
				'default' => false,
			]
		);

		register_setting(
			'wsm_settings_group',
			'wsm_log_retention_days',
			[
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'default'           => 180,
			]
		);

		// Page Customization Options (HTML, CSS, JS separate for each page)
		$pages = [ 'dashboard', 'orders', 'products', 'categories', 'attributes', 'brands', 'coupons', 'reports', 'sms', 'login' ];
		$types = [ 'html', 'css', 'js' ];
		foreach ( $pages as $page ) {
			foreach ( $types as $type ) {
				register_setting(
					'wsm_settings_group',
					'wsm_custom_' . $type . '_' . $page,
					[
						'type'              => 'string',
						'sanitize_callback' => [ $this, 'sanitize_custom_code' ],
						'default'           => '',
					]
				);
			}
		}

		// Color Styling Settings
		register_setting(
			'wsm_settings_group',
			'wsm_primary_color',
			[
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_hex_color',
				'default'           => '#6366f1',
			]
		);
		register_setting(
			'wsm_settings_group',
			'wsm_accent_color',
			[
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_hex_color',
				'default'           => '#06b6d4',
			]
		);

		// Section Enable/Disable Switches
		$sections = [ 'dashboard', 'orders', 'products', 'categories', 'attributes', 'brands', 'coupons', 'reports', 'sms' ];
		foreach ( $sections as $sec ) {
			register_setting(
				'wsm_settings_group',
				'wsm_enable_' . $sec,
				[
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
					'default'           => 'yes',
				]
			);
		}

		// 2. Define setting sections.
		add_settings_section(
			'wsm_general_section',
			__( 'تنظیمات عمومی پنل', 'karasu-woo-pannel' ),
			[ $this, 'render_general_section' ],
			'wsm_settings'
		);

		add_settings_section(
			'wsm_sms_section',
			__( 'تنظیمات پیامک (ملی‌پیامک)', 'karasu-woo-pannel' ),
			[ $this, 'render_sms_section' ],
			'wsm_settings'
		);

		// 3. Register settings fields.
		add_settings_field(
			'wsm_panel_slug',
			__( 'آدرس URL پنل (Slug)', 'karasu-woo-pannel' ),
			[ $this, 'render_slug_field' ],
			'wsm_settings',
			'wsm_general_section'
		);

		add_settings_field(
			'wsm_session_lifetime',
			__( 'مدت اعتبار نشست (ساعت)', 'karasu-woo-pannel' ),
			[ $this, 'render_session_field' ],
			'wsm_settings',
			'wsm_general_section'
		);

		add_settings_field(
			'wsm_low_stock_threshold',
			__( 'آستانه هشدار موجودی کم', 'karasu-woo-pannel' ),
			[ $this, 'render_low_stock_field' ],
			'wsm_settings',
			'wsm_general_section'
		);

		add_settings_field(
			'wsm_admin_mobile',
			__( 'شماره موبایل مدیر', 'karasu-woo-pannel' ),
			[ $this, 'render_mobile_field' ],
			'wsm_settings',
			'wsm_sms_section'
		);

		add_settings_field(
			'wsm_sms_username',
			__( 'نام کاربری سامانه پیامک', 'karasu-woo-pannel' ),
			[ $this, 'render_sms_user_field' ],
			'wsm_settings',
			'wsm_sms_section'
		);

		add_settings_field(
			'wsm_sms_password',
			__( 'رمز عبور سامانه پیامک', 'karasu-woo-pannel' ),
			[ $this, 'render_sms_pass_field' ],
			'wsm_settings',
			'wsm_sms_section'
		);

		add_settings_field(
			'wsm_sms_from_line',
			__( 'خط فرستنده پیامک', 'karasu-woo-pannel' ),
			[ $this, 'render_sms_line_field' ],
			'wsm_settings',
			'wsm_sms_section'
		);

		add_settings_field(
			'wsm_sms_evt_new_order',
			__( 'ارسال پیامک سفارش جدید', 'karasu-woo-pannel' ),
			[ $this, 'render_sms_order_field' ],
			'wsm_settings',
			'wsm_sms_section'
		);

		add_settings_field(
			'wsm_sms_evt_order_status',
			__( 'ارسال پیامک تغییر وضعیت سفارش', 'karasu-woo-pannel' ),
			[ $this, 'render_sms_status_field' ],
			'wsm_settings',
			'wsm_sms_section'
		);

		add_settings_field(
			'wsm_sms_evt_low_stock',
			__( 'ارسال پیامک اتمام موجودی انبار', 'karasu-woo-pannel' ),
			[ $this, 'render_sms_stock_field' ],
			'wsm_settings',
			'wsm_sms_section'
		);
	}

	/**
	 * Sanitize custom HTML/CSS/JS code, allowing full code if current user has unfiltered_html capability.
	 *
	 * @param string $code Input code.
	 * @return string Sanitized code.
	 */
	public function sanitize_custom_code( string $code ): string {
		if ( current_user_can( 'unfiltered_html' ) ) {
			return $code;
		}
		return wp_kses_post( $code );
	}

	/**
	 * Sanitize and validate panel slug changes.
	 *
	 * @param string $slug Custom slug value.
	 * @return string Sanitized slug.
	 */
	public function sanitize_slug( string $slug ): string {
		$slug = sanitize_title( $slug );
		if ( empty( $slug ) ) {
			$slug = 'store-admin';
		}

		// Flush rules on shutdown to prevent 404 on the new URL.
		if ( get_option( 'wsm_panel_slug' ) !== $slug ) {
			add_action( 'shutdown', 'flush_rewrite_rules' );
		}
		return $slug;
	}

	/**
	 * Sanitize manager mobile phone number.
	 *
	 * @param string $mobile Mobile number input.
	 * @return string Sanitized mobile number.
	 */
	public function sanitize_mobile( string $mobile ): string {
		if ( class_exists( '\WooStoreManager\Helpers\WSM_Sanitizer' ) ) {
			return \WooStoreManager\Helpers\WSM_Sanitizer::phone_number( $mobile );
		}
		return sanitize_text_field( $mobile );
	}

	/**
	 * Render general section description.
	 */
	public function render_general_section(): void {
		echo '<p>' . esc_html__( 'تنظیمات اولیه مربوط به آدرس و امنیت پنل مدیریت اختصاصی فروشگاه.', 'karasu-woo-pannel' ) . '</p>';
	}

	/**
	 * Render SMS section description.
	 */
	public function render_sms_section(): void {
		echo '<p>' . esc_html__( 'اعتبارنامه و تنظیمات رویدادهای ارسالی پیامک از طریق درگاه ملی‌پیامک.', 'karasu-woo-pannel' ) . '</p>';
	}

	/**
	 * Render slug input.
	 */
	public function render_slug_field(): void {
		$value = get_option( 'wsm_panel_slug', 'store-admin' );
		echo '<input type="text" name="wsm_panel_slug" value="' . esc_attr( $value ) . '" class="regular-text">';
		echo '<p class="description">' . esc_html__( 'آدرس اسلاگ دسترسی به پنل مدیریت (مثلا: store-admin).', 'karasu-woo-pannel' ) . '</p>';
	}

	/**
	 * Render session length input.
	 */
	public function render_session_field(): void {
		$value = get_option( 'wsm_session_lifetime', 8 );
		echo '<input type="number" name="wsm_session_lifetime" value="' . esc_attr( $value ) . '" min="1" max="168" class="small-text">';
	}

	/**
	 * Render low stock threshold.
	 */
	public function render_low_stock_field(): void {
		$value = get_option( 'wsm_low_stock_threshold', 5 );
		echo '<input type="number" name="wsm_low_stock_threshold" value="' . esc_attr( $value ) . '" min="0" class="small-text">';
	}

	/**
	 * Render manager mobile.
	 */
	public function render_mobile_field(): void {
		$value = get_option( 'wsm_admin_mobile', '' );
		echo '<input type="text" name="wsm_admin_mobile" value="' . esc_attr( $value ) . '" class="regular-text" placeholder="09xxxxxxxxx">';
	}

	/**
	 * Render SMS user field.
	 */
	public function render_sms_user_field(): void {
		$value = get_option( 'wsm_sms_username', '' );
		echo '<input type="text" name="wsm_sms_username" value="' . esc_attr( $value ) . '" class="regular-text">';
	}

	/**
	 * Render SMS password.
	 */
	public function render_sms_pass_field(): void {
		$value = get_option( 'wsm_sms_password', '' );
		echo '<input type="password" name="wsm_sms_password" value="' . esc_attr( $value ) . '" class="regular-text">';
	}

	/**
	 * Render SMS origin line.
	 */
	public function render_sms_line_field(): void {
		$value = get_option( 'wsm_sms_from_line', '' );
		echo '<input type="text" name="wsm_sms_from_line" value="' . esc_attr( $value ) . '" class="regular-text">';
	}

	/**
	 * Render check box new order sms event.
	 */
	public function render_sms_order_field(): void {
		$checked = get_option( 'wsm_sms_evt_new_order' ) ? 'checked' : '';
		echo '<input type="checkbox" name="wsm_sms_evt_new_order" value="1" ' . $checked . '>';
	}

	/**
	 * Render check box order status changed sms event.
	 */
	public function render_sms_status_field(): void {
		$checked = get_option( 'wsm_sms_evt_order_status' ) ? 'checked' : '';
		echo '<input type="checkbox" name="wsm_sms_evt_order_status" value="1" ' . $checked . '>';
	}

	/**
	 * Render check box low stock sms event.
	 */
	public function render_sms_stock_field(): void {
		$checked = get_option( 'wsm_sms_evt_low_stock' ) ? 'checked' : '';
		echo '<input type="checkbox" name="wsm_sms_evt_low_stock" value="1" ' . $checked . '>';
	}

	/**
	 * Sanitize and encrypt SMS password.
	 *
	 * @param string $password Raw input password.
	 * @return string Encrypted password string.
	 */
	public function sanitize_sms_password( string $password ): string {
		$password = sanitize_text_field( $password );
		if ( empty( $password ) ) {
			return get_option( 'wsm_sms_password', '' );
		}
		return wsm_encrypt_password( $password );
	}
}

<?php
/**
 * Custom Elementor Login Button Widget
 *
 * @package KarasuWooPannel
 * @version 1.0.4
 * @date 2026-06-23
 */

namespace WooStoreManager\Core;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class WSM_Login_Widget
 */
class WSM_Login_Widget extends Widget_Base {

	/**
	 * Get widget name.
	 *
	 * @return string Widget name.
	 */
	public function get_name(): string {
		return 'wsm_login_button';
	}

	/**
	 * Get widget title.
	 *
	 * @return string Widget title.
	 */
	public function get_title(): string {
		return __( 'دکمه ورود KarasuWooPannel', 'karasu-woo-pannel' );
	}

	/**
	 * Get widget icon.
	 *
	 * @return string Widget icon.
	 */
	public function get_icon(): string {
		return 'eicon-button';
	}

	/**
	 * Get widget categories.
	 *
	 * @return array Categories keys.
	 */
	public function get_categories(): array {
		return [ 'karasu-woo-pannel' ];
	}

	/**
	 * Register widget controls.
	 */
	protected function register_controls(): void {
		// Content Controls Section
		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'محتوای دکمه', 'karasu-woo-pannel' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'guest_text',
			[
				'label'       => __( 'متن دکمه (مهمان)', 'karasu-woo-pannel' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'ورود به حساب کاربری', 'karasu-woo-pannel' ),
				'placeholder' => __( 'متن را اینجا وارد کنید', 'karasu-woo-pannel' ),
			]
		);

		$this->add_control(
			'manager_text',
			[
				'label'       => __( 'متن دکمه (مدیر فروشگاه)', 'karasu-woo-pannel' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'ورود به پنل مدیریت', 'karasu-woo-pannel' ),
				'placeholder' => __( 'متن را اینجا وارد کنید', 'karasu-woo-pannel' ),
			]
		);

		$this->add_control(
			'alignment',
			[
				'label'   => __( 'تراز دکمه', 'karasu-woo-pannel' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => [
					'left'    => [
						'title' => __( 'چپ', 'karasu-woo-pannel' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center'  => [
						'title' => __( 'وسط', 'karasu-woo-pannel' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right'   => [
						'title' => __( 'راست', 'karasu-woo-pannel' ),
						'icon'  => 'eicon-text-align-right',
					],
					'justify' => [
						'title' => __( 'تمام عرض', 'karasu-woo-pannel' ),
						'icon'  => 'eicon-text-align-justify',
					],
				],
				'default' => 'center',
			]
		);

		$this->add_control(
			'selected_icon',
			[
				'label'            => __( 'آیکون دکمه', 'karasu-woo-pannel' ),
				'type'             => Controls_Manager::ICONS,
				'fa4compatibility' => 'icon',
			]
		);

		$this->add_control(
			'icon_align',
			[
				'label'     => __( 'موقعیت آیکون', 'karasu-woo-pannel' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'left',
				'options'   => [
					'left'  => __( 'قبل از متن', 'karasu-woo-pannel' ),
					'right' => __( 'بعد از متن', 'karasu-woo-pannel' ),
				],
				'condition' => [
					'selected_icon[value]!' => '',
				],
			]
		);

		$this->end_controls_section();

		// Style Controls Section
		$this->start_controls_section(
			'section_style',
			[
				'label' => __( 'استایل دکمه', 'karasu-woo-pannel' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'typography',
				'selector' => '{{WRAPPER}} .wsm-ele-btn',
			]
		);

		$this->start_controls_tabs( 'tabs_button_style' );

		// Normal State tab
		$this->start_controls_tab(
			'tab_button_normal',
			[
				'label' => __( 'عادی', 'karasu-woo-pannel' ),
			]
		);

		$this->add_control(
			'text_color',
			[
				'label'     => __( 'رنگ متن', 'karasu-woo-pannel' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .wsm-ele-btn' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'bg_color',
			[
				'label'     => __( 'رنگ پس‌زمینه', 'karasu-woo-pannel' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#4f46e5',
				'selectors' => [
					'{{WRAPPER}} .wsm-ele-btn' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		// Hover State tab
		$this->start_controls_tab(
			'tab_button_hover',
			[
				'label' => __( 'هاور', 'karasu-woo-pannel' ),
			]
		);

		$this->add_control(
			'hover_text_color',
			[
				'label'     => __( 'رنگ متن (هاور)', 'karasu-woo-pannel' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wsm-ele-btn:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'hover_bg_color',
			[
				'label'     => __( 'رنگ پس‌زمینه (هاور)', 'karasu-woo-pannel' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#4338ca',
				'selectors' => [
					'{{WRAPPER}} .wsm-ele-btn:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'hover_animation',
			[
				'label' => __( 'انیمیشن هاور', 'karasu-woo-pannel' ),
				'type'  => Controls_Manager::HOVER_ANIMATION,
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_control(
			'border_radius',
			[
				'label'      => __( 'شعاع مرز (Border Radius)', 'karasu-woo-pannel' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .wsm-ele-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'box_shadow',
				'selector' => '{{WRAPPER}} .wsm-ele-btn',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget output in HTML.
	 */
	protected function render(): void {
		$settings = $this->get_settings_for_display();

		$is_auth = \WooStoreManager\Auth\WSM_Auth::is_authenticated();
		$has_access = current_user_can( 'wsm_access_panel' );

		// Decide label and url
		if ( $is_auth && $has_access ) {
			$label = ! empty( $settings['manager_text'] ) ? $settings['manager_text'] : __( 'ورود به پنل مدیریت', 'karasu-woo-pannel' );
			$url   = wsm_panel_url();
		} else {
			$label = ! empty( $settings['guest_text'] ) ? $settings['guest_text'] : __( 'ورود به حساب کاربری', 'karasu-woo-pannel' );
			$url   = wsm_login_url();
		}

		// CSS classes
		$btn_classes = 'wsm-ele-btn';
		if ( ! empty( $settings['hover_animation'] ) ) {
			$btn_classes .= ' elementor-animation-' . $settings['hover_animation'];
		}

		// Inline style alignment
		$align = $settings['alignment'] ?? 'center';
		$wrapper_styles = '';
		if ( 'justify' === $align ) {
			$wrapper_styles = 'width: 100%;';
			$btn_styles     = 'display: block; width: 100%; text-align: center;';
		} else {
			$wrapper_styles = 'display: flex; justify-content: ' . ( 'left' === $align ? 'flex-start' : ( 'right' === $align ? 'flex-end' : 'center' ) ) . ';';
			$btn_styles     = 'display: inline-flex; align-items: center;';
		}

		$btn_styles .= ' padding: 12px 24px; text-decoration: none; font-weight: 600; font-family: Vazirmatn, sans-serif; transition: all 0.3s ease;';

		echo '<div class="wsm-ele-btn-wrapper" style="' . esc_attr( $wrapper_styles ) . '">';
		echo '<a href="' . esc_url( $url ) . '" class="' . esc_attr( $btn_classes ) . '" style="' . esc_attr( $btn_styles ) . '">';

		// Icon render
		$has_icon = ! empty( $settings['selected_icon']['value'] );
		if ( $has_icon && 'left' === $settings['icon_align'] ) {
			echo '<span class="wsm-ele-btn-icon" style="margin-left: 8px;">';
			\Elementor\Icons_Manager::render_icon( $settings['selected_icon'], [ 'aria-hidden' => 'true' ] );
			echo '</span>';
		}

		echo esc_html( $label );

		if ( $has_icon && 'right' === $settings['icon_align'] ) {
			echo '<span class="wsm-ele-btn-icon" style="margin-right: 8px;">';
			\Elementor\Icons_Manager::render_icon( $settings['selected_icon'], [ 'aria-hidden' => 'true' ] );
			echo '</span>';
		}

		echo '</a>';
		echo '</div>';
	}
}

<?php
/**
 * Custom Store Admin Panel Base Layout Template
 *
 * @package KarasuWooPannel
 * @version 1.1.1
 * @date 2026-06-23
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$is_login     = isset( $view ) && 'login' === $view;
$current_user = wp_get_current_user();
?>
<!DOCTYPE html>
<html dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>" lang="<?php echo esc_attr( get_bloginfo( 'language' ) ); ?>" class="wsm-h-full wsm-bg-slate-950">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo esc_html( get_bloginfo( 'name' ) ); ?> — <?php echo esc_html( __( 'Store Management Panel', 'karasu-woo-pannel' ) ); ?></title>

	<?php if ( function_exists( 'wp_site_icon' ) ) { wp_site_icon(); } ?>

	<script>
		(function() {
			const savedTheme = localStorage.getItem('wsm-theme');
			if (savedTheme === 'light') {
				document.documentElement.classList.add('wsm-light');
			}
		})();
	</script>

	<meta name="robots" content="noindex, nofollow, noarchive">
	<?php wp_print_styles( [ 'wsm-font-vazirmatn', 'wsm-jalalidatepicker-css', 'wsm-panel-css' ] ); ?>

	<!-- Custom Checkbox, Jalali Datepicker & SVG styles -->
	<style>
		/* Premium Custom Checkboxes styling */
		input[type="checkbox"] {
			-webkit-appearance: none;
			appearance: none;
			background-color: #020617 !important; /* Slate 950 */
			border: 1px solid #334155 !important; /* Slate 700 */
			border-radius: 0.375rem !important; /* 6px */
			width: 1.125rem !important; /* 18px */
			height: 1.125rem !important; /* 18px */
			display: inline-grid !important;
			place-content: center !important;
			cursor: pointer !important;
			transition: all 0.2s ease-in-out !important;
			margin: 0 !important;
			vertical-align: middle !important;
		}
		input[type="checkbox"]:hover {
			border-color: #6366f1 !important; /* Indigo 500 */
			box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2) !important;
		}
		input[type="checkbox"]:checked {
			background-color: #4f46e5 !important; /* Indigo 600 */
			border-color: #6366f1 !important; /* Indigo 500 */
		}
		input[type="checkbox"]:checked::before {
			content: "" !important;
			width: 0.55rem !important;
			height: 0.55rem !important;
			background-color: #ffffff !important;
			clip-path: polygon(14% 44%, 0 65%, 50% 100%, 100% 16%, 80% 0%, 43% 62%) !important;
			transform: scale(1) !important;
		}
		input[type="checkbox"]:focus-visible {
			outline: 2px solid #6366f1;
			outline-offset: 2px;
		}

		/* Jalali Datepicker Dark Theme Overrides */
		.jdp-container {
			background-color: #0f172a !important; /* Slate 900 */
			border: 1px solid #1e293b !important; /* Slate 800 */
			border-radius: 1rem !important; /* 16px */
			box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5), 0 4px 6px -4px rgba(0, 0, 0, 0.5) !important;
			color: #e2e8f0 !important; /* Slate 200 */
			font-family: 'Vazirmatn', sans-serif !important;
			padding: 12px !important;
			z-index: 99999 !important;
			direction: rtl !important;
		}
		.jdp-header {
			border-bottom: 1px solid #1e293b !important;
			padding-bottom: 8px !important;
			margin-bottom: 8px !important;
			display: flex !important;
			justify-content: space-between !important;
			align-items: center !important;
			gap: 4px !important;
		}
		.jdp-header select {
			background-color: #020617 !important; /* Slate 950 */
			border: 1px solid #1e293b !important;
			border-radius: 0.5rem !important;
			color: #f1f5f9 !important;
			padding: 4px 8px !important;
			font-size: 0.85rem !important;
			cursor: pointer !important;
			outline: none !important;
			font-family: 'Vazirmatn', sans-serif !important;
		}
		.jdp-header select:focus {
			border-color: #6366f1 !important;
		}
		.jdp-week-day {
			color: #64748b !important; /* Slate 500 */
			font-weight: 600 !important;
			font-size: 0.75rem !important;
		}
		.jdp-day {
			border-radius: 0.5rem !important;
			cursor: pointer !important;
			transition: all 0.15s ease !important;
			font-size: 0.85rem !important;
			color: #cbd5e1 !important;
			display: inline-flex !important;
			align-items: center !important;
			justify-content: center !important;
		}
		.jdp-day:hover {
			background-color: rgba(99, 102, 241, 0.15) !important;
			color: #818cf8 !important;
		}
		.jdp-day.selected {
			background-color: #4f46e5 !important; /* Indigo 600 */
			color: #ffffff !important;
			font-weight: bold !important;
		}
		.jdp-day.today {
			border: 1px solid #818cf8 !important;
			color: #818cf8 !important;
		}
		.jdp-day.out-of-range {
			color: #475569 !important;
			opacity: 0.5 !important;
			cursor: not-allowed !important;
		}
		.jdp-btn {
			background-color: #1e293b !important;
			border: 1px solid #334155 !important;
			color: #cbd5e1 !important;
			border-radius: 0.5rem !important;
			cursor: pointer !important;
			padding: 6px 12px !important;
			font-size: 0.8rem !important;
			transition: all 0.15s ease !important;
			font-family: 'Vazirmatn', sans-serif !important;
		}
		.jdp-btn:hover {
			background-color: #334155 !important;
			color: #f1f5f9 !important;
		}

		/* Inline SVG sizing fixes for safety */
		svg.wsm-w-5, svg.wsm-w-6, svg.wsm-w-3.5 {
			flex-shrink: 0 !important;
		}

		/* ==========================================================================
		   Light Mode CSS Palette Mapping Overrides
		   ========================================================================== */
		html.wsm-light {
			background-color: #f8fafc !important; /* Slate 50 */
			color: #334155 !important; /* Slate 700 */
		}
		html.wsm-light body {
			background-color: #f8fafc !important;
			color: #334155 !important;
		}
		html.wsm-light .wsm-bg-slate-950 {
			background-color: #f8fafc !important;
		}
		html.wsm-light .wsm-bg-slate-900 {
			background-color: #ffffff !important;
		}
		html.wsm-light .wsm-bg-slate-900\/60 {
			background-color: rgba(255, 255, 255, 0.8) !important;
			backdrop-filter: blur(12px);
		}
		html.wsm-light .wsm-bg-slate-900\/40 {
			background-color: rgba(255, 255, 255, 0.5) !important;
			backdrop-filter: blur(12px);
		}
		html.wsm-light .wsm-bg-slate-950\/80 {
			background-color: #ffffff !important;
		}
		html.wsm-light .wsm-bg-slate-950\/60 {
			background-color: rgba(241, 245, 249, 0.7) !important;
		}
		html.wsm-light .wsm-bg-slate-950\/20 {
			background-color: #f1f5f9 !important;
		}
		html.wsm-light .wsm-border-slate-800 {
			border-color: #cbd5e1 !important; /* Slate 300 */
		}
		html.wsm-light .wsm-border-slate-800\/80 {
			border-color: #cbd5e1 !important;
		}
		html.wsm-light .wsm-border-slate-800\/40 {
			border-color: #e2e8f0 !important;
		}
		html.wsm-light .wsm-text-slate-100 {
			color: #0f172a !important; /* Slate 900 */
		}
		html.wsm-light .wsm-text-slate-200 {
			color: #1e293b !important; /* Slate 800 */
		}
		html.wsm-light .wsm-text-slate-300 {
			color: #334155 !important; /* Slate 700 */
		}
		html.wsm-light .wsm-text-slate-400 {
			color: #475569 !important; /* Slate 600 */
		}
		html.wsm-light .wsm-text-slate-500 {
			color: #64748b !important; /* Slate 500 */
		}
		html.wsm-light .wsm-text-slate-600 {
			color: #94a3b8 !important; /* Slate 400 */
		}
		html.wsm-light input, 
		html.wsm-light select, 
		html.wsm-light textarea {
			background-color: #ffffff !important;
			color: #0f172a !important;
			border-color: #cbd5e1 !important;
		}
		html.wsm-light input:focus, 
		html.wsm-light select:focus, 
		html.wsm-light textarea:focus {
			border-color: #6366f1 !important;
		}
		html.wsm-light input[type="checkbox"] {
			background-color: #ffffff !important;
			border-color: #cbd5e1 !important;
		}
		html.wsm-light input[type="checkbox"]:checked {
			background-color: #4f46e5 !important;
			border-color: #6366f1 !important;
		}
		html.wsm-light .wsm-bg-gradient-to-br {
			background-image: linear-gradient(to bottom right, #f8fafc, #f1f5f9, #e2e8f0) !important;
		}
		html.wsm-light .hover\:wsm-bg-slate-800\/50:hover {
			background-color: #f1f5f9 !important;
		}
		html.wsm-light .hover\:wsm-text-slate-100:hover {
			color: #0f172a !important;
		}

		/* Jalali Datepicker Light Theme */
		html.wsm-light .jdp-container {
			background-color: #ffffff !important;
			border-color: #cbd5e1 !important;
			color: #1e293b !important;
			box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1) !important;
		}
		html.wsm-light .jdp-header {
			border-bottom-color: #e2e8f0 !important;
		}
		html.wsm-light .jdp-header select {
			background-color: #f8fafc !important;
			border-color: #cbd5e1 !important;
			color: #0f172a !important;
		}
		html.wsm-light .jdp-day {
			color: #334155 !important;
		}
		html.wsm-light .jdp-day:hover {
			background-color: rgba(99, 102, 241, 0.1) !important;
		}
		html.wsm-light .jdp-day.selected {
			background-color: #4f46e5 !important;
			color: #ffffff !important;
		}
		html.wsm-light .jdp-btn {
			background-color: #f1f5f9 !important;
			border-color: #cbd5e1 !important;
			color: #334155 !important;
		}
		html.wsm-light .jdp-btn:hover {
			background-color: #e2e8f0 !important;
		}

		/* ==========================================================================
		   LTR Layout Adjustment Overrides
		   ========================================================================== */
		html[dir="ltr"] {
			direction: ltr !important;
		}
		html[dir="ltr"] #wsm-sidebar {
			left: 0 !important;
			right: auto !important;
			border-right: 1px solid #1e293b;
			border-left: none !important;
			transform: translateX(-100%);
		}
		html.wsm-light[dir="ltr"] #wsm-sidebar {
			border-right-color: #cbd5e1;
		}
		html[dir="ltr"] .wsm-ml-3 {
			margin-right: 0.75rem !important;
			margin-left: 0 !important;
		}
		html[dir="ltr"] .wsm-ml-2 {
			margin-right: 0.5rem !important;
			margin-left: 0 !important;
		}
		html[dir="ltr"] .wsm-text-right {
			text-align: left !important;
		}
		html[dir="ltr"] .wsm-space-x-reverse {
			margin-left: 0 !important;
		}
		html[dir="ltr"] .wsm-space-x-reverse > :not([hidden]) ~ :not([hidden]) {
			margin-right: 0 !important;
			margin-left: 0.75rem !important;
		}
		@media (min-width: 768px) {
			html[dir="ltr"] #wsm-sidebar {
				transform: translateX(0) !important;
				position: relative !important;
			}
			html[dir="ltr"] #wsm-sidebar-backdrop {
				display: none !important;
			}
		}
	</style>

	<!-- Global JavaScript Settings configuration -->
	<script>
		window.wsmConfig = {
			apiUrl: '<?php echo esc_url( rest_url( 'wsm/v1' ) ); ?>',
			nonce: '<?php echo esc_js( wp_create_nonce( 'wp_rest' ) ); ?>',
			panelUrl: '<?php echo esc_url( wsm_panel_url() ); ?>',
			translations: {
				loading: '<?php echo esc_js( __( 'Loading...', 'karasu-woo-pannel' ) ); ?>',
				delete: '<?php echo esc_js( __( 'Delete', 'karasu-woo-pannel' ) ); ?>',
				confirmDeleteAttribute: '<?php echo esc_js( __( 'Are you sure you want to delete this attribute? All of its values will be deleted too.', 'karasu-woo-pannel' ) ); ?>',
				confirmDeleteBrand: '<?php echo esc_js( __( 'Are you sure you want to delete this brand?', 'karasu-woo-pannel' ) ); ?>',
				addValue: '<?php echo esc_js( __( 'Add Value', 'karasu-woo-pannel' ) ); ?>',
				noAttributes: '<?php echo esc_js( __( 'No attributes registered.', 'karasu-woo-pannel' ) ); ?>',
				noBrands: '<?php echo esc_js( __( 'No brands registered.', 'karasu-woo-pannel' ) ); ?>',
				categories: '<?php echo esc_js( __( 'Categories', 'karasu-woo-pannel' ) ); ?>',
				noCategories: '<?php echo esc_js( __( 'No categories exist.', 'karasu-woo-pannel' ) ); ?>',
				brands: '<?php echo esc_js( __( 'Brands', 'karasu-woo-pannel' ) ); ?>',
				noBrandsExist: '<?php echo esc_js( __( 'No brands exist.', 'karasu-woo-pannel' ) ); ?>'
			}
		};
	</script>
</head>
<body class="wsm-h-full wsm-font-sans wsm-text-slate-200 wsm-antialiased">

<?php if ( $is_login ) : ?>
	<!-- Login Layout -->
	<div class="wsm-min-h-full wsm-flex wsm-items-center wsm-justify-center wsm-bg-gradient-to-br wsm-from-slate-950 wsm-via-slate-900 wsm-to-slate-850 wsm-px-4">
		<?php
		if ( isset( $view_file ) && file_exists( $view_file ) ) {
			require $view_file;
		}
		?>
	</div>
<?php else : ?>
	<!-- Main Panel Layout (Sidebar + Header + Content view) -->
	<div class="wsm-flex wsm-h-screen wsm-overflow-hidden wsm-bg-slate-950">
		<!-- Mobile Sidebar Backdrop (only visible when sidebar is open on mobile) -->
		<div id="wsm-sidebar-backdrop" class="wsm-fixed wsm-inset-0 wsm-z-40 wsm-bg-slate-950/60 wsm-backdrop-blur-sm wsm-hidden wsm-opacity-0 wsm-transition-opacity wsm-duration-300"></div>

		<!-- Sidebar -->
		<aside id="wsm-sidebar" class="wsm-fixed wsm-inset-y-0 wsm-right-0 wsm-z-50 wsm-w-64 wsm-bg-slate-900 md:wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border-l wsm-border-slate-800 wsm-flex wsm-flex-col wsm-transform wsm-translate-x-full md:wsm-translate-x-0 wsm-transition-transform wsm-duration-300 wsm-ease-in-out md:wsm-relative">
			<div class="wsm-flex wsm-items-center wsm-justify-between wsm-h-16 wsm-border-b wsm-border-slate-800 wsm-px-6">
				<span class="wsm-text-lg wsm-font-bold wsm-bg-gradient-to-r wsm-from-indigo-400 wsm-to-cyan-400 wsm-bg-clip-text wsm-text-transparent">
					KarasuWooPannel
				</span>
				<!-- Mobile Close Button -->
				<button id="wsm-sidebar-close" class="md:wsm-hidden wsm-text-slate-400 hover:wsm-text-slate-100 focus:wsm-outline-none" style="padding: 4px;">
					<svg style="width: 24px; height: 24px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
				</button>
			</div>
			
			<!-- User info in sidebar (Visible on mobile since we hide it from header to avoid cluttering) -->
			<div class="md:wsm-hidden wsm-px-6 wsm-py-4 wsm-border-b wsm-border-slate-800/60">
				<span class="wsm-text-xs wsm-text-slate-500 wsm-block"><?php echo esc_html( __( 'Current User:', 'karasu-woo-pannel' ) ); ?></span>
				<span class="wsm-text-sm wsm-font-semibold wsm-text-slate-200 wsm-block wsm-mt-1"><?php echo esc_html( $current_user->display_name ); ?></span>
			</div>

			<nav class="wsm-flex-1 wsm-px-4 wsm-py-6 wsm-space-y-1 wsm-overflow-y-auto">
				<?php
				$is_active = function( $page ) use ( $view ) {
					switch ( $page ) {
						case 'dashboard':
							return 'dashboard' === $view;
						case 'orders':
							return 0 === strpos( $view, 'orders/' );
						case 'products':
							return 0 === strpos( $view, 'products/' );
						case 'categories':
							return 0 === strpos( $view, 'categories/' );
						case 'coupons':
							return 0 === strpos( $view, 'coupons/' );
						case 'attributes':
							return 0 === strpos( $view, 'attributes/' );
						case 'brands':
							return 0 === strpos( $view, 'brands/' );
						case 'reports':
							return 0 === strpos( $view, 'reports/' ) && 'reports/sms-log' !== $view;
						case 'sms-settings':
							return 'sms/settings' === $view;
						case 'sms-log':
							return 'reports/sms-log' === $view;
						default:
							return false;
					}
				};
				?>
				<a href="<?php echo esc_url( wsm_panel_url() ); ?>" class="wsm-flex wsm-items-center wsm-px-4 wsm-py-3 wsm-text-sm wsm-font-medium wsm-rounded-xl wsm-transition-colors <?php echo $is_active('dashboard') ? 'wsm-bg-indigo-600/10 wsm-text-indigo-400' : 'wsm-text-slate-400 hover:wsm-bg-slate-800/50 hover:wsm-text-slate-100'; ?>">
					<svg style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="wsm-ml-3"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
					<span><?php echo esc_html( __( 'Dashboard', 'karasu-woo-pannel' ) ); ?></span>
				</a>
				<a href="<?php echo esc_url( wsm_panel_url( 'orders' ) ); ?>" class="wsm-flex wsm-items-center wsm-px-4 wsm-py-3 wsm-text-sm wsm-font-medium wsm-rounded-xl wsm-transition-colors <?php echo $is_active('orders') ? 'wsm-bg-indigo-600/10 wsm-text-indigo-400' : 'wsm-text-slate-400 hover:wsm-bg-slate-800/50 hover:wsm-text-slate-100'; ?>">
					<svg style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="wsm-ml-3"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
					<span><?php echo esc_html( __( 'Orders', 'karasu-woo-pannel' ) ); ?></span>
				</a>
				<a href="<?php echo esc_url( wsm_panel_url( 'products' ) ); ?>" class="wsm-flex wsm-items-center wsm-px-4 wsm-py-3 wsm-text-sm wsm-font-medium wsm-rounded-xl wsm-transition-colors <?php echo $is_active('products') ? 'wsm-bg-indigo-600/10 wsm-text-indigo-400' : 'wsm-text-slate-400 hover:wsm-bg-slate-800/50 hover:wsm-text-slate-100'; ?>">
					<svg style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="wsm-ml-3"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
					<span><?php echo esc_html( __( 'Products', 'karasu-woo-pannel' ) ); ?></span>
				</a>
				<a href="<?php echo esc_url( wsm_panel_url( 'categories' ) ); ?>" class="wsm-flex wsm-items-center wsm-px-4 wsm-py-3 wsm-text-sm wsm-font-medium wsm-rounded-xl wsm-transition-colors <?php echo $is_active('categories') ? 'wsm-bg-indigo-600/10 wsm-text-indigo-400' : 'wsm-text-slate-400 hover:wsm-bg-slate-800/50 hover:wsm-text-slate-100'; ?>">
					<svg style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="wsm-ml-3"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
					<span><?php echo esc_html( __( 'Categories', 'karasu-woo-pannel' ) ); ?></span>
				</a>
				<a href="<?php echo esc_url( wsm_panel_url( 'attributes' ) ); ?>" class="wsm-flex wsm-items-center wsm-px-4 wsm-py-3 wsm-text-sm wsm-font-medium wsm-rounded-xl wsm-transition-colors <?php echo $is_active('attributes') ? 'wsm-bg-indigo-600/10 wsm-text-indigo-400' : 'wsm-text-slate-400 hover:wsm-bg-slate-800/50 hover:wsm-text-slate-100'; ?>">
					<svg style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="wsm-ml-3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" /></svg>
					<span><?php echo esc_html( __( 'Attributes', 'karasu-woo-pannel' ) ); ?></span>
				</a>
				<a href="<?php echo esc_url( wsm_panel_url( 'brands' ) ); ?>" class="wsm-flex wsm-items-center wsm-px-4 wsm-py-3 wsm-text-sm wsm-font-medium wsm-rounded-xl wsm-transition-colors <?php echo $is_active('brands') ? 'wsm-bg-indigo-600/10 wsm-text-indigo-400' : 'wsm-text-slate-400 hover:wsm-bg-slate-800/50 hover:wsm-text-slate-100'; ?>">
					<svg style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="wsm-ml-3"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
					<span><?php echo esc_html( __( 'Brands', 'karasu-woo-pannel' ) ); ?></span>
				</a>
				<a href="<?php echo esc_url( wsm_panel_url( 'coupons' ) ); ?>" class="wsm-flex wsm-items-center wsm-px-4 wsm-py-3 wsm-text-sm wsm-font-medium wsm-rounded-xl wsm-transition-colors <?php echo $is_active('coupons') ? 'wsm-bg-indigo-600/10 wsm-text-indigo-400' : 'wsm-text-slate-400 hover:wsm-bg-slate-800/50 hover:wsm-text-slate-100'; ?>">
					<svg style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="wsm-ml-3"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
					<span><?php echo esc_html( __( 'Discounts', 'karasu-woo-pannel' ) ); ?></span>
				</a>
				<a href="<?php echo esc_url( wsm_panel_url( 'reports' ) ); ?>" class="wsm-flex wsm-items-center wsm-px-4 wsm-py-3 wsm-text-sm wsm-font-medium wsm-rounded-xl wsm-transition-colors <?php echo $is_active('reports') ? 'wsm-bg-indigo-600/10 wsm-text-indigo-400' : 'wsm-text-slate-400 hover:wsm-bg-slate-800/50 hover:wsm-text-slate-100'; ?>">
					<svg style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="wsm-ml-3"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
					<span><?php echo esc_html( __( 'Reports', 'karasu-woo-pannel' ) ); ?></span>
				</a>
				
				<!-- Back to Site Link -->
				<a href="<?php echo esc_url( home_url() ); ?>" class="wsm-flex wsm-items-center wsm-px-4 wsm-py-3 wsm-text-sm wsm-font-medium wsm-text-slate-400 wsm-rounded-xl wsm-transition-colors hover:wsm-bg-slate-800/50 hover:wsm-text-slate-100">
					<svg style="width: 20px; height: 20px;" class="wsm-ml-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
					<span><?php echo esc_html( __( 'Back to Site', 'karasu-woo-pannel' ) ); ?></span>
				</a>
			</nav>
			
			<div class="wsm-p-4 wsm-border-t wsm-border-slate-800">
				<!-- Using custom logout action link handler in our login form / JS instead of WP-login standard direct redirects -->
				<button id="wsm-logout-btn" class="wsm-flex wsm-items-center wsm-w-full wsm-px-4 wsm-py-3 wsm-text-sm wsm-font-medium wsm-text-rose-400 wsm-rounded-xl wsm-transition-colors hover:wsm-bg-rose-500/10">
					<svg style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="wsm-ml-3"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
					<span><?php echo esc_html( __( 'Log Out', 'karasu-woo-pannel' ) ); ?></span>
				</button>
			</div>
		</aside>

		<!-- Main Content View Area -->
		<div class="wsm-flex-1 wsm-flex wsm-flex-col wsm-overflow-hidden">
			<?php
			$back_url = '';
			$back_label = '';
			if ( isset( $view ) && 'dashboard' !== $view ) {
				if ( in_array( $view, [ 'orders/list', 'products/list', 'categories/list', 'attributes/list', 'brands/list', 'coupons/list', 'reports/dashboard' ], true ) ) {
					$back_url = wsm_panel_url();
					$back_label = __( 'Back to Dashboard', 'karasu-woo-pannel' );
				} elseif ( 'orders/detail' === $view ) {
					$back_url = wsm_panel_url( 'orders' );
					$back_label = __( 'Back to Orders', 'karasu-woo-pannel' );
				} elseif ( 'products/edit' === $view ) {
					$back_url = wsm_panel_url( 'products' );
					$back_label = __( 'Back to Products', 'karasu-woo-pannel' );
				} elseif ( 'coupons/edit' === $view ) {
					$back_url = wsm_panel_url( 'coupons' );
					$back_label = __( 'Back to Discounts', 'karasu-woo-pannel' );
				} elseif ( in_array( $view, [ 'reports/sales', 'reports/products', 'reports/customers' ], true ) ) {
					$back_url = wsm_panel_url( 'reports' );
					$back_label = __( 'Back to Reports', 'karasu-woo-pannel' );
				}
			}
			?>
			<!-- Header -->
			<header class="wsm-h-16 wsm-bg-slate-900/40 wsm-backdrop-blur-md wsm-border-b wsm-border-slate-800 wsm-flex wsm-items-center wsm-justify-between wsm-px-4 md:wsm-px-6">
				<!-- Hamburger toggle & logo on mobile -->
				<div class="wsm-flex wsm-items-center">
					<button id="wsm-sidebar-toggle" class="md:wsm-hidden wsm-text-slate-400 hover:wsm-text-slate-100 focus:wsm-outline-none wsm-ml-3" style="padding: 4px;">
						<svg style="width: 24px; height: 24px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
					</button>
					<span class="wsm-text-base md:wsm-text-lg wsm-font-bold wsm-bg-gradient-to-r wsm-from-indigo-400 wsm-to-cyan-400 wsm-bg-clip-text wsm-text-transparent">KarasuWooPannel</span>
				</div>

				<div class="wsm-flex wsm-items-center wsm-space-x-3 wsm-space-x-reverse">
					<?php if ( ! empty( $back_url ) ) : ?>
						<a href="<?php echo esc_url( $back_url ); ?>" class="wsm-flex wsm-items-center wsm-gap-1.5 wsm-px-3 wsm-py-1.5 wsm-text-xs wsm-font-semibold wsm-text-indigo-400 wsm-bg-indigo-600/10 hover:wsm-bg-indigo-600/20 wsm-rounded-xl wsm-transition-colors">
							<span>&rarr;</span> <span class="wsm-hidden sm:wsm-inline"><?php echo esc_html( $back_label ); ?></span><span class="sm:wsm-hidden"><?php echo esc_html( __( 'Back', 'karasu-woo-pannel' ) ); ?></span>
						</a>
					<?php elseif ( isset( $view ) && 'dashboard' === $view ) : ?>
						<a href="<?php echo esc_url( home_url() ); ?>" class="wsm-flex wsm-items-center wsm-gap-1.5 wsm-px-3 wsm-py-1.5 wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-bg-slate-800/50 hover:wsm-bg-slate-800 wsm-rounded-xl wsm-transition-colors">
							<svg style="width: 14px; height: 14px;" class="wsm-ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
							<span class="wsm-hidden sm:wsm-inline"><?php echo esc_html( __( 'Back to Site', 'karasu-woo-pannel' ) ); ?></span><span class="sm:wsm-hidden"><?php echo esc_html( __( 'Site', 'karasu-woo-pannel' ) ); ?></span>
						</a>
					<?php endif; ?>
				</div>
				<div class="wsm-flex wsm-items-center wsm-space-x-4 wsm-space-x-reverse">
					<!-- Theme Toggle Button -->
					<button id="wsm-theme-toggle" class="wsm-flex wsm-items-center wsm-justify-center wsm-w-9 wsm-h-9 wsm-rounded-xl wsm-bg-slate-800/50 hover:wsm-bg-slate-800 wsm-text-slate-400 hover:wsm-text-slate-100 wsm-transition-colors" title="<?php esc_attr_e( 'Change Theme', 'karasu-woo-pannel' ); ?>">
						<svg id="wsm-theme-moon" style="width: 18px; height: 18px;" class="wsm-hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
						<svg id="wsm-theme-sun" style="width: 18px; height: 18px;" class="wsm-hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707M14 12a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
					</button>
					<span class="wsm-text-sm wsm-text-slate-400 wsm-hidden md:wsm-inline"><?php echo esc_html( $current_user->display_name ); ?></span>
				</div>
			</header>

			<!-- Page Content View -->
			<main class="wsm-flex-1 wsm-overflow-y-auto wsm-p-6 wsm-bg-slate-950">
				<?php
				// Output custom user content for the current page if configured.
				$primary_view = 'dashboard';
				if ( isset( $view ) ) {
					if ( 0 === strpos( $view, 'orders/' ) ) {
						$primary_view = 'orders';
					} elseif ( 0 === strpos( $view, 'products/' ) ) {
						$primary_view = 'products';
					} elseif ( 0 === strpos( $view, 'categories/' ) ) {
						$primary_view = 'categories';
					} elseif ( 0 === strpos( $view, 'attributes/' ) ) {
						$primary_view = 'attributes';
					} elseif ( 0 === strpos( $view, 'brands/' ) ) {
						$primary_view = 'brands';
					} elseif ( 0 === strpos( $view, 'coupons/' ) ) {
						$primary_view = 'coupons';
					} elseif ( 0 === strpos( $view, 'reports/' ) ) {
						$primary_view = 'reports';
					} elseif ( 'sms/settings' === $view ) {
						$primary_view = 'sms';
					} elseif ( 'login' === $view ) {
						$primary_view = 'login';
					}
				}
				$custom_content = get_option( 'wsm_custom_content_' . $primary_view, '' );
				if ( ! empty( $custom_content ) ) {
					echo '<div class="wsm-custom-page-content wsm-mb-6 wsm-p-4 wsm-bg-slate-900/40 wsm-border wsm-border-slate-800/80 wsm-rounded-2xl">' . do_shortcode( $custom_content ) . '</div>';
				}

				if ( isset( $view_file ) && file_exists( $view_file ) ) {
					require $view_file;
				} else {
					?>
					<div class="wsm-bg-slate-900 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-p-8 wsm-text-center">
						<h2 class="wsm-text-xl wsm-font-bold wsm-text-slate-100 wsm-mb-2"><?php echo esc_html( __( 'Welcome!', 'karasu-woo-pannel' ) ); ?></h2>
						<p class="wsm-text-slate-400"><?php echo esc_html( __( 'The dashboard is currently available.', 'karasu-woo-pannel' ) ); ?></p>
					</div>
					<?php
				}
				?>
			</main>
		</div>
	</div>
<?php endif; ?>

<?php
$printed_scripts = [ 'wsm-panel-js', 'wsm-jalalidatepicker-js' ];
if ( wp_script_is( 'wsm-attributes-js', 'enqueued' ) ) {
	$printed_scripts[] = 'wsm-attributes-js';
}
wp_print_scripts( $printed_scripts );
?>
<script>
document.addEventListener("DOMContentLoaded", function() {
	const sidebar = document.getElementById("wsm-sidebar");
	const backdrop = document.getElementById("wsm-sidebar-backdrop");
	const toggleBtn = document.getElementById("wsm-sidebar-toggle");
	const closeBtn = document.getElementById("wsm-sidebar-close");

	function openSidebar() {
		if (!sidebar || !backdrop) return;
		backdrop.classList.remove("wsm-hidden");
		// Force reflow
		backdrop.offsetHeight;
		backdrop.classList.remove("wsm-opacity-0");
		backdrop.classList.add("wsm-opacity-100");

		if (document.documentElement.dir === "ltr") {
			sidebar.classList.remove("-wsm-translate-x-full");
		} else {
			sidebar.classList.remove("wsm-translate-x-full");
		}
		sidebar.classList.add("wsm-translate-x-0");
	}

	function closeSidebar() {
		if (!sidebar || !backdrop) return;
		backdrop.classList.remove("wsm-opacity-100");
		backdrop.classList.add("wsm-opacity-0");
		
		sidebar.classList.remove("wsm-translate-x-0");
		if (document.documentElement.dir === "ltr") {
			sidebar.classList.add("-wsm-translate-x-full");
		} else {
			sidebar.classList.add("wsm-translate-x-full");
		}

		setTimeout(() => {
			if (backdrop.classList.contains("wsm-opacity-0")) {
				backdrop.classList.add("wsm-hidden");
			}
		}, 300);
	}

	if (toggleBtn) toggleBtn.addEventListener("click", openSidebar);
	if (closeBtn) closeBtn.addEventListener("click", closeSidebar);
	if (backdrop) backdrop.addEventListener("click", closeSidebar);

	// Theme Toggle Logic
	const htmlEl = document.documentElement;
	const themeToggleBtn = document.getElementById("wsm-theme-toggle");
	const sunIcon = document.getElementById("wsm-theme-sun");
	const moonIcon = document.getElementById("wsm-theme-moon");

	function updateIcons() {
		if (htmlEl.classList.contains("wsm-light")) {
			sunIcon.classList.add("wsm-hidden");
			moonIcon.classList.remove("wsm-hidden");
		} else {
			moonIcon.classList.add("wsm-hidden");
			sunIcon.classList.remove("wsm-hidden");
		}
	}

	updateIcons();

	if (themeToggleBtn) {
		themeToggleBtn.addEventListener("click", function() {
			if (htmlEl.classList.contains("wsm-light")) {
				htmlEl.classList.remove("wsm-light");
				localStorage.setItem("wsm-theme", "dark");
			} else {
				htmlEl.classList.add("wsm-light");
				localStorage.setItem("wsm-theme", "light");
			}
			updateIcons();
		});
	}

	// Initialize Jalali Datepicker if loaded
	if (typeof jalaliDatepicker !== 'undefined') {
		jalaliDatepicker.startWatch({
			minDate: "attr",
			maxDate: "attr",
			minTime: "attr",
			maxTime: "attr",
			hideAfterSelect: true,
			autoShow: true,
			showTodayBtn: true,
			showEmptyBtn: true
		});
	}
});
</script>
</body>
</html>

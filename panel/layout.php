<?php
/**
 * Custom Store Admin Panel Base Layout Template
 *
 * @package KarasuWooPannel
 * @version 1.0.10
 * @date 2026-06-23
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$is_login     = isset( $view ) && 'login' === $view;
$current_user = wp_get_current_user();
?>
<!DOCTYPE html>
<html dir="rtl" lang="fa-IR" class="wsm-h-full wsm-bg-slate-950">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo esc_html( get_bloginfo( 'name' ) ); ?> — <?php esc_html_e( 'پنل مدیریت فروشگاه', 'karasu-woo-pannel' ); ?></title>

	<!-- Google Fonts: Vazirmatn -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100..900&display=swap" rel="stylesheet">



	<!-- Base Custom Stylesheet -->
	<link rel="stylesheet" href="<?php echo esc_url( WSM_PLUGIN_URL . 'assets/css/wsm-panel.css' ); ?>">

	<!-- Jalali Datepicker CSS CDN -->
	<link rel="stylesheet" href="https://unpkg.com/@majidh1/jalalidatepicker/dist/jalalidatepicker.min.css">

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
	</style>

	<!-- Global JavaScript Settings configuration -->
	<script>
		window.wsmConfig = {
			apiUrl: '<?php echo esc_url( rest_url( 'wsm/v1' ) ); ?>',
			nonce: '<?php echo esc_js( wp_create_nonce( 'wp_rest' ) ); ?>',
			panelUrl: '<?php echo esc_url( wsm_panel_url() ); ?>',
			sessionToken: '<?php echo esc_js( $_COOKIE['wsm_session'] ?? '' ); ?>'
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
				<span class="wsm-text-xs wsm-text-slate-500 wsm-block">کاربر فعلی:</span>
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
					<span>داشبورد</span>
				</a>
				<a href="<?php echo esc_url( wsm_panel_url( 'orders' ) ); ?>" class="wsm-flex wsm-items-center wsm-px-4 wsm-py-3 wsm-text-sm wsm-font-medium wsm-rounded-xl wsm-transition-colors <?php echo $is_active('orders') ? 'wsm-bg-indigo-600/10 wsm-text-indigo-400' : 'wsm-text-slate-400 hover:wsm-bg-slate-800/50 hover:wsm-text-slate-100'; ?>">
					<svg style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="wsm-ml-3"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
					<span>سفارش‌ها</span>
				</a>
				<a href="<?php echo esc_url( wsm_panel_url( 'products' ) ); ?>" class="wsm-flex wsm-items-center wsm-px-4 wsm-py-3 wsm-text-sm wsm-font-medium wsm-rounded-xl wsm-transition-colors <?php echo $is_active('products') ? 'wsm-bg-indigo-600/10 wsm-text-indigo-400' : 'wsm-text-slate-400 hover:wsm-bg-slate-800/50 hover:wsm-text-slate-100'; ?>">
					<svg style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="wsm-ml-3"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
					<span>محصولات</span>
				</a>
				<a href="<?php echo esc_url( wsm_panel_url( 'categories' ) ); ?>" class="wsm-flex wsm-items-center wsm-px-4 wsm-py-3 wsm-text-sm wsm-font-medium wsm-rounded-xl wsm-transition-colors <?php echo $is_active('categories') ? 'wsm-bg-indigo-600/10 wsm-text-indigo-400' : 'wsm-text-slate-400 hover:wsm-bg-slate-800/50 hover:wsm-text-slate-100'; ?>">
					<svg style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="wsm-ml-3"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
					<span>دسته‌بندی‌ها</span>
				</a>
				<a href="<?php echo esc_url( wsm_panel_url( 'coupons' ) ); ?>" class="wsm-flex wsm-items-center wsm-px-4 wsm-py-3 wsm-text-sm wsm-font-medium wsm-rounded-xl wsm-transition-colors <?php echo $is_active('coupons') ? 'wsm-bg-indigo-600/10 wsm-text-indigo-400' : 'wsm-text-slate-400 hover:wsm-bg-slate-800/50 hover:wsm-text-slate-100'; ?>">
					<svg style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="wsm-ml-3"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
					<span>تخفیف‌ها</span>
				</a>
				<a href="<?php echo esc_url( wsm_panel_url( 'reports' ) ); ?>" class="wsm-flex wsm-items-center wsm-px-4 wsm-py-3 wsm-text-sm wsm-font-medium wsm-rounded-xl wsm-transition-colors <?php echo $is_active('reports') ? 'wsm-bg-indigo-600/10 wsm-text-indigo-400' : 'wsm-text-slate-400 hover:wsm-bg-slate-800/50 hover:wsm-text-slate-100'; ?>">
					<svg style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="wsm-ml-3"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
					<span>گزارش‌ها</span>
				</a>
				
				<!-- Back to Site Link -->
				<a href="<?php echo esc_url( home_url() ); ?>" class="wsm-flex wsm-items-center wsm-px-4 wsm-py-3 wsm-text-sm wsm-font-medium wsm-text-slate-400 wsm-rounded-xl wsm-transition-colors hover:wsm-bg-slate-800/50 hover:wsm-text-slate-100">
					<svg style="width: 20px; height: 20px;" class="wsm-ml-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
					<span>بازگشت به سایت</span>
				</a>
			</nav>
			
			<div class="wsm-p-4 wsm-border-t wsm-border-slate-800">
				<!-- Using custom logout action link handler in our login form / JS instead of WP-login standard direct redirects -->
				<button id="wsm-logout-btn" class="wsm-flex wsm-items-center wsm-w-full wsm-px-4 wsm-py-3 wsm-text-sm wsm-font-medium wsm-text-rose-400 wsm-rounded-xl wsm-transition-colors hover:wsm-bg-rose-500/10">
					<svg style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="wsm-ml-3"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
					<span>خروج از حساب</span>
				</button>
			</div>
		</aside>

		<!-- Main Content View Area -->
		<div class="wsm-flex-1 wsm-flex wsm-flex-col wsm-overflow-hidden">
			<?php
			$back_url = '';
			$back_label = '';
			if ( isset( $view ) && 'dashboard' !== $view ) {
				if ( in_array( $view, [ 'orders/list', 'products/list', 'categories/list', 'coupons/list', 'reports/dashboard' ], true ) ) {
					$back_url = wsm_panel_url();
					$back_label = 'بازگشت به داشبورد';
				} elseif ( 'orders/detail' === $view ) {
					$back_url = wsm_panel_url( 'orders' );
					$back_label = 'بازگشت به سفارش‌ها';
				} elseif ( 'products/edit' === $view ) {
					$back_url = wsm_panel_url( 'products' );
					$back_label = 'بازگشت به محصولات';
				} elseif ( 'coupons/edit' === $view ) {
					$back_url = wsm_panel_url( 'coupons' );
					$back_label = 'بازگشت به تخفیف‌ها';
				} elseif ( in_array( $view, [ 'reports/sales', 'reports/products', 'reports/customers' ], true ) ) {
					$back_url = wsm_panel_url( 'reports' );
					$back_label = 'بازگشت به گزارش‌ها';
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
							<span>&rarr;</span> <span class="wsm-hidden sm:wsm-inline"><?php echo esc_html( $back_label ); ?></span><span class="sm:wsm-hidden">بازگشت</span>
						</a>
					<?php elseif ( isset( $view ) && 'dashboard' === $view ) : ?>
						<a href="<?php echo esc_url( home_url() ); ?>" class="wsm-flex wsm-items-center wsm-gap-1.5 wsm-px-3 wsm-py-1.5 wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-bg-slate-800/50 hover:wsm-bg-slate-800 wsm-rounded-xl wsm-transition-colors">
							<svg style="width: 14px; height: 14px;" class="wsm-ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
							<span class="wsm-hidden sm:wsm-inline">بازگشت به سایت</span><span class="sm:wsm-hidden">سایت</span>
						</a>
					<?php endif; ?>
				</div>
				<div class="wsm-flex wsm-items-center wsm-space-x-4 wsm-space-x-reverse">
					<span class="wsm-text-sm wsm-text-slate-400 wsm-hidden md:wsm-inline"><?php echo esc_html( $current_user->display_name ); ?></span>
				</div>
			</header>

			<!-- Page Content View -->
			<main class="wsm-flex-1 wsm-overflow-y-auto wsm-p-6 wsm-bg-slate-950">
				<?php
				if ( isset( $view_file ) && file_exists( $view_file ) ) {
					require $view_file;
				} else {
					?>
					<div class="wsm-bg-slate-900 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-p-8 wsm-text-center">
						<h2 class="wsm-text-xl wsm-font-bold wsm-text-slate-100 wsm-mb-2">خوش آمدید!</h2>
						<p class="wsm-text-slate-400">بخش داشبورد در حال حاضر در دسترس است.</p>
					</div>
					<?php
				}
				?>
			</main>
		</div>
	</div>
<?php endif; ?>

<!-- Core JavaScript -->
<script src="<?php echo esc_url( WSM_PLUGIN_URL . 'assets/js/wsm-panel.js' ); ?>"></script>
<script src="https://unpkg.com/@majidh1/jalalidatepicker/dist/jalalidatepicker.min.js"></script>
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

		sidebar.classList.remove("wsm-translate-x-full");
		sidebar.classList.add("wsm-translate-x-0");
	}

	function closeSidebar() {
		if (!sidebar || !backdrop) return;
		backdrop.classList.remove("wsm-opacity-100");
		backdrop.classList.add("wsm-opacity-0");
		
		sidebar.classList.remove("wsm-translate-x-0");
		sidebar.classList.add("wsm-translate-x-full");

		setTimeout(() => {
			if (backdrop.classList.contains("wsm-opacity-0")) {
				backdrop.classList.add("wsm-hidden");
			}
		}, 300);
	}

	if (toggleBtn) toggleBtn.addEventListener("click", openSidebar);
	if (closeBtn) closeBtn.addEventListener("click", closeSidebar);
	if (backdrop) backdrop.addEventListener("click", closeSidebar);

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

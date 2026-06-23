<?php
/**
 * Custom Store Admin Panel Base Layout Template
 *
 * @package KarasuWooPannel
 * @version 1.0.8
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
		<!-- Sidebar -->
		<aside class="wsm-hidden md:wsm-flex md:wsm-flex-col md:wsm-w-64 wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border-l wsm-border-slate-800">
			<div class="wsm-flex wsm-items-center wsm-justify-center wsm-h-16 wsm-border-b wsm-border-slate-800 wsm-px-6">
				<span class="wsm-text-lg wsm-font-bold wsm-bg-gradient-to-r wsm-from-indigo-400 wsm-to-cyan-400 wsm-bg-clip-text wsm-text-transparent">
					KarasuWooPannel
				</span>
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
					<span class="wsm-ml-3">داشبورد</span>
				</a>
				<a href="<?php echo esc_url( wsm_panel_url( 'orders' ) ); ?>" class="wsm-flex wsm-items-center wsm-px-4 wsm-py-3 wsm-text-sm wsm-font-medium wsm-rounded-xl wsm-transition-colors <?php echo $is_active('orders') ? 'wsm-bg-indigo-600/10 wsm-text-indigo-400' : 'wsm-text-slate-400 hover:wsm-bg-slate-800/50 hover:wsm-text-slate-100'; ?>">
					<span class="wsm-ml-3">سفارش‌ها</span>
				</a>
				<a href="<?php echo esc_url( wsm_panel_url( 'products' ) ); ?>" class="wsm-flex wsm-items-center wsm-px-4 wsm-py-3 wsm-text-sm wsm-font-medium wsm-rounded-xl wsm-transition-colors <?php echo $is_active('products') ? 'wsm-bg-indigo-600/10 wsm-text-indigo-400' : 'wsm-text-slate-400 hover:wsm-bg-slate-800/50 hover:wsm-text-slate-100'; ?>">
					<span class="wsm-ml-3">محصولات</span>
				</a>
				<a href="<?php echo esc_url( wsm_panel_url( 'categories' ) ); ?>" class="wsm-flex wsm-items-center wsm-px-4 wsm-py-3 wsm-text-sm wsm-font-medium wsm-rounded-xl wsm-transition-colors <?php echo $is_active('categories') ? 'wsm-bg-indigo-600/10 wsm-text-indigo-400' : 'wsm-text-slate-400 hover:wsm-bg-slate-800/50 hover:wsm-text-slate-100'; ?>">
					<span class="wsm-ml-3">دسته‌بندی‌ها</span>
				</a>
				<a href="<?php echo esc_url( wsm_panel_url( 'coupons' ) ); ?>" class="wsm-flex wsm-items-center wsm-px-4 wsm-py-3 wsm-text-sm wsm-font-medium wsm-rounded-xl wsm-transition-colors <?php echo $is_active('coupons') ? 'wsm-bg-indigo-600/10 wsm-text-indigo-400' : 'wsm-text-slate-400 hover:wsm-bg-slate-800/50 hover:wsm-text-slate-100'; ?>">
					<span class="wsm-ml-3">کوپن‌ها</span>
				</a>
				<a href="<?php echo esc_url( wsm_panel_url( 'reports' ) ); ?>" class="wsm-flex wsm-items-center wsm-px-4 wsm-py-3 wsm-text-sm wsm-font-medium wsm-rounded-xl wsm-transition-colors <?php echo $is_active('reports') ? 'wsm-bg-indigo-600/10 wsm-text-indigo-400' : 'wsm-text-slate-400 hover:wsm-bg-slate-800/50 hover:wsm-text-slate-100'; ?>">
					<span class="wsm-ml-3">گزارش‌ها</span>
				</a>
				
				<!-- Back to Site Link -->
				<a href="<?php echo esc_url( home_url() ); ?>" class="wsm-flex wsm-items-center wsm-px-4 wsm-py-3 wsm-text-sm wsm-font-medium wsm-text-slate-400 wsm-rounded-xl wsm-transition-colors hover:wsm-bg-slate-800/50 hover:wsm-text-slate-100">
					<svg class="wsm-w-5 wsm-h-5 wsm-ml-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
					<span>بازگشت به سایت</span>
				</a>
			</nav>
			<div class="wsm-p-4 wsm-border-t wsm-border-slate-800">
				<!-- Using custom logout action link handler in our login form / JS instead of WP-login standard direct redirects -->
				<button id="wsm-logout-btn" class="wsm-flex wsm-items-center wsm-w-full wsm-px-4 wsm-py-3 wsm-text-sm wsm-font-medium wsm-text-rose-400 wsm-rounded-xl wsm-transition-colors hover:wsm-bg-rose-500/10">
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
					$back_label = 'بازگشت به کوپن‌ها';
				} elseif ( in_array( $view, [ 'reports/sales', 'reports/products', 'reports/customers' ], true ) ) {
					$back_url = wsm_panel_url( 'reports' );
					$back_label = 'بازگشت به گزارش‌ها';
				}
			}
			?>
			<!-- Header -->
			<header class="wsm-h-16 wsm-bg-slate-900/40 wsm-backdrop-blur-md wsm-border-b wsm-border-slate-800 wsm-flex wsm-items-center wsm-justify-between wsm-px-6">
				<div class="wsm-flex wsm-items-center md:wsm-hidden">
					<span class="wsm-text-lg wsm-font-bold wsm-bg-gradient-to-r wsm-from-indigo-400 wsm-to-cyan-400 wsm-bg-clip-text wsm-text-transparent">KarasuWooPannel</span>
				</div>
				<div class="wsm-flex wsm-items-center wsm-space-x-3 wsm-space-x-reverse">
					<?php if ( ! empty( $back_url ) ) : ?>
						<a href="<?php echo esc_url( $back_url ); ?>" class="wsm-flex wsm-items-center wsm-gap-1.5 wsm-px-3 wsm-py-1.5 wsm-text-xs wsm-font-semibold wsm-text-indigo-400 wsm-bg-indigo-600/10 hover:wsm-bg-indigo-600/20 wsm-rounded-xl wsm-transition-colors">
							<span>&rarr;</span> <?php echo esc_html( $back_label ); ?>
						</a>
					<?php elseif ( isset( $view ) && 'dashboard' === $view ) : ?>
						<a href="<?php echo esc_url( home_url() ); ?>" class="wsm-flex wsm-items-center wsm-gap-1.5 wsm-px-3 wsm-py-1.5 wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-bg-slate-800/50 hover:wsm-bg-slate-800 wsm-rounded-xl wsm-transition-colors">
							<svg class="wsm-w-3.5 wsm-h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg> بازگشت به سایت
						</a>
					<?php endif; ?>
				</div>
				<div class="wsm-flex wsm-items-center wsm-space-x-4 wsm-space-x-reverse">
					<span class="wsm-text-sm wsm-text-slate-400"><?php echo esc_html( $current_user->display_name ); ?></span>
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
</body>
</html>

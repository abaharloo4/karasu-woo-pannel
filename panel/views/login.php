<?php
/**
 * Custom Store Admin Login Form Template
 *
 * @package KarasuWooPannel
 * @version 1.1.1
 * @date 2026-06-23
 */

$primary_color = get_option( 'wsm_primary_color', '#6366f1' );
$accent_color  = get_option( 'wsm_accent_color', '#06b6d4' );

if ( ! function_exists( 'wsm_hex2rgb' ) ) {
	function wsm_hex2rgb( $hex ) {
		$hex = str_replace( '#', '', $hex );
		if ( strlen( $hex ) == 3 ) {
			$r = hexdec( substr( $hex, 0, 1 ) . substr( $hex, 0, 1 ) );
			$g = hexdec( substr( $hex, 1, 1 ) . substr( $hex, 1, 1 ) );
			$b = hexdec( substr( $hex, 2, 1 ) . substr( $hex, 2, 1 ) );
		} else {
			$r = hexdec( substr( $hex, 0, 2 ) );
			$g = hexdec( substr( $hex, 2, 2 ) );
			$b = hexdec( substr( $hex, 4, 2 ) );
		}
		return "$r, $g, $b";
	}
}

$primary_rgb = wsm_hex2rgb( $primary_color );
$accent_rgb  = wsm_hex2rgb( $accent_color );
?>
<!-- Dynamic user custom theme colors -->
<style>
	:root {
		--wsm-primary: <?php echo esc_html( $primary_color ); ?>;
		--wsm-accent: <?php echo esc_html( $accent_color ); ?>;
		--wsm-primary-rgb: <?php echo esc_html( $primary_rgb ); ?>;
		--wsm-accent-rgb: <?php echo esc_html( $accent_rgb ); ?>;
	}

	/* Theme overrides */
	.wsm-bg-indigo-600 {
		background-color: var(--wsm-primary) !important;
	}
	.hover\:wsm-bg-indigo-500:hover {
		background-color: var(--wsm-primary) !important;
		filter: brightness(1.1) !important;
	}
	.wsm-text-indigo-400 {
		color: var(--wsm-primary) !important;
		filter: brightness(1.2) !important;
	}
	.wsm-border-indigo-500, .focus\:wsm-border-indigo-500:focus, .hover\:wsm-border-indigo-500:hover {
		border-color: var(--wsm-primary) !important;
	}
	.wsm-from-indigo-400 {
		--tw-gradient-from: var(--wsm-primary) !important;
		--tw-gradient-to: rgba(var(--wsm-primary-rgb), 0) !important;
		--tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to) !important;
	}
	.wsm-to-cyan-400 {
		--tw-gradient-to: var(--wsm-accent) !important;
	}
</style>

<div class="wsm-w-full wsm-max-w-md">
	<div class="wsm-bg-slate-900/60 wsm-backdrop-blur-xl wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-shadow-2xl wsm-overflow-hidden wsm-p-8 md:wsm-p-10">
		<div class="wsm-text-center wsm-mb-8">
			<h1 class="wsm-text-2xl wsm-font-bold wsm-bg-gradient-to-r wsm-from-indigo-400 wsm-to-cyan-400 wsm-bg-clip-text wsm-text-transparent wsm-mb-2">
				<?php echo esc_html( __( 'پنل مدیریت کاراسو', 'karasu-woo-pannel' ) ); ?>
			</h1>
			<p class="wsm-text-sm wsm-text-slate-400"><?php echo esc_html( __( 'Log in to your dedicated store management panel', 'karasu-woo-pannel' ) ); ?></p>
		</div>

		<!-- Error Alert Box -->
		<div id="login-error-alert" class="wsm-hidden wsm-bg-rose-500/10 wsm-border wsm-border-rose-500/20 wsm-text-rose-400 wsm-text-sm wsm-rounded-2xl wsm-p-4 wsm-mb-6">
			<span id="login-error-message"></span>
		</div>

		<?php
		$custom_login_html = get_option( 'wsm_custom_html_login', '' );
		$custom_login_css  = get_option( 'wsm_custom_css_login', '' );
		$custom_login_js   = get_option( 'wsm_custom_js_login', '' );

		if ( ! empty( $custom_login_css ) ) {
			echo '<style>' . $custom_login_css . '</style>';
		}
		if ( ! empty( $custom_login_html ) ) {
			echo '<div class="wsm-custom-page-content wsm-mb-6 wsm-p-4 wsm-bg-slate-950/40 wsm-border wsm-border-slate-800/80 wsm-rounded-2xl wsm-text-sm">' . do_shortcode( $custom_login_html ) . '</div>';
		}
		if ( ! empty( $custom_login_js ) ) {
			echo '<script>' . $custom_login_js . '</script>';
		}
		?>

		<form id="wsm-login-form" class="wsm-space-y-6">
			<!-- Nonce Field -->
			<input type="hidden" name="wsm_login_nonce" id="wsm_login_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wsm_login_action' ) ); ?>">

			<div>
				<label for="username" class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-uppercase wsm-tracking-wider wsm-mb-2">
					<?php echo esc_html( __( 'Username or Email', 'karasu-woo-pannel' ) ); ?>
				</label>
				<input type="text" id="username" name="username" required
					class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-px-4 wsm-py-3.5 wsm-text-sm wsm-text-slate-100 wsm-placeholder-slate-600 focus:wsm-outline-none focus:wsm-border-indigo-500 focus:wsm-ring-1 focus:wsm-ring-indigo-500 wsm-transition-all"
					placeholder="<?php echo esc_attr( __( 'e.g. admin', 'karasu-woo-pannel' ) ); ?>">
			</div>

			<div>
				<label for="password" class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-uppercase wsm-tracking-wider wsm-mb-2">
					<?php echo esc_html( __( 'Password', 'karasu-woo-pannel' ) ); ?>
				</label>
				<div class="wsm-relative">
					<input type="password" id="password" name="password" required
						class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-pl-12 wsm-pr-4 wsm-py-3.5 wsm-text-sm wsm-text-slate-100 wsm-placeholder-slate-600 focus:wsm-outline-none focus:wsm-border-indigo-500 focus:wsm-ring-1 focus:wsm-ring-indigo-500 wsm-transition-all"
						placeholder="••••••••">
					<button type="button" id="toggle-password" class="wsm-absolute wsm-left-3 wsm-text-slate-500 hover:wsm-text-slate-300 wsm-flex wsm-items-center wsm-justify-center" style="height: 24px; top: calc(50% - 12px);">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="wsm-w-5 wsm-h-5" style="width: 20px; height: 20px;" id="eye-icon">
							<path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
							<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
						</svg>
					</button>
				</div>
			</div>

			<button type="submit" id="wsm-submit-btn"
				class="wsm-w-full wsm-bg-gradient-to-r wsm-from-indigo-600 wsm-to-indigo-500 hover:wsm-from-indigo-500 hover:wsm-to-indigo-400 wsm-text-white wsm-font-semibold wsm-rounded-2xl wsm-py-4 wsm-shadow-lg wsm-shadow-indigo-500/20 focus:wsm-outline-none focus:wsm-ring-2 focus:wsm-ring-indigo-500 focus:wsm-ring-offset-2 focus:wsm-ring-offset-slate-900 wsm-transition-all wsm-flex wsm-items-center wsm-justify-center">
				<span><?php echo esc_html( __( 'Log In', 'karasu-woo-pannel' ) ); ?></span>
			</button>
		</form>
	</div>
</div>

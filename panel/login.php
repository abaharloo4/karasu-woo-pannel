<?php
/**
 * Custom Store Admin Login Form Template
 *
 * @package KarasuWooPannel
 * @version 1.0.0
 * @date 2026-06-23
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div class="wsm-w-full wsm-max-w-md">
	<div class="wsm-bg-slate-900/60 wsm-backdrop-blur-xl wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-shadow-2xl wsm-overflow-hidden wsm-p-8 md:wsm-p-10">
		<div class="wsm-text-center wsm-mb-8">
			<h1 class="wsm-text-2xl wsm-font-bold wsm-bg-gradient-to-r wsm-from-indigo-400 wsm-to-cyan-400 wsm-bg-clip-text wsm-text-transparent wsm-mb-2">
				KarasuWooPannel
			</h1>
			<p class="wsm-text-sm wsm-text-slate-400">ورود به پنل مدیریت اختصاصی فروشگاه</p>
		</div>

		<!-- Error Alert Box -->
		<div id="login-error-alert" class="wsm-hidden wsm-bg-rose-500/10 wsm-border wsm-border-rose-500/20 wsm-text-rose-400 wsm-text-sm wsm-rounded-2xl wsm-p-4 wsm-mb-6">
			<span id="login-error-message"></span>
		</div>

		<form id="wsm-login-form" class="wsm-space-y-6">
			<!-- Nonce Field -->
			<input type="hidden" name="wsm_login_nonce" id="wsm_login_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wsm_login_action' ) ); ?>">

			<div>
				<label for="username" class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-uppercase wsm-tracking-wider wsm-mb-2">
					نام کاربری یا ایمیل
				</label>
				<input type="text" id="username" name="username" required
					class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-px-4 wsm-py-3.5 wsm-text-sm wsm-text-slate-100 wsm-placeholder-slate-600 focus:wsm-outline-none focus:wsm-border-indigo-500 focus:wsm-ring-1 focus:wsm-ring-indigo-500 wsm-transition-all"
					placeholder="مثال: admin">
			</div>

			<div>
				<label for="password" class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-uppercase wsm-tracking-wider wsm-mb-2">
					رمز عبور
				</label>
				<input type="password" id="password" name="password" required
					class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-px-4 wsm-py-3.5 wsm-text-sm wsm-text-slate-100 wsm-placeholder-slate-600 focus:wsm-outline-none focus:wsm-border-indigo-500 focus:wsm-ring-1 focus:wsm-ring-indigo-500 wsm-transition-all"
					placeholder="••••••••">
			</div>

			<button type="submit" id="wsm-submit-btn"
				class="wsm-w-full wsm-bg-gradient-to-r wsm-from-indigo-600 wsm-to-indigo-500 hover:wsm-from-indigo-500 hover:wsm-to-indigo-400 wsm-text-white wsm-font-semibold wsm-rounded-2xl wsm-py-4 wsm-shadow-lg wsm-shadow-indigo-500/20 focus:wsm-outline-none focus:wsm-ring-2 focus:wsm-ring-indigo-500 focus:wsm-ring-offset-2 focus:wsm-ring-offset-slate-900 wsm-transition-all wsm-flex wsm-items-center wsm-justify-center">
				<span>ورود به پنل</span>
			</button>
		</form>
	</div>
</div>

<?php
/**
 * Custom Store Admin Login Form Template
 *
 * @package KarasuWooPannel
 * @version 1.0.10
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
				<span>ورود به پنل</span>
			</button>
		</form>
	</div>
</div>

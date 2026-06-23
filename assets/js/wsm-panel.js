/**
 * KarasuWooPannel Client JavaScript Library
 *
 * @package KarasuWooPannel
 * @version 1.0.10
 * @date 2026-06-23
 */

(function() {
	'use strict';

	const { apiUrl, nonce, panelUrl, sessionToken } = window.wsmConfig ?? {};

	/**
	 * Wrapper for communicating with KarasuWooPannel REST endpoints.
	 *
	 * @param {string} endpoint API endpoint relative to namespace.
	 * @param {Object} options Fetch call options.
	 * @returns {Promise<Object>} Response JSON.
	 */
	async function wsmFetch(endpoint, options = {}) {
		const defaults = {
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': nonce,
			},
		};

		if (sessionToken) {
			defaults.headers['X-WSM-Token'] = sessionToken;
		}

		const mergedOptions = {
			...defaults,
			...options,
			headers: {
				...defaults.headers,
				...(options.headers ?? {}),
			}
		};

		try {
			const response = await fetch(apiUrl + endpoint, mergedOptions);

			if (response.status === 401 && !endpoint.includes('/auth/login')) {
				window.location.href = panelUrl + '/login';
				return;
			}

			const data = await response.json();

			if (!response.ok || !data.success) {
				throw new Error(data.message ?? 'خطای ناشناخته در سرور');
			}

			return data;
		} catch (error) {
			wsmShowError(error.message);
			throw error;
		}
	}

	/**
	 * Escape HTML to prevent XSS.
	 *
	 * @param {string} str Target string.
	 * @returns {string} Escaped string.
	 */
	function wsmEscHtml(str) {
		const div = document.createElement('div');
		div.appendChild(document.createTextNode(String(str)));
		return div.innerHTML;
	}

	/**
	 * Display error alerts in the template UI.
	 *
	 * @param {string} message Error message.
	 */
	function wsmShowError(message) {
		const alertBox = document.getElementById('login-error-alert');
		const messageEl = document.getElementById('login-error-message');
		if (alertBox && messageEl) {
			messageEl.textContent = message;
			alertBox.classList.remove('wsm-hidden');
		}
	}

	// Export global helpers.
	window.WSM = { fetch: wsmFetch, escHtml: wsmEscHtml, showError: wsmShowError };

	// Setup DOM triggers.
	document.addEventListener('DOMContentLoaded', () => {
		// 1. Submit Login Credentials.
		const loginForm = document.getElementById('wsm-login-form');
		if (loginForm) {
			loginForm.addEventListener('submit', async (e) => {
				e.preventDefault();
				const usernameInput = document.getElementById('username');
				const passwordInput = document.getElementById('password');
				const nonceInput = document.getElementById('wsm_login_nonce');
				const submitBtn = document.getElementById('wsm-submit-btn');

				if (!usernameInput || !passwordInput) {
					return;
				}

				const submitBtnText = submitBtn ? submitBtn.innerHTML : '';
				if (submitBtn) {
					submitBtn.disabled = true;
					submitBtn.innerHTML = '<span>در حال بررسی...</span>';
				}

				const alertBox = document.getElementById('login-error-alert');
				if (alertBox) {
					alertBox.classList.add('wsm-hidden');
				}

				try {
					await wsmFetch('/auth/login', {
						method: 'POST',
						body: JSON.stringify({
							username: usernameInput.value,
							password: passwordInput.value,
							nonce: nonceInput ? nonceInput.value : '',
						})
					});

					// Redirect to panel dashboard.
					window.location.href = panelUrl;
				} catch (error) {
					if (submitBtn) {
						submitBtn.disabled = false;
						submitBtn.innerHTML = submitBtnText;
					}
				}
			});
		}

		// 2. Perform Logout operation.
		const logoutBtn = document.getElementById('wsm-logout-btn');
		if (logoutBtn) {
			logoutBtn.addEventListener('click', async (e) => {
				e.preventDefault();
				if (confirm('آیا مایل به خروج از حساب کاربری خود هستید؟')) {
					try {
						await wsmFetch('/auth/logout', { method: 'POST' });
					} catch (err) {
						// Fail-safe redirect even if REST endpoint fails
					}
					window.location.href = panelUrl + '/login';
				}
			});
		}

		// 3. Toggle Password Visibility.
		const togglePasswordBtn = document.getElementById('toggle-password');
		if (togglePasswordBtn) {
			togglePasswordBtn.addEventListener('click', () => {
				const passwordInput = document.getElementById('password');
				if (passwordInput) {
					const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
					passwordInput.setAttribute('type', type);
					if (type === 'text') {
						togglePasswordBtn.classList.add('wsm-text-indigo-400');
						togglePasswordBtn.classList.remove('wsm-text-slate-500');
					} else {
						togglePasswordBtn.classList.remove('wsm-text-indigo-400');
						togglePasswordBtn.classList.add('wsm-text-slate-500');
					}
				}
			});
		}
	});
})();

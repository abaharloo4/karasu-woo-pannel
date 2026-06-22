/**
 * KarasuWooPannel SMS Settings and Logs Script
 *
 * @package KarasuWooPannel
 * @version 1.0.0
 * @date 2026-06-23
 */

(function() {
	'use strict';

	let currentLogPage = 1;
	const perPage = 20;

	// Persian mappings for events
	const eventLabels = {
		'pending': 'در انتظار پرداخت (مشتری)',
		'processing': 'در حال پردازش (مشتری)',
		'on-hold': 'معلق (مشتری)',
		'completed': 'تکمیل شده (مشتری)',
		'cancelled': 'لغو شده (مشتری)',
		'refunded': 'مرجوع شده (مشتری)',
		'failed': 'ناموفق (مشتری)',
		'new_order': 'سفارش جدید (مدیر)',
		'low_stock': 'کمبود موجودی (مدیر)',
		'test_message': 'پیامک تست',
	};

	/**
	 * Load SMS templates and populate the form fields.
	 */
	async function loadSmsTemplates() {
		const form = document.getElementById('wsm-sms-settings-form');
		if (!form) return;

		try {
			const response = await WSM.fetch('/sms/templates', { method: 'GET' });
			const templates = response.data;

			const keys = ['pending', 'processing', 'on-hold', 'completed', 'cancelled', 'refunded', 'failed', 'new_order', 'low_stock'];

			keys.forEach(key => {
				const checkbox = document.getElementById(`sms-${key}-enabled`);
				const textarea = document.getElementById(`sms-${key}-text`);

				if (templates[key]) {
					if (checkbox) checkbox.checked = !!templates[key].enabled;
					if (textarea) textarea.value = templates[key].text || '';
				}
			});
		} catch (err) {
			// Handled globally
		}
	}

	/**
	 * Save updated SMS templates.
	 */
	async function saveSmsTemplates(e) {
		e.preventDefault();
		const submitBtn = document.getElementById('wsm-save-sms-btn');
		const originalText = submitBtn.innerHTML;
		submitBtn.disabled = true;
		submitBtn.innerHTML = 'در حال ذخیره...';

		const keys = ['pending', 'processing', 'on-hold', 'completed', 'cancelled', 'refunded', 'failed', 'new_order', 'low_stock'];
		const payload = {};

		keys.forEach(key => {
			const checkbox = document.getElementById(`sms-${key}-enabled`);
			const textarea = document.getElementById(`sms-${key}-text`);

			payload[key] = {
				enabled: checkbox ? checkbox.checked : false,
				text: textarea ? textarea.value : '',
			};
		});

		try {
			await WSM.fetch('/sms/templates', {
				method: 'POST',
				body: JSON.stringify(payload)
			});
		} catch (err) {
			// Handled globally
		} finally {
			submitBtn.disabled = false;
			submitBtn.innerHTML = originalText;
		}
	}

	/**
	 * Send test SMS.
	 */
	async function sendTestSms() {
		const phone = document.getElementById('test-phone')?.value;
		const message = document.getElementById('test-message')?.value;
		const btn = document.getElementById('wsm-send-test-sms-btn');

		if (!phone || !message) {
			alert('لطفاً شماره موبایل و متن پیامک تست را وارد کنید.');
			return;
		}

		const originalText = btn.innerHTML;
		btn.disabled = true;
		btn.innerHTML = 'در حال ارسال...';

		try {
			await WSM.fetch('/sms/test', {
				method: 'POST',
				body: JSON.stringify({ phone, message })
			});
		} catch (err) {
			// Handled globally
		} finally {
			btn.disabled = false;
			btn.innerHTML = originalText;
		}
	}

	/**
	 * Load outgoing SMS logs.
	 */
	async function loadSmsLogs() {
		const tableBody = document.getElementById('sms-logs-table-body');
		if (!tableBody) return;

		tableBody.innerHTML = `
			<tr>
				<td colspan="7" class="wsm-px-6 wsm-py-12 wsm-text-center wsm-text-slate-500 wsm-animate-pulse">
					در حال دریافت لاگ‌های پیامک...
				</td>
			</tr>
		`;

		try {
			const queryParams = new URLSearchParams({
				page: currentLogPage,
				per_page: perPage,
			});

			const response = await WSM.fetch('/sms/logs?' + queryParams.toString(), { method: 'GET' });
			const { logs, total, pages } = response.data;

			if (logs.length === 0) {
				tableBody.innerHTML = `
					<tr>
						<td colspan="7" class="wsm-px-6 wsm-py-12 wsm-text-center wsm-text-slate-500">
							هیچ لاگی ثبت نشده است.
						</td>
					</tr>
				`;
				updatePaginationInfo(0, 0, 0);
				renderPaginationControls(0);
				return;
			}

			let rowsHtml = '';
			logs.forEach(log => {
				const statusBadge = Number(log.status) === 1 
					? 'wsm-bg-emerald-500/10 wsm-text-emerald-400 wsm-border-emerald-500/20' 
					: 'wsm-bg-rose-500/10 wsm-text-rose-400 wsm-border-rose-500/20';
				const statusLabel = Number(log.status) === 1 ? 'موفق' : 'ناموفق';
				const eventLabel  = eventLabels[log.event_type] || log.event_type;

				rowsHtml += `
					<tr class="wsm-border-b wsm-border-slate-800/40 hover:wsm-bg-slate-900/20 wsm-transition-colors">
						<td class="wsm-px-6 wsm-py-4 wsm-text-sm wsm-text-slate-400">
							${log.related_id ? `#${log.related_id}` : '—'}
						</td>
						<td class="wsm-px-6 wsm-py-4 wsm-text-sm wsm-font-bold wsm-text-slate-200">
							${WSM.escHtml(eventLabel)}
						</td>
						<td class="wsm-px-6 wsm-py-4 wsm-text-sm wsm-text-slate-300 wsm-font-mono" dir="ltr">
							${WSM.escHtml(log.recipient)}
						</td>
						<td class="wsm-px-6 wsm-py-4 wsm-text-xs wsm-text-slate-400 wsm-max-w-xs wsm-truncate" title="${WSM.escHtml(log.message)}">
							${WSM.escHtml(log.message)}
						</td>
						<td class="wsm-px-6 wsm-py-4 wsm-text-sm">
							<span class="wsm-px-2.5 wsm-py-1 wsm-text-xs wsm-font-semibold wsm-rounded-full wsm-border ${statusBadge}">
								${statusLabel}
							</span>
						</td>
						<td class="wsm-px-6 wsm-py-4 wsm-text-xs wsm-text-slate-500">
							${WSM.escHtml(log.api_response || '—')}
						</td>
						<td class="wsm-px-6 wsm-py-4 wsm-text-xs wsm-text-slate-400 wsm-font-mono" dir="ltr">
							${WSM.escHtml(log.sent_at_jalali)}
						</td>
					</tr>
				`;
			});

			tableBody.innerHTML = rowsHtml;

			const start = (currentLogPage - 1) * perPage + 1;
			const end = Math.min(currentLogPage * perPage, total);
			updatePaginationInfo(start, end, total);
			renderPaginationControls(pages);

		} catch (err) {
			tableBody.innerHTML = `
				<tr>
					<td colspan="7" class="wsm-px-6 wsm-py-12 wsm-text-center wsm-text-rose-400">
						خطا در دریافت لاگ‌های پیامک.
					</td>
				</tr>
			`;
		}
	}

	function updatePaginationInfo(start, end, total) {
		const startEl = document.getElementById('logs-count-start');
		const endEl   = document.getElementById('logs-count-end');
		const totalEl = document.getElementById('logs-count-total');

		if (startEl) startEl.textContent = start.toLocaleString('fa-IR');
		if (endEl) endEl.textContent = end.toLocaleString('fa-IR');
		if (totalEl) totalEl.textContent = total.toLocaleString('fa-IR');
	}

	function renderPaginationControls(totalPages) {
		const container = document.getElementById('logs-pagination-controls');
		if (!container) return;

		let html = '';
		if (totalPages > 1) {
			html += `
				<button class="pag-btn wsm-px-3 wsm-py-1.5 wsm-text-xs wsm-bg-slate-900 wsm-border wsm-border-slate-800 wsm-rounded-xl hover:wsm-bg-slate-800 ${currentLogPage === 1 ? 'wsm-opacity-50 wsm-pointer-events-none' : ''}" data-page="${currentLogPage - 1}">
					قبلی
				</button>
			`;

			for (let i = 1; i <= totalPages; i++) {
				const activeClass = i === currentLogPage ? 'wsm-bg-indigo-600 wsm-text-white' : 'wsm-bg-slate-900 wsm-text-slate-400 hover:wsm-bg-slate-800';
				html += `
					<button class="pag-btn wsm-px-3 wsm-py-1.5 wsm-text-xs wsm-border wsm-border-slate-800 wsm-rounded-xl ${activeClass}" data-page="${i}">
						${i.toLocaleString('fa-IR')}
					</button>
				`;
			}

			html += `
				<button class="pag-btn wsm-px-3 wsm-py-1.5 wsm-text-xs wsm-bg-slate-900 wsm-border wsm-border-slate-800 wsm-rounded-xl hover:wsm-bg-slate-800 ${currentLogPage === totalPages ? 'wsm-opacity-50 wsm-pointer-events-none' : ''}" data-page="${currentLogPage + 1}">
					بعدی
				</button>
			`;
		}

		container.innerHTML = html;

		container.querySelectorAll('.pag-btn').forEach(btn => {
			btn.addEventListener('click', (e) => {
				e.preventDefault();
				currentLogPage = parseInt(btn.getAttribute('data-page'));
				loadSmsLogs();
			});
		});
	}

	// Bootstrap
	document.addEventListener('DOMContentLoaded', () => {
		loadSmsTemplates();
		loadSmsLogs();

		document.getElementById('wsm-sms-settings-form')?.addEventListener('submit', saveSmsTemplates);
		document.getElementById('wsm-send-test-sms-btn')?.addEventListener('click', sendTestSms);
	});

})();

/**
 * KarasuWooPannel Coupons Management Script
 *
 * @package KarasuWooPannel
 * @version 1.0.10
 * @date 2026-06-23
 */

(function() {
	'use strict';

	let currentPage = 1;
	const perPage = 20;

	// 1. COUPON LIST HANDLER
	async function loadCouponsList() {
		const tableBody = document.getElementById('coupons-table-body');
		if (!tableBody) return;

		const search = document.getElementById('coupon-search')?.value || '';

		tableBody.innerHTML = `
			<tr>
				<td colspan="6" class="wsm-px-6 wsm-py-12 wsm-text-center wsm-text-slate-500 wsm-animate-pulse">
					در حال دریافت لیست تخفیف‌ها...
				</td>
			</tr>
		`;

		try {
			const queryParams = new URLSearchParams({
				page: currentPage,
				per_page: perPage,
				search,
			});

			const response = await WSM.fetch('/coupons?' + queryParams.toString(), { method: 'GET' });
			const { coupons, total, pages } = response.data;

			if (coupons.length === 0) {
				tableBody.innerHTML = `
					<tr>
						<td colspan="6" class="wsm-px-6 wsm-py-12 wsm-text-center wsm-text-slate-500">
							هیچ کد تخفیفی یافت نشد.
						</td>
					</tr>
				`;
				updatePaginationInfo(0, 0, 0);
				renderPaginationControls(0);
				return;
			}

			let rowsHtml = '';
			coupons.forEach(c => {
				const discountLabel = c.discount_type === 'percent' 
					? c.amount.toLocaleString('fa-IR') + ' ٪' 
					: c.amount.toLocaleString('fa-IR') + ' تومان';

				rowsHtml += `
					<tr class="wsm-border-b wsm-border-slate-800/40 hover:wsm-bg-slate-900/20 wsm-transition-colors">
						<td class="wsm-px-6 wsm-py-4 wsm-text-sm wsm-font-bold wsm-text-slate-200">${WSM.escHtml(c.code)}</td>
						<td class="wsm-px-6 wsm-py-4 wsm-text-sm wsm-text-slate-400">${WSM.escHtml(c.discount_type_label)}</td>
						<td class="wsm-px-6 wsm-py-4 wsm-text-sm wsm-font-semibold wsm-text-indigo-400">${discountLabel}</td>
						<td class="wsm-px-6 wsm-py-4 wsm-text-sm wsm-text-slate-400">
							${c.usage_count.toLocaleString('fa-IR')} ${c.usage_limit ? '/ ' + c.usage_limit.toLocaleString('fa-IR') : ''}
						</td>
						<td class="wsm-px-6 wsm-py-4 wsm-text-sm wsm-text-slate-400 wsm-font-mono">${WSM.escHtml(c.date_expires_jalali)}</td>
						<td class="wsm-px-6 wsm-py-4 wsm-text-center wsm-text-sm wsm-space-x-2 wsm-space-x-reverse">
							<a href="${window.wsmConfig.panelUrl}/coupons/edit?id=${c.id}" class="wsm-text-indigo-400 hover:wsm-text-indigo-300 wsm-font-semibold">ویرایش</a>
							<button class="delete-coupon-btn wsm-text-rose-400 hover:wsm-text-rose-300" data-id="${c.id}">حذف</button>
						</td>
					</tr>
				`;
			});

			tableBody.innerHTML = rowsHtml;

			// Bind delete coupon triggers.
			document.querySelectorAll('.delete-coupon-btn').forEach(btn => {
				btn.addEventListener('click', async (e) => {
					e.preventDefault();
					const id = btn.getAttribute('data-id');
					if (confirm('آیا از حذف دائم این کد تخفیف اطمینان دارید؟')) {
						try {
							await WSM.fetch(`/coupons/${id}`, { method: 'DELETE' });
							loadCouponsList();
						} catch (err) {
							// Handled globally
						}
					}
				});
			});

			const start = (currentPage - 1) * perPage + 1;
			const end = Math.min(currentPage * perPage, total);
			updatePaginationInfo(start, end, total);
			renderPaginationControls(pages);

		} catch (error) {
			tableBody.innerHTML = `
				<tr>
					<td colspan="6" class="wsm-px-6 wsm-py-12 wsm-text-center wsm-text-rose-400">
						خطا در دریافت اطلاعات تخفیف‌ها.
					</td>
				</tr>
			`;
		}
	}

	function updatePaginationInfo(start, end, total) {
		const startEl = document.getElementById('coupons-count-start');
		const endEl = document.getElementById('coupons-count-end');
		const totalEl = document.getElementById('coupons-count-total');

		if (startEl) startEl.textContent = start.toLocaleString('fa-IR');
		if (endEl) endEl.textContent = end.toLocaleString('fa-IR');
		if (totalEl) totalEl.textContent = total.toLocaleString('fa-IR');
	}

	function renderPaginationControls(totalPages) {
		const container = document.getElementById('coupons-pagination-controls');
		if (!container) return;

		let html = '';
		if (totalPages > 1) {
			html += `
				<button class="pag-btn wsm-px-3 wsm-py-1.5 wsm-text-xs wsm-bg-slate-900 wsm-border wsm-border-slate-800 wsm-rounded-xl hover:wsm-bg-slate-800 ${currentPage === 1 ? 'wsm-opacity-50 wsm-pointer-events-none' : ''}" data-page="${currentPage - 1}">
					قبلی
				</button>
			`;

			for (let i = 1; i <= totalPages; i++) {
				const activeClass = i === currentPage ? 'wsm-bg-indigo-600 wsm-text-white' : 'wsm-bg-slate-900 wsm-text-slate-400 hover:wsm-bg-slate-800';
				html += `
					<button class="pag-btn wsm-px-3 wsm-py-1.5 wsm-text-xs wsm-border wsm-border-slate-800 wsm-rounded-xl ${activeClass}" data-page="${i}">
						${i.toLocaleString('fa-IR')}
					</button>
				`;
			}

			html += `
				<button class="pag-btn wsm-px-3 wsm-py-1.5 wsm-text-xs wsm-bg-slate-900 wsm-border wsm-border-slate-800 wsm-rounded-xl hover:wsm-bg-slate-800 ${currentPage === totalPages ? 'wsm-opacity-50 wsm-pointer-events-none' : ''}" data-page="${currentPage + 1}">
					بعدی
				</button>
			`;
		}

		container.innerHTML = html;

		container.querySelectorAll('.pag-btn').forEach(btn => {
			btn.addEventListener('click', (e) => {
				e.preventDefault();
				currentPage = parseInt(btn.getAttribute('data-page'));
				loadCouponsList();
			});
		});
	}

	// 2. FORM BUILDER & EDITOR HANDLER
	async function renderCouponForm(container, couponId) {
		let couponData = null;
		if (couponId > 0) {
			try {
				const response = await WSM.fetch(`/coupons/${couponId}`, { method: 'GET' });
				couponData = response.data;
			} catch (err) {
				container.innerHTML = `
					<div class="wsm-bg-slate-900 wsm-border wsm-border-rose-500/20 wsm-rounded-3xl wsm-p-8 wsm-text-center wsm-text-rose-400">
						خطا در دریافت اطلاعات کد تخفیف.
					</div>
				`;
				return;
			}
		}

		const isNew = !couponData;
		const pageTitle = isNew ? 'ایجاد کد تخفیف جدید' : `ویرایش کد تخفیف: ${WSM.escHtml(couponData.code)}`;

		container.innerHTML = `
			<div class="wsm-flex wsm-items-center wsm-space-x-3 wsm-space-x-reverse">
				<a href="${window.wsmConfig.panelUrl}/coupons" class="wsm-text-slate-400 hover:wsm-text-slate-200">
					&larr; بازگشت به تخفیف‌ها
				</a>
				<span class="wsm-text-slate-600">/</span>
				<h1 class="wsm-text-2xl wsm-font-bold wsm-text-slate-100">${pageTitle}</h1>
			</div>

			<form id="coupon-form" class="wsm-grid wsm-grid-cols-1 lg:wsm-grid-cols-3 wsm-gap-6">
				<!-- Right Column: Settings fields -->
				<div class="lg:wsm-col-span-2 wsm-space-y-6">
					<!-- General Section Card -->
					<div class="wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-p-6 wsm-shadow-lg wsm-space-y-4">
						<h3 class="wsm-font-semibold wsm-text-slate-200">مشخصات اصلی تخفیف</h3>
						
						<div class="wsm-grid wsm-grid-cols-1 md:wsm-grid-cols-2 wsm-gap-4">
							<div>
								<label for="c-code" class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-mb-2">کد تخفیف</label>
								<div class="wsm-flex wsm-space-x-2 wsm-space-x-reverse">
									<input type="text" id="c-code" required value="${isNew ? '' : WSM.escHtml(couponData.code)}" class="wsm-flex-1 wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-px-4 wsm-py-3 wsm-text-sm wsm-text-slate-200 focus:wsm-outline-none focus:wsm-border-indigo-500">
									<button type="button" id="wsm-gen-code-btn" class="wsm-bg-slate-950 wsm-border wsm-border-slate-800 hover:wsm-bg-slate-800 wsm-text-slate-300 wsm-text-xs wsm-px-3 wsm-rounded-2xl wsm-transition-colors">تولید کد</button>
								</div>
							</div>
							<div>
								<label for="c-type" class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-mb-2">نوع تخفیف</label>
								<select id="c-type" class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-px-4 wsm-py-3 wsm-text-sm focus:wsm-outline-none">
									<option value="percent" ${couponData?.discount_type === 'percent' ? 'selected' : ''}>درصدی</option>
									<option value="fixed_cart" ${couponData?.discount_type === 'fixed_cart' ? 'selected' : ''}>تخفیف ثابت سبد خرید</option>
									<option value="fixed_product" ${couponData?.discount_type === 'fixed_product' ? 'selected' : ''}>تخفیف ثابت محصول</option>
								</select>
							</div>
						</div>

						<div class="wsm-grid wsm-grid-cols-1 md:wsm-grid-cols-2 wsm-gap-4">
							<div>
								<label for="c-amount" class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-mb-2">میزان تخفیف (درصد یا تومان)</label>
								<input type="number" id="c-amount" required value="${isNew ? '' : couponData.amount}" class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-px-4 wsm-py-3 wsm-text-sm wsm-text-slate-200 focus:wsm-outline-none focus:wsm-border-indigo-500">
							</div>
							<div>
								<label for="c-expiry" class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-mb-2">تاریخ انقضا (مثال: ۱۴۰۵/۱۲/۲۹)</label>
								<input type="text" id="c-expiry" data-jdp placeholder="۱۴۰۵/۱۲/۲۹" value="${isNew ? '' : WSM.escHtml(couponData.date_expires)}" class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-px-4 wsm-py-3 wsm-text-sm wsm-text-slate-200 focus:wsm-outline-none focus:wsm-border-indigo-500">
							</div>
						</div>

						<div>
							<label for="c-desc" class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-mb-2">توضیحات کد تخفیف</label>
							<textarea id="c-desc" rows="3" class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-p-3 wsm-text-sm wsm-text-slate-200 focus:wsm-outline-none focus:wsm-border-indigo-500">${isNew ? '' : WSM.escHtml(couponData.description)}</textarea>
						</div>
					</div>

					<!-- Restrictions Card -->
					<div class="wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-p-6 wsm-shadow-lg wsm-space-y-4">
						<h3 class="wsm-font-semibold wsm-text-slate-200">محدودیت‌های استفاده</h3>
						
						<div class="wsm-grid wsm-grid-cols-1 md:wsm-grid-cols-2 wsm-gap-4">
							<div>
								<label for="c-min" class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-mb-2">حداقل خرید (تومان)</label>
								<input type="number" id="c-min" value="${isNew ? '' : couponData.minimum_amount}" class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-px-4 wsm-py-3 wsm-text-sm focus:wsm-outline-none">
							</div>
							<div>
								<label for="c-max" class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-mb-2">حداکثر خرید (تومان)</label>
								<input type="number" id="c-max" value="${isNew ? '' : couponData.maximum_amount}" class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-px-4 wsm-py-3 wsm-text-sm focus:wsm-outline-none">
							</div>
						</div>

						<div class="wsm-grid wsm-grid-cols-1 md:wsm-grid-cols-2 wsm-gap-4 wsm-pt-2">
							<label class="wsm-flex wsm-items-center wsm-text-sm wsm-text-slate-400 wsm-cursor-pointer">
								<input type="checkbox" id="c-individual" ${couponData?.individual_use ? 'checked' : ''} class="wsm-ml-2">
								استفاده انفرادی؟ (عدم ترکیب با بقیه کدهای تخفیف)
							</label>
							<label class="wsm-flex wsm-items-center wsm-text-sm wsm-text-slate-400 wsm-cursor-pointer">
								<input type="checkbox" id="c-exclude-sale" ${couponData?.exclude_sale_items ? 'checked' : ''} class="wsm-ml-2">
								عدم اعمال روی کالاهای حراج؟
							</label>
						</div>
					</div>
				</div>

				<!-- Left Column: Usage limits and Actions -->
				<div class="wsm-space-y-6">
					<!-- Usage limits Card -->
					<div class="wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-p-6 wsm-shadow-lg wsm-space-y-4">
						<h3 class="wsm-font-semibold wsm-text-slate-200">حدود استفاده</h3>
						<div>
							<label for="c-limit" class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-mb-2">محدودیت استفاده کل</label>
							<input type="number" id="c-limit" value="${isNew ? '' : couponData.usage_limit}" class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-px-4 wsm-py-3 wsm-text-sm focus:wsm-outline-none">
						</div>
						<div>
							<label for="c-limit-user" class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-mb-2">محدودیت استفاده برای هر کاربر</label>
							<input type="number" id="c-limit-user" value="${isNew ? '' : couponData.usage_limit_per_user}" class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-px-4 wsm-py-3 wsm-text-sm focus:wsm-outline-none">
						</div>
						<div class="wsm-pt-2">
							<label class="wsm-flex wsm-items-center wsm-text-sm wsm-text-slate-400 wsm-cursor-pointer">
								<input type="checkbox" id="c-shipping" ${couponData?.free_shipping ? 'checked' : ''} class="wsm-ml-2">
								حمل و نقل رایگان؟ (ارسال رایگان با این کد)
							</label>
						</div>
					</div>

					<!-- Submit Card -->
					<div class="wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-p-6 wsm-shadow-lg">
						<button type="submit" id="wsm-save-coupon-btn" class="wsm-w-full wsm-bg-indigo-600 hover:wsm-bg-indigo-500 wsm-text-white wsm-font-semibold wsm-rounded-2xl wsm-py-4 wsm-shadow-lg wsm-shadow-indigo-500/20 wsm-transition-colors">
							<span>ذخیره نهایی کد تخفیف</span>
						</button>
					</div>
				</div>
			</form>
		`;

		// Bind random code generator button
		document.getElementById('wsm-gen-code-btn')?.addEventListener('click', () => {
			const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
			let rand = '';
			for (let i = 0; i < 8; i++) {
				rand += chars.charAt(Math.floor(Math.random() * chars.length));
			}
			document.getElementById('c-code').value = 'COUPON-' + rand;
		});

		// Bind submit
		const form = document.getElementById('coupon-form');
		form?.addEventListener('submit', async (e) => {
			e.preventDefault();
			const submitBtn = document.getElementById('wsm-save-coupon-btn');
			const submitText = submitBtn.innerHTML;
			submitBtn.disabled = true;
			submitBtn.innerHTML = 'در حال ذخیره...';

			const payload = {
				code: document.getElementById('c-code').value,
				discount_type: document.getElementById('c-type').value,
				amount: document.getElementById('c-amount').value,
				date_expires: document.getElementById('c-expiry').value,
				description: document.getElementById('c-desc').value,
				minimum_amount: document.getElementById('c-min').value,
				maximum_amount: document.getElementById('c-max').value,
				individual_use: document.getElementById('c-individual').checked,
				exclude_sale_items: document.getElementById('c-exclude-sale').checked,
				usage_limit: document.getElementById('c-limit').value,
				usage_limit_per_user: document.getElementById('c-limit-user').value,
				free_shipping: document.getElementById('c-shipping').checked,
			};

			try {
				if (isNew) {
					await WSM.fetch('/coupons', {
						method: 'POST',
						body: JSON.stringify(payload)
					});
				} else {
					await WSM.fetch(`/coupons/${couponId}`, {
						method: 'PUT',
						body: JSON.stringify(payload)
					});
				}
				window.location.href = window.wsmConfig.panelUrl + '/coupons';
			} catch (err) {
				submitBtn.disabled = false;
				submitBtn.innerHTML = submitText;
			}
		});
	}

	// Bootstrap
	document.addEventListener('DOMContentLoaded', () => {
		loadCouponsList();

		// Bind search filters
		const search = document.getElementById('coupon-search');
		const clearBtn = document.getElementById('clear-coupon-filters');

		let searchTimeout;
		search?.addEventListener('input', () => {
			clearTimeout(searchTimeout);
			searchTimeout = setTimeout(() => {
				currentPage = 1;
				loadCouponsList();
			}, 400);
		});

		clearBtn?.addEventListener('click', (e) => {
			e.preventDefault();
			if (search) search.value = '';
			currentPage = 1;
			loadCouponsList();
		});

		const formContainer = document.getElementById('coupon-edit-container');
		if (formContainer) {
			const couponId = parseInt(formContainer.getAttribute('data-coupon-id') || '0');
			renderCouponForm(formContainer, couponId);
		}
	});

})();

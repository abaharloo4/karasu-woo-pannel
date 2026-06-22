/**
 * KarasuWooPannel Orders Management Script
 *
 * @package KarasuWooPannel
 * @version 1.0.1
 * @date 2026-06-23
 */

(function() {
	'use strict';

	// Whitelist of status badges classes.
	const statusClasses = {
		pending: 'wsm-bg-yellow-500/10 wsm-text-yellow-400 wsm-border-yellow-500/20',
		processing: 'wsm-bg-blue-500/10 wsm-text-blue-400 wsm-border-blue-500/20',
		'on-hold': 'wsm-bg-orange-500/10 wsm-text-orange-400 wsm-border-orange-500/20',
		completed: 'wsm-bg-emerald-500/10 wsm-text-emerald-400 wsm-border-emerald-500/20',
		cancelled: 'wsm-bg-rose-500/10 wsm-text-rose-400 wsm-border-rose-500/20',
		refunded: 'wsm-bg-purple-500/10 wsm-text-purple-400 wsm-border-purple-500/20',
		failed: 'wsm-bg-red-500/10 wsm-text-red-400 wsm-border-red-500/20',
	};

	let currentPage = 1;
	const perPage = 20;

	/**
	 * Formats numeric price string to Persian currency.
	 *
	 * @param {number|string} price Raw price.
	 * @returns {string} Formatted price with Tomans.
	 */
	function formatPrice(price) {
		return Number(price).toLocaleString('fa-IR') + ' تومان';
	}

	// 1. ORDERS LIST HANDLER
	async function loadOrdersList() {
		const tableBody = document.getElementById('orders-table-body');
		if (!tableBody) return;

		// Read filters.
		const search = document.getElementById('order-search')?.value || '';
		const status = document.getElementById('order-status-filter')?.value || '';
		const dateFrom = document.getElementById('order-date-from')?.value || '';
		const dateTo = document.getElementById('order-date-to')?.value || '';

		tableBody.innerHTML = `
			<tr>
				<td colspan="7" class="wsm-px-6 wsm-py-12 wsm-text-center wsm-text-slate-500 wsm-animate-pulse">
					در حال دریافت سفارش‌ها...
				</td>
			</tr>
		`;

		try {
			const queryParams = new URLSearchParams({
				page: currentPage,
				per_page: perPage,
				search,
				status,
				date_from: dateFrom,
				date_to: dateTo,
			});

			const response = await WSM.fetch('/orders?' + queryParams.toString(), { method: 'GET' });
			const { orders, total, pages } = response.data;

			if (orders.length === 0) {
				tableBody.innerHTML = `
					<tr>
						<td colspan="7" class="wsm-px-6 wsm-py-12 wsm-text-center wsm-text-slate-500">
							هیچ سفارشی یافت نشد.
						</td>
					</tr>
				`;
				updatePaginationInfo(0, 0, 0);
				renderPaginationControls(0);
				return;
			}

			// Render rows
			let rowsHtml = '';
			orders.forEach(order => {
				const badgeClass = statusClasses[order.status] ?? 'wsm-bg-slate-500/10 wsm-text-slate-400 wsm-border-slate-500/20';
				rowsHtml += `
					<tr class="wsm-border-b wsm-border-slate-800/40 hover:wsm-bg-slate-900/20 wsm-transition-colors">
						<td class="wsm-px-6 wsm-py-4 wsm-text-sm wsm-font-bold wsm-text-slate-200">#${order.id}</td>
						<td class="wsm-px-6 wsm-py-4 wsm-text-sm wsm-text-slate-300">${WSM.escHtml(order.customer_name)}</td>
						<td class="wsm-px-6 wsm-py-4 wsm-text-sm wsm-text-slate-400">${order.date}</td>
						<td class="wsm-px-6 wsm-py-4 wsm-text-sm wsm-text-slate-400">${WSM.escHtml(order.payment_method || 'ناشناخته')}</td>
						<td class="wsm-px-6 wsm-py-4 wsm-text-sm wsm-font-semibold wsm-text-indigo-400">${formatPrice(order.total)}</td>
						<td class="wsm-px-6 wsm-py-4 wsm-text-sm">
							<span class="wsm-px-2.5 wsm-py-1 wsm-text-xs wsm-font-semibold wsm-rounded-full wsm-border ${badgeClass}">
								${WSM.escHtml(order.status_label)}
							</span>
						</td>
						<td class="wsm-px-6 wsm-py-4 wsm-text-center wsm-text-sm wsm-space-x-2 wsm-space-x-reverse">
							<a href="${window.wsmConfig.panelUrl}/orders/view?id=${order.id}" class="wsm-text-indigo-400 hover:wsm-text-indigo-300 wsm-font-semibold">مشاهده</a>
							<button class="quick-status-btn wsm-text-xs wsm-text-emerald-400 hover:wsm-text-emerald-300" data-id="${order.id}" data-status="completed">تکمیل</button>
							<button class="quick-status-btn wsm-text-xs wsm-text-rose-400 hover:wsm-text-rose-300" data-id="${order.id}" data-status="cancelled">لغو</button>
						</td>
					</tr>
				`;
			});

			tableBody.innerHTML = rowsHtml;

			// Bind inline quick status updates.
			document.querySelectorAll('.quick-status-btn').forEach(btn => {
				btn.addEventListener('click', async (e) => {
					e.preventDefault();
					const orderId = btn.getAttribute('data-id');
					const newStatus = btn.getAttribute('data-status');
					if (confirm(`آیا از تغییر وضعیت سفارش #${orderId} مطمئن هستید؟`)) {
						try {
							await WSM.fetch(`/orders/${orderId}/status`, {
								method: 'PATCH',
								body: JSON.stringify({ status: newStatus })
							});
							loadOrdersList();
						} catch (err) {
							// WSM.fetch handles error alerts.
						}
					}
				});
			});

			// Update Pagination stats.
			const start = (currentPage - 1) * perPage + 1;
			const end = Math.min(currentPage * perPage, total);
			updatePaginationInfo(start, end, total);
			renderPaginationControls(pages);

		} catch (error) {
			tableBody.innerHTML = `
				<tr>
					<td colspan="7" class="wsm-px-6 wsm-py-12 wsm-text-center wsm-text-rose-400">
						خطا در بارگذاری اطلاعات.
					</td>
				</tr>
			`;
		}
	}

	function updatePaginationInfo(start, end, total) {
		document.getElementById('orders-count-start').textContent = start.toLocaleString('fa-IR');
		document.getElementById('orders-count-end').textContent = end.toLocaleString('fa-IR');
		document.getElementById('orders-count-total').textContent = total.toLocaleString('fa-IR');
	}

	function renderPaginationControls(totalPages) {
		const container = document.getElementById('orders-pagination-controls');
		if (!container) return;

		let html = '';
		if (totalPages > 1) {
			// Previous button.
			html += `
				<button class="pag-btn wsm-px-3 wsm-py-1.5 wsm-text-xs wsm-bg-slate-900 wsm-border wsm-border-slate-800 wsm-rounded-xl hover:wsm-bg-slate-800 ${currentPage === 1 ? 'wsm-opacity-50 wsm-pointer-events-none' : ''}" data-page="${currentPage - 1}">
					قبلی
				</button>
			`;

			// Page numbers.
			for (let i = 1; i <= totalPages; i++) {
				const activeClass = i === currentPage ? 'wsm-bg-indigo-600 wsm-text-white' : 'wsm-bg-slate-900 wsm-text-slate-400 hover:wsm-bg-slate-800';
				html += `
					<button class="pag-btn wsm-px-3 wsm-py-1.5 wsm-text-xs wsm-border wsm-border-slate-800 wsm-rounded-xl ${activeClass}" data-page="${i}">
						${i.toLocaleString('fa-IR')}
					</button>
				`;
			}

			// Next button.
			html += `
				<button class="pag-btn wsm-px-3 wsm-py-1.5 wsm-text-xs wsm-bg-slate-900 wsm-border wsm-border-slate-800 wsm-rounded-xl hover:wsm-bg-slate-800 ${currentPage === totalPages ? 'wsm-opacity-50 wsm-pointer-events-none' : ''}" data-page="${currentPage + 1}">
					بعدی
				</button>
			`;
		}

		container.innerHTML = html;

		// Bind pagination button clicks
		container.querySelectorAll('.pag-btn').forEach(btn => {
			btn.addEventListener('click', (e) => {
				e.preventDefault();
				currentPage = parseInt(btn.getAttribute('data-page'));
				loadOrdersList();
			});
		});
	}

	// 2. ORDER DETAILS HANDLER
	async function loadOrderDetail(orderId) {
		const container = document.getElementById('order-detail-container');
		if (!container || !orderId) return;

		try {
			const response = await WSM.fetch(`/orders/${orderId}`, { method: 'GET' });
			const order = response.data;

			const badgeClass = statusClasses[order.status] ?? 'wsm-bg-slate-500/10 wsm-text-slate-400 wsm-border-slate-500/20';

			let itemsRowsHtml = '';
			order.items.forEach(item => {
				const imgUrl = item.image || 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="%23334155"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>';
				itemsRowsHtml += `
					<tr class="wsm-border-b wsm-border-slate-800/40">
						<td class="wsm-px-6 wsm-py-4 wsm-text-sm">
							<div class="wsm-flex wsm-items-center wsm-space-x-3 wsm-space-x-reverse">
								<img src="${imgUrl}" class="wsm-w-10 wsm-h-10 wsm-rounded-xl wsm-object-cover wsm-border wsm-border-slate-800" alt="${WSM.escHtml(item.name)}">
								<div>
									<div class="wsm-font-semibold wsm-text-slate-200">${WSM.escHtml(item.name)}</div>
									<div class="wsm-text-xs wsm-text-slate-500">کد کالا: ${WSM.escHtml(item.sku || 'ندارد')}</div>
								</div>
							</div>
						</td>
						<td class="wsm-px-6 wsm-py-4 wsm-text-sm wsm-text-slate-300">${item.quantity.toLocaleString('fa-IR')}</td>
						<td class="wsm-px-6 wsm-py-4 wsm-text-sm wsm-text-slate-300">${formatPrice(item.total / item.quantity)}</td>
						<td class="wsm-px-6 wsm-py-4 wsm-text-sm wsm-font-semibold wsm-text-slate-200">${formatPrice(item.total)}</td>
					</tr>
				`;
			});

			let notesTimelineHtml = '';
			order.notes.forEach(note => {
				const noteBadge = note.customer_note ? 'wsm-bg-indigo-500/10 wsm-text-indigo-400 wsm-border-indigo-500/20' : 'wsm-bg-slate-800 wsm-text-slate-400 wsm-border-slate-700';
				const noteBadgeText = note.customer_note ? 'یادداشت مشتری' : 'یادداشت داخلی';
				notesTimelineHtml += `
					<div class="wsm-bg-slate-950/40 wsm-border wsm-border-slate-900 wsm-rounded-2xl wsm-p-4 wsm-space-y-2">
						<div class="wsm-flex wsm-items-center wsm-justify-between">
							<span class="wsm-text-xs wsm-text-slate-500">${note.date}</span>
							<span class="wsm-px-2 wsm-py-0.5 wsm-text-[10px] wsm-rounded-full wsm-border ${noteBadge}">${noteBadgeText}</span>
						</div>
						<p class="wsm-text-sm wsm-text-slate-300 wsm-leading-relaxed">${WSM.escHtml(note.content)}</p>
					</div>
				`;
			});

			container.innerHTML = `
				<div class="wsm-flex wsm-items-center wsm-justify-between">
					<div class="wsm-flex wsm-items-center wsm-space-x-3 wsm-space-x-reverse">
						<a href="${window.wsmConfig.panelUrl}/orders" class="wsm-text-slate-400 hover:wsm-text-slate-200">
							&larr; بازگشت به لیست
						</a>
						<span class="wsm-text-slate-600">/</span>
						<h1 class="wsm-text-2xl wsm-font-bold wsm-text-slate-100">جزئیات سفارش #${order.id}</h1>
					</div>
					<div class="wsm-flex wsm-items-center wsm-space-x-3 wsm-space-x-reverse">
						<select id="wsm-detail-status" class="wsm-bg-slate-900 wsm-border wsm-border-slate-800 wsm-rounded-xl wsm-px-4 wsm-py-2 wsm-text-sm focus:wsm-outline-none">
							<option value="pending" ${order.status === 'pending' ? 'selected' : ''}>در انتظار پرداخت</option>
							<option value="processing" ${order.status === 'processing' ? 'selected' : ''}>در حال انجام</option>
							<option value="on-hold" ${order.status === 'on-hold' ? 'selected' : ''}>در انتظار بررسی</option>
							<option value="completed" ${order.status === 'completed' ? 'selected' : ''}>تکمیل شده</option>
							<option value="cancelled" ${order.status === 'cancelled' ? 'selected' : ''}>لغو شده</option>
							<option value="refunded" ${order.status === 'refunded' ? 'selected' : ''}>مسترد شده</option>
							<option value="failed" ${order.status === 'failed' ? 'selected' : ''}>ناموفق</option>
						</select>
					</div>
				</div>

				<div class="wsm-grid wsm-grid-cols-1 lg:wsm-grid-cols-3 wsm-gap-6">
					<!-- Right side: details and items list -->
					<div class="lg:wsm-col-span-2 wsm-space-y-6">
						<!-- Items Panel -->
						<div class="wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-overflow-hidden wsm-shadow-lg">
							<div class="wsm-px-6 wsm-py-4 wsm-border-b wsm-border-slate-800 wsm-font-semibold">اقلام سفارش</div>
							<div class="wsm-overflow-x-auto">
								<table class="wsm-w-full wsm-text-right wsm-border-collapse">
									<thead>
										<tr class="wsm-border-b wsm-border-slate-800 wsm-bg-slate-950/20">
											<th class="wsm-px-6 wsm-py-3 wsm-text-xs wsm-text-slate-500">نام محصول</th>
											<th class="wsm-px-6 wsm-py-3 wsm-text-xs wsm-text-slate-500">تعداد</th>
											<th class="wsm-px-6 wsm-py-3 wsm-text-xs wsm-text-slate-500">قیمت واحد</th>
											<th class="wsm-px-6 wsm-py-3 wsm-text-xs wsm-text-slate-500">جمع کل</th>
										</tr>
									</thead>
									<tbody class="wsm-divide-y wsm-divide-slate-800/40">
										${itemsRowsHtml}
									</tbody>
								</table>
							</div>
							<div class="wsm-p-6 wsm-bg-slate-950/20 wsm-border-t wsm-border-slate-800/80 wsm-flex wsm-justify-end">
								<div class="wsm-w-64 wsm-space-y-2">
									<div class="wsm-flex wsm-justify-between wsm-text-sm">
										<span class="wsm-text-slate-400">جمع جزء:</span>
										<span class="wsm-font-medium">${formatPrice(order.subtotal)}</span>
									</div>
									<div class="wsm-flex wsm-justify-between wsm-text-sm">
										<span class="wsm-text-slate-400">هزینه ارسال:</span>
										<span class="wsm-font-medium">${formatPrice(order.shipping)}</span>
									</div>
									<div class="wsm-flex wsm-justify-between wsm-text-sm">
										<span class="wsm-text-slate-400">تخفیف:</span>
										<span class="wsm-font-medium wsm-text-emerald-400">${formatPrice(order.discount)}</span>
									</div>
									<div class="wsm-flex wsm-justify-between wsm-border-t wsm-border-slate-800 wsm-pt-2 wsm-text-base wsm-font-bold">
										<span class="wsm-text-slate-100">جمع کل:</span>
										<span class="wsm-text-indigo-400">${formatPrice(order.total)}</span>
									</div>
								</div>
							</div>
						</div>

						<!-- Billing & Shipping details -->
						<div class="wsm-grid wsm-grid-cols-1 md:wsm-grid-cols-2 wsm-gap-6">
							<!-- Billing Card -->
							<div class="wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-p-6 wsm-shadow-lg wsm-space-y-4">
								<h3 class="wsm-font-semibold wsm-text-slate-200">اطلاعات خریدار</h3>
								<div class="wsm-space-y-2 wsm-text-sm">
									<div class="wsm-flex wsm-justify-between"><span class="wsm-text-slate-500">نام:</span> <span class="wsm-text-slate-300">${WSM.escHtml(order.billing.name)}</span></div>
									<div class="wsm-flex wsm-justify-between"><span class="wsm-text-slate-500">تلفن تماس:</span> <span class="wsm-text-slate-300">${WSM.escHtml(order.billing.phone)}</span></div>
									<div class="wsm-flex wsm-justify-between"><span class="wsm-text-slate-500">ایمیل:</span> <span class="wsm-text-slate-300">${WSM.escHtml(order.billing.email)}</span></div>
									<div class="wsm-flex wsm-justify-between"><span class="wsm-text-slate-500">شهر / استان:</span> <span class="wsm-text-slate-300">${WSM.escHtml(order.billing.city)} / ${WSM.escHtml(order.billing.state)}</span></div>
									<div class="wsm-text-slate-500 wsm-pt-1">نشانی:</div>
									<p class="wsm-text-xs wsm-text-slate-400 wsm-leading-relaxed">${WSM.escHtml(order.billing.address)}</p>
								</div>
							</div>

							<!-- Shipping Card -->
							<div class="wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-p-6 wsm-shadow-lg wsm-space-y-4">
								<h3 class="wsm-font-semibold wsm-text-slate-200">اطلاعات ارسال گیرنده</h3>
								<div class="wsm-space-y-2 wsm-text-sm">
									<div class="wsm-flex wsm-justify-between"><span class="wsm-text-slate-500">نام گیرنده:</span> <span class="wsm-text-slate-300">${WSM.escHtml(order.shipping_info.name || order.billing.name)}</span></div>
									<div class="wsm-flex wsm-justify-between"><span class="wsm-text-slate-500">شهر / استان:</span> <span class="wsm-text-slate-300">${WSM.escHtml(order.shipping_info.city || order.billing.city)} / ${WSM.escHtml(order.shipping_info.state || order.billing.state)}</span></div>
									<div class="wsm-text-slate-500 wsm-pt-1">نشانی ارسال:</div>
									<p class="wsm-text-xs wsm-text-slate-400 wsm-leading-relaxed">${WSM.escHtml(order.shipping_info.address || order.billing.address)}</p>
								</div>
							</div>
						</div>
					</div>

					<!-- Left side: Notes and interactions -->
					<div class="wsm-space-y-6">
						<!-- Notes box -->
						<div class="wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-p-6 wsm-shadow-lg wsm-flex wsm-flex-col wsm-h-[500px]">
							<h3 class="wsm-font-semibold wsm-text-slate-200 wsm-mb-4">یادداشت‌های سفارش</h3>
							<!-- Timeline container -->
							<div class="wsm-flex-1 wsm-overflow-y-auto wsm-space-y-3 wsm-mb-4 wsm-pr-1" id="order-notes-timeline">
								${notesTimelineHtml || '<p class="wsm-text-xs wsm-text-slate-500 wsm-text-center wsm-py-8">یادداشتی برای این سفارش ثبت نشده است.</p>'}
							</div>
							<!-- Create note form -->
							<form id="wsm-add-note-form" class="wsm-border-t wsm-border-slate-800 wsm-pt-4 wsm-space-y-3">
								<textarea id="new-note-text" required rows="3" class="wsm-w-full wsm-bg-slate-950/85 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-p-3 wsm-text-xs wsm-text-slate-200 focus:wsm-outline-none focus:wsm-border-indigo-500 wsm-transition-colors" placeholder="متن یادداشت..."></textarea>
								<div class="wsm-flex wsm-items-center wsm-justify-between">
									<label class="wsm-flex wsm-items-center wsm-text-xs wsm-text-slate-400 wsm-cursor-pointer">
										<input type="checkbox" id="note-is-customer" class="wsm-ml-1.5">
										یادداشت برای مشتری؟
									</label>
									<button type="submit" class="wsm-px-4 wsm-py-2 wsm-bg-indigo-600 hover:wsm-bg-indigo-500 wsm-text-white wsm-rounded-xl wsm-text-xs wsm-font-semibold wsm-transition-all">
										ثبت یادداشت
									</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			`;

			// Bind status select change
			document.getElementById('wsm-detail-status').addEventListener('change', async (e) => {
				const newStatus = e.target.value;
				try {
					await WSM.fetch(`/orders/${orderId}/status`, {
						method: 'PATCH',
						body: JSON.stringify({ status: newStatus })
					});
					loadOrderDetail(orderId);
				} catch (err) {
					// WSM.fetch handles error alerts.
				}
			});

			// Bind add note form submit
			document.getElementById('wsm-add-note-form').addEventListener('submit', async (e) => {
				e.preventDefault();
				const noteTextarea = document.getElementById('new-note-text');
				const isCustomer = document.getElementById('note-is-customer').checked;
				const noteText = noteTextarea.value.trim();
				if (!noteText) return;

				try {
					await WSM.fetch(`/orders/${orderId}/notes`, {
						method: 'POST',
						body: JSON.stringify({
							note: noteText,
							customer_note: isCustomer,
						})
					});
					noteTextarea.value = '';
					loadOrderDetail(orderId);
				} catch (err) {
					// WSM.fetch handles error alerts.
				}
			});

		} catch (error) {
			container.innerHTML = `
				<div class="wsm-bg-slate-900 wsm-border wsm-border-rose-500/20 wsm-rounded-3xl wsm-p-8 wsm-text-center wsm-text-rose-400">
					خطا در بارگذاری جزئیات سفارش.
				</div>
			`;
		}
	}

	// 3. BOOTSTRAP TRIGGERS
	document.addEventListener('DOMContentLoaded', () => {
		// Verify if we are on list view.
		const tableBody = document.getElementById('orders-table-body');
		if (tableBody) {
			loadOrdersList();

			// Bind list filter triggers.
			const searchInput = document.getElementById('order-search');
			const statusFilter = document.getElementById('order-status-filter');
			const dateFromInput = document.getElementById('order-date-from');
			const dateToInput = document.getElementById('order-date-to');
			const clearBtn = document.getElementById('clear-filters-btn');

			// Simple debounce search trigger.
			let searchTimeout;
			searchInput?.addEventListener('input', () => {
				clearTimeout(searchTimeout);
				searchTimeout = setTimeout(() => {
					currentPage = 1;
					loadOrdersList();
				}, 400);
			});

			statusFilter?.addEventListener('change', () => {
				currentPage = 1;
				loadOrdersList();
			});

			dateFromInput?.addEventListener('change', () => {
				currentPage = 1;
				loadOrdersList();
			});

			dateToInput?.addEventListener('change', () => {
				currentPage = 1;
				loadOrdersList();
			});

			clearBtn?.addEventListener('click', (e) => {
				e.preventDefault();
				if (searchInput) searchInput.value = '';
				if (statusFilter) statusFilter.value = '';
				if (dateFromInput) dateFromInput.value = '';
				if (dateToInput) dateToInput.value = '';
				currentPage = 1;
				loadOrdersList();
			});
		}

		// Verify if we are on detail view.
		const detailContainer = document.getElementById('order-detail-container');
		if (detailContainer) {
			const orderId = detailContainer.getAttribute('data-order-id');
			loadOrderDetail(orderId);
		}
	});

})();

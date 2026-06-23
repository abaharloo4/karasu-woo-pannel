/**
 * KarasuWooPannel Dashboard Client Logic Script
 *
 * @package KarasuWooPannel
 * @version 1.0.6
 * @date 2026-06-23
 */

(function() {
	'use strict';

	const statusClasses = {
		pending: 'wsm-bg-yellow-500/10 wsm-text-yellow-400 wsm-border-yellow-500/20',
		processing: 'wsm-bg-blue-500/10 wsm-text-blue-400 wsm-border-blue-500/20',
		'on-hold': 'wsm-bg-orange-500/10 wsm-text-orange-400 wsm-border-orange-500/20',
		completed: 'wsm-bg-emerald-500/10 wsm-text-emerald-400 wsm-border-emerald-500/20',
		cancelled: 'wsm-bg-rose-500/10 wsm-text-rose-400 wsm-border-rose-500/20',
		refunded: 'wsm-bg-purple-500/10 wsm-text-purple-400 wsm-border-purple-500/20',
		failed: 'wsm-bg-red-500/10 wsm-text-red-400 wsm-border-red-500/20',
	};

	function formatPrice(price) {
		return Number(price).toLocaleString('fa-IR') + ' تومان';
	}

	async function loadDashboardStats() {
		try {
			const response = await WSM.fetch('/reports/dashboard-stats', { method: 'GET' });
			const data = response.data;

			const todaySalesEl = document.getElementById('dash-today-sales');
			const todayOrdersEl = document.getElementById('dash-today-orders');
			const monthSalesEl = document.getElementById('dash-month-sales');
			const monthOrdersEl = document.getElementById('dash-month-orders');

			if (todaySalesEl) todaySalesEl.textContent = formatPrice(data.today_sales ?? 0);
			if (todayOrdersEl) todayOrdersEl.textContent = Number(data.today_orders ?? 0).toLocaleString('fa-IR') + ' سفارش';
			if (monthSalesEl) monthSalesEl.textContent = formatPrice(data.month_sales ?? 0);
			if (monthOrdersEl) monthOrdersEl.textContent = Number(data.month_orders ?? 0).toLocaleString('fa-IR') + ' سفارش';
		} catch (err) {
			console.error('Failed to load dashboard stats', err);
		}
	}

	async function loadRecentOrders() {
		const tbody = document.getElementById('dash-orders-table-body');
		if (!tbody) return;

		try {
			const response = await WSM.fetch('/orders?per_page=5', { method: 'GET' });
			const { orders } = response.data;

			if (orders.length === 0) {
				tbody.innerHTML = `
					<tr>
						<td colspan="4" class="wsm-py-4 wsm-text-center wsm-text-slate-500">
							هیچ سفارشی یافت نشد.
						</td>
					</tr>
				`;
				return;
			}

			let html = '';
			orders.forEach(order => {
				const badgeClass = statusClasses[order.status] ?? 'wsm-bg-slate-500/10 wsm-text-slate-400 wsm-border-slate-500/20';
				html += `
					<tr class="wsm-border-b wsm-border-slate-800/40 hover:wsm-bg-slate-900/20 wsm-transition-colors">
						<td class="wsm-py-3 wsm-text-sm wsm-font-bold wsm-text-slate-200">
							<a href="${window.wsmConfig.panelUrl}/orders/view?id=${order.id}" class="wsm-text-indigo-400 hover:wsm-underline">#${order.id}</a>
						</td>
						<td class="wsm-py-3 wsm-text-sm wsm-text-slate-300">${WSM.escHtml(order.customer_name)}</td>
						<td class="wsm-py-3 wsm-text-sm">
							<span class="wsm-px-2.5 wsm-py-0.5 wsm-text-[11px] wsm-font-semibold wsm-rounded-full wsm-border ${badgeClass}">
								${WSM.escHtml(order.status_label)}
							</span>
						</td>
						<td class="wsm-py-3 wsm-text-sm wsm-font-semibold wsm-text-indigo-400">${formatPrice(order.total)}</td>
					</tr>
				`;
			});
			tbody.innerHTML = html;
		} catch (err) {
			tbody.innerHTML = `
				<tr>
					<td colspan="4" class="wsm-py-4 wsm-text-center wsm-text-rose-500">
						خطا در دریافت لیست سفارش‌ها.
					</td>
				</tr>
			`;
		}
	}

	async function loadLowStockAlerts() {
		const tbody = document.getElementById('dash-inventory-table-body');
		if (!tbody) return;

		try {
			const response = await WSM.fetch('/reports/products-inventory', { method: 'GET' });
			const products = response.data;

			if (products.length === 0) {
				tbody.innerHTML = `
					<tr>
						<td colspan="3" class="wsm-py-4 wsm-text-center wsm-text-emerald-400 wsm-text-xs">
							تمام محصولات موجودی کافی دارند و انبار در وضعیت عالی است!
						</td>
					</tr>
				`;
				return;
			}

			// Show max 5 products
			const itemsToShow = products.slice(0, 5);

			let html = '';
			itemsToShow.forEach(p => {
				const isCritical = p.stock <= p.threshold;
				html += `
					<tr class="wsm-border-b wsm-border-slate-800/40 hover:wsm-bg-slate-900/20 wsm-transition-colors">
						<td class="wsm-py-3 wsm-text-sm wsm-font-semibold wsm-text-slate-300">
							<a href="${window.wsmConfig.panelUrl}/products/edit?id=${p.id}" class="hover:wsm-text-indigo-400 hover:wsm-underline">${WSM.escHtml(p.name)}</a>
						</td>
						<td class="wsm-py-3 wsm-text-sm wsm-text-slate-400 wsm-font-mono">${WSM.escHtml(p.sku || '—')}</td>
						<td class="wsm-py-3 wsm-text-sm wsm-text-center wsm-font-bold ${isCritical ? 'wsm-text-rose-400' : 'wsm-text-amber-400'}">
							${Number(p.stock).toLocaleString('fa-IR')} عدد
						</td>
					</tr>
				`;
			});
			tbody.innerHTML = html;
		} catch (err) {
			tbody.innerHTML = `
				<tr>
					<td colspan="3" class="wsm-py-4 wsm-text-center wsm-text-rose-500">
						خطا در دریافت وضعیت انبار.
					</td>
				</tr>
			`;
		}
	}

	document.addEventListener('DOMContentLoaded', () => {
		loadDashboardStats();
		loadRecentOrders();
		loadLowStockAlerts();
	});

})();

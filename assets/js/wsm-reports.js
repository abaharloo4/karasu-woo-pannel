/**
 * KarasuWooPannel Reports Script
 *
 * @package KarasuWooPannel
 * @version 1.0.10
 * @date 2026-06-23
 */

(function() {
	'use strict';

	let salesChart = null;
	let currentReportData = []; // Cache for current viewed records to export CSV

	function formatPrice(price) {
		if (price === undefined || price === null) return '۰ تومان';
		return Number(price).toLocaleString('fa-IR') + ' تومان';
	}

	function formatStatus(status) {
		const statuses = {
			'pending': 'در انتظار پرداخت',
			'processing': 'در حال انجام',
			'on-hold': 'در انتظار بررسی',
			'completed': 'تکمیل شده',
			'cancelled': 'لغو شده',
			'refunded': 'مرجوع شده',
			'failed': 'ناموفق'
		};
		return statuses[status] || status;
	}

	/**
	 * Render or update Chart.js instance.
	 *
	 * @param {Array} labels Date labels.
	 * @param {Array} values Sales values.
	 */
	function renderChart(labels, values) {
		const canvas = document.getElementById('sales-line-chart');
		if (!canvas) return;

		const ctx = canvas.getContext('2d');
		if (!ctx) return;

		if (salesChart) {
			salesChart.destroy();
		}

		salesChart = new Chart(ctx, {
			type: 'line',
			data: {
				labels: labels,
				datasets: [{
					label: 'میزان فروش',
					data: values,
					borderColor: '#6366f1',
					backgroundColor: 'rgba(99, 102, 241, 0.1)',
					borderWidth: 3,
					fill: true,
					tension: 0.4,
					pointBackgroundColor: '#4f46e5',
					pointBorderColor: '#818cf8',
					pointHoverRadius: 6,
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				plugins: {
					legend: {
						display: false,
					},
					tooltip: {
						titleFont: { family: 'Vazirmatn' },
						bodyFont: { family: 'Vazirmatn' },
						callbacks: {
							label: function(context) {
								let label = context.dataset.label || '';
								if (label) {
									label += ': ';
								}
								if (context.parsed.y !== null) {
									label += formatPrice(context.parsed.y);
								}
								return label;
							}
						}
					}
				},
				scales: {
					x: {
						grid: {
							color: 'rgba(51, 65, 85, 0.2)',
						},
						ticks: {
							color: '#94a3b8',
							font: { family: 'Vazirmatn', size: 10 }
						}
					},
					y: {
						grid: {
							color: 'rgba(51, 65, 85, 0.2)',
						},
						ticks: {
							color: '#94a3b8',
							font: { family: 'Vazirmatn', size: 10 },
							callback: function(value) {
								return value.toLocaleString('fa-IR');
							}
						}
					}
				}
			}
		});
	}

	/**
	 * Load quick landing overview numbers.
	 */
	async function loadQuickStats() {
		try {
			const response = await WSM.fetch('/reports/dashboard-stats', { method: 'GET' });
			const data = response.data;

			const todaySalesEl = document.getElementById('stat-today-sales');
			const todayOrdersEl = document.getElementById('stat-today-orders');

			if (todaySalesEl) todaySalesEl.textContent = formatPrice(data.today_sales);
			if (todayOrdersEl) todayOrdersEl.textContent = data.today_orders.toLocaleString('fa-IR') + ' سفارش';

		} catch (err) {
			// Fail silently for dashboard widgets
		}
	}

	/**
	 * Fetch report data based on date ranges and update view.
	 */
	async function loadPeriodReport() {
		const start = document.getElementById('rep-start')?.value || '';
		const end   = document.getElementById('rep-end')?.value || '';

		const queryParams = new URLSearchParams({
			start_date: start,
			end_date: end,
		});

		try {
			const response = await WSM.fetch('/reports/sales?' + queryParams.toString(), { method: 'GET' });
			const data = response.data;

			currentReportData = data.daily;

			// Update period widgets
			const periodSalesEl = document.getElementById('stat-period-sales');
			const periodOrdersEl = document.getElementById('stat-period-orders');

			if (periodSalesEl) periodSalesEl.textContent = formatPrice(data.total_sales);
			if (periodOrdersEl) periodOrdersEl.textContent = data.total_orders.toLocaleString('fa-IR') + ' سفارش';

			// Render Best Selling Products
			const topProductsTable = document.getElementById('top-products-table-body');
			if (topProductsTable) {
				if (data.top_products.length === 0) {
					topProductsTable.innerHTML = `
						<tr>
							<td colspan="2" class="wsm-py-4 wsm-text-center wsm-text-slate-500">هیچ کالایی یافت نشد.</td>
						</tr>
					`;
				} else {
					let rowsHtml = '';
					data.top_products.forEach(p => {
						rowsHtml += `
							<tr class="hover:wsm-bg-slate-900/10">
								<td class="wsm-py-3 wsm-text-sm wsm-text-slate-300 wsm-font-semibold">${WSM.escHtml(p.name)}</td>
								<td class="wsm-py-3 wsm-text-sm wsm-text-slate-400 wsm-text-center wsm-font-bold">${p.total_sales.toLocaleString('fa-IR')}</td>
							</tr>
						`;
					});
					topProductsTable.innerHTML = rowsHtml;
				}
			}

			// Render sales chart
			const labels = data.daily.map(day => day.date_jalali);
			const sales  = data.daily.map(day => day.sales);

			renderChart(labels, sales);

		} catch (err) {
			// Handled globally
		}
	}

	/**
	 * Export current data to Jalali CSV file.
	 */
	function exportToCSV() {
		if (currentReportData.length === 0) {
			alert('داده‌ای برای خروجی فایل CSV وجود ندارد.');
			return;
		}

		// Unicode BOM for Persian Excel support
		let csvContent = "data:text/csv;charset=utf-8,\uFEFF";
		csvContent += "تاریخ,مجموع فروش (تومان),تعداد سفارشات,اقلام فروخته شده,مجموع حمل و نقل (تومان)\n";

		currentReportData.forEach(row => {
			const date = row.date_jalali || row.date;
			csvContent += `"${date}","${row.sales}","${row.orders}","${row.items}","${row.shipping}"\n`;
		});

		const encodedUri = encodeURI(csvContent);
		const link = document.createElement("a");
		link.setAttribute("href", encodedUri);
		link.setAttribute("download", `sales-report-${new Date().toISOString().slice(0, 10)}.csv`);
		document.body.appendChild(link);
		link.click();
		document.body.removeChild(link);
	}

	/* ==========================================
	   DETAILED SALES LOG REPORT (sales.php)
	   ========================================== */
	async function loadDetailedSalesReport() {
		const start = document.getElementById('sales-start')?.value || '';
		const end   = document.getElementById('sales-end')?.value || '';
		const tbody = document.getElementById('sales-detailed-table-body');
		if (!tbody) return;

		tbody.innerHTML = '<tr><td colspan="8" class="wsm-py-6 wsm-text-center wsm-text-slate-500">در حال بارگذاری...</td></tr>';

		const queryParams = new URLSearchParams({ start_date: start, end_date: end });

		try {
			const response = await WSM.fetch('/reports/sales-detailed?' + queryParams.toString(), { method: 'GET' });
			const data = response.data;
			currentReportData = data;

			if (data.length === 0) {
				tbody.innerHTML = '<tr><td colspan="8" class="wsm-py-6 wsm-text-center wsm-text-slate-500">هیچ سفارشی در این بازه ثبت نشده است.</td></tr>';
				return;
			}

			let rows = '';
			data.forEach(order => {
				rows += `
					<tr class="hover:wsm-bg-slate-900/10">
						<td class="wsm-py-4 wsm-text-sm wsm-font-bold wsm-text-slate-300">#${order.id}</td>
						<td class="wsm-py-4 wsm-text-sm wsm-text-slate-400">${order.date_jalali}</td>
						<td class="wsm-py-4 wsm-text-sm wsm-text-slate-300 wsm-font-semibold">${WSM.escHtml(order.customer)}</td>
						<td class="wsm-py-4 wsm-text-sm wsm-text-slate-400 wsm-max-w-xs wsm-truncate" title="${WSM.escHtml(order.items_desc)}">${WSM.escHtml(order.items_desc)}</td>
						<td class="wsm-py-4 wsm-text-sm wsm-text-slate-400 wsm-text-center">${formatPrice(order.tax)}</td>
						<td class="wsm-py-4 wsm-text-sm wsm-text-slate-400 wsm-text-center">${formatPrice(order.shipping)}</td>
						<td class="wsm-py-4 wsm-text-sm wsm-font-bold wsm-text-indigo-400 wsm-text-center">${formatPrice(order.total)}</td>
						<td class="wsm-py-4 wsm-text-sm wsm-text-center">
							<span class="wsm-px-2.5 wsm-py-1 wsm-rounded-full wsm-text-xs wsm-font-semibold wsm-bg-slate-800 wsm-text-slate-300">${formatStatus(order.status)}</span>
						</td>
					</tr>
				`;
			});
			tbody.innerHTML = rows;
		} catch (err) {
			tbody.innerHTML = '<tr><td colspan="8" class="wsm-py-6 wsm-text-center wsm-text-rose-500">خطا در بارگذاری اطلاعات گزارش.</td></tr>';
		}
	}

	function exportSalesCSV() {
		if (currentReportData.length === 0) {
			alert('داده‌ای برای خروجی وجود ندارد.');
			return;
		}
		let csv = "data:text/csv;charset=utf-8,\uFEFF";
		csv += "شماره سفارش,تاریخ سفارش,مشتری,جزئیات کالا,مالیات (تومان),هزینه ارسال (تومان),مجموع پرداختی (تومان),وضعیت\n";
		currentReportData.forEach(o => {
			csv += `"${o.id}","${o.date_jalali}","${o.customer}","${o.items_desc}","${o.tax}","${o.shipping}","${o.total}","${formatStatus(o.status)}"\n`;
		});
		const link = document.createElement("a");
		link.setAttribute("href", encodeURI(csv));
		link.setAttribute("download", `sales-detailed-report-${new Date().toISOString().slice(0, 10)}.csv`);
		document.body.appendChild(link);
		link.click();
		document.body.removeChild(link);
	}

	/* ==========================================
	   PRODUCTS INVENTORY REPORT (products.php)
	   ========================================== */
	async function loadProductsInventory() {
		const tbody = document.getElementById('products-inventory-table-body');
		if (!tbody) return;

		tbody.innerHTML = '<tr><td colspan="5" class="wsm-py-6 wsm-text-center wsm-text-slate-500">در حال بارگذاری...</td></tr>';

		try {
			const response = await WSM.fetch('/reports/products-inventory', { method: 'GET' });
			const data = response.data;
			currentReportData = data;

			if (data.length === 0) {
				tbody.innerHTML = '<tr><td colspan="5" class="wsm-py-6 wsm-text-center wsm-text-emerald-400">تمام محصولات موجودی کافی دارند و انبار در وضعیت عالی است!</td></tr>';
				return;
			}

			let rows = '';
			data.forEach(p => {
				const isLow = p.stock <= p.threshold;
				rows += `
					<tr class="hover:wsm-bg-slate-900/10">
						<td class="wsm-py-4 wsm-text-sm wsm-font-bold wsm-text-slate-400">#${p.id}</td>
						<td class="wsm-py-4 wsm-text-sm wsm-font-semibold wsm-text-slate-300">${WSM.escHtml(p.name)}</td>
						<td class="wsm-py-4 wsm-text-sm wsm-text-center wsm-font-bold ${isLow ? 'wsm-text-rose-500' : 'wsm-text-amber-500'}">${p.stock.toLocaleString('fa-IR')} عدد</td>
						<td class="wsm-py-4 wsm-text-sm wsm-text-center wsm-text-slate-400">${p.threshold.toLocaleString('fa-IR')} عدد</td>
						<td class="wsm-py-4 wsm-text-sm wsm-text-center">
							<span class="wsm-px-2.5 wsm-py-1 wsm-rounded-full wsm-text-xs wsm-font-semibold ${isLow ? 'wsm-bg-rose-900/30 wsm-text-rose-400' : 'wsm-bg-amber-900/30 wsm-text-amber-400'}">
								${isLow ? 'اتمام موجودی / بحرانی' : 'رو به اتمام'}
							</span>
						</td>
					</tr>
				`;
			});
			tbody.innerHTML = rows;
		} catch (err) {
			tbody.innerHTML = '<tr><td colspan="5" class="wsm-py-6 wsm-text-center wsm-text-rose-500">خطا در بارگذاری گزارش وضعیت انبار.</td></tr>';
		}
	}

	function exportProductsCSV() {
		if (currentReportData.length === 0) {
			alert('داده‌ای برای خروجی وجود ندارد.');
			return;
		}
		let csv = "data:text/csv;charset=utf-8,\uFEFF";
		csv += "شناسه کالا,نام محصول,موجودی فعلی,آستانه هشدار,وضعیت\n";
		currentReportData.forEach(p => {
			const status = p.stock <= p.threshold ? 'اتمام موجودی / بحرانی' : 'رو به اتمام';
			csv += `"${p.id}","${p.name}","${p.stock}","${p.threshold}","${status}"\n`;
		});
		const link = document.createElement("a");
		link.setAttribute("href", encodeURI(csv));
		link.setAttribute("download", `products-inventory-report-${new Date().toISOString().slice(0, 10)}.csv`);
		document.body.appendChild(link);
		link.click();
		document.body.removeChild(link);
	}

	/* ==========================================
	   CUSTOMERS REPORT (customers.php)
	   ========================================== */
	async function loadCustomersReport() {
		const start = document.getElementById('cust-start')?.value || '';
		const end   = document.getElementById('cust-end')?.value || '';
		const type  = document.getElementById('cust-type')?.value || 'top';
		const tbody = document.getElementById('customers-table-body');
		const thead = document.getElementById('customers-table-head');
		if (!tbody || !thead) return;

		tbody.innerHTML = '<tr><td colspan="5" class="wsm-py-6 wsm-text-center wsm-text-slate-500">در حال بارگذاری...</td></tr>';

		// Update Table Headers
		if ('new' === type) {
			thead.innerHTML = `
				<tr class="wsm-border-b wsm-border-slate-800/80">
					<th class="wsm-pb-3 wsm-text-xs wsm-text-slate-500">شناسه</th>
					<th class="wsm-pb-3 wsm-text-xs wsm-text-slate-500">نام مشتری</th>
					<th class="wsm-pb-3 wsm-text-xs wsm-text-slate-500">پست الکترونیک</th>
					<th class="wsm-pb-3 wsm-text-xs wsm-text-slate-500 wsm-text-center">تاریخ ثبت نام</th>
				</tr>
			`;
		} else {
			thead.innerHTML = `
				<tr class="wsm-border-b wsm-border-slate-800/80">
					<th class="wsm-pb-3 wsm-text-xs wsm-text-slate-500">شناسه</th>
					<th class="wsm-pb-3 wsm-text-xs wsm-text-slate-500">نام مشتری</th>
					<th class="wsm-pb-3 wsm-text-xs wsm-text-slate-500">پست الکترونیک</th>
					<th class="wsm-pb-3 wsm-text-xs wsm-text-slate-500 wsm-text-center">تعداد سفارش</th>
					<th class="wsm-pb-3 wsm-text-xs wsm-text-slate-500 wsm-text-center">مجموع خرید</th>
				</tr>
			`;
		}

		const queryParams = new URLSearchParams({ type: type, start_date: start, end_date: end });

		try {
			const response = await WSM.fetch('/reports/customers?' + queryParams.toString(), { method: 'GET' });
			const data = response.data;
			currentReportData = data;

			if (data.length === 0) {
				tbody.innerHTML = `<tr><td colspan="${'new' === type ? 4 : 5}" class="wsm-py-6 wsm-text-center wsm-text-slate-500">مشتری یافت نشد.</td></tr>`;
				return;
			}

			let rows = '';
			data.forEach(c => {
				if ('new' === type) {
					rows += `
						<tr class="hover:wsm-bg-slate-900/10">
							<td class="wsm-py-4 wsm-text-sm wsm-font-bold wsm-text-slate-400">#${c.id}</td>
							<td class="wsm-py-4 wsm-text-sm wsm-font-semibold wsm-text-slate-300">${WSM.escHtml(c.name)}</td>
							<td class="wsm-py-4 wsm-text-sm wsm-text-slate-400">${WSM.escHtml(c.email)}</td>
							<td class="wsm-py-4 wsm-text-sm wsm-text-center wsm-text-slate-300">${c.registered_jalali}</td>
						</tr>
					`;
				} else {
					rows += `
						<tr class="hover:wsm-bg-slate-900/10">
							<td class="wsm-py-4 wsm-text-sm wsm-font-bold wsm-text-slate-400">${c.id ? '#' + c.id : 'مهمان'}</td>
							<td class="wsm-py-4 wsm-text-sm wsm-font-semibold wsm-text-slate-300">${WSM.escHtml(c.name)}</td>
							<td class="wsm-py-4 wsm-text-sm wsm-text-slate-400">${WSM.escHtml(c.email)}</td>
							<td class="wsm-py-4 wsm-text-sm wsm-text-center wsm-font-bold wsm-text-slate-300">${c.orders_count.toLocaleString('fa-IR')} سفارش</td>
							<td class="wsm-py-4 wsm-text-sm wsm-text-center wsm-font-bold wsm-text-indigo-400">${formatPrice(c.total_spent)}</td>
						</tr>
					`;
				}
			});
			tbody.innerHTML = rows;
		} catch (err) {
			tbody.innerHTML = '<tr><td colspan="5" class="wsm-py-6 wsm-text-center wsm-text-rose-500">خطا در دریافت گزارش مشتریان.</td></tr>';
		}
	}

	function exportCustomersCSV() {
		if (currentReportData.length === 0) {
			alert('داده‌ای برای خروجی وجود ندارد.');
			return;
		}
		const type = document.getElementById('cust-type')?.value || 'top';
		let csv = "data:text/csv;charset=utf-8,\uFEFF";

		if ('new' === type) {
			csv += "شناسه,نام مشتری,ایمیل,تاریخ ثبت نام\n";
			currentReportData.forEach(c => {
				csv += `"${c.id}","${c.name}","${c.email}","${c.registered_jalali}"\n`;
			});
		} else {
			csv += "شناسه,نام مشتری,ایمیل,تعداد سفارش,مجموع خرید (تومان)\n";
			currentReportData.forEach(c => {
				csv += `"${c.id || 'مهمان'}","${c.name}","${c.email}","${c.orders_count}","${c.total_spent}"\n`;
			});
		}

		const link = document.createElement("a");
		link.setAttribute("href", encodeURI(csv));
		link.setAttribute("download", `customers-${type}-report-${new Date().toISOString().slice(0, 10)}.csv`);
		document.body.appendChild(link);
		link.click();
		document.body.removeChild(link);
	}

	// Bootstrap page logic router
	document.addEventListener('DOMContentLoaded', () => {
		if (document.getElementById('wsm-sales-report-page')) {
			loadDetailedSalesReport();
			document.getElementById('wsm-filter-sales-btn')?.addEventListener('click', (e) => {
				e.preventDefault();
				loadDetailedSalesReport();
			});
			document.getElementById('wsm-export-sales-csv-btn')?.addEventListener('click', (e) => {
				e.preventDefault();
				exportSalesCSV();
			});
		} else if (document.getElementById('wsm-products-report-page')) {
			loadProductsInventory();
			document.getElementById('wsm-export-products-csv-btn')?.addEventListener('click', (e) => {
				e.preventDefault();
				exportProductsCSV();
			});
		} else if (document.getElementById('wsm-customers-report-page')) {
			loadCustomersReport();
			document.getElementById('wsm-filter-cust-btn')?.addEventListener('click', (e) => {
				e.preventDefault();
				loadCustomersReport();
			});
			document.getElementById('wsm-export-customers-csv-btn')?.addEventListener('click', (e) => {
				e.preventDefault();
				exportCustomersCSV();
			});
			// Reload when changing select type
			document.getElementById('cust-type')?.addEventListener('change', () => {
				loadCustomersReport();
			});
		} else {
			// Dashboard Report
			loadQuickStats();
			loadPeriodReport();

			document.getElementById('wsm-filter-reports-btn')?.addEventListener('click', (e) => {
				e.preventDefault();
				loadPeriodReport();
			});

			document.getElementById('wsm-export-csv-btn')?.addEventListener('click', (e) => {
				e.preventDefault();
				exportToCSV();
			});
		}
	});

})();

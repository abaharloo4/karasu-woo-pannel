/**
 * KarasuWooPannel Products Management Script
 *
 * @package KarasuWooPannel
 * @version 1.0.10
 * @date 2026-06-23
 */

(function() {
	'use strict';

	let currentPage = 1;
	const perPage = 20;

	/**
	 * Formats numeric price string to Persian currency.
	 *
	 * @param {number|string} price Raw price.
	 * @returns {string} Formatted price with Tomans.
	 */
	function formatPrice(price) {
		if (!price || Number(price) === 0) return 'ثبت نشده';
		return Number(price).toLocaleString('fa-IR') + ' تومان';
	}

	// 1. PRODUCTS LIST HANDLER
	function updateBulkProductsPanel() {
		const checkboxes = document.querySelectorAll('.product-checkbox:checked');
		const bulkPanel = document.getElementById('products-bulk-actions');
		const countSpan = document.getElementById('selected-products-count');
		const selectAll = document.getElementById('select-all-products');

		if (!bulkPanel) return;

		const count = checkboxes.length;
		if (count > 0) {
			bulkPanel.classList.remove('wsm-hidden');
			countSpan.textContent = count.toLocaleString('fa-IR');
		} else {
			bulkPanel.classList.add('wsm-hidden');
		}

		const totalCheckboxes = document.querySelectorAll('.product-checkbox').length;
		if (selectAll) {
			selectAll.checked = totalCheckboxes > 0 && count === totalCheckboxes;
		}
	}

	async function loadCategoriesFilter() {
		const filterSelect = document.getElementById('product-category-filter');
		if (!filterSelect) return;

		try {
			const response = await WSM.fetch('/categories', { method: 'GET' });
			const categories = response.data;
			let optionsHtml = '<option value="">همه دسته‌بندی‌ها</option>';
			categories.forEach(cat => {
				optionsHtml += `<option value="${cat.slug}">${WSM.escHtml(cat.name)}</option>`;
			});
			filterSelect.innerHTML = optionsHtml;
		} catch (error) {
			// Fail silently for filters dropdown loading
		}
	}

	async function loadProductsList() {
		const tableBody = document.getElementById('products-table-body');
		if (!tableBody) return;

		const search = document.getElementById('product-search')?.value || '';
		const category = document.getElementById('product-category-filter')?.value || '';
		const stockStatus = document.getElementById('product-stock-filter')?.value || '';
		const status = document.getElementById('product-status-filter')?.value || '';

		const selectAll = document.getElementById('select-all-products');
		if (selectAll) selectAll.checked = false;
		const bulkPanel = document.getElementById('products-bulk-actions');
		if (bulkPanel) bulkPanel.classList.add('wsm-hidden');

		tableBody.innerHTML = `
			<tr>
				<td colspan="9" class="wsm-px-6 wsm-py-12 wsm-text-center wsm-text-slate-500 wsm-animate-pulse">
					در حال دریافت لیست محصولات...
				</td>
			</tr>
		`;

		try {
			const queryParams = new URLSearchParams({
				page: currentPage,
				per_page: perPage,
				search,
				category,
				stock_status: stockStatus,
				status,
			});

			const response = await WSM.fetch('/products?' + queryParams.toString(), { method: 'GET' });
			const { products, total, pages } = response.data;

			if (products.length === 0) {
				tableBody.innerHTML = `
					<tr>
						<td colspan="9" class="wsm-px-6 wsm-py-12 wsm-text-center wsm-text-slate-500">
							هیچ محصولی یافت نشد.
						</td>
					</tr>
				`;
				updatePaginationInfo(0, 0, 0);
				renderPaginationControls(0);
				return;
			}

			let rowsHtml = '';
			products.forEach(p => {
				const imgUrl = p.image || 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIGZpbGw9Im5vbmUiIHZpZXdCb3g9IjAgMCAyNCAyNCIgc3Ryb2tlPSIjMzM0MTU1Ij48cGF0aCBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiIHN0cm9rZS13aWR0aD0iMiIgZD0iTTQgMTZsNC41ODYtNC41ODZhMiAyIDAgMDEyLjgyOCAwTDE2IDE2bS0yLTJsMS41ODYtMS41ODZhMiAyIDAgMDEyLjgyOCAwTDIwIDE0bS0yLTZoLjAxTTYgMjBoMTJhMiAyIDAgMDAyLTJWNmEyIDIgMCAwMC0yLTJINmEyIDIgMCAwMC0yIDJ2MTJhMiAyIDAgMDAyIDJ6Ii8+PC9zdmc+';
				const statusBadge = p.status === 'publish' ? 'wsm-bg-emerald-500/10 wsm-text-emerald-400 wsm-border-emerald-500/20' : 'wsm-bg-slate-800 wsm-text-slate-400 wsm-border-slate-700';
				const stockBadge = p.stock_status === 'instock' ? 'wsm-bg-emerald-500/10 wsm-text-emerald-400' : 'wsm-bg-rose-500/10 wsm-text-rose-400';

				rowsHtml += `
					<tr class="wsm-border-b wsm-border-slate-800/40 hover:wsm-bg-slate-900/20 wsm-transition-colors">
						<td class="wsm-px-6 wsm-py-4">
							<input type="checkbox" class="product-checkbox wsm-rounded wsm-bg-slate-950 wsm-border-slate-800 focus:wsm-ring-indigo-500" value="${p.id}">
						</td>
						<td class="wsm-px-6 wsm-py-4">
							<img src="${imgUrl}" class="wsm-w-10 wsm-h-10 wsm-rounded-xl wsm-object-cover wsm-border wsm-border-slate-800" alt="${WSM.escHtml(p.name)}">
						</td>
						<td class="wsm-px-6 wsm-py-4 wsm-text-sm wsm-font-bold wsm-text-slate-200">${WSM.escHtml(p.name)}</td>
						<td class="wsm-px-6 wsm-py-4 wsm-text-sm wsm-text-slate-400">${WSM.escHtml(p.sku || 'ندارد')}</td>
						<td class="wsm-px-6 wsm-py-4 wsm-text-sm wsm-text-slate-400">${WSM.escHtml(p.categories || 'بدون دسته')}</td>
						<td class="wsm-px-6 wsm-py-4 wsm-text-sm wsm-font-semibold wsm-text-indigo-400">${formatPrice(p.price)}</td>
						<td class="wsm-px-6 wsm-py-4 wsm-text-sm">
							<button class="toggle-stock-btn wsm-px-2.5 wsm-py-1 wsm-text-xs wsm-font-semibold wsm-rounded-full wsm-border ${stockBadge}" data-id="${p.id}" data-status="${p.stock_status}">
								${WSM.escHtml(p.stock_label)} (${p.stock !== null ? p.stock.toLocaleString('fa-IR') : '∞'})
							</button>
						</td>
						<td class="wsm-px-6 wsm-py-4 wsm-text-sm">
							<span class="wsm-px-2.5 wsm-py-1 wsm-text-xs wsm-font-semibold wsm-rounded-full wsm-border ${statusBadge}">
								${WSM.escHtml(p.status_label)}
							</span>
						</td>
						<td class="wsm-px-6 wsm-py-4 wsm-text-center wsm-text-sm wsm-space-x-2 wsm-space-x-reverse">
							<a href="${window.wsmConfig.panelUrl}/products/edit?id=${p.id}" class="wsm-text-indigo-400 hover:wsm-text-indigo-300 wsm-font-semibold">ویرایش</a>
							<button class="delete-product-btn wsm-text-rose-400 hover:wsm-text-rose-300" data-id="${p.id}">حذف</button>
						</td>
					</tr>
				`;
			});

			tableBody.innerHTML = rowsHtml;

			// Bind checkboxes change
			document.querySelectorAll('.product-checkbox').forEach(cb => {
				cb.addEventListener('change', updateBulkProductsPanel);
			});

			// Bind inline stock triggers.
			document.querySelectorAll('.toggle-stock-btn').forEach(btn => {
				btn.addEventListener('click', async (e) => {
					e.preventDefault();
					const id = btn.getAttribute('data-id');
					const currentStatus = btn.getAttribute('data-status');
					const nextStatus = currentStatus === 'instock' ? 'outofstock' : 'instock';

					try {
						await WSM.fetch(`/products/${id}`, {
							method: 'PUT',
							body: JSON.stringify({ stock_status: nextStatus })
						});
						loadProductsList();
					} catch (err) {
						// Error is handled globally.
					}
				});
			});

			// Bind delete product triggers.
			document.querySelectorAll('.delete-product-btn').forEach(btn => {
				btn.addEventListener('click', async (e) => {
					e.preventDefault();
					const id = btn.getAttribute('data-id');
					if (confirm('آیا از انتقال این محصول به زباله‌دان مطمئن هستید؟')) {
						try {
							await WSM.fetch(`/products/${id}`, { method: 'DELETE' });
							loadProductsList();
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
					<td colspan="9" class="wsm-px-6 wsm-py-12 wsm-text-center wsm-text-rose-400">
						خطا در دریافت اطلاعات محصولات.
					</td>
				</tr>
			`;
		}
	}

	function updatePaginationInfo(start, end, total) {
		document.getElementById('products-count-start').textContent = start.toLocaleString('fa-IR');
		document.getElementById('products-count-end').textContent = end.toLocaleString('fa-IR');
		document.getElementById('products-count-total').textContent = total.toLocaleString('fa-IR');
	}

	function renderPaginationControls(totalPages) {
		const container = document.getElementById('products-pagination-controls');
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
				loadProductsList();
			});
		});
	}

	// 2. PRODUCT EDIT / CREATE FORM HANDLER
	async function renderProductForm(container, productId) {
		// Fetch categories and brands first.
		let categories = [];
		let brands = [];
		try {
			const catResponse = await WSM.fetch('/categories', { method: 'GET' });
			categories = catResponse.data;
		} catch (err) {
			// Ignore category fetch error.
		}
		try {
			const brandResponse = await WSM.fetch('/brands', { method: 'GET' });
			brands = brandResponse.data;
		} catch (err) {
			// Ignore brand fetch error.
		}

		let productData = null;
		if (productId > 0) {
			try {
				const prodResponse = await WSM.fetch(`/products/${productId}`, { method: 'GET' });
				productData = prodResponse.data;
			} catch (err) {
				container.innerHTML = `
					<div class="wsm-bg-slate-900 wsm-border wsm-border-rose-500/20 wsm-rounded-3xl wsm-p-8 wsm-text-center wsm-text-rose-400">
						خطا در دریافت اطلاعات محصول.
					</div>
				`;
				return;
			}
		}

		const isNew     = !productData;
		const pageTitle = isNew ? 'افزودن محصول جدید' : `ویرایش محصول: ${WSM.escHtml(productData.name)}`;
		const imageId   = productData?.image?.id || '';
		const imageUrl  = productData?.image?.url || '';

		let attributes = productData?.attributes || [];
		let variations = productData?.variations || [];

		// Build categories checkboxes.
		let catCheckboxes = '';
		categories.forEach(cat => {
			const checked = productData && productData.category_ids.includes(cat.id) ? 'checked' : '';
			catCheckboxes += `
				<label class="wsm-flex wsm-items-center wsm-text-sm wsm-text-slate-300 wsm-cursor-pointer wsm-py-1">
					<input type="checkbox" name="category_ids" value="${cat.id}" ${checked} class="wsm-ml-2">
					${WSM.escHtml(cat.name)}
				</label>
			`;
		});

		// Build brands checkboxes.
		let brandCheckboxes = '';
		brands.forEach(brand => {
			const checked = productData && productData.brand_ids && productData.brand_ids.includes(brand.id) ? 'checked' : '';
			brandCheckboxes += `
				<label class="wsm-flex wsm-items-center wsm-text-sm wsm-text-slate-300 wsm-cursor-pointer wsm-py-1">
					<input type="checkbox" name="brand_ids" value="${brand.id}" ${checked} class="wsm-ml-2">
					${WSM.escHtml(brand.name)}
				</label>
			`;
		});

		container.innerHTML = `
			<div class="wsm-flex wsm-items-center wsm-space-x-3 wsm-space-x-reverse">
				<a href="${window.wsmConfig.panelUrl}/products" class="wsm-text-slate-400 hover:wsm-text-slate-200">
					&larr; بازگشت به لیست
				</a>
				<span class="wsm-text-slate-600">/</span>
				<h1 class="wsm-text-2xl wsm-font-bold wsm-text-slate-100">${pageTitle}</h1>
			</div>

			<form id="product-form" class="wsm-grid wsm-grid-cols-1 lg:wsm-grid-cols-3 wsm-gap-6">
				<!-- Right side fields: Description, pricing, stock -->
				<div class="lg:wsm-col-span-2 wsm-space-y-6">
					<!-- General Info Card -->
					<div class="wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-p-6 wsm-shadow-lg wsm-space-y-4">
						<h3 class="wsm-font-semibold wsm-text-slate-200">اطلاعات اصلی محصول</h3>
						<div>
							<label for="p-name" class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-mb-2">عنوان محصول</label>
							<input type="text" id="p-name" required value="${isNew ? '' : WSM.escHtml(productData.name)}" class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-px-4 wsm-py-3 wsm-text-sm wsm-text-slate-200 focus:wsm-outline-none focus:wsm-border-indigo-500 wsm-transition-colors">
						</div>
						<div>
							<label for="p-type" class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-mb-2">نوع محصول</label>
							<select id="p-type" class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-px-4 wsm-py-3 wsm-text-sm focus:wsm-outline-none">
								<option value="simple" ${productData?.type === 'simple' || isNew ? 'selected' : ''}>محصول ساده</option>
								<option value="variable" ${productData?.type === 'variable' ? 'selected' : ''}>محصول متغیر</option>
							</select>
						</div>
						<div>
							<label for="p-desc" class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-mb-2">توضیحات کامل</label>
							<textarea id="p-desc" rows="6" class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-p-3 wsm-text-sm wsm-text-slate-200 focus:wsm-outline-none focus:wsm-border-indigo-500 wsm-transition-colors">${isNew ? '' : WSM.escHtml(productData.description)}</textarea>
						</div>
						<div>
							<label for="p-short-desc" class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-mb-2">توضیحات کوتاه</label>
							<textarea id="p-short-desc" rows="3" class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-p-3 wsm-text-sm wsm-text-slate-200 focus:wsm-outline-none focus:wsm-border-indigo-500 wsm-transition-colors">${isNew ? '' : WSM.escHtml(productData.short_description)}</textarea>
						</div>
					</div>

					<!-- Pricing & Inventory (For Simple Product) -->
					<div id="simple-pricing-inventory-card" class="wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-p-6 wsm-shadow-lg wsm-space-y-4">
						<h3 class="wsm-font-semibold wsm-text-slate-200">قیمت‌گذاری و انبار</h3>
						<div class="wsm-grid wsm-grid-cols-1 md:wsm-grid-cols-3 wsm-gap-4">
							<div>
								<label for="p-regular-price" class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-mb-2">قیمت اصلی (تومان)</label>
								<input type="number" id="p-regular-price" value="${isNew ? '' : productData.regular_price}" class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-px-4 wsm-py-3 wsm-text-sm wsm-text-slate-200 focus:wsm-outline-none focus:wsm-border-indigo-500">
							</div>
							<div>
								<label for="p-sale-price" class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-mb-2">قیمت حراج (تومان)</label>
								<input type="number" id="p-sale-price" value="${isNew || !productData.sale_price ? '' : productData.sale_price}" class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-px-4 wsm-py-3 wsm-text-sm wsm-text-slate-200 focus:wsm-outline-none focus:wsm-border-indigo-500">
							</div>
							<div>
								<label for="p-sku" class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-mb-2">شناسه کالا (SKU)</label>
								<input type="text" id="p-sku" value="${isNew ? '' : WSM.escHtml(productData.sku)}" class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-px-4 wsm-py-3 wsm-text-sm wsm-text-slate-200 focus:wsm-outline-none focus:wsm-border-indigo-500">
							</div>
						</div>
						<div class="wsm-grid wsm-grid-cols-1 md:wsm-grid-cols-3 wsm-gap-4 wsm-pt-2">
							<div class="wsm-flex wsm-items-center wsm-h-full">
								<label class="wsm-flex wsm-items-center wsm-text-sm wsm-text-slate-400 wsm-cursor-pointer">
									<input type="checkbox" id="p-manage-stock" ${productData?.manage_stock ? 'checked' : ''} class="wsm-ml-2">
									مدیریت موجودی انبار؟
								</label>
							</div>
							<div id="p-stock-qty-wrapper" class="${productData?.manage_stock ? '' : 'wsm-hidden'}">
								<label for="p-stock-qty" class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-mb-2">تعداد موجودی</label>
								<input type="number" id="p-stock-qty" value="${isNew ? '' : (productData.stock_quantity ?? '')}" class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-px-4 wsm-py-3 wsm-text-sm wsm-text-slate-200 focus:wsm-outline-none focus:wsm-border-indigo-500">
							</div>
							<div>
								<label for="p-stock-status" class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-mb-2">وضعیت موجودی</label>
								<select id="p-stock-status" class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-px-4 wsm-py-3 wsm-text-sm focus:wsm-outline-none">
									<option value="instock" ${productData?.stock_status === 'instock' ? 'selected' : ''}>موجود در انبار</option>
									<option value="outofstock" ${productData?.stock_status === 'outofstock' ? 'selected' : ''}>ناموجود</option>
								</select>
							</div>
						</div>
					</div>

					<!-- Variable Attributes & Variations Card (For Variable Product) -->
					<div id="p-variable-card" class="wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-p-6 wsm-shadow-lg wsm-space-y-4 wsm-hidden">
						<!-- Tabs Header -->
						<div class="wsm-flex wsm-border-b wsm-border-slate-800/80 wsm-pb-2">
							<button type="button" id="tab-btn-attributes" class="wsm-px-4 wsm-py-2 wsm-text-sm wsm-font-semibold wsm-text-indigo-400 wsm-border-b-2 wsm-border-indigo-500">ویژگی‌ها</button>
							<button type="button" id="tab-btn-variations" class="wsm-px-4 wsm-py-2 wsm-text-sm wsm-font-semibold wsm-text-slate-400 hover:wsm-text-slate-200">متغیرها</button>
						</div>

						<!-- Attributes Tab Content -->
						<div id="tab-content-attributes" class="wsm-space-y-4">
							<div class="wsm-flex wsm-items-center wsm-justify-between">
								<h4 class="wsm-text-sm wsm-font-semibold wsm-text-slate-300">ویژگی‌های کلا</h4>
								<button type="button" id="wsm-add-attr-btn" class="wsm-px-3 wsm-py-1.5 wsm-text-xs wsm-bg-indigo-600 hover:wsm-bg-indigo-500 wsm-text-white wsm-rounded-xl wsm-transition-colors">افزودن ویژگی جدید</button>
							</div>
							<div id="attributes-list-container" class="wsm-space-y-3">
								<!-- Rendered dynamically -->
							</div>
						</div>

						<!-- Variations Tab Content -->
						<div id="tab-content-variations" class="wsm-space-y-4 wsm-hidden">
							<div class="wsm-flex wsm-items-center wsm-justify-between">
								<h4 class="wsm-text-sm wsm-font-semibold wsm-text-slate-300">لیست متغیرها</h4>
								<button type="button" id="wsm-generate-vars-btn" class="wsm-px-3 wsm-py-1.5 wsm-text-xs wsm-bg-emerald-600 hover:wsm-bg-emerald-500 wsm-text-white wsm-rounded-xl wsm-transition-colors">تولید متغیرها از روی ویژگی‌ها</button>
							</div>
							<div id="variations-list-container" class="wsm-space-y-4">
								<!-- Rendered dynamically -->
							</div>
						</div>
					</div>

					<!-- Shipping dimensions -->
					<div class="wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-p-6 wsm-shadow-lg wsm-space-y-4">
						<h3 class="wsm-font-semibold wsm-text-slate-200">حمل و نقل (ابعاد و وزن)</h3>
						<div class="wsm-grid wsm-grid-cols-1 md:wsm-grid-cols-4 wsm-gap-4">
							<div>
								<label for="p-weight" class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-mb-2">وزن (کیلوگرم)</label>
								<input type="text" id="p-weight" value="${isNew ? '' : WSM.escHtml(productData.weight)}" class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-px-4 wsm-py-3 wsm-text-sm wsm-text-slate-200 focus:wsm-outline-none focus:wsm-border-indigo-500">
							</div>
							<div>
								<label for="p-length" class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-mb-2">طول (سانتی‌متر)</label>
								<input type="text" id="p-length" value="${isNew ? '' : WSM.escHtml(productData.length)}" class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-px-4 wsm-py-3 wsm-text-sm wsm-text-slate-200 focus:wsm-outline-none focus:wsm-border-indigo-500">
							</div>
							<div>
								<label for="p-width" class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-mb-2">عرض (سانتی‌متر)</label>
								<input type="text" id="p-width" value="${isNew ? '' : WSM.escHtml(productData.width)}" class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-px-4 wsm-py-3 wsm-text-sm wsm-text-slate-200 focus:wsm-outline-none focus:wsm-border-indigo-500">
							</div>
							<div>
								<label for="p-height" class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-mb-2">ارتفاع (سانتی‌متر)</label>
								<input type="text" id="p-height" value="${isNew ? '' : WSM.escHtml(productData.height)}" class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-px-4 wsm-py-3 wsm-text-sm wsm-text-slate-200 focus:wsm-outline-none focus:wsm-border-indigo-500">
							</div>
						</div>
					</div>
				</div>

				<!-- Left side: Image and Categories -->
				<div class="wsm-space-y-6">
					<!-- Image Card -->
					<div class="wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-p-6 wsm-shadow-lg wsm-space-y-4">
						<h3 class="wsm-font-semibold wsm-text-slate-200 text-center">تصویر شاخص محصول</h3>
						<div class="wsm-flex wsm-flex-col wsm-items-center">
							<!-- Image Upload Preview trigger Box -->
							<div id="image-upload-trigger" class="wsm-relative wsm-w-full wsm-h-48 wsm-bg-slate-950/60 wsm-border-2 wsm-border-dashed wsm-border-slate-800 wsm-rounded-2xl wsm-overflow-hidden wsm-flex wsm-items-center wsm-justify-center wsm-cursor-pointer group">
								<img id="image-preview" src="${imageUrl}" class="wsm-w-full wsm-h-full wsm-object-cover ${imageUrl ? '' : 'wsm-hidden'}" alt="Preview">
								<div id="upload-placeholder" class="text-center wsm-p-4 ${imageUrl ? 'wsm-hidden' : ''}">
									<svg class="wsm-w-10 wsm-h-10 wsm-mx-auto wsm-text-slate-600 group-hover:wsm-text-slate-400 wsm-transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
									</svg>
									<span class="wsm-block wsm-text-xs wsm-text-slate-500 wsm-mt-2">برای آپلود تصویر کلیک کنید</span>
								</div>
								<div id="uploading-overlay" class="wsm-hidden wsm-absolute wsm-inset-0 wsm-bg-slate-950/80 wsm-flex wsm-items-center wsm-justify-center wsm-text-xs wsm-text-slate-400">
									در حال آپلود...
								</div>
							</div>
							<input type="hidden" id="p-image-id" value="${imageId}">
							<input type="file" id="product-image-file" class="wsm-hidden" accept="image/*">
						</div>
					</div>

					<!-- Categories list Card -->
					<div class="wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-p-6 wsm-shadow-lg wsm-space-y-4">
						<h3 class="wsm-font-semibold wsm-text-slate-200">${window.wsmConfig.translations?.categories || 'دسته‌بندی‌ها'}</h3>
						<div class="wsm-max-h-60 wsm-overflow-y-auto wsm-pr-1 wsm-flex wsm-flex-col">
							${catCheckboxes || `<p class="wsm-text-xs wsm-text-slate-500">${window.wsmConfig.translations?.noCategories || 'هیچ دسته‌بندی وجود ندارد.'}</p>`}
						</div>
					</div>

					<!-- Brands list Card -->
					<div class="wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-p-6 wsm-shadow-lg wsm-space-y-4">
						<h3 class="wsm-font-semibold wsm-text-slate-200">${window.wsmConfig.translations?.brands || 'برندها'}</h3>
						<div class="wsm-max-h-60 wsm-overflow-y-auto wsm-pr-1 wsm-flex wsm-flex-col">
							${brandCheckboxes || `<p class="wsm-text-xs wsm-text-slate-500">${window.wsmConfig.translations?.noBrands || 'هیچ برندی وجود ندارد.'}</p>`}
						</div>
					</div>

					<!-- Publish button options -->
					<div class="wsm-bg-slate-900/60 wsm-backdrop-blur-md wsm-border wsm-border-slate-800 wsm-rounded-3xl wsm-p-6 wsm-shadow-lg wsm-space-y-4">
						<div>
							<label for="p-status" class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-mb-2">وضعیت انتشار</label>
							<select id="p-status" class="wsm-w-full wsm-bg-slate-950/80 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-px-4 wsm-py-3 wsm-text-sm focus:wsm-outline-none">
								<option value="publish" ${productData?.status === 'publish' ? 'selected' : ''}>انتشار عمومی</option>
								<option value="draft" ${isNew || productData?.status === 'draft' ? 'selected' : ''}>پیش‌نویس</option>
							</select>
						</div>
						<button type="submit" id="wsm-save-product-btn" class="wsm-w-full wsm-bg-indigo-600 hover:wsm-bg-indigo-500 wsm-text-white wsm-font-semibold wsm-rounded-2xl wsm-py-4 wsm-shadow-lg wsm-shadow-indigo-500/20 wsm-transition-colors wsm-flex wsm-items-center wsm-justify-center">
							<span>ذخیره نهایی محصول</span>
						</button>
					</div>
				</div>
			</form>
		`;

		// BINDINGS
		const form       = document.getElementById('product-form');
		const fileInput  = document.getElementById('product-image-file');
		const triggerBox = document.getElementById('image-upload-trigger');
		const manageStock = document.getElementById('p-manage-stock');
		const stockQtyWrapper = document.getElementById('p-stock-qty-wrapper');

		const pType = document.getElementById('p-type');
		const simpleCard = document.getElementById('simple-pricing-inventory-card');
		const variableCard = document.getElementById('p-variable-card');

		// Toggle stock quantity input.
		manageStock?.addEventListener('change', (e) => {
			if (e.target.checked) {
				stockQtyWrapper.classList.remove('wsm-hidden');
			} else {
				stockQtyWrapper.classList.add('wsm-hidden');
			}
		});

		// Trigger file upload dialog.
		triggerBox?.addEventListener('click', () => {
			fileInput.removeAttribute('data-target-variation-idx');
			fileInput.click();
		});

		// Handle file upload.
		fileInput?.addEventListener('change', async () => {
			if (fileInput.files.length === 0) return;
			const file = fileInput.files[0];
			const formData = new FormData();
			formData.append('file', file);

			const varIdxAttr = fileInput.getAttribute('data-target-variation-idx');
			const isVariation = varIdxAttr !== null;

			let overlay = document.getElementById('uploading-overlay');
			if (!isVariation && overlay) overlay.classList.remove('wsm-hidden');

			try {
				const response = await WSM.fetch('/products/media', {
					method: 'POST',
					headers: {
						'Content-Type': undefined,
					},
					body: formData
				});

				const { id, url } = response.data;

				if (isVariation) {
					const idx = parseInt(varIdxAttr);
					variations[idx].image_id = id;
					variations[idx].image_url = url;
					renderVariations();
					fileInput.removeAttribute('data-target-variation-idx');
				} else {
					document.getElementById('p-image-id').value = id;
					const previewImg = document.getElementById('image-preview');
					previewImg.src = url;
					previewImg.classList.remove('wsm-hidden');
					document.getElementById('upload-placeholder').classList.add('wsm-hidden');
				}

			} catch (err) {
				// Handled globally
			} finally {
				if (!isVariation && overlay) overlay.classList.add('wsm-hidden');
			}
		});

		// VARIABLE PRODUCT ACTIONS
		function toggleProductType() {
			if (pType && pType.value === 'variable') {
				simpleCard?.classList.add('wsm-hidden');
				variableCard?.classList.remove('wsm-hidden');
				renderAttributes();
				renderVariations();
			} else {
				simpleCard?.classList.remove('wsm-hidden');
				variableCard?.classList.add('wsm-hidden');
			}
		}

		pType?.addEventListener('change', toggleProductType);

		// Tabs Switching
		const tabBtnAttrs = document.getElementById('tab-btn-attributes');
		const tabBtnVars = document.getElementById('tab-btn-variations');
		const tabContentAttrs = document.getElementById('tab-content-attributes');
		const tabContentVars = document.getElementById('tab-content-variations');

		tabBtnAttrs?.addEventListener('click', () => {
			tabBtnAttrs.classList.add('wsm-text-indigo-400', 'wsm-border-b-2', 'wsm-border-indigo-500');
			tabBtnAttrs.classList.remove('wsm-text-slate-400');
			tabBtnVars.classList.remove('wsm-text-indigo-400', 'wsm-border-b-2', 'wsm-border-indigo-500');
			tabBtnVars.classList.add('wsm-text-slate-400');
			tabContentAttrs.classList.remove('wsm-hidden');
			tabContentVars.classList.add('wsm-hidden');
		});

		tabBtnVars?.addEventListener('click', () => {
			tabBtnVars.classList.add('wsm-text-indigo-400', 'wsm-border-b-2', 'wsm-border-indigo-500');
			tabBtnVars.classList.remove('wsm-text-slate-400');
			tabBtnAttrs.classList.remove('wsm-text-indigo-400', 'wsm-border-b-2', 'wsm-border-indigo-500');
			tabBtnAttrs.classList.add('wsm-text-slate-400');
			tabContentVars.classList.remove('wsm-hidden');
			tabContentAttrs.classList.add('wsm-hidden');
		});

		// Add Attribute
		document.getElementById('wsm-add-attr-btn')?.addEventListener('click', () => {
			attributes.push({
				name: '',
				options: '',
				is_visible: true,
				is_variation: true
			});
			renderAttributes();
		});

		// Generate Variations
		document.getElementById('wsm-generate-vars-btn')?.addEventListener('click', () => {
			generateVariationsFromAttributes();
		});

		function renderAttributes() {
			const container = document.getElementById('attributes-list-container');
			if (!container) return;

			if (attributes.length === 0) {
				container.innerHTML = `<p class="wsm-text-xs wsm-text-slate-500">هیچ ویژگی ثبت نشده است. ویژگی‌هایی مانند رنگ یا سایز اضافه کنید.</p>`;
				return;
			}

			let html = '';
			attributes.forEach((attr, idx) => {
				html += `
					<div class="attr-row wsm-bg-slate-950/40 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-p-4 wsm-relative" data-index="${idx}">
						<button type="button" class="wsm-remove-attr-btn wsm-absolute wsm-top-3 wsm-left-3 wsm-text-xs wsm-text-rose-400 hover:wsm-text-rose-300">حذف ویژگی</button>
						<div class="wsm-grid wsm-grid-cols-1 md:wsm-grid-cols-2 wsm-gap-4">
							<div>
								<label class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-mb-1.5">نام ویژگی (مثال: رنگ)</label>
								<input type="text" class="attr-name wsm-w-full wsm-bg-slate-900 wsm-border wsm-border-slate-800 wsm-rounded-xl wsm-px-3 wsm-py-2 wsm-text-sm wsm-text-slate-200" value="${WSM.escHtml(attr.name)}">
							</div>
							<div>
								<label class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-mb-1.5">مقادیر (با علامت | جدا کنید. مثال: آبی | قرمز)</label>
								<input type="text" class="attr-options wsm-w-full wsm-bg-slate-900 wsm-border wsm-border-slate-800 wsm-rounded-xl wsm-px-3 wsm-py-2 wsm-text-sm wsm-text-slate-200" value="${WSM.escHtml(attr.options)}">
							</div>
						</div>
					</div>
				`;
			});
			container.innerHTML = html;

			// Bind removals & inputs
			container.querySelectorAll('.wsm-remove-attr-btn').forEach(btn => {
				btn.addEventListener('click', () => {
					const idx = parseInt(btn.closest('.attr-row').getAttribute('data-index'));
					attributes.splice(idx, 1);
					renderAttributes();
				});
			});

			container.querySelectorAll('.attr-name').forEach(input => {
				input.addEventListener('change', () => {
					const idx = parseInt(input.closest('.attr-row').getAttribute('data-index'));
					attributes[idx].name = input.value;
				});
			});

			container.querySelectorAll('.attr-options').forEach(input => {
				input.addEventListener('change', () => {
					const idx = parseInt(input.closest('.attr-row').getAttribute('data-index'));
					attributes[idx].options = input.value;
				});
			});
		}

		function generateVariationsFromAttributes() {
			if (attributes.length === 0) {
				alert('ابتدا حداقل یک ویژگی با مقادیر معتبر وارد کنید.');
				return;
			}

			const sets = [];
			const attrNames = [];
			attributes.forEach(attr => {
				const opts = attr.options.split('|').map(o => o.trim()).filter(Boolean);
				if (opts.length > 0) {
					sets.push(opts);
					attrNames.push(attr.name);
				}
			});

			if (sets.length === 0) {
				alert('هیچ مقداری برای ویژگی‌ها وارد نشده است.');
				return;
			}

			function cartesian(args) {
				const r = [];
				const max = args.length - 1;
				function helper(arr, i) {
					for (let j = 0, l = args[i].length; j < l; j++) {
						const a = arr.slice(0);
						a.push(args[i][j]);
						if (i === max) {
							r.push(a);
						} else {
							helper(a, i + 1);
						}
					}
				}
				helper([], 0);
				return r;
			}

			const combinations = cartesian(sets);
			const newVariations = [];

			combinations.forEach(combo => {
				const comboAttrs = {};
				attrNames.forEach((name, idx) => {
					const key = 'attribute_' + name.toLowerCase().replace(/[^a-z0-9_آ-ی]/g, '-');
					comboAttrs[key] = combo[idx];
				});

				const label = combo.join(' - ');
				const existing = variations.find(v => {
					return Object.keys(comboAttrs).every(k => v.attributes[k] === comboAttrs[k]);
				});

				if (existing) {
					newVariations.push(existing);
				} else {
					newVariations.push({
						id: 0,
						sku: '',
						regular_price: '',
						sale_price: '',
						manage_stock: false,
						stock_quantity: '',
						stock_status: 'instock',
						image_id: '',
						image_url: '',
						attributes: comboAttrs,
						_label: label
					});
				}
			});

			variations = newVariations;
			renderVariations();
		}

		function renderVariations() {
			const container = document.getElementById('variations-list-container');
			if (!container) return;

			if (variations.length === 0) {
				container.innerHTML = `<p class="wsm-text-xs wsm-text-slate-500">هیچ متغیری ایجاد نشده است. روی دکمه "تولید متغیرها" کلیک کنید.</p>`;
				return;
			}

			let html = '';
			variations.forEach((v, idx) => {
				let label = v._label;
				if (!label) {
					const vals = [];
					Object.keys(v.attributes).forEach(k => {
						if (k.startsWith('attribute_')) {
							vals.push(v.attributes[k]);
						}
					});
					label = vals.join(' - ') || `متغیر #${v.id || idx + 1}`;
				}

				const imgUrl = v.image_url || 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIGZpbGw9Im5vbmUiIHZpZXdCb3g9IjAgMCAyNCAyNCIgc3Ryb2tlPSIjMzM0MTU1Ij48cGF0aCBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiIHN0cm9rZS13aWR0aD0iMiIgZD0iTTQgMTZsNC41ODYtNC41ODZhMiAyIDAgMDEyLjgyOCAwTDE2IDE2bS0yLTJsMS41ODYtMS41ODZhMiAyIDAgMDEyLjgyOCAwTDIwIDE0bS0yLTZoLjAxTTYgMjBoMTJhMiAyIDAgMDAyLTJWNmEyIDIgMCAwMC0yLTJINmEyIDIgMCAwMC0yIDJ2MTJhMiAyIDAgMDAyIDJ6Ii8+PC9zdmc+';

				html += `
					<div class="var-row wsm-bg-slate-950/40 wsm-border wsm-border-slate-800 wsm-rounded-2xl wsm-overflow-hidden" data-index="${idx}">
						<!-- Accordion Header -->
						<div class="wsm-flex wsm-items-center wsm-justify-between wsm-px-4 wsm-py-3 wsm-bg-slate-950/60 wsm-cursor-pointer hover:wsm-bg-slate-900/50 wsm-transition-colors var-header">
							<div class="wsm-flex wsm-items-center wsm-space-x-3 wsm-space-x-reverse">
								<img src="${imgUrl}" class="var-img-preview-mini wsm-w-8 wsm-h-8 wsm-rounded-lg wsm-object-cover wsm-border wsm-border-slate-800" alt="Preview">
								<span class="wsm-text-sm wsm-font-bold wsm-text-slate-200">${WSM.escHtml(label)}</span>
								${v.sku ? `<span class="wsm-text-xs wsm-text-slate-500">(${WSM.escHtml(v.sku)})</span>` : ''}
							</div>
							<div class="wsm-flex wsm-items-center wsm-space-x-2 wsm-space-x-reverse">
								<span class="wsm-text-xs wsm-text-indigo-400 wsm-font-semibold">${v.regular_price ? formatPrice(v.regular_price) : 'بدون قیمت'}</span>
								<svg class="chevron wsm-w-4 wsm-h-4 wsm-text-slate-500 wsm-transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
								</svg>
							</div>
						</div>

						<!-- Accordion Body -->
						<div class="wsm-p-4 wsm-border-t wsm-border-slate-800/40 wsm-space-y-4 var-body wsm-hidden">
							<div class="wsm-grid wsm-grid-cols-1 md:wsm-grid-cols-3 wsm-gap-4">
								<div>
									<label class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-mb-1.5">قیمت متغیر (تومان)</label>
									<input type="number" class="var-regular-price wsm-w-full wsm-bg-slate-900 wsm-border wsm-border-slate-800 wsm-rounded-xl wsm-px-3 wsm-py-2 wsm-text-sm wsm-text-slate-200" value="${v.regular_price}">
								</div>
								<div>
									<label class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-mb-1.5">قیمت حراج (تومان)</label>
									<input type="number" class="var-sale-price wsm-w-full wsm-bg-slate-900 wsm-border wsm-border-slate-800 wsm-rounded-xl wsm-px-3 wsm-py-2 wsm-text-sm wsm-text-slate-200" value="${v.sale_price}">
								</div>
								<div>
									<label class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-mb-1.5">شناسه کالا (SKU)</label>
									<input type="text" class="var-sku wsm-w-full wsm-bg-slate-900 wsm-border wsm-border-slate-800 wsm-rounded-xl wsm-px-3 wsm-py-2 wsm-text-sm wsm-text-slate-200" value="${WSM.escHtml(v.sku)}">
								</div>
							</div>

							<div class="wsm-grid wsm-grid-cols-1 md:wsm-grid-cols-3 wsm-gap-4 wsm-pt-2">
								<div class="wsm-flex wsm-items-center">
									<label class="wsm-flex wsm-items-center wsm-text-xs wsm-text-slate-400 wsm-cursor-pointer">
										<input type="checkbox" class="var-manage-stock wsm-ml-2" ${v.manage_stock ? 'checked' : ''}>
										مدیریت موجودی انبار؟
									</label>
								</div>
								<div class="var-stock-qty-wrapper ${v.manage_stock ? '' : 'wsm-hidden'}">
									<label class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-mb-1.5">تعداد موجودی</label>
									<input type="number" class="var-stock-qty wsm-w-full wsm-bg-slate-900 wsm-border wsm-border-slate-800 wsm-rounded-xl wsm-px-3 wsm-py-2 wsm-text-sm wsm-text-slate-200" value="${v.stock_quantity ?? ''}">
								</div>
								<div>
									<label class="wsm-block wsm-text-xs wsm-font-semibold wsm-text-slate-400 wsm-mb-1.5">وضعیت موجودی</label>
									<select class="var-stock-status wsm-w-full wsm-bg-slate-900 wsm-border wsm-border-slate-800 wsm-rounded-xl wsm-px-3 wsm-py-2 wsm-text-sm focus:wsm-outline-none">
										<option value="instock" ${v.stock_status === 'instock' ? 'selected' : ''}>موجود در انبار</option>
										<option value="outofstock" ${v.stock_status === 'outofstock' ? 'selected' : ''}>ناموجود</option>
									</select>
								</div>
							</div>

							<div class="wsm-pt-2 wsm-flex wsm-items-center wsm-space-x-3 wsm-space-x-reverse">
								<div class="var-img-trigger wsm-w-16 wsm-h-16 wsm-bg-slate-900/60 wsm-border-2 wsm-border-dashed wsm-border-slate-800 wsm-rounded-xl wsm-overflow-hidden wsm-flex wsm-items-center wsm-justify-center wsm-cursor-pointer group">
									<img class="var-img-preview wsm-w-full wsm-h-full wsm-object-cover ${v.image_url ? '' : 'wsm-hidden'}" src="${v.image_url}" alt="Preview">
									<span class="var-img-placeholder wsm-text-slate-600 group-hover:wsm-text-slate-400 wsm-transition-colors ${v.image_url ? 'wsm-hidden' : ''}">+</span>
								</div>
								<input type="hidden" class="var-image-id" value="${v.image_id}">
								<span class="wsm-text-xs wsm-text-slate-500">تصویر متغیر</span>
							</div>
						</div>
					</div>
				`;
			});

			container.innerHTML = html;

			// Bind Accordion headers
			container.querySelectorAll('.var-header').forEach(header => {
				header.addEventListener('click', () => {
					const body = header.nextElementSibling;
					const chevron = header.querySelector('.chevron');
					if (body.classList.contains('wsm-hidden')) {
						body.classList.remove('wsm-hidden');
						chevron.style.transform = 'rotate(180deg)';
					} else {
						body.classList.add('wsm-hidden');
						chevron.style.transform = 'rotate(0deg)';
					}
				});
			});

			// Bind fields
			container.querySelectorAll('.var-row').forEach(row => {
				const idx = parseInt(row.getAttribute('data-index'));

				row.querySelector('.var-regular-price').addEventListener('change', (e) => {
					variations[idx].regular_price = e.target.value;
				});
				row.querySelector('.var-sale-price').addEventListener('change', (e) => {
					variations[idx].sale_price = e.target.value;
				});
				row.querySelector('.var-sku').addEventListener('change', (e) => {
					variations[idx].sku = e.target.value;
				});
				row.querySelector('.var-stock-status').addEventListener('change', (e) => {
					variations[idx].stock_status = e.target.value;
				});

				const manageStock = row.querySelector('.var-manage-stock');
				const qtyWrapper = row.querySelector('.var-stock-qty-wrapper');
				const stockQty = row.querySelector('.var-stock-qty');

				manageStock.addEventListener('change', (e) => {
					variations[idx].manage_stock = e.target.checked;
					if (e.target.checked) {
						qtyWrapper.classList.remove('wsm-hidden');
					} else {
						qtyWrapper.classList.add('wsm-hidden');
					}
				});

				stockQty.addEventListener('change', (e) => {
					variations[idx].stock_quantity = e.target.value;
				});

				const imgTrigger = row.querySelector('.var-img-trigger');
				imgTrigger.addEventListener('click', (e) => {
					e.stopPropagation();
					fileInput.setAttribute('data-target-variation-idx', idx);
					fileInput.click();
				});
			});
		}

		// Initial toggle check
		toggleProductType();

		// Handle form submit.
		form?.addEventListener('submit', async (e) => {
			e.preventDefault();
			const submitBtn = document.getElementById('wsm-save-product-btn');
			const submitText = submitBtn.innerHTML;
			submitBtn.disabled = true;
			submitBtn.innerHTML = '<span>در حال ذخیره...</span>';

			// Collect checked categories.
			const catIds = [];
			document.querySelectorAll('input[name="category_ids"]:checked').forEach(cb => {
				catIds.push(parseInt(cb.value));
			});

			// Collect checked brands.
			const brandIds = [];
			document.querySelectorAll('input[name="brand_ids"]:checked').forEach(cb => {
				brandIds.push(parseInt(cb.value));
			});

			const payload = {
				name: document.getElementById('p-name').value,
				type: pType ? pType.value : 'simple',
				description: document.getElementById('p-desc').value,
				short_description: document.getElementById('p-short-desc').value,
				regular_price: document.getElementById('p-regular-price').value,
				sale_price: document.getElementById('p-sale-price').value,
				sku: document.getElementById('p-sku').value,
				manage_stock: document.getElementById('p-manage-stock').checked,
				stock_quantity: document.getElementById('p-stock-qty').value,
				stock_status: document.getElementById('p-stock-status').value,
				image_id: document.getElementById('p-image-id').value,
				category_ids: catIds,
				brand_ids: brandIds,
				status: document.getElementById('p-status').value,
				weight: document.getElementById('p-weight').value,
				length: document.getElementById('p-length').value,
				width: document.getElementById('p-width').value,
				height: document.getElementById('p-height').value,
				attributes: attributes,
				variations: variations,
			};

			try {
				if (isNew) {
					await WSM.fetch('/products', {
						method: 'POST',
						body: JSON.stringify(payload)
					});
				} else {
					await WSM.fetch(`/products/${productId}`, {
						method: 'PUT',
						body: JSON.stringify(payload)
					});
				}
				window.location.href = window.wsmConfig.panelUrl + '/products';
			} catch (err) {
				submitBtn.disabled = false;
				submitBtn.innerHTML = submitText;
			}
		});
	}

	// 4. CATEGORIES PAGE HANDLER
	async function initCategoriesPage() {
		const tableBody = document.getElementById('categories-table-body');
		if (!tableBody) return;

		const form = document.getElementById('wsm-add-category-form');
		const parentSelect = document.getElementById('cat-parent');
		const imageTrigger = document.getElementById('cat-image-trigger');
		const imageFile = document.getElementById('cat-image-file');
		const imageIdInput = document.getElementById('cat-image-id');
		const imagePreview = document.getElementById('cat-image-preview');
		const imagePlaceholder = document.getElementById('cat-image-placeholder');

		// Load Categories Tree and populate parent dropdown
		async function loadCategories() {
			tableBody.innerHTML = `
				<tr>
					<td colspan="4" class="wsm-px-6 wsm-py-12 wsm-text-center wsm-text-slate-500 wsm-animate-pulse">
						در حال دریافت لیست دسته‌بندی‌ها...
					</td>
				</tr>
			`;

			try {
				const response = await WSM.fetch('/categories', { method: 'GET' });
				const categories = response.data;

				// Build parent options
				let optionsHtml = '<option value="0">بدون والد</option>';
				categories.forEach(cat => {
					optionsHtml += `<option value="${cat.id}">${WSM.escHtml(cat.name)}</option>`;
				});
				if (parentSelect) parentSelect.innerHTML = optionsHtml;

				if (categories.length === 0) {
					tableBody.innerHTML = `
						<tr>
							<td colspan="4" class="wsm-px-6 wsm-py-12 wsm-text-center wsm-text-slate-500">
								هیچ دسته‌بندی وجود ندارد.
							</td>
						</tr>
					`;
					return;
				}

				// Helper to build hierarchical list
				function buildTree(list, parentId = 0, depth = 0) {
					let html = '';
					const items = list.filter(item => Number(item.parent) === Number(parentId));
					items.forEach(item => {
						const indent = depth > 0 ? '— '.repeat(depth) : '';
						const imgUrl = item.image?.url || 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIGZpbGw9Im5vbmUiIHZpZXdCb3g9IjAgMCAyNCAyNCIgc3Ryb2tlPSIjMzM0MTU1Ij48cGF0aCBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiIHN0cm9rZS13aWR0aD0iMiIgZD0iTTQgMTZsNC41ODYtNC41ODZhMiAyIDAgMDEyLjgyOCAwTDE2IDE2bS0yLTJsMS41ODYtMS41ODZhMiAyIDAgMDEyLjgyOCAwTDIwIDE0bS0yLTZoLjAxTTYgMjBoMTJhMiAyIDAgMDAyLTJWNmEyIDIgMCAwMC0yLTJINmEyIDIgMCAwMC0yIDJ2MTJhMiAyIDAgMDAyIDJ6Ii8+PC9zdmc+';
						html += `
							<tr class="wsm-border-b wsm-border-slate-800/40 hover:wsm-bg-slate-900/20 wsm-transition-colors">
								<td class="wsm-px-6 wsm-py-4">
									<img src="${imgUrl}" class="wsm-w-10 wsm-h-10 wsm-rounded-xl wsm-object-cover wsm-border wsm-border-slate-800" alt="${WSM.escHtml(item.name)}">
								</td>
								<td class="wsm-px-6 wsm-py-4 wsm-text-sm wsm-font-bold wsm-text-slate-200">
									${indent}${WSM.escHtml(item.name)}
								</td>
								<td class="wsm-px-6 wsm-py-4 wsm-text-sm wsm-text-slate-400">
									${WSM.escHtml(item.slug)}
								</td>
								<td class="wsm-px-6 wsm-py-4 wsm-text-center wsm-text-sm">
									<button class="delete-cat-btn wsm-text-rose-400 hover:wsm-text-rose-300" data-id="${item.id}">حذف</button>
								</td>
							</tr>
						`;
						html += buildTree(list, item.id, depth + 1);
					});
					return html;
				}

				tableBody.innerHTML = buildTree(categories) || `
					<tr>
						<td colspan="4" class="wsm-px-6 wsm-py-12 wsm-text-center wsm-text-slate-500">
							خطا در چیدمان درختی دسته‌بندی‌ها.
						</td>
					</tr>
				`;

				// Bind delete buttons
				tableBody.querySelectorAll('.delete-cat-btn').forEach(btn => {
					btn.addEventListener('click', async (e) => {
						e.preventDefault();
						const id = btn.getAttribute('data-id');
						if (confirm('آیا از حذف این دسته‌بندی مطمئن هستید؟')) {
							try {
								await WSM.fetch(`/categories/${id}`, { method: 'DELETE' });
								loadCategories();
							} catch (err) {
								// Handled globally
							}
						}
					});
				});

			} catch (error) {
				tableBody.innerHTML = `
					<tr>
						<td colspan="4" class="wsm-px-6 wsm-py-12 wsm-text-center wsm-text-rose-400">
							خطا در دریافت اطلاعات دسته‌بندی‌ها.
						</td>
					</tr>
				`;
			}
		}

		// Handle category image upload
		imageTrigger?.addEventListener('click', () => {
			imageFile.click();
		});

		imageFile?.addEventListener('change', async () => {
			if (imageFile.files.length === 0) return;
			const file = imageFile.files[0];
			const formData = new FormData();
			formData.append('file', file);

			if (imagePlaceholder) imagePlaceholder.textContent = '...';

			try {
				const response = await WSM.fetch('/products/media', {
					method: 'POST',
					headers: { 'Content-Type': undefined },
					body: formData
				});
				const { id, url } = response.data;
				if (imageIdInput) imageIdInput.value = id;
				if (imagePreview) {
					imagePreview.src = url;
					imagePreview.classList.remove('wsm-hidden');
				}
				if (imagePlaceholder) imagePlaceholder.classList.add('wsm-hidden');
			} catch (err) {
				if (imagePlaceholder) imagePlaceholder.textContent = '+';
			}
		});

		// Handle form submit
		form?.addEventListener('submit', async (e) => {
			e.preventDefault();
			const submitBtn = form.querySelector('button[type="submit"]');
			const submitText = submitBtn.innerHTML;
			submitBtn.disabled = true;
			submitBtn.innerHTML = 'در حال ثبت...';

			const payload = {
				name: document.getElementById('cat-name').value,
				slug: document.getElementById('cat-slug').value,
				parent: parentSelect ? parseInt(parentSelect.value) : 0,
				description: document.getElementById('cat-desc').value,
				image_id: imageIdInput ? imageIdInput.value : '',
			};

			try {
				await WSM.fetch('/categories', {
					method: 'POST',
					body: JSON.stringify(payload)
				});

				// Reset form
				form.reset();
				if (imageIdInput) imageIdInput.value = '';
				if (imagePreview) {
					imagePreview.src = '';
					imagePreview.classList.add('wsm-hidden');
				}
				if (imagePlaceholder) {
					imagePlaceholder.classList.remove('wsm-hidden');
					imagePlaceholder.textContent = '+';
				}

				// Reload list
				loadCategories();
			} catch (err) {
				// Handled globally
			} finally {
				submitBtn.disabled = false;
				submitBtn.innerHTML = submitText;
			}
		});

		// Initial load
		loadCategories();
	}

	// 3. BOOTSTRAP TRIGGERS
	document.addEventListener('DOMContentLoaded', () => {
		const tableBody = document.getElementById('products-table-body');
		if (tableBody) {
			loadCategoriesFilter();
			loadProductsList();

			// Bind select-all products checkbox
			const selectAllProducts = document.getElementById('select-all-products');
			if (selectAllProducts) {
				selectAllProducts.addEventListener('change', (e) => {
					const checked = e.target.checked;
					document.querySelectorAll('.product-checkbox').forEach(cb => {
						cb.checked = checked;
					});
					updateBulkProductsPanel();
				});
			}

			// Bind bulk action apply button for products
			const applyProductsBulkBtn = document.getElementById('apply-products-bulk');
			if (applyProductsBulkBtn) {
				applyProductsBulkBtn.addEventListener('click', async (e) => {
					e.preventDefault();
					const actionSelect = document.getElementById('products-bulk-action-select');
					const selectedVal = actionSelect.value;
					if (!selectedVal) {
						alert('لطفا یک عملیات دسته جمعی را انتخاب کنید.');
						return;
					}

					const checkboxes = document.querySelectorAll('.product-checkbox:checked');
					const ids = Array.from(checkboxes).map(cb => parseInt(cb.value));
					if (ids.length === 0) return;

					let confirmMsg = '';
					let requestBody = { ids };

					if (selectedVal.startsWith('status_')) {
						const status = selectedVal.replace('status_', '');
						requestBody.action = 'status';
						requestBody.status = status;
						confirmMsg = `آیا از تغییر وضعیت ${ids.length.toLocaleString('fa-IR')} محصول مطمئن هستید؟`;
					} else if (selectedVal.startsWith('stock_')) {
						const stockStatus = selectedVal.replace('stock_', '');
						requestBody.action = 'stock_status';
						requestBody.stock_status = stockStatus;
						confirmMsg = `آیا از تغییر موجودی ${ids.length.toLocaleString('fa-IR')} محصول مطمئن هستید؟`;
					} else if (selectedVal === 'delete') {
						requestBody.action = 'delete';
						confirmMsg = `آیا از انتقال ${ids.length.toLocaleString('fa-IR')} محصول به زباله‌دان مطمئن هستید؟`;
					}

					if (confirm(confirmMsg)) {
						try {
							applyProductsBulkBtn.disabled = true;
							applyProductsBulkBtn.textContent = 'در حال اعمال...';
							await WSM.fetch('/products/bulk', {
								method: 'POST',
								body: JSON.stringify(requestBody)
							});
							loadProductsList();
						} catch (err) {
							// Handled globally
						} finally {
							applyProductsBulkBtn.disabled = false;
							applyProductsBulkBtn.textContent = 'اعمال تغییر';
							actionSelect.value = '';
						}
					}
				});
			}

			// Bind filters.
			const search = document.getElementById('product-search');
			const category = document.getElementById('product-category-filter');
			const stock = document.getElementById('product-stock-filter');
			const status = document.getElementById('product-status-filter');
			const clearBtn = document.getElementById('clear-product-filters');

			let searchTimeout;
			search?.addEventListener('input', () => {
				clearTimeout(searchTimeout);
				searchTimeout = setTimeout(() => {
					currentPage = 1;
					loadProductsList();
				}, 400);
			});

			category?.addEventListener('change', () => { currentPage = 1; loadProductsList(); });
			stock?.addEventListener('change', () => { currentPage = 1; loadProductsList(); });
			status?.addEventListener('change', () => { currentPage = 1; loadProductsList(); });

			clearBtn?.addEventListener('click', (e) => {
				e.preventDefault();
				if (search) search.value = '';
				if (category) category.value = '';
				if (stock) stock.value = '';
				if (status) status.value = '';
				currentPage = 1;
				loadProductsList();
			});
		}

		const editContainer = document.getElementById('product-edit-container');
		if (editContainer) {
			const productId = parseInt(editContainer.getAttribute('data-product-id') || '0');
			renderProductForm(editContainer, productId);
		}

		const catTableBody = document.getElementById('categories-table-body');
		if (catTableBody) {
			initCategoriesPage();
		}
	});

})();

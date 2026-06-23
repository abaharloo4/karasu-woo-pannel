/**
 * KarasuWooPannel Attributes and Brands Screen JavaScript
 *
 * @package KarasuWooPannel
 * @version 1.1.1
 * @date 2026-06-23
 */

(function() {
	'use strict';

	document.addEventListener('DOMContentLoaded', function() {
		if (document.getElementById('attributes-table-body')) {
			initAttributesPage();
		}
		if (document.getElementById('brands-table-body')) {
			initBrandsPage();
		}
	});

	/**
	 * 1. ATTRIBUTES MANAGEMENT PAGE
	 */
	async function initAttributesPage() {
		const tableBody = document.getElementById('attributes-table-body');
		const addForm = document.getElementById('wsm-add-attribute-form');

		// Load Attributes List
		async function loadAttributes() {
			try {
				tableBody.innerHTML = `
					<tr>
						<td colspan="3" class="wsm-px-6 wsm-py-12 wsm-text-center wsm-text-slate-500">
							${window.wsmConfig.translations?.loading || 'در حال بارگذاری...'}
						</td>
					</tr>
				`;

				const response = await WSM.fetch('/attributes', { method: 'GET' });
				const attributes = response.data || [];

				if (attributes.length === 0) {
					tableBody.innerHTML = `
						<tr>
							<td colspan="3" class="wsm-px-6 wsm-py-12 wsm-text-center wsm-text-slate-500">
								${window.wsmConfig.translations?.noAttributes || 'هیچ ویژگی ثبت نشده است.'}
							</td>
						</tr>
					`;
					return;
				}

				let html = '';
				attributes.forEach(attr => {
					html += `
						<tr class="wsm-border-b wsm-border-slate-800/40 hover:wsm-bg-slate-900/20" data-slug="${attr.slug}">
							<td class="wsm-px-6 wsm-py-4 wsm-text-sm wsm-text-slate-200 wsm-font-semibold">${WSM.escHtml(attr.name)}</td>
							<td class="wsm-px-6 wsm-py-4 wsm-text-sm wsm-text-slate-400 font-mono">pa_${WSM.escHtml(attr.slug)}</td>
							<td class="wsm-px-6 wsm-py-4 wsm-text-center">
								<button class="wsm-delete-attr-btn wsm-text-xs wsm-text-rose-400 hover:wsm-text-rose-300 wsm-font-semibold" data-id="${attr.id}">
									${window.wsmConfig.translations?.delete || 'حذف'}
								</button>
							</td>
						</tr>
						<tr class="wsm-bg-slate-950/20">
							<td colspan="3" class="wsm-px-8 wsm-py-3.5 wsm-border-b wsm-border-slate-800/20">
								<div class="wsm-flex wsm-flex-wrap wsm-items-center wsm-gap-2" id="terms-container-${attr.slug}">
									<span class="wsm-text-xs wsm-text-slate-500">${window.wsmConfig.translations?.loading || 'در حال دریافت مقادیر...'}</span>
								</div>
							</td>
						</tr>
					`;
				});
				tableBody.innerHTML = html;

				// Fetch terms for each attribute
				attributes.forEach(attr => {
					loadAttributeTerms(attr.slug);
				});

				// Bind delete attributes
				tableBody.querySelectorAll('.wsm-delete-attr-btn').forEach(btn => {
					btn.addEventListener('click', async () => {
						const id = btn.getAttribute('data-id');
						if (confirm(window.wsmConfig.translations?.confirmDeleteAttribute || 'آیا از حذف این ویژگی اطمینان دارید؟ تمامی مقادیر آن نیز حذف خواهد شد.')) {
							try {
								await WSM.fetch(`/attributes/${id}`, { method: 'DELETE' });
								loadAttributes();
							} catch (err) {
								alert(err.message || 'خطا در حذف ویژگی.');
							}
						}
					});
				});

			} catch (err) {
				tableBody.innerHTML = `
					<tr>
						<td colspan="3" class="wsm-px-6 wsm-py-12 wsm-text-center wsm-text-rose-400">
							خطا در بارگذاری اطلاعات.
						</td>
					</tr>
				`;
			}
		}

		// Load Terms inline for an attribute
		async function loadAttributeTerms(slug) {
			const container = document.getElementById(`terms-container-${slug}`);
			if (!container) return;

			try {
				const response = await WSM.fetch(`/attributes/${slug}/terms`, { method: 'GET' });
				const terms = response.data || [];

				let html = '';
				terms.forEach(term => {
					html += `
						<span class="wsm-inline-flex wsm-items-center wsm-gap-1 wsm-bg-slate-800 wsm-text-slate-300 wsm-text-xs wsm-px-2.5 wsm-py-1 wsm-rounded-lg wsm-border wsm-border-slate-700/60">
							${WSM.escHtml(term.name)}
							<button class="wsm-delete-term-btn wsm-text-slate-500 hover:wsm-text-rose-400 wsm-transition-colors focus:wsm-outline-none" data-id="${term.id}" data-slug="${slug}" style="font-size: 10px; padding: 2px;">
								&times;
							</button>
						</span>
					`;
				});

				// Add inline input for adding new term
				html += `
					<div class="wsm-inline-flex wsm-items-center wsm-gap-1.5 wsm-ml-2">
						<input type="text" placeholder="${window.wsmConfig.translations?.addValue || 'مقدار جدید'}" class="wsm-inline-add-term-input wsm-w-24 wsm-bg-slate-950 wsm-border wsm-border-slate-800 wsm-rounded-lg wsm-px-2 wsm-py-1 wsm-text-xs wsm-text-slate-300 focus:wsm-outline-none focus:wsm-border-indigo-500" data-slug="${slug}">
						<button class="wsm-inline-add-term-btn wsm-bg-indigo-600 hover:wsm-bg-indigo-500 wsm-text-white wsm-text-xs wsm-px-2 wsm-py-1 wsm-rounded-lg wsm-transition-colors" data-slug="${slug}">
							+
						</button>
					</div>
				`;

				container.innerHTML = html;

				// Bind term deletion
				container.querySelectorAll('.wsm-delete-term-btn').forEach(btn => {
					btn.addEventListener('click', async () => {
						const termId = btn.getAttribute('data-id');
						const attrSlug = btn.getAttribute('data-slug');
						try {
							await WSM.fetch(`/attributes/${attrSlug}/terms/${termId}`, { method: 'DELETE' });
							loadAttributeTerms(attrSlug);
						} catch (err) {
							alert(err.message || 'خطا در حذف مقدار ویژگی.');
						}
					});
				});

				// Bind inline term creation
				const addBtn = container.querySelector('.wsm-inline-add-term-btn');
				const inputEl = container.querySelector('.wsm-inline-add-term-input');

				const submitInlineTerm = async () => {
					const val = inputEl.value.trim();
					if (!val) return;
					addBtn.disabled = true;
					try {
						await WSM.fetch(`/attributes/${slug}/terms`, {
							method: 'POST',
							body: JSON.stringify({ name: val })
						});
						loadAttributeTerms(slug);
					} catch (err) {
						alert(err.message || 'خطا در ثبت مقدار جدید.');
						addBtn.disabled = false;
					}
				};

				addBtn.addEventListener('click', submitInlineTerm);
				inputEl.addEventListener('keypress', function(e) {
					if (e.key === 'Enter') {
						e.preventDefault();
						submitInlineTerm();
					}
				});

			} catch (err) {
				container.innerHTML = `<span class="wsm-text-xs wsm-text-rose-400">خطا در دریافت مقادیر</span>`;
			}
		}

		// Bind Form submit to create Attribute
		if (addForm) {
			addForm.addEventListener('submit', async function(e) {
				e.preventDefault();
				const nameInput = document.getElementById('attr-name');
				const slugInput = document.getElementById('attr-slug');

				const name = nameInput.value.trim();
				const slug = slugInput.value.trim();

				try {
					await WSM.fetch('/attributes', {
						method: 'POST',
						body: JSON.stringify({ name, slug })
					});

					nameInput.value = '';
					slugInput.value = '';
					loadAttributes();
				} catch (err) {
					alert(err.message || 'خطا در ثبت ویژگی.');
				}
			});
		}

		loadAttributes();
	}

	/**
	 * 2. BRANDS MANAGEMENT PAGE
	 */
	async function initBrandsPage() {
		const tableBody = document.getElementById('brands-table-body');
		const addForm = document.getElementById('wsm-add-brand-form');

		// Load Brands list
		async function loadBrands() {
			try {
				tableBody.innerHTML = `
					<tr>
						<td colspan="4" class="wsm-px-6 wsm-py-12 wsm-text-center wsm-text-slate-500">
							${window.wsmConfig.translations?.loading || 'در حال بارگذاری...'}
						</td>
					</tr>
				`;

				const response = await WSM.fetch('/brands', { method: 'GET' });
				const brands = response.data || [];

				if (brands.length === 0) {
					tableBody.innerHTML = `
						<tr>
							<td colspan="4" class="wsm-px-6 wsm-py-12 wsm-text-center wsm-text-slate-500">
								${window.wsmConfig.translations?.noBrands || 'هیچ برندی ثبت نشده است.'}
							</td>
						</tr>
					`;
					return;
				}

				let html = '';
				brands.forEach(brand => {
					html += `
						<tr class="wsm-border-b wsm-border-slate-800/40 hover:wsm-bg-slate-900/20">
							<td class="wsm-px-6 wsm-py-4 wsm-text-sm wsm-text-slate-200 wsm-font-semibold">${WSM.escHtml(brand.name)}</td>
							<td class="wsm-px-6 wsm-py-4 wsm-text-sm wsm-text-slate-400 font-mono">${WSM.escHtml(brand.slug)}</td>
							<td class="wsm-px-6 wsm-py-4 wsm-text-sm wsm-text-slate-400">${WSM.escHtml(brand.description || '')}</td>
							<td class="wsm-px-6 wsm-py-4 wsm-text-center">
								<button class="wsm-delete-brand-btn wsm-text-xs wsm-text-rose-400 hover:wsm-text-rose-300 wsm-font-semibold" data-id="${brand.id}">
									${window.wsmConfig.translations?.delete || 'حذف'}
								</button>
							</td>
						</tr>
					`;
				});
				tableBody.innerHTML = html;

				// Bind delete brands
				tableBody.querySelectorAll('.wsm-delete-brand-btn').forEach(btn => {
					btn.addEventListener('click', async () => {
						const id = btn.getAttribute('data-id');
						if (confirm(window.wsmConfig.translations?.confirmDeleteBrand || 'آیا از حذف این برند اطمینان دارید؟')) {
							try {
								await WSM.fetch(`/brands/${id}`, { method: 'DELETE' });
								loadBrands();
							} catch (err) {
								alert(err.message || 'خطا در حذف برند.');
							}
						}
					});
				});

			} catch (err) {
				tableBody.innerHTML = `
					<tr>
						<td colspan="4" class="wsm-px-6 wsm-py-12 wsm-text-center wsm-text-rose-400">
							خطا در بارگذاری اطلاعات.
						</td>
					</tr>
				`;
			}
		}

		// Bind Form submit to create Brand
		if (addForm) {
			addForm.addEventListener('submit', async function(e) {
				e.preventDefault();
				const nameInput = document.getElementById('brand-name');
				const slugInput = document.getElementById('brand-slug');
				const descInput = document.getElementById('brand-desc');

				const name = nameInput.value.trim();
				const slug = slugInput.value.trim();
				const description = descInput.value.trim();

				try {
					await WSM.fetch('/brands', {
						method: 'POST',
						body: JSON.stringify({ name, slug, description })
					});

					nameInput.value = '';
					slugInput.value = '';
					descInput.value = '';
					loadBrands();
				} catch (err) {
					alert(err.message || 'خطا در ثبت برند.');
				}
			});
		}

		loadBrands();
	}

})();

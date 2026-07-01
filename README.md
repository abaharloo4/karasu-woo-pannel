# KarasuWooPannel

<p align="center">
  <img src="assets/css/wsm-panel.css" alt="KarasuWooPannel Banner" style="display:none;" />
</p>

A completely independent, responsive, RTL, and TailwindCSS-based store administration panel for WooCommerce.

**KarasuWooPannel** decouples the daily store management workflow from the traditional WordPress `/wp-admin` dashboard, providing shop managers with a secure, modern, and high-performance standalone environment.

---

## 🚀 Key Features

### 📦 Complete Product Type Support
Full creation, editing, and listing capability for all standard WooCommerce product types and options:
- **Simple & Variable Products**: Manage prices, SKU, attributes, and variation permutations.
- **Grouped Products**: Link and manage group child products (with nested grouping prevention).
- **External / Affiliate Products**: Add target purchase URLs and custom button labels.
- **Virtual & Downloadable Products**: Upload download files securely, enforce download limit/expiry rules, and prevent Local File Inclusion (LFI) via secure path validations.

### 📋 Order Management & Cancellation
- **Interactive Details**: View order totals, customer billing/shipping details, dynamic purchase lists, and inline card-to-card payment receipt previews.
- **State-Machine Transition Rules**: Enforce safe status transitions (e.g. preventing direct transition of completed/refunded orders to cancelled).
- **One-Click Cancellation**: Cancel orders with a single click (including a confirmation dialog) which automatically logs the panel operator's name and triggers WooCommerce stock restoration.

### 🛡️ Telemetry & Security Hardening
- **Zero External Connections option**: Turn on the "Disable Automatic Updates" option in WP-Admin settings to completely halt outbound HTTP requests to `api.github.com`.
- **Self-Hosted Assets**: Vazirmatn font is enqueued locally from plugin assets, eliminating third-party CDNs and Google Fonts requests.
- **Role-based Access (UAC)**: Manage granular panel page accessibility for different user roles using the settings panel.

### 💬 SMS Notification Gateway
- Integrated **MeliPayamak** SMS API for transactional updates.
- Supports both Pattern (Template) sending and standard line sending.
- Send automated notifications to customers and shop administrators upon new orders, status modifications, or low stock warnings.

---

## 🛠️ Installation

1. Upload the `karasu-woo-pannel` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Configure settings via **WooCommerce -> KarasuWooPannel** in your WP Admin dashboard.
4. Access your decoupled store dashboard using the configured custom URL slug (e.g., `https://yourdomain.com/store-admin`).

---

## 🧪 Requirements

- **WordPress**: 6.0+
- **WooCommerce**: 7.0+ (Fully compatible with HPOS / High-Performance Order Storage)
- **PHP**: 8.0+

---

## 📝 License

Distributed under the GPLv2 or later License. See `readme.txt` for more details.

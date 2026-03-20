# FAQ Manager for Zen Cart

A modern, fully encapsulated FAQ (Frequently Asked Questions) Manager for Zen Cart 2.0+. This plugin allows store owners to create, manage, and group FAQs into categories directly from the Admin dashboard using a responsive Bootstrap interface.

## Features

* **Encapsulated Plugin:** Follows the Zen Cart 2.0+ `zc_plugins` architecture for clean installation and maintenance.
* **Modern Admin UI:** Uses Bootstrap 3 for a clean, responsive interface consistent with modern Zen Cart admin panels.
* **Categorization:** Group questions into specific topics (e.g., Shipping, Returns, Products).
* **Multi-Language:** Full support for multi-language stores.
* **Sort Control:** Fully sortable Categories and Questions via sort order fields.
* **Instant Toggle:** Enable or disable questions instantly without deleting them.
* **Frontend Accordion:** Uses lightweight, SEO-friendly HTML5 `<details>` and `<summary>` tags for a fast, JavaScript-free accordion effect on the storefront.

## Requirements

* Zen Cart v2.0.0 or later (Tested on v2.1.0)
* PHP 7.4+ (PHP 8.x supported)

## Installation

### 1. File Upload
Upload the contents of the `zc_plugins` directory to your store's `zc_plugins` directory.

### 2. Run Installer
1. Log in to your Zen Cart Admin.
2. Navigate to **Modules > Plugin Manager**.
3. Locate **ZX FAQ Manager** in the list.
4. Click **Install**.
   * *This will automatically create the necessary database tables (`faq_items`, `faq_categories`, etc.) and register the admin pages.*

## Usage

### Managing FAQs
1.  Navigate to **Tools > FAQ Manager**.
2.  **To Add a Category:** Click the **Manage Categories** button (top right). Add categories like "Shipping" or "Returns".
3.  **To Add a Question:** Click **New FAQ**. Select a category, enter your Question (Title) and Answer (Content), and ensure the Status is set to **Enabled**.

### Frontend Display
The FAQ page is automatically available at:
`index.php?main_page=faq`

You can add this link to your footer, information sidebox, or EZ-Pages menu **manually**.

## Uninstallation

To remove the plugin completely:
1.  Navigate to **Modules > Plugin Manager** in your Zen Cart Admin.
2.  Find **FAQ Manager** and click **Remove**.
    * *This will NOT drop the new database tables but will remove the admin menu entries. To remove the database tables, please use phpMyAdmin or similar tools to drop the 4 faq_ tables.*
3.  (Optional) Remove the `ZxFAQ` directory from your `zc_plugins` directory on your server.

## License
Portions Copyright 2003-2026 Zen Cart Development Team. Released under the **GNU Public License V2.0**.

<?php
/**
 * FAQ Observer - loads FAQ stylesheet into the HTML <head>
 *
 * Listens for NOTIFY_HTML_HEAD_END and outputs a <link> tag for faq.css.
 * This is the proven approach used by established Zen Cart plugins (e.g. POSM)
 * to inject CSS from zc_plugins into the storefront.
 *
 * @package ZxFAQ
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: ZenExpert - https://zenexpert.com - Mar 26, 2026 $
 */

class zcObserverFaqObserver extends base
{
    private string $cssWebPath = '';

    public function __construct()
    {
        // Build the web-relative path to the CSS file.
        // __DIR__ = .../zc_plugins/ZxFAQ/1.0.0/catalog/includes/classes/observers
        // We need: zc_plugins/ZxFAQ/1.0.0/catalog/includes/templates/default/css/faq.css
        //
        // Strategy: find 'zc_plugins/' in the path and use everything from there,
        // which avoids any symlink/realpath mismatches with DIR_FS_CATALOG.
        $dir = str_replace('\\', '/', __DIR__);
        $marker = 'zc_plugins/';
        $pos = strpos($dir, $marker);
        if ($pos !== false) {
            $pluginRelative = substr($dir, $pos);
            // Go from classes/observers up to the plugin's catalog root
            // zc_plugins/ZxFAQ/1.0.0/catalog/includes/classes/observers -> zc_plugins/ZxFAQ/1.0.0/catalog/
            $pluginRoot = dirname($pluginRelative, 3) . '/';
            $this->cssWebPath = $pluginRoot . 'includes/templates/default/css/faq.css';
        }

        $this->attach($this, ['NOTIFY_HTML_HEAD_END']);
    }

    /**
     * Issued at the end of the active template's html_header.php (just before </head>)
     * Outputs the FAQ stylesheet link tag.
     */
    protected function notify_html_head_end(&$class, string $e): void
    {
        global $current_page_base;

        // Only load the stylesheet on the FAQ page
        if ($current_page_base !== 'faq') {
            return;
        }

        if (!empty($this->cssWebPath) && file_exists(DIR_FS_CATALOG . $this->cssWebPath)) {
            echo '<link rel="stylesheet" href="' . $this->cssWebPath . '">' . "\n";
        }
    }
}

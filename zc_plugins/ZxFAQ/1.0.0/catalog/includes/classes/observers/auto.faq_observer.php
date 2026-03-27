<?php
/**
 * FAQ Observer - loads FAQ stylesheet into the HTML <head>
 *
 * Listens for NOTIFY_HTML_HEAD_END and outputs a <link> tag for faq.css.
 *
 * @package ZxFAQ
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: ZenExpert - https://zenexpert.com - Mar 26, 2026 $
 */

use Zencart\Traits\InteractsWithPlugins;
use Zencart\Traits\NotifierManager;
use Zencart\Traits\ObserverManager;

class zcObserverFaqObserver extends base
{
    use InteractsWithPlugins;
    use NotifierManager;
    use ObserverManager;
    
    public function __construct()
    {
        $this->attach($this, ['NOTIFY_HTML_HEAD_END']);

        $this->detectZcPluginDetails(__DIR__);
    }

    /**
     * Catalog: Runs at the end of the active template's html_header.php (just before the </head> tag)
     * Enables the plugin's CSS file to be inserted.
     */
    public function notify_html_head_end(&$class, $eventID, string $current_page_base): void
    {
        $this->linkCatalogStylesheet('faq.css', $current_page_base);
    }
}
<?php
/**
 * Auto-loader configuration for ZxFAQ plugin
 *
 * Loads the FAQ observer class which handles injecting the FAQ stylesheet
 * into the storefront HTML <head>.
 *
 * @package ZxFAQ
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: ZenExpert - https://zenexpert.com - Mar 26, 2026 $
 */

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

$autoLoadConfig[200][] = [
    'autoType' => 'class',
    'loadFile' => 'observers/auto.faq_observer.php',
];
$autoLoadConfig[200][] = [
    'autoType' => 'classInstantiate',
    'className' => 'zcObserverFaqObserver',
    'objectName' => 'faqObserver',
];

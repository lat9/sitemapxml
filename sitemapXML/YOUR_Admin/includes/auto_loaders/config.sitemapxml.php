<?php
/**
 * @package functions
 * @copyright Copyright 2016 iSO Network - https://isonetwork.net.au
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

$autoLoadConfig[999][] = [
    'autoType' => 'init_script',
    'loadFile' => 'init_sitemapxml.php'
];

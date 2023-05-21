<?php
/**
 * SitemapXML Installation
 * @package     SitemapXML
 * @copyright Copyright 2005-2017 Andrew Berezin eCommerce-Service.com
 * @copyright   Copyright 2017 iSO Network [www.isonetwork.net.au]
 * @copyright   Copyright 2019 mc12345678 [mc12345678.com]
 * @copyright   Portions Copyright 2003-2019 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: sitemapxml.php, v 3.9.9 lat9 21/05/2023
 */

if ($current_version !== $installer) {
    $ext_modules = new ext_modules;
    $ext_modules->install_configuration_group('SITEMAPXML_', 'BOX_CONFIGURATION_SITEMAPXML', 'SitemapXML', 'sitemapxmlConfig');
    $ext_modules->install_configuration($install_configuration);

    // -----
    // Update any existing 'ping URL' setting to use https:// protocol instead of http://
    //
    $db->Execute(
        "UPDATE " . TABLE_CONFIGURATION . "
            SET configuration_value = REPLACE(configuration_value, 'http://', 'https://')
         WHERE configuration_key = 'SITEMAPXML_PING_URLS'
         LIMIT 1"
    );

    // -----
    // Update the plugin's version configuration value's description, removing the no-longer
    // valid link.
    //
    $db->Execute(
        "UPDATE " . TABLE_CONFIGURATION . "
            SET configuration_description = 'SitemapXML Version'
         WHERE configuration_key = 'SITEMAPXML_VERSION'
         LIMIT 1"
    );
}

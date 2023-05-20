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
 * @version $Id: sitemapxml.php, v 3.9.7 highburyeye 02/05/2023
 */

//var_dump(SITEMAPXML_VERSION, $current_version, $installer, (SITEMAPXML_VERSION != $current_version));
if ($current_version != $installer) {
  $ext_modules = new ext_modules;
  $ext_modules->install_configuration_group('SITEMAPXML_', 'BOX_CONFIGURATION_SITEMAPXML', 'SitemapXML', 'sitemapxmlConfig');
  $ext_modules->install_configuration($install_configuration);
}

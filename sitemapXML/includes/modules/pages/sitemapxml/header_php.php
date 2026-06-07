<?php
/**
 * Sitemap XML
 *
 * @param $errno
 * @param $errstr
 * @param $errfile
 * @param $errline
 * @return
 * @package Sitemap XML
 * @copyright Copyright 2005-2015 Andrew Berezin eCommerce-Service.com
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @link http://www.sitemaps.org/
 * @version $Id: sitemapxml.php, v 3.3.1 31.01.2015 16:27:07 AndrewBerezin $
 *
 * Last updated: v4.1.0
 */
set_time_limit(0);

// This should be first line of the script:
$zco_notifier->notify('NOTIFY_HEADER_START_SITEMAPXML');

/**
 * load the site map class
 */
require DIR_WS_CLASSES . 'sitemapxml.php';

/**
 * load language files
 */
require DIR_WS_MODULES . zen_get_module_directory('require_languages.php');
$breadcrumb->add(NAVBAR_TITLE);

$inline = (($_GET['inline'] ?? '') === 'yes');
$genxml  = (($_GET['genxml'] ?? 'yes') !== 'no');
$checkurl = (($_GET['checkurl'] ?? '') === 'yes');
$rebuild = (($_GET['rebuild'] ?? '') === 'yes');

if (zen_config('SITEMAPXML_EXECUTION_TOKEN') !== '' && (($_GET['token'] ?? null) !== zen_config('SITEMAPXML_EXECUTION_TOKEN'))) {
    header('HTTP/1.1 401 Unauthorized');
    echo 'Incorrect Start Security Token';
    exit(0);
}

$sitemapXML = new zen_SiteMapXML($inline, $rebuild, $genxml);

$sitemapXML->setCheckURL($checkurl);

$tpl_dir = $template->get_template_dir('gss.xsl', DIR_WS_TEMPLATE, $current_page_base, 'css');
if (is_file($tpl_dir . '/gss.xsl')) {
    $sitemapXML->setStylesheet($tpl_dir . '/gss.xsl');
}

$SiteMapXMLmodules = glob(DIR_WS_MODULES . 'pages/' . $current_page_base . '/sitemapxml_*.php');

$pluginsFilesActive = explode(';', zen_config('SITEMAPXML_PLUGINS'));
$temp = [];
foreach ($SiteMapXMLmodules as $pluginFile) {
    if (in_array(basename($pluginFile), $pluginsFilesActive)) {
        $temp[] = $pluginFile;
    }
}
$SiteMapXMLmodules = $temp;

// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_END_SITEMAPXML');

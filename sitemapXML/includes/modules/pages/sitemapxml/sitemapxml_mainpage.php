<?php
/**
 * Sitemap XML
 *
 * @package Sitemap XML
 * @copyright Copyright 2005-2012 Andrew Berezin eCommerce-Service.com
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: sitemapxml_mainpage.php, v 3.2.2 07.05.2012 19:12 AndrewBerezin $
 */
echo '<h3>' . TEXT_HEAD_MAINPAGE . '</h3>';
if ($sitemapXML->SitemapOpen('mainpage')) {
    $languages = $db->Execute(
        "SELECT *
           FROM " . TABLE_LANGUAGES . " l
          WHERE l.languages_id IN (" . $sitemapXML->getLanguagesIDs() . ") " .
          (SITEMAPXML_HOMEPAGE_ORDERBY !== '' ? "ORDER BY " . SITEMAPXML_HOMEPAGE_ORDERBY : ''));
    $sitemapXML->SitemapSetMaxItems($languages->RecordCount());
    foreach ($languages as $next_language) {
        $sitemapXML->writeItem(FILENAME_DEFAULT, '', $next_language['languages_id'], '', SITEMAPXML_HOMEPAGE_CHANGEFREQ);
    }
    $sitemapXML->SitemapClose();
    unset($languages);
}

<?php
/**
 * Sitemap XML Feed
 *
 * @package Sitemap XML Feed
 * @copyright Copyright 2005-2015 Andrew Berezin eCommerce-Service.com
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @link http://www.sitemaps.org/
 * @version $Id: sitemapxml.php, v 3.8 07.07.2016 12:39:33 AndrewBerezin $
 */
$define = [
    'NAVBAR_TITLE' => 'SiteMapXML',
    'HEADING_TITLE' => 'SiteMapXML (' . SITEMAPXML_VERSION . ')',

    'TEXT_EXECUTION_TIME' => 'Total: Execution Time %s, DB Queries %s, DB Queries Time %s.',
    'TEXT_TOTAL_SITEMAP' => 'Total: files %s, items %s (%s bytes), execution time %s, db queries %s, db queries time %s.',
    'TEXT_FILE_SITEMAP_INFO' => 'File <a href="%s" target="_blank">%s</a>. Written %s items (%s bytes), Filesize: %s bytes',
    'TEXT_WRITTEN' => 'Written %s items (%s bytes), Filesize: %s bytes',

    'TEXT_URL_FILE' => 'URL - ',
    'TEXT_INCLUDE_FILE' => 'Include ',
    'TEXT_FILE_NOT_CHANGED' => 'have not changed - using existing file(s)',
    'TEXT_FAILED_TO_OPEN' => 'Failed to open file "%s"!!!',
    'TEXT_FAILED_TO_CREATE' => 'Unable to create file "%s". You may need to use your webhost control panel/file-manager to change the permissions effectively.',
    'TEXT_FAILED_TO_CHMOD' => 'File "%s" is Read-Only. You may need to use your webhost control panel/file-manager to change the permissions effectively.',

    'TEXT_HEAD_SITEMAP_INDEX' => 'Sitemap Index',
    'TEXT_HEAD_SITEMAP_INDEX_NONE' => 'Sitemap Index not generated: no sitemaps found (no plugins selected)',

    'TEXT_ERROR_CURL_NOT_FOUND' => 'CURL functions not found - required for ping/checkURL functions',
    'TEXT_ERROR_CURL_INIT' => 'cURL Error: init cURL',
    'TEXT_ERROR_CURL_EXEC' => 'cURL Error: "<b>%s</b>" reading "%s"',
    'TEXT_ERROR_CURL_NO_HTTPCODE' => 'cURL Error: No http_code reading "%s"',
    'TEXT_ERROR_CURL_ERR_HTTPCODE' => 'cURL Error: Error http_code "<b>%s</b>" reading "%s"',
    'TEXT_ERROR_CURL_0_DOWNLOAD' => 'cURL Error: Zero download size reading "%s"',
    'TEXT_ERROR_CURL_ERR_DOWNLOAD' => 'cURL Error: Reading less than page size "%s". Download = %s, Content length = %s.',

    'TEXT_HEAD_PRODUCTS' => 'Products Sitemap',
    'TEXT_HEAD_CATEGORIES' => 'Categories Sitemap',
    'TEXT_HEAD_CATS2MAN' => 'Categories to Manufacturers Sitemap',
    'TEXT_HEAD_MANUFACTURERS' => 'Manufacturers Sitemap',
    'TEXT_HEAD_MAINPAGE' => 'Mainpage Sitemap',
    'TEXT_HEAD_EZPAGES' => 'Ezpages Sitemap',
    'TEXT_HEAD_REVIEWS' => 'Reviews Sitemap',
    'TEXT_HEAD_PRODUCTS_REVIEWS' => 'Products Reviews Sitemap',
    'TEXT_HEAD_TESTIMONIALS' => 'Testimonials Manager Sitemap',

    'TEXT_HEAD_NEWS' => 'News Sitemap',
    'TEXT_HEAD_NEWS_ARTICLES' => 'News Articles Sitemap',

    'TEXT_HEAD_PRODUCTS_VIDEO' => 'Products Video Sitemap',

    'TEXT_ERRROR_EZPAGES_OUTOFBASE' => 'EZ-Page ignored (out of base url): "<b>%s</b>" (%s)',
    'TEXT_ERRROR_EZPAGES_ROBOTS' => 'EZ-Page ignored (found in ROBOTS_PAGES_TO_SKIP): "<b>%s</b>" (%s)',

    'TEXT_HEAD_BOXNEWS' => 'News Box Manager Sitemap',
];
return $define;

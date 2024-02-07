<?php
/**
 * Sitemap XML Feed
 *
 * @package Sitemap XML Feed
 * @copyright Copyright 2005-2016 Andrew Berezin eCommerce-Service.com
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @link http://www.sitemaps.org/
 * @version $Id: sitemapxml.php, v 3.8 07.07.2016 12:39:33 AndrewBerezin $
 */
global $current_page;   //- Needed for zc158 since language files are now loaded by a class

if (defined('SITEMAPXML_SITEMAPINDEX')) {
    define('SITEMAPXML_SITEMAPINDEX_HTTP_LINK', HTTP_CATALOG_SERVER . DIR_WS_CATALOG . SITEMAPXML_SITEMAPINDEX . '.xml');
    define('TEXT_SITEMAPXML_TIPS_TEXT',
        '<p>You can read all about sitemaps at <strong><a href="https://sitemaps.org/" target="_blank" rel="noopener noreferrer" class="splitPageLink">[Sitemaps.org]</a></strong>.</p>
        <p>Once the sitemaps are generated, you need to get them noticed:</p>
        <ol>
            <li>Register or login to your account: <strong><a href="https://www.google.com/webmasters/tools/home" target="_blank" rel="noopener noreferrer" class="splitPageLink">[Google]</a></strong>, <strong><a href="https://ssl.bing.com/webmaster" target="_blank" rel="noopener noreferrer" class="splitPageLink">[Bing]</a></strong>.</li>
            <li>Submit your Sitemap <code>' . SITEMAPXML_SITEMAPINDEX_HTTP_LINK . '</code> via the search engine\'s submission interface <strong><a href="https://www.google.com/webmasters/tools/home" target="_blank" rel="noopener noreferrer" class="splitPageLink">[Google]</a></strong>.</li>
            <li>Specify the Sitemap location in your <a href="' . HTTP_CATALOG_SERVER . DIR_WS_CATALOG . 'robots.txt' . '" target="_blank" class="splitPageLink">robots.txt</a> file (<a href="https://sitemaps.org/protocol.php#submit_robots" target="_blank" rel="noopener noreferrer" class="splitPageLink">more...</a>): <code>Sitemap: ' . SITEMAPXML_SITEMAPINDEX_HTTP_LINK . '</code></li>
            <li>Notify crawlers of the update to your XML sitemap.<br><span><b>Note:</b> <i>CURL is used for communication with the crawlers, so must be active on your hosting server. If you need to use a CURL proxy, set the CURL proxy settings under <b>Configuration :: My Store</b>.</i></span></li>
        </ol>
        <p>To <em>automatically</em> update sitemaps and notify crawlers, you will need to set up a Cron job via your host\'s control panel.</p>
        <p>To run the generation as a cron job (at 5am for example), you will need to create something similar to the following examples.</p>
        <samp>0 5 * * * GET \'https://your_domain/index.php?main_page=sitemapxml\&amp;rebuild=yes\'</samp><br>
        <samp>0 5 * * * wget -q \'https://your_domain/index.php?main_page=sitemapxml\&amp;rebuild=yes\' -O /dev/null</samp><br>
        <samp>0 5 * * * curl -s \'https://your_domain/index.php?main_page=sitemapxml\&amp;rebuild=yes\'</samp><br>
        <samp>0 5 * * * php -f &lt;path to shop&gt;/cgi-bin/sitemapxml.php rebuild=yes</samp><br>');
}
define('HEADING_TITLE', 'Sitemap XML');
define('TEXT_SITEMAPXML_TIPS_HEAD', 'Tips');
if (!defined('TEXT_SITEMAPXML_TIPS_TEXT')) {
    define('TEXT_SITEMAPXML_TIPS_TEXT', '<p>To learn more about how to manage the sitemaps of this software, please <a href="' . zen_href_link($current_page, zen_get_all_get_params()) . '">reload</a> this page.</p>');
}
define('TEXT_SITEMAPXML_INSTRUCTIONS_HEAD', 'Create / update your site map(s)');
define('TEXT_SITEMAPXML_CHOOSE_PARAMETERS', 'Select Actions');
define('TEXT_SITEMAPXML_CHOOSE_PARAMETERS_REBUILD', 'Rebuild all sitemap*.xml files!');

define('TEXT_SITEMAPXML_PLUGINS_LIST', 'Sitemap Plugins');
define('TEXT_SITEMAPXML_PLUGINS_LIST_SELECT', 'Select Sitemaps to Generate');

define('TEXT_SITEMAPXML_FILE_LIST', 'Sitemaps File List');
define('TEXT_SITEMAPXML_FILE_LIST_TABLE_FNAME', 'Name');
define('TEXT_SITEMAPXML_FILE_LIST_TABLE_FSIZE', 'Size');
define('TEXT_SITEMAPXML_FILE_LIST_TABLE_FTIME', 'Last modified');
define('TEXT_SITEMAPXML_FILE_LIST_TABLE_FPERMS', 'Permissions');
define('TEXT_SITEMAPXML_FILE_LIST_TABLE_TYPE', 'Type');
define('TEXT_SITEMAPXML_FILE_LIST_TABLE_ITEMS', 'Items');
define('TEXT_SITEMAPXML_FILE_LIST_TABLE_COMMENTS', 'Comments');
define('TEXT_SITEMAPXML_FILE_LIST_TABLE_ACTION', 'Action');

define('TEXT_SITEMAPXML_IMAGE_POPUP_ALT', 'open sitemap in new window');
define('TEXT_SITEMAPXML_RELOAD_WINDOW', 'Refresh File List');

define('TEXT_SITEMAPXML_FILE_LIST_COMMENTS_READONLY', 'Read Only!!!');
define('TEXT_SITEMAPXML_FILE_LIST_COMMENTS_IGNORED', 'Ignored');

define('TEXT_SITEMAPXML_FILE_LIST_TYPE_URLSET', 'UrlSet');
define('TEXT_SITEMAPXML_FILE_LIST_TYPE_SITEMAPINDEX', 'SitemapIndex');
define('TEXT_SITEMAPXML_FILE_LIST_TYPE_UNDEFINED', 'Undefined!!!');

define('TEXT_ACTION_VIEW_FILE', 'View');
define('TEXT_ACTION_TRUNCATE_FILE', 'Truncate');
define('TEXT_ACTION_TRUNCATE_FILE_CONFIRM', 'You really want to truncate the file %s?');
define('TEXT_ACTION_DELETE_FILE', 'Delete');
define('TEXT_ACTION_DELETE_FILE_CONFIRM', 'You really want to delete the file %s?');

define('TEXT_MESSAGE_FILE_ERROR_OPENED', 'Error opening file %s');
define('TEXT_MESSAGE_FILE_TRUNCATED', 'File %s truncated');
define('TEXT_MESSAGE_FILE_DELETED', 'File %s deleted');
define('TEXT_MESSAGE_FILE_ERROR_DELETED', 'Error deleting file %s');

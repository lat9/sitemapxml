<?php
/**
 * Sitemap XML Feed
 *
 * @package Sitemap XML Feed
 * @copyright Copyright 2005-2017 Andrew Berezin eCommerce-Service.com
 * @copyright Copyright 2003-2017 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @link http://www.sitemaps.org/
 * @version $Id: sitemapxml_install.php, v 3.9.3 19.02.2017 18:11:03 AndrewBerezin $
 */
if (defined('SITEMAPXML_CHECK_DUBLICATES')) {
    $sql = "UPDATE " . TABLE_CONFIGURATION . " SET configuration_key = 'SITEMAPXML_CHECK_DUPLICATES' WHERE configuration_key = 'SITEMAPXML_CHECK_DUBLICATES' LIMIT 1";
    $db->Execute($sql);
}

$install_configuration = [
    'SITEMAPXML_VERSION' => [
        'Module version',
        $current_version,
        'SitemapXML Version',
        -10,
        null,
        'zen_cfg_read_only('
    ],

    'SITEMAPXML_SITEMAPINDEX' => [
        'SitemapXML Index file name',
        'sitemap', 
        'SitemapXML Index file name - this file should be given to the search engines',
        1,
        null,
        null
    ],

    'SITEMAPXML_DIR_WS' => [
        'Sitemap directory',
        'sitemap',
        'Directory for sitemap files. If empty all sitemap xml files saved on shop root directory.',
        1,
        null,
        null
    ],

    'SITEMAPXML_COMPRESS' => [
        'Compress SitemapXML Files?',
        'false',
        'Compress SitemapXML files',
        2,
        null,
        'zen_cfg_select_option([\'true\', \'false\'],'
    ],

    'SITEMAPXML_LASTMOD_FORMAT' => [
        'Lastmod tag format',
        'date',
        'Lastmod tag format:<ul><li>date - Complete date: YYYY-MM-DD (eg 1997-07-16)</li><li>full - Complete date plus hours, minutes and seconds: YYYY-MM-DDThh:mm:ssTZD (eg 1997-07-16T19:20:30+01:00)</li></ul>',
        3,
        null,
        'zen_cfg_select_option([\'date\', \'full\'],'
    ],

    'SITEMAPXML_EXECUTION_TOKEN' =>[
        'Start Security Token',
        '',
        'Used to prevent a third-party not authorized start of the generator Sitemap XML. To avoid the creation of intentional excessive load on the server, DDoS-attacks.',
        3,
        null,
        null
    ],

    'SITEMAPXML_USE_EXISTING_FILES' => [
        'Use Existing Files',
        'true',
        'Use Existing XML Files',
        4,
        null,
        'zen_cfg_select_option([\'true\', \'false\'],'
    ],

    'SITEMAPXML_USE_ONLY_DEFAULT_LANGUAGE' => [
        'Generate links only for default language',
        'false',
        'Generate links for all languages (<code>false</code> or only for default language (<code>true</code>).',
        5,
        null,
        'zen_cfg_select_option([\'true\', \'false\'],'
    ],

    'SITEMAPXML_USE_LANGUAGE_PARM' => [
        'Using parameter language in links',
        'true',
        'Using parameter language in links:<ul><li>true - normally use it</li><li>all - using for all languages including pages for default language</li><li>false - don\'t use it</li></ul>',
        6,
        null,
        'zen_cfg_select_option([\'true\', \'all\', \'false\'],'
    ],

    'SITEMAPXML_CHECK_DUPLICATES' => [
        'Check Duplicates',
        'true',
        '<ul><li>true - check duplicates</li><li>mysql - check duplicates using mySQL (used to store a large number of products)</li><li>false - don\'t check duplicates</li></ul>',
        7,
        null,
        'zen_cfg_select_option([\'true\', \'mysql\', \'false\'],'
    ],

    'SITEMAPXML_PING_URLS' => [
        'Ping urls',
        'Google => https://www.google.com/webmasters/sitemaps/ping?sitemap=%s; Bing => https://www.bing.com/webmaster/ping.aspx?siteMap=%s',
        'List of pinging urls separated by <code>;</code>.',
        10,
        null,
        'zen_cfg_textarea('
    ],

    'SITEMAPXML_PLUGINS' => [
        'Active plugins',
        'sitemapxml_categories.php;sitemapxml_mainpage.php;sitemapxml_manufacturers.php;sitemapxml_products.php;sitemapxml_products_reviews.php',
        'What plug-ins from existing uses to generate the site map',
        15,
        null,
        'zen_cfg_read_only('
    ],

    'SITEMAPXML_HOMEPAGE_ORDERBY' => [
        'Home page order by',
        'sort_order ASC',
        '',
        20,
        null,
        null
    ],
    'SITEMAPXML_HOMEPAGE_CHANGEFREQ' => [
        'Home page changefreq',
        'weekly',
        'How frequently the Home page is likely to change.',
        21,
        null,
        'zen_cfg_select_option([\'no\', \'always\', \'hourly\', \'daily\', \'weekly\', \'monthly\', \'yearly\', \'never\'],'
    ],

    'SITEMAPXML_PRODUCTS_ORDERBY' => [
        'Products order by',
        'products_sort_order ASC, last_date DESC',
        '',
        30,
        null,
        null
    ],
    'SITEMAPXML_PRODUCTS_CHANGEFREQ' => [
        'Products changefreq',
        'weekly',
        'How frequently the Product pages page is likely to change.',
        31,
        null,
        'zen_cfg_select_option([\'no\', \'always\', \'hourly\', \'daily\', \'weekly\', \'monthly\', \'yearly\', \'never\'],'
    ],
    'SITEMAPXML_PRODUCTS_USE_CPATH' => [
        'Use cPath parameter',
        'false',
        'Use cPath parameter in products url. Coordinate this value with the value of variable $includeCPath in file init_canonical.php',
        32,
        null,
        'zen_cfg_select_option([\'true\', \'false\'],'
    ],
    'SITEMAPXML_PRODUCTS_IMAGES' => [
        'Add Products Images',
        'false',
         'Generate Products Image tags for products urls',
        35,
        null,
        'zen_cfg_select_option([\'true\', \'false\'],'
    ],
    'SITEMAPXML_PRODUCTS_IMAGES_CAPTION' => [
        'Use Products Images Caption/Title',
        'false',
        'Generate Product image tags Title and Caption',
        36,
        null,
        'zen_cfg_select_option([\'true\', \'false\'],'
    ],
    'SITEMAPXML_PRODUCTS_IMAGES_LICENSE' => [
        'Products Images license',
        '',
        'A URL to the license of the Products images',
        37,
        null,
        null
    ],

    'SITEMAPXML_CATEGORIES_ORDERBY' => [
        'Categories order by',
        'sort_order ASC, last_date DESC',
        '',
        40,
        null,
        null
    ],
    'SITEMAPXML_CATEGORIES_CHANGEFREQ' => [
        'Category changefreq',
        'weekly',
        'How frequently the Category pages page is likely to change.',
        41,
        null,
        'zen_cfg_select_option([\'no\', \'always\', \'hourly\', \'daily\', \'weekly\', \'monthly\', \'yearly\', \'never\'],'
    ],
    'SITEMAPXML_CATEGORIES_IMAGES' => [
        'Add Categories Images',
        'false',
        'Generate Categories Image tags for categories urls',
        42,
        null,
        'zen_cfg_select_option([\'true\', \'false\'],'
    ],
    'SITEMAPXML_CATEGORIES_IMAGES_CAPTION' => [
        'Use Categories Images Caption/Title',
        'false',
        'Generate Categories image tags Title and Caption',
        43,
        null,
        'zen_cfg_select_option([\'true\', \'false\'],'
    ],
    'SITEMAPXML_CATEGORIES_IMAGES_LICENSE' => [
        'Categories Images license',
        '',
        'A URL to the license of the Categories images',
        44,
        null,
        null
    ],
    'SITEMAPXML_CATEGORIES_PAGING' => [
        'Category paging',
        'false',
        'Add all category pages (with page=) to sitemap',
        45,
        null,
        'zen_cfg_select_option([\'true\', \'false\'],'
    ],

    'SITEMAPXML_REVIEWS_ORDERBY' => [
        'Reviews order by',
        'reviews_rating ASC, last_date DESC',
        '',
        50,
        null,
        null]
    ,
    'SITEMAPXML_REVIEWS_CHANGEFREQ' => [
        'Reviews changefreq',
        'weekly',
        'How frequently the Reviews pages page is likely to change.',
        51,
        null,
        'zen_cfg_select_option([\'no\', \'always\', \'hourly\', \'daily\', \'weekly\', \'monthly\', \'yearly\', \'never\'],'
    ],

    'SITEMAPXML_EZPAGES_ORDERBY' => [
        'EZPages order by',
        'sidebox_sort_order ASC, header_sort_order ASC, footer_sort_order ASC',
        '',
        60,
        null,
        null
    ],
    'SITEMAPXML_EZPAGES_CHANGEFREQ' => [
        'EZPages changefreq',
        'weekly',
        'How frequently the EZPages pages page is likely to change.',
        61,
        null,
        'zen_cfg_select_option([\'no\', \'always\', \'hourly\', \'daily\', \'weekly\', \'monthly\', \'yearly\', \'never\'],'
    ],

    'SITEMAPXML_TESTIMONIALS_ORDERBY' => [
        'Testimonials order by',
        'last_date DESC',
        '',
        70,
        null,
        null
    ],
    'SITEMAPXML_TESTIMONIALS_CHANGEFREQ' => [
        'Testimonials changefreq',
        'weekly',
        'How frequently the Testimonials page is likely to change.',
        71,
        null,
        'zen_cfg_select_option([\'no\', \'always\', \'hourly\', \'daily\', \'weekly\', \'monthly\', \'yearly\', \'never\'],'
    ],

    'SITEMAPXML_NEWS_ORDERBY' => [
        'News Articles order by',
        'last_date DESC',
        '',
        80,
        null,
        null
    ],
    'SITEMAPXML_NEWS_CHANGEFREQ' => [
        'News Articles changefreq',
        'weekly',
        'How frequently the News Articles is likely to change.',
        81,
        null,
        'zen_cfg_select_option([\'no\', \'always\', \'hourly\', \'daily\', \'weekly\', \'monthly\', \'yearly\', \'never\'],'
    ],

    'SITEMAPXML_MANUFACTURERS_ORDERBY' => [
        'Manufacturers order by',
        'last_date DESC',
        '',
        90,
        null,
        null
    ],
    'SITEMAPXML_MANUFACTURERS_CHANGEFREQ' => [
        'Manufacturers changefreq',
        'weekly',
        'How frequently the Manufacturers is likely to change.',
        91,
        null,
        'zen_cfg_select_option([\'no\', \'always\', \'hourly\', \'daily\', \'weekly\', \'monthly\', \'yearly\', \'never\'],'
    ],
    'SITEMAPXML_MANUFACTURERS_IMAGES' => [
        'Add Manufacturers Images',
        'false',
        'Generate Manufacturers Image tags for manufacturers urls',
        92,
        null,
        'zen_cfg_select_option([\'true\', \'false\'],'
    ],
    'SITEMAPXML_MANUFACTURERS_IMAGES_CAPTION' => [
        'Use Images Caption/Title',
        'false',
        'Generate Manufacturer image tags Title and Caption',
        93,
        null,
        'zen_cfg_select_option([\'true\', \'false\'],'
    ],
    'SITEMAPXML_MANUFACTURERS_IMAGES_LICENSE' => [
        'Manufacturers Images license',
        '',
        'A URL to the license of the Manufacturers images',
        94,
        null,
        null
    ],

    'SITEMAPXML_BOXNEWS_ORDERBY' => [
        'News Box Manager - order by',
        'last_date DESC',
        '',
        100,
        null,
        null
    ],
    'SITEMAPXML_BOXNEWS_CHANGEFREQ' => [
        'News Box Manager - changefreq',
        'weekly',
        'How frequently the News Box Manager is likely to change.',
        101,
        null,
        'zen_cfg_select_option([\'no\', \'always\', \'hourly\', \'daily\', \'weekly\', \'monthly\', \'yearly\', \'never\'],'
    ],

    'SITEMAPXML_PRODUCTS_REVIEWS_ORDERBY' => [
        'Products Reviews - order by',
        'last_date DESC',
        '',
        110,
        null,
        null
    ],
    'SITEMAPXML_PRODUCTS_REVIEWS_CHANGEFREQ' => [
        'Products Reviews - changefreq',
        'weekly',
        'How frequently the Products Reviews is likely to change.',
        111,
        null,
        'zen_cfg_select_option([\'no\', \'always\', \'hourly\', \'daily\', \'weekly\', \'monthly\', \'yearly\', \'never\'],'
    ],
];

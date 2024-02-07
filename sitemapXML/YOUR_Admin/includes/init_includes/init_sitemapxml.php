<?php
/**
 * @package functions
 * @copyright Copyright 2016 iSO Network - https://isonetwork.net.au
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: 2.6.1 17.04.2017 13:44:49 AndrewBerezin $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

define('SITEMAPXML_CURRENT_VERSION', '4.0.0-beta1');

// -----
// Wait until an admin is logged in before seeing if any initialization steps need to be performed.
// That ensures that "someone" will see the plugin's installation/update messages!
//
// Nothing to do if already installed at the current version!
//
if (!isset($_SESSION['admin_id']) || (defined('SITEMAPXML_VERSION') && SITEMAPXML_VERSION === SITEMAPXML_CURRENT_VERSION)) {
    return;
}

// -----
// Create the configuration group, if it doesn't already exist.  Note that
// some versions of the older plugin might have used either 'SitemapXML' or
// 'Sitemap XML' as the group title.  Either way, need to check to see
// whether multiple configuration-groups are associated with the Sitemap XML
// settings.
//
$config_check = $db->Execute(
    "SELECT DISTINCT configuration_group_id
       FROM " . TABLE_CONFIGURATION . "
      WHERE configuration_key LIKE 'SITEMAPXML%'"
);

// -----
// If this is an initial install, create the plugin's configuration group.
//
if ($config_check->EOF) {
    $config_group_title = 'Sitemap XML';
    $db->Execute(
        "INSERT INTO " . TABLE_CONFIGURATION_GROUP . "
            (configuration_group_title, configuration_group_description, sort_order, visible)
         VALUES 
            ('$config_group_title', '$config_group_title', 1, 1)"
    );
    $cgi = $db->Insert_ID();
    $db->Execute(
        "UPDATE " . TABLE_CONFIGURATION_GROUP . "
            SET sort_order = $cgi
          WHERE configuration_group_id = $cgi
          LIMIT 1"
    );
// -----
// If this is an update and only one configuration group is present, that's
// the one to use!
//
} elseif ($config_check->RecordCount() == 1) {
    $cgi = $config_check->fields['configuration_group_id'];
// -----
// Otherwise, this is an update and multiple configuration groups are associated
// with the plugin's settings; combine them into one.
//
} else {
    foreach ($config_check as $next_group) {
        $old_cgi = $next_group['configuration_group_id'];
        $cgi = $cgi ?? $old_cgi;
        if ($cgi !== $old_cgi) {
            $db->Execute(
                "UPDATE " . TABLE_CONFIGURATION . "
                    SET configuration_group_id = $cgi
                  WHERE configuration_group_id = $old_cgi"
            );
            $db->Execute(
                "DELETE FROM " . TABLE_CONFIGURATION_GROUP . " WHERE configuration_group_id = $old_cgi"
            );
            $db->Execute(
                "UPDATE " . TABLE_ADMIN_PAGES . "
                    SET page_params = 'gID=$cgi'
                  WHERE page_key = 'sitemapxmlConfig'
                  LIMIT 1"
            );
        }
    }
}

if (!defined('SITEMAPXML_VERSION')) {
    define('SITEMAPXML_VERSION', '0.0.0');
}

// ------
// Correct an older, misnamed configuration setting.
//
$db->Execute("UPDATE " . TABLE_CONFIGURATION . " SET configuration_key = 'SITEMAPXML_CHECK_DUPLICATES' WHERE configuration_key = 'SITEMAPXML_CHECK_DUBLICATES' LIMIT 1");

// -----
// Currently-supported configuration settings.
//
$install_configuration = [
    'SITEMAPXML_VERSION' => [
        'Module version',
        '0.0.0',
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

// -----
// Apply configuration additions and updates.
//
foreach ($install_configuration as $key => $sql_values) {
    list($configuration_title, $configuration_value, $configuration_description, $sort_order, $use_function, $set_function) = $sql_values;
    $configuration_title = $db->prepare_input($configuration_title);
    $configuration_description = $db->prepare_input($configuration_description);
    $use_function = ($use_function === null) ? 'NULL' : ("'" . $db->prepare_input($use_function) . "'");
    $set_function = ($set_function === null) ? 'NULL' : ("'" . $db->prepare_input($set_function) . "'");
    $db->Execute(
        "INSERT IGNORE INTO " . TABLE_CONFIGURATION . "
            (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) 
         VALUES
            ('$configuration_title', '$key', '$configuration_value', '$configuration_description', $cgi, $sort_order, now(), $use_function, $set_function)"
    );
    $db->Execute(
        "UPDATE " . TABLE_CONFIGURATION . "
            SET configuration_title = '$configuration_title',
                configuration_description = '$configuration_description',
                sort_order = $sort_order,
                use_function = $use_function,
                set_function = $set_function
          WHERE configuration_key = '$key'
          LIMIT 1"
    );
}

// -----
// Remove any no-longer-used configuration settings.
//
$db->Execute(
    "DELETE FROM " . TABLE_CONFIGURATION . "
      WHERE configuration_key LIKE 'SITEMAPXML%'
        AND configuration_key NOT IN ('" . implode("', '", array_keys($install_configuration)) . "')"
);

// -----
// Add configuration/tool to the admin menus
//
if (!zen_page_key_exists('sitemapxml')) {
    zen_register_admin_page('sitemapxml', 'BOX_SITEMAPXML', 'FILENAME_SITEMAPXML', '', 'tools', 'Y');
}
if (!zen_page_key_exists('sitemapxmlConfig')) {
    zen_register_admin_page('sitemapxmlConfig', 'BOX_CONFIGURATION_SITEMAPXML', 'FILENAME_CONFIGURATION', "gID=$cgi", 'configuration', 'Y');
}

// -----
// Update the currently-installed version in the database.
//
$db->Execute(
    "UPDATE " . TABLE_CONFIGURATION . "
        SET configuration_value = '" . SITEMAPXML_CURRENT_VERSION . "'
      WHERE configuration_key = 'SITEMAPXML_VERSION'
      LIMIT 1"
);
$messageStack->add('Sitemap XML v' . SITEMAPXML_CURRENT_VERSION . ' has been installed or updated.', 'success');

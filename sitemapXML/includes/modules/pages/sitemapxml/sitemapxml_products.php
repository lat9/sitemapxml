<?php
/**
 * Sitemap XML
 *
 * @package Sitemap XML
 * @copyright Copyright 2005-2015 Andrew Berezin eCommerce-Service.com
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: sitemapxml_products.php, v 3.2.7 16.03.2016 5:02:08 AndrewBerezin $
 */
if (!defined('SITEMAPXML_PRODUCTS_IMAGES_SIZE')) {
    define('SITEMAPXML_PRODUCTS_IMAGES_SIZE', 'large');
}
if (!defined('SITEMAPXML_PRODUCTS_IMAGES_ADDITIONAL')) {
    define('SITEMAPXML_PRODUCTS_IMAGES_ADDITIONAL', 'false'); // true false
}
if (!defined('SITEMAPXML_PRODUCTS_IMAGES_FUNCTION')) {
    define('SITEMAPXML_PRODUCTS_IMAGES_FUNCTION', 'false'); // true false
}

echo '<h3>' . TEXT_HEAD_PRODUCTS . '</h3>';

$sql = "SELECT * FROM " . TABLE_PRODUCT_TYPES;
$products_handler_array = [
    0 => 'product_info'
];
$zp_handler = $db->Execute($sql);
foreach ($zp_handler as $next_handler) {
    $products_handler_array[$next_handler['type_id']] = $next_handler['type_handler'] . '_info';
}
unset($zp_handler);

// BOF hideCategories
if ($sitemapXML->dbTableExist('TABLE_HIDE_CATEGORIES')) {
    $from = " INNER JOIN " . TABLE_HIDE_CATEGORIES . " h ON p.master_categories_id = h.categories_id";
    $where = ' AND (h.visibility_status < 2 OR h.visibility_status IS NULL)';
} else {
    $from = '';
    $where = '';
}
// EOF hideCategories

$catsArray = [];
$last_date = $db->Execute(
    "SELECT MAX(GREATEST(p.products_date_added, IFNULL(p.products_last_modified, '0001-01-01 00:00:00'))) AS last_date
       FROM " . TABLE_PRODUCTS . " p
      WHERE p.products_status = 1"
);
$table_status = $db->Execute("SHOW TABLE STATUS LIKE '" . TABLE_PRODUCTS . "'");
$last_date = max($table_status->fields['Update_time'], $last_date->fields['last_date']);
if ($sitemapXML->SitemapOpen('products', $last_date)) {
    global $queryCache;

    $query_cache_can_reset = (isset($queryCache) && is_object($queryCache) && method_exists($queryCache, 'reset'));
    
    $file_main_product_image = DIR_WS_MODULES . zen_get_module_directory(FILENAME_MAIN_PRODUCT_IMAGE);
    $file_additional_images = DIR_WS_MODULES . zen_get_module_directory('additional_images.php');

    $select = (SITEMAPXML_PRODUCTS_IMAGES === 'true') ? ', p.products_image, pd.products_name' : '';
    $products = $db->Execute(
        "SELECT p.products_id, p.master_categories_id, GREATEST(p.products_date_added, IFNULL(p.products_last_modified, '0001-01-01 00:00:00')) AS last_date,
                p.products_sort_order AS priority, pd.language_id, p.products_type" . $select . "
           FROM " . TABLE_PRODUCTS . " p
                INNER JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd
                    ON p.products_id = pd.products_id
                   AND pd.language_id IN (" . $sitemapXML->getLanguagesIDs() . ") " .
                $from . "
          WHERE p.products_status = 1" . $where .
          (SITEMAPXML_PRODUCTS_ORDERBY != '' ? ' ORDER BY ' . SITEMAPXML_PRODUCTS_ORDERBY : '')
    );
    $sitemapXML->SitemapSetMaxItems($products->RecordCount());
    foreach ($products as $next_product) {
        $xtra = '';
        if (!empty($next_product['products_image']) && is_file(DIR_FS_CATALOG . DIR_WS_IMAGES . $products->fields['products_image'])) {
            $products_image = $next_product['products_image'];
            $products_name = $next_product['products_name'];
            $_GET['products_id'] = $next_product['products_id'];
            require $file_main_product_image;
            if (SITEMAPXML_PRODUCTS_IMAGES_ADDITIONAL === 'true') {
                $flag_show_product_info_additional_images = 1;
                require $file_additional_images;
            }
            unset($_GET['products_id']);
            switch (SITEMAPXML_PRODUCTS_IMAGES_SIZE) {
                case 'small':
                    $img = DIR_WS_IMAGES . $products_image;
                    $width = SMALL_IMAGE_WIDTH;
                    $height = SMALL_IMAGE_HEIGHT;
                    break;
                case 'medium':
                    $img = $products_image_medium;
                    $width = MEDIUM_IMAGE_WIDTH;
                    $height = MEDIUM_IMAGE_HEIGHT;
                    break;
                case 'large':
                default:
                    $img = $products_image_large;
                    $width = '';
                    $height = '';
                    break;
            }
            if (SITEMAPXML_PRODUCTS_IMAGES_FUNCTION === 'true') {
                preg_match('@src="([^"]*)"@', zen_image($img, '', $width, $height), $image_src);
                $img = $image_src[1];
            }
            $images = [
                [
                    'file' => $img,
                     'title' => $next_product['products_name'],
                ],
            ];
            $xtra = $sitemapXML->imagesTags($images, SITEMAPXML_PRODUCTS_IMAGES_CAPTION, SITEMAPXML_PRODUCTS_IMAGES_LICENSE);
        }

        if (SITEMAPXML_PRODUCTS_USE_CPATH !== 'true') {
            $cPath_parm = '';
        } else {
            if (!isset($catsArray[$next_product['master_categories_id']])) {
                $catsArray[$next_product['master_categories_id']] = zen_get_generated_category_path_rev($next_product['master_categories_id']);
            }
            $cPath_parm = 'cPath=' . $catsArray[$next_product['master_categories_id']] . '&';
        }

        $info_page = $products_handler_array[$next_product['products_type']];

        if ($query_cache_can_reset === true) {
            $queryCache->reset('ALL');
        }

        $sitemapXML->writeItem($info_page, $cPath_parm . 'products_id=' . $next_product['products_id'], $next_product['language_id'], $next_product['last_date'], SITEMAPXML_PRODUCTS_CHANGEFREQ, $xtra);
    }

    $sitemapXML->SitemapClose();
    unset($products, $next_product);
}
unset($catsArray);

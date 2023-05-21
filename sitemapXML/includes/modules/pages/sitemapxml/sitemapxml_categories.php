<?php
/**
 * Sitemap XML
 *
 * @package Sitemap XML
 * @copyright Copyright 2005-2016 Andrew Berezin eCommerce-Service.com
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @link hideCategories http://www.zen-cart.com/downloads.php?do=file&id=254
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: sitemapxml_categories.php, v 3.7 07.07.2016 11:25:41 AndrewBerezin $
 */
// BOF hideCategories
// BOF products_in_subcategories
// BOF categories_paging

echo '<h3>' . TEXT_HEAD_CATEGORIES . '</h3>';

// BOF hideCategories
if ($sitemapXML->dbTableExist('TABLE_HIDE_CATEGORIES')) {
    $from = " INNER JOIN " . TABLE_HIDE_CATEGORIES . " h ON c.categories_id = h.categories_id";
    $where = " AND (h.visibility_status < 2 OR h.visibility_status IS NULL)";
} else {
    $from = '';
    $where = '';
}
// EOF hideCategories

$last_date = $db->Execute(
    "SELECT MAX(GREATEST(IFNULL(c.date_added, '0001-01-01 00:00:00'), IFNULL(c.last_modified, '0001-01-01 00:00:00'))) AS last_date
       FROM " . TABLE_CATEGORIES . " c
      WHERE c.categories_status = 1"
);
$table_status = $db->Execute("SHOW TABLE STATUS LIKE '" . TABLE_CATEGORIES . "'");
$last_date = max($table_status->fields['Update_time'], $last_date->fields['last_date']);

$select = '';
if ($sitemapXML->SitemapOpen('categories', $last_date)) {
    if (SITEMAPXML_CATEGORIES_IMAGES === 'true') {
        $select = ", c.categories_image, cd.categories_name";
    }
    $categories = $db->Execute(
        "SELECT c.categories_id, GREATEST(c.date_added, IFNULL(c.last_modified, '0001-01-01 00:00:00')) AS last_date, c.sort_order AS priority, cd.language_id" . $select . "
           FROM " . TABLE_CATEGORIES . " c
                INNER JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd
                    ON cd.categories_id = c.categories_id
                   AND cd.language_id IN (" . $sitemapXML->getLanguagesIDs() . ") " .
                $from . "
          WHERE c.categories_status = 1" .
                $where .
         (SITEMAPXML_CATEGORIES_ORDERBY !== '' ? ' ORDER BY ' . SITEMAPXML_CATEGORIES_ORDERBY : '')
    );
    $sitemapXML->SitemapSetMaxItems($categories->RecordCount());
    foreach ($categories as $next_category) {
        $xtra = '';
        $sql =
            "SELECT COUNT(*) AS total
               FROM " . TABLE_PRODUCTS . " p
                    INNER JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                        ON p.products_id = p2c.products_id
              WHERE p.products_status = 1
                AND p2c.categories_id = " . $next_category['categories_id'];

        // BOF products_in_subcategories
        if (defined('SHOW_NESTED_AS_PRODUCTS') && SHOW_NESTED_AS_PRODUCTS === 'True') {
            $subcategories_array = [$next_category['categories_id']];
            zen_get_subcategories($subcategories_array, (int)$next_category['categories_id']);
            $sql =
                "SELECT COUNT(*) AS total
                   FROM " . TABLE_PRODUCTS . " p
                        INNER JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                            ON p.products_id = p2c.products_id
                  WHERE p.products_status = 1
                    AND p2c.categories_id IN (" . implode(',', $subcategories_array) . ")";
        }
        // EOF products_in_subcategories

        $products = $db->Execute($sql);
        if (SKIP_SINGLE_PRODUCT_CATEGORIES !== 'True' || $products->fields['total'] !== '1') {
            if (SITEMAPXML_CATEGORIES_IMAGES === 'true' && !empty($next_category['categories_image']) && is_file(DIR_FS_CATALOG . DIR_WS_IMAGES . $next_category['categories_image'])) {
                $images = [
                    [
                        'file' => DIR_WS_IMAGES . $next_category['categories_image'],
                        'title' => $next_category['categories_name'],
                    ],
                ];
                $xtra = $sitemapXML->imagesTags($images, SITEMAPXML_CATEGORIES_IMAGES_CAPTION, SITEMAPXML_CATEGORIES_IMAGES_LICENSE);
            }

            $cat_path = $sitemapXML->GetFullcPath($next_category['categories_id']);
            $sitemapXML->writeItem(FILENAME_DEFAULT, 'cPath=' . $cat_path, $next_category['language_id'], $next_category['last_date'], SITEMAPXML_CATEGORIES_CHANGEFREQ, $xtra);

            // BOF categories_paging
            if (SITEMAPXML_CATEGORIES_PAGING === 'true') {
                $total_pages = ceil($products->fields['total']/MAX_DISPLAY_PRODUCTS_LISTING);
                for ($ind_page = 2; $ind_page <= $total_pages; $ind_page++) {
                    $sitemapXML->writeItem(FILENAME_DEFAULT, 'cPath=' . $cat_path . '&page=' . $ind_page, $next_category['language_id'], $next_category['last_date'], SITEMAPXML_CATEGORIES_CHANGEFREQ);
                }

            }
            // EOF categories_paging
        }
    }

    $sitemapXML->SitemapClose();
    unset($categories, $products, $images);
}

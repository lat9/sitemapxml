<?php
/**
 * Sitemap XML
 *
 * @package Sitemap XML
 * @copyright Copyright 2005-2015 Andrew Berezin eCommerce-Service.com
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @link hideCategories http://www.zen-cart.com/downloads.php?do=file&id=254
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: sitemapxml_cats2man.php v 1.1 27.03.2015 17:07:46 AndrewBerezin $
 */
echo '<h3>' . TEXT_HEAD_CATS2MAN . '</h3>';

// BOF hideCategories
if ($sitemapXML->dbTableExist('TABLE_HIDE_CATEGORIES')) {
    $from = " INNER JOIN " . TABLE_HIDE_CATEGORIES . " h ON c.categories_id = h.categories_id";
    $where = ' AND (h.visibility_status < 2 OR h.visibility_status IS NULL)';
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
if ($sitemapXML->SitemapOpen('cats2man', $last_date)) {
    $categories = $db->Execute(
        "SELECT c.categories_id, GREATEST(c.date_added, IFNULL(c.last_modified, '0001-01-01 00:00:00')) AS last_date, c.sort_order AS priority, cd.language_id
           FROM " . TABLE_CATEGORIES . " c
                INNER JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd
                    ON cd.categories_id = c.categories_id
                   AND cd.language_id IN (" . $sitemapXML->getLanguagesIDs() . ') ' .
                $from . "
          WHERE c.categories_status = 1" .
          $where .
          (SITEMAPXML_CATEGORIES_ORDERBY !== '' ? ' ORDER BY ' . SITEMAPXML_CATEGORIES_ORDERBY : ''));
    $sitemapXML->SitemapSetMaxItems($categories->RecordCount());
    foreach ($categories as $next_category) {
        $subcategories_array = [$next_category['categories_id']];

        // BOF products_in_subcategories
        if (defined('SHOW_NESTED_AS_PRODUCTS') && SHOW_NESTED_AS_PRODUCTS === 'True') {
            zen_get_subcategories($subcategories_array, (int)$next_category['categories_id']);
        }
        // EOF products_in_subcategories

        $subcategories_list = implode(',', $subcategories_array);
        $sql =
            "SELECT COUNT(*) AS total
               FROM " . TABLE_PRODUCTS . " p
                    INNER JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                        ON p.products_id = p2c.products_id
                       AND p2c.categories_id IN ($subcategories_list)
              WHERE p.products_status = 1";
        $products = $db->Execute($sql);
        if (SKIP_SINGLE_PRODUCT_CATEGORIES !== 'True' && $products->fields['total'] === '1') {
            $products->fields['total'] = 2;
        }
        if ($products->fields['total'] > 1) {
            $cat_path = $sitemapXML->GetFullcPath($next_category['categories_id']);
            $sql =
                "SELECT DISTINCT m.manufacturers_id
                   FROM " . TABLE_PRODUCTS . " p
                        INNER JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                            ON p2c.products_id = p.products_id
                           AND p2c.categories_id IN ($subcategories_list)
                        INNER JOIN " . TABLE_MANUFACTURERS . " m
                            ON m.manufacturers_id = p.manufacturers_id
                  WHERE p.products_status = 1";
            $manufacturers = $db->Execute($sql);
            foreach ($manufacturers as $next_manufacturer) {
                $sitemapXML->writeItem(FILENAME_DEFAULT, 'cPath=' . $cat_path . '&filter_id=' . $next_manufacturer['manufacturers_id'], $next_category['language_id'], $next_category['last_date'], SITEMAPXML_CATEGORIES_CHANGEFREQ, '');
                $sql =
                    "SELECT COUNT(*) AS total
                      FROM " . TABLE_PRODUCTS . " p
                            INNER JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                                ON p2c.products_id = p.products_id
                               AND p2c.categories_id IN ($subcategories_list)
                     WHERE p.products_status = 1
                       AND p.manufacturers_id = " . $next_manufacturer['manufacturers_id'];
                $products = $db->Execute($sql);
                $total_pages = ceil($products->fields['total']/MAX_DISPLAY_PRODUCTS_LISTING);
                for ($ind_page = 2; $ind_page <= $total_pages; $ind_page++) {
                    $sitemapXML->writeItem(FILENAME_DEFAULT, 'cPath=' . $cat_path . '&filter_id=' . $next_manufacturer['manufacturers_id'] . '&page=' . $ind_page, $next_category['language_id'], $next_category['last_date'], SITEMAPXML_CATEGORIES_CHANGEFREQ);
                }
            }
          unset($manufacturers);
        }
    }

    $sitemapXML->SitemapClose();
    unset($categories);
}

<?php
/**
 * Sitemap XML
 *
 * Last updated: v4.0.4
 *
 * @package Sitemap XML
 * @copyright Copyright 2005-2012 Andrew Berezin eCommerce-Service.com
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: sitemapxml_manufacturers.php, v 3.2.2 07.05.2012 19:12 AndrewBerezin $
 */
echo '<h3>' . TEXT_HEAD_MANUFACTURERS . '</h3>';
$last_date = $db->Execute(
    "SELECT MAX(GREATEST(IFNULL(m.date_added, '0001-01-01 00:00:00'), IFNULL(m.last_modified, '0001-01-01 00:00:00'))) AS last_date
       FROM " . TABLE_MANUFACTURERS . " m"
);
$table_status = $db->Execute("SHOW TABLE STATUS LIKE '" . TABLE_MANUFACTURERS . "'");
$last_date = max($table_status->fields['Update_time'], $last_date->fields['last_date']);
$select = (SITEMAPXML_MANUFACTURERS_IMAGES === 'true') ? ', m.manufacturers_image, m.manufacturers_name' : '';
if ($sitemapXML->SitemapOpen('manufacturers', $last_date)) {
    $manufacturers = $db->Execute(
        "SELECT m.manufacturers_id, GREATEST(m.date_added, IFNULL(m.last_modified, '0001-01-01 00:00:00')) AS last_date, mi.languages_id" . $select . "
           FROM " . TABLE_MANUFACTURERS . " m
                INNER JOIN " . TABLE_MANUFACTURERS_INFO . " mi
                    ON mi.manufacturers_id = m.manufacturers_id
                   AND mi.languages_id IN (" . $sitemapXML->getLanguagesIDs() . ") " .
           (SITEMAPXML_MANUFACTURERS_ORDERBY !== '' ? 'ORDER BY ' . SITEMAPXML_MANUFACTURERS_ORDERBY : '')
    );
    $sitemapXML->SitemapSetMaxItems($manufacturers->RecordCount());
    foreach ($manufacturers as $next_manufacturer) {
        // -----
        // Don't include manufacturers with no products.
        //
        $manufacturers_products = $db->Execute(
            "SELECT products_id
               FROM " . TABLE_PRODUCTS . "
              WHERE manufacturers_id = " . (int)$next_manufacturer['manufacturers_id'] . "
                AND products_status = 1
              LIMIT 1"
        );
        if ($manufacturers_products->EOF) {
            continue;
        }

        $xtra = '';
        if (!empty($next_manufacturer['manufacturers_image']) && is_file(DIR_FS_CATALOG . DIR_WS_IMAGES . $next_manufacturer['manufacturers_image'])) {
            $images = [
                [
                    'file' => DIR_WS_IMAGES . $next_manufacturer['manufacturers_image'],
                    'title' => $next_manufacturer['manufacturers_name'],
                ],
            ];
            $xtra = $sitemapXML->imagesTags($images, SITEMAPXML_MANUFACTURERS_IMAGES_CAPTION, SITEMAPXML_MANUFACTURERS_IMAGES_LICENSE);
        }
        $sitemapXML->writeItem(
            FILENAME_DEFAULT,
            'manufacturers_id=' . $next_manufacturer['manufacturers_id'],
            $next_manufacturer['languages_id'],
            $next_manufacturer['last_date'] ?? $last_date,
            SITEMAPXML_MANUFACTURERS_CHANGEFREQ,
            $xtra
        );
    }

    $sitemapXML->SitemapClose();
    unset($manufacturers);
}

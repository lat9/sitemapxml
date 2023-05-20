<?php
/**
 * Sitemap XML
 *
 * @package Sitemap XML
 * @copyright Copyright 2005-2015 Andrew Berezin eCommerce-Service.com
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: sitemapxml_products_reviews.php, v 1.1 31.01.2015 15:29:52 AndrewBerezin $
 */
echo '<h3>' . TEXT_HEAD_PRODUCTS_REVIEWS . '</h3>';
$last_date = $db->Execute(
    "SELECT MAX(GREATEST(r.date_added, IFNULL(r.last_modified, '0001-01-01 00:00:00'))) AS last_date
       FROM " . TABLE_REVIEWS . " r
      WHERE r.status = 1"
);
$table_status = $db->Execute("SHOW TABLE STATUS LIKE '" . TABLE_REVIEWS . "'");
$last_date = max($table_status->fields['Update_time'], $last_date->fields['last_date']);
if ($sitemapXML->SitemapOpen('products_reviews', $last_date)) {
    $sql =
        "SELECT r.products_id, MAX(r.date_added) AS date_added, MAX(r.last_modified) AS last_modified, GREATEST(MAX(r.date_added), IFNULL(MAX(r.last_modified), '0001-01-01 00:00:00')) AS last_date, rd.languages_id
           FROM " . TABLE_REVIEWS . " r
                LEFT JOIN " . TABLE_REVIEWS_DESCRIPTION . " rd ON (r.reviews_id = rd.reviews_id),
               " . TABLE_PRODUCTS . " p
          WHERE p.products_id = r.products_id
            AND p.products_status = 1
            AND r.status = 1
            AND rd.languages_id IN (" . $sitemapXML->getLanguagesIDs() . ")
          GROUP BY r.products_id" .
          (SITEMAPXML_PRODUCTS_REVIEWS_ORDERBY !== '' ? " ORDER BY " . SITEMAPXML_PRODUCTS_REVIEWS_ORDERBY : '');
    $reviews = $db->Execute($sql);

    $sitemapXML->SitemapSetMaxItems($reviews->RecordCount());
    foreach ($reviews as $next_review) {
        $sitemapXML->writeItem(FILENAME_PRODUCT_REVIEWS, 'products_id=' . $next_review['products_id'], $next_review['languages_id'], $next_review['last_date'], SITEMAPXML_PRODUCTS_REVIEWS_CHANGEFREQ);
    }
    $sitemapXML->SitemapClose();
    unset($reviews);
}

<?php
/**
 * Sitemap XML
 *
 * Last updated: v4.0.3
 *
 * @package Sitemap XML
 * @copyright Copyright 2005-2012 Andrew Berezin eCommerce-Service.com
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: sitemapxml_reviews.php, v 3.2.2 07.05.2012 19:12 AndrewBerezin $
 */
echo '<h3>' . TEXT_HEAD_REVIEWS . '</h3>';
$last_date = $db->Execute(
    "SELECT MAX(GREATEST(r.date_added, IFNULL(r.last_modified, '0001-01-01 00:00:00'))) AS last_date
       FROM " . TABLE_REVIEWS . " r
       WHERE r.status = 1"
);
$table_status = $db->Execute("SHOW TABLE STATUS LIKE '" . TABLE_REVIEWS . "'");
$last_date = max($table_status->fields['Update_time'], $last_date->fields['last_date']);
if ($sitemapXML->SitemapOpen('reviews', $last_date)) {
    $reviews = $db->Execute(
        "SELECT r.reviews_id, GREATEST(r.date_added, IFNULL(r.last_modified, '0001-01-01 00:00:00')) AS last_date, r.products_id, r.reviews_rating AS priority, rd.languages_id AS language_id
           FROM " . TABLE_REVIEWS . " r
                INNER JOIN " . TABLE_REVIEWS_DESCRIPTION . " rd
                    ON r.reviews_id = rd.reviews_id
                   AND rd.languages_id IN (" . $sitemapXML->getLanguagesIDs() . ")
                 WHERE r.status = 1 " .
                 (SITEMAPXML_REVIEWS_ORDERBY !== '' ? 'ORDER BY ' . SITEMAPXML_REVIEWS_ORDERBY : '')
    );
    $sitemapXML->SitemapSetMaxItems($reviews->RecordCount());
    foreach ($reviews as $next_review) {
        $sitemapXML->writeItem(
            FILENAME_PRODUCT_REVIEWS_INFO,
            'products_id=' . $next_review['products_id'] . '&reviews_id=' . $next_review['reviews_id'],
            $next_review['language_id'],
            $next_review['last_date'] ?? $last_date,
            SITEMAPXML_REVIEWS_CHANGEFREQ
        );
    }

    $sitemapXML->SitemapClose();
    unset($reviews);
}

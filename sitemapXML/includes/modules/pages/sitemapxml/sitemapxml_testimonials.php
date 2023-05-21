<?php
/**
 * Sitemap XML
 *
 * @package Sitemap XML
 * @copyright Copyright 2005-2012 Andrew Berezin eCommerce-Service.com
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @link Testimonial Manager http://www.zen-cart.com/downloads.php?do=file&id=299
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: sitemapxml_testimonials.php, v 3.2.2 07.05.2012 19:12 AndrewBerezin $
 */
if ($sitemapXML->dbTableExist('TABLE_TESTIMONIALS_MANAGER')) {
    echo '<h3>' . TEXT_HEAD_TESTIMONIALS . '</h3>';
    $last_date = $db->Execute(
        "SELECT MAX(GREATEST(t.date_added, IFNULL(t.last_update, '0001-01-01 00:00:00'))) AS last_date
           FROM " . TABLE_TESTIMONIALS_MANAGER . " t
          WHERE t.status = 1"
    );
    if ($sitemapXML->SitemapOpen('testimonials', $last_date->fields['last_date'])) {
        $testimonials = $db->Execute(
            "SELECT t.testimonials_id, GREATEST(t.date_added, IFNULL(t.last_update, '0001-01-01 00:00:00')) AS last_date, t.language_id
               FROM " . TABLE_TESTIMONIALS_MANAGER . " t
              WHERE t.status = 1
                AND t.language_id IN (" . $sitemapXML->getLanguagesIDs() . ") " .
              (SITEMAPXML_TESTIMONIALS_ORDERBY != '' ? "ORDER BY " . SITEMAPXML_TESTIMONIALS_ORDERBY : '')
        );
        $sitemapXML->SitemapSetMaxItems($testimonials->RecordCount());
        foreach ($testimonials as $next_item) {
            $sitemapXML->writeItem(FILENAME_TESTIMONIALS_MANAGER, 'testimonials_id=' . $next_item['testimonials_id'], $next_item['language_id'], $next_item['last_date'], SITEMAPXML_TESTIMONIALS_CHANGEFREQ);
        }

        $sitemapXML->SitemapClose();
        unset($testimonials);
    }
}

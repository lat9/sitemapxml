<?php
/**
 * Sitemap XML
 *
 * @package Sitemap XML
 * @copyright Copyright 2005-2012 Andrew Berezin eCommerce-Service.com
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: sitemapxml_video_products.php, v 2.3.1 21.07.2010 9:18:18 AndrewBerezin $
 */
// http://www.google.com/support/webmasters/bin/answer.py?hl=en&answer=80472
zen_define_default('SITEMAPXML_VIDEO_FAMILY_FRIENDLY', 'yes'); // yes no
zen_define_default('SITEMAPXML_VIDEO_THUMBNAIL_SIZE', 'medium'); // small medium large
zen_define_default('SITEMAPXML_VIDEO_PLAYER', 'player.swf?file=/%s');
zen_define_default('SITEMAPXML_VIDEO_DESCRIPTION_MAXSIZE', '300');

if (!function_exists('zen_get_products_video_file')) {
    return;
}

echo '<h3>' . TEXT_HEAD_PRODUCTS_VIDEO . '</h3>';
$file_main_product_image = DIR_WS_MODULES . zen_get_module_directory(FILENAME_MAIN_PRODUCT_IMAGE);
$last_date = 0;
if ($zen_SiteMapXML->SitemapOpen('products', $last_date, 'video')) {
    $products = $db->Execute(
        "SELECT p.products_id, p.products_image,
                pd.products_name, pd.products_description, pd.language_id,
                pm.metatags_title, pm.metatags_keywords, pm.metatags_description
           FROM " . TABLE_PRODUCTS . " p
                INNER JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd
                    ON p.products_id = pd.products_id
                   AND pd.language_id IN (" . $zen_SiteMapXML->getLanguagesIDs() . ")
                INNER JOIN " . TABLE_META_TAGS_PRODUCTS_DESCRIPTION . " pm
                    ON p.products_id = pm.products_id
                   AND pd.language_id = pm.language_id
           WHERE p.products_status = 1"
    );
    $zen_SiteMapXML->SitemapSetMaxItems(0);
    foreach ($products as $next_product) {
        $langParm = $zen_SiteMapXML->getLanguageParameter($next_product['language_id']);
        if ($langParm !== false) {
            if ($videoFile = zen_get_products_video_file($next_product['products_id'], $zen_SiteMapXML->getLanguageDirectory($next_product['language_id']))) {
                $link = zen_href_link(zen_get_info_page($next_product['products_id']), 'products_id=' . $next_product['products_id'] . $langParm, 'NONSSL', false);
                $videoContentLoc = DIR_WS_VIDEOS . $videoFile;
                $videoPlayerLoc = sprintf(SITEMAPXML_VIDEO_PLAYER, $videoContentLoc);
                $products_image = $next_product['products_image'];
                require $file_main_product_image;
                switch (SITEMAPXML_VIDEO_THUMBNAIL_SIZE) {
                    case 'small':
                        $img = zen_image(DIR_WS_IMAGES . $products_image, '', SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT);
                        break;
                    case 'medium':
                        $img = zen_image($products_image_medium, '', MEDIUM_IMAGE_WIDTH, MEDIUM_IMAGE_HEIGHT);
                        break;
                    case 'large':
                    default:
                        $img = zen_image($products_image_large, '', MEDIUM_IMAGE_WIDTH, MEDIUM_IMAGE_HEIGHT);
                        break;
                }
                if (preg_match('@src="([^"]*)"@', $img, $match)) {
                    $videoThumbnailLoc = $zen_SiteMapXML->_clear_url($match[1]); // http://www.usim.co.il/images/thumbnails/nokia/nokia_5800-450x300.jpg
                } else {
                    $videoThumbnailLoc = '';
                }

                $videoTitle = (!empty($next_product['metatags_title'])) ? $next_product['metatags_title'] : strip_tags($next_product['products_name']);
                if (strlen($videoTitle) > 100) {
                    $videoTitle = zen_trunc_string($videoTitle, 100, false);
                }

                $videoDescription = (!empty($next_product['metatags_title'])) ? $next_product['metatags_description'] : strip_tags($next_product['products_description']);
                if (strlen($videoDescription) > SITEMAPXML_VIDEO_DESCRIPTION_MAXSIZE) {
                    $videoDescription = zen_trunc_string($videoDescription, SITEMAPXML_VIDEO_DESCRIPTION_MAXSIZE, false);
                }

                $videoTags = '';
                if (!empty($next_product['metatags_keywords'])) {
                    $words = explode(',', $next_product['metatags_keywords']);
                    $n = min(count($words), 32);
                    for ($i = 0; $i < $n; $i++) {
                        $videoTags .= '  <video:tag>' . '<![CDATA[ ' . $words[$i] . ' ]]>' . '</video:tag>' . "\n";
                    }
                }

                $videoCategory = '';

                if ($videoDuration = GetFLVDuration(DIR_FS_CATALOG . $videoContentLoc)) {
                    $videoDuration = intval($videoDuration/1000);
                }

                $videomapXML = '';
                $videomapXML .= '<video:video>' . "\n";
                $videomapXML .= '  <video:content_loc>' . HTTP_SERVER . DIR_WS_CATALOG . $videoContentLoc . '</video:content_loc>' . "\n";
                $videomapXML .= '  <video:player_loc allow_embed="yes">' . HTTP_SERVER . DIR_WS_CATALOG . $videoPlayerLoc . '</video:player_loc>' . "\n";
                if ($videoThumbnailLoc != '') {
                    $videomapXML .= '  <video:thumbnail_loc>' . HTTP_SERVER . DIR_WS_CATALOG . $videoThumbnailLoc . '</video:thumbnail_loc>' . "\n";
                }
                $videomapXML .= '  <video:title>' . $videoTitle . '</video:title>' . "\n";
                $videomapXML .= '  <video:description>' . '<![CDATA[ ' . $videoDescription . ' ]]>' . '</video:description>' . "\n";
                $videomapXML .= $videoTags;
                if ($videoCategory !== '') {
                    $videomapXML .= '  <video:category>' . $videoCategory . '</video:category>' . "\n";
                }
                if ($videoDuration !== '') {
                    $videomapXML .= '  <video:duration>' . $videoDuration . '</video:duration>' . "\n";
                }
                $videomapXML .= '  <video:family_friendly>' . SITEMAPXML_VIDEO_FAMILY_FRIENDLY . '</video:family_friendly>' . "\n";
                $videomapXML .= '</video:video>' . "\n";

                $zen_SiteMapXML->SitemapWriteItem($link, '', '', $videomapXML);
            }
        }
    }

    $zen_SiteMapXML->SitemapClose();
    unset($products);
}

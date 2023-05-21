<?php
/**
 * Override Template for common/tpl_main_page.php
 *
 * @package templateSystem
 * @copyright Copyright 2005-2015 Andrew Berezin eCommerce-Service.com
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: sitemapxml.php, v 2.2.0 08.06.2009 16:45 AndrewBerezin $
 */
?>
<body id="sitemapxmlBody">
    <div id="mainWrapper">
        <div class="centerColumn" id="siteMapXML">
            <h1 id="siteMapXMLHeading"><?php echo HEADING_TITLE; ?></h1>
<?php
if ($genxml === true) {
    foreach ($SiteMapXMLmodules as $module) {
        $sitemapXML->SitemapClose();
        require $module;
    }
}
$sitemapXML->GenerateSitemapIndex();

$time_start = explode(' ', PAGE_PARSE_START_TIME);
$time_end = explode(' ', microtime());
$parse_time = number_format(($time_end[1] + $time_end[0] - ($time_start[1] + $time_start[0])), 3);
?>
            <div><?php echo sprintf(TEXT_EXECUTION_TIME, $sitemapXML->timefmt($parse_time), $db->queryCount(), number_format($db->queryTime(), 3)); ?></div>
        </div>
    </div>
    <script>if (window.opener) window.opener.location.reload(true);</script>
</body>
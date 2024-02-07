<?php
/**
 * Sitemap XML Feed
 *
 * @package Sitemap XML Feed
 * @copyright Copyright 2005-2012 Andrew Berezin eCommerce-Service.com
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @link http://www.sitemaps.org/
 * @version $Id: sitemapxml.php, v 3.8 07.07.2016 12:39:33 AndrewBerezin $
 */
// php -f /home/XXXXXXX/domains/XXXX.ru/public_html/cgi-bin/sitemapxml.php rebuild=yes
// /usr/local/bin/php -f /home/XXXXXXX/data/www/XXXX.ru/cgi-bin/sitemapxml.php rebuild=yes

if (!is_file(__DIR__ . '/includes/configure.php')) {
    chdir(__DIR__ . '/../');
} else {
    chdir(__DIR__);
}

if (isset($_SERVER['argc']) && $_SERVER['argc'] > 1 && empty($_GET)) {
    for ($i = 1, $n = count($_SERVER['argv']); $i < $n; $i++) {
        list($key, $val) = explode('=', $_SERVER['argv'][$i]);
        $_GET[$key] = $val;
        $_REQUEST[$key] = $val;
    }
}

if (isset($_GET['debug']) && $_GET['debug'] === 'yes') {
    define('STRICT_ERROR_REPORTING', true);
    ini_set('display_errors', TRUE);
    error_reporting(E_ALL & ~E_NOTICE);
}

if (empty($_SERVER['REQUEST_URI'])) {
    $_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];
}
if (empty($_SERVER['REMOTE_ADDR'])) {
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
}
if (empty($_SERVER['SERVER_NAME'])) {
    $_SERVER['SERVER_NAME'] = '';
}
if (empty($_SERVER['PHP_SELF'])) {
    $_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];
}
if (empty($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = $_SERVER['SERVER_NAME'];
}
if (empty($_SERVER['HTTP_USER_AGENT'])) {
    $_SERVER['HTTP_USER_AGENT'] = 'Cron /usr/local/bin/php -f ';
}

$_GET['main_page'] = 'sitemapxml';

function zen_sitemapxml_callback($html)
{
    $html = str_replace('&nbsp;', ' ', $html);
    $html = preg_replace('@\s\s+@', ' ', $html);
    $html = preg_replace('@<head>(.*)</'.'head>@si', '', $html);
    $html = preg_replace('@<script(.*)</'.'script>@si', '', $html);
    $html = preg_replace('@<title>(.*)</'.'title>@si', '', $html);
    $html = preg_replace('@(</h[1-4]>)@si', "$1\n", $html);
    $html = preg_replace('@(<h[1-4]>|<div)@si', "\n$1", $html);
    $html = preg_replace('@(<br\s*[/]*>|<p.*>|</p>|</div>|</h\d+>)@si', "$1\n", $html);
    $html = preg_replace("@\n\s+@", "\n", $html);
    $html = strip_tags($html);
    $html = trim($html);
    return $html;
}
ob_start('zen_sitemapxml_callback');

require 'index.php';

<?php
/**
 * Sitemap XML Feed
 *
 * @package Sitemap XML Feed
 * @copyright Copyright 2005-2016 Andrew Berezin eCommerce-Service.com
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @link http://www.sitemaps.org/
 * @version $Id: sitemapxml.php, v 3.8 07.07.2016 12:39:33 AndrewBerezin $
 */
global $current_page;   //- Needed for zc158 since language files are now loaded by a class

if (defined('SITEMAPXML_SITEMAPINDEX')) {
    define('SITEMAPXML_SITEMAPINDEX_HTTP_LINK', HTTP_CATALOG_SERVER . DIR_WS_CATALOG . SITEMAPXML_SITEMAPINDEX . '.xml');
    define('TEXT_SITEMAPXML_TIPS_TEXT',
        '<p>Подробно о Sitemaps xml Вы можете прочитать на <strong><a href="https://sitemaps.org/" target="_blank" rel="noopener noreferrer" class="splitPageLink">[Sitemaps.org]</a></strong>.</p>
        <ol>
            <li>Зарегистрируйтесь: <strong><a href="https://www.google.com/webmasters/tools/home" target="_blank" rel="noopener noreferrer" class="splitPageLink">[Google]</a></strong>, <strong><a href="https://webmaster.yandex.ru/" target="_blank" rel="noopener noreferrer" class="splitPageLink">[Yandex]</a></strong>, <strong><a href="https://ssl.bing.com/webmaster" target="_blank" rel="noopener noreferrer" class="splitPageLink">[Bing]</a></strong>.</li>
            <li>Укажите Ваш Sitemap <code>' . SITEMAPXML_SITEMAPINDEX_HTTP_LINK . '</code> в <strong><a href="https://www.google.com/webmasters/tools/home" target="_blank" rel="noopener noreferrer" class="splitPageLink">[Google]</a></strong>, <strong><a href="https://webmaster.yandex.ru/" target="_blank" rel="noopener noreferrer" class="splitPageLink">[Yandex]</a></strong>.</li>
            <li>Укажите адрес Sitemap в Вашем файле <a href="' . HTTP_CATALOG_SERVER . DIR_WS_CATALOG . 'robots.txt' . '" target="_blank" class="splitPageLink">robots.txt</a> (<a href="https://sitemaps.org/protocol.php#submit_robots" target="_blank" rel="noopener noreferrer" class="splitPageLink">подробнее...</a>): <code>Sitemap: ' . SITEMAPXML_SITEMAPINDEX_HTTP_LINK . '</code></li>
            <li>Оповестите поисковые системы об изменениях Ваших Sitemap XML.</li>
        </ol>
        <p>Чтобы автоматически обновлять sitemaps и автоматически оповещать (пинговать) поисковые системы, необходимо создать cron-задания в Вашей управляющей панели Вашего хостинга.</p>
        <p>Например, для запуска задания ежедневно в 5:0 утра, задайте следующие параметры задания cron (конкретные команды могут отличаться в зависимости от хостинга):</p>
        <samp>0 5 * * * GET \'https://your_domain/index.php?main_page=sitemapxml\&amp;rebuild=yes\'</samp><br>
        <samp>0 5 * * * wget -q \'https://your_domain/index.php?main_page=sitemapxml\&amp;rebuild=yes\' -O /dev/null</samp><br>
        <samp>0 5 * * * curl -s \'https://your_domain/index.php?main_page=sitemapxml\&amp;rebuild=yes\'</samp><br>
        <samp>0 5 * * * php -f &lt;path to shop&gt;/cgi-bin/sitemapxml.php rebuild=yes</samp><br>');
}
define('HEADING_TITLE', 'Sitemap XML');
define('TEXT_SITEMAPXML_TIPS_HEAD', 'Советы:');
if (!defined('TEXT_SITEMAPXML_TIPS_TEXT')) {
    define('TEXT_SITEMAPXML_TIPS_TEXT', '<p>To learn more about how to manage the sitemaps of this software, please <a href="' . zen_href_link($current_page, zen_get_all_get_params()) . '">reload</a> this page.</p>');
}
define('TEXT_SITEMAPXML_INSTRUCTIONS_HEAD', 'Создать / обновить Ваши Sitemap:');
define('TEXT_SITEMAPXML_CHOOSE_PARAMETERS', 'Выберите параметры:');
define('TEXT_SITEMAPXML_CHOOSE_PARAMETERS_REBUILD', 'Перезаписать все существующие файлы sitemap*.xml!');

define('TEXT_SITEMAPXML_PLUGINS_LIST', 'Плагины');
define('TEXT_SITEMAPXML_PLUGINS_LIST_SELECT', 'Отметьте активные плагины');

define('TEXT_SITEMAPXML_FILE_LIST', 'Список файлов Sitemap');
define('TEXT_SITEMAPXML_FILE_LIST_TABLE_FNAME', 'Имя');
define('TEXT_SITEMAPXML_FILE_LIST_TABLE_FSIZE', 'Размер');
define('TEXT_SITEMAPXML_FILE_LIST_TABLE_FTIME', 'Дата');
define('TEXT_SITEMAPXML_FILE_LIST_TABLE_FPERMS', 'Permissions');
define('TEXT_SITEMAPXML_FILE_LIST_TABLE_TYPE', 'Тип');
define('TEXT_SITEMAPXML_FILE_LIST_TABLE_ITEMS', 'Записей');
define('TEXT_SITEMAPXML_FILE_LIST_TABLE_COMMENTS', 'Комментарии');
define('TEXT_SITEMAPXML_FILE_LIST_TABLE_ACTION', 'Действие');

define('TEXT_SITEMAPXML_IMAGE_POPUP_ALT', 'открыть sitemap в новом окне');
define('TEXT_SITEMAPXML_RELOAD_WINDOW', 'Обновить список файлов');

define('TEXT_SITEMAPXML_FILE_LIST_COMMENTS_READONLY', 'Не доступен для записи!!!');
define('TEXT_SITEMAPXML_FILE_LIST_COMMENTS_IGNORED', 'Игнорируется');

define('TEXT_SITEMAPXML_FILE_LIST_TYPE_URLSET', 'UrlSet');
define('TEXT_SITEMAPXML_FILE_LIST_TYPE_SITEMAPINDEX', 'SitemapIndex');
define('TEXT_SITEMAPXML_FILE_LIST_TYPE_UNDEFINED', 'Не определён!!!');

define('TEXT_ACTION_VIEW_FILE', 'Просмотр');
define('TEXT_ACTION_TRUNCATE_FILE', 'Очистить');
define('TEXT_ACTION_TRUNCATE_FILE_CONFIRM', 'Вы действительно хотите очистить файл %s?');
define('TEXT_ACTION_DELETE_FILE', 'Удалить');
define('TEXT_ACTION_DELETE_FILE_CONFIRM', 'Вы действительно хотите удалить файл %s?');

define('TEXT_MESSAGE_FILE_ERROR_OPENED', 'Ошибка при открытии файла %s');
define('TEXT_MESSAGE_FILE_TRUNCATED', 'Файл %s очищен');
define('TEXT_MESSAGE_FILE_DELETED', 'Файл %s удалён');
define('TEXT_MESSAGE_FILE_ERROR_DELETED', 'Ошибка при удалении файла %s');

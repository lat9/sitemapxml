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
 * @version $Id: sitemapxml.php, v 3.9.7 highburyeye 02/05/2023
 * @version $Id: sitemapxml.php, v 3.9.9 lat9 20230521
 * 
 * TC updated for 1.5.8
 */
require 'includes/application_top.php';

$action = $_POST['action'] ?? '';

if ($action !== '') {
    switch ($action) {
        case 'view_file':
        case 'truncate_file':
        case 'delete_file':
            if (empty($_POST['file'])) {
                zen_redirect(zen_href_link(FILENAME_SITEMAPXML));
            }
            $ext = substr($_POST['file'], strpos($_POST['file'], '.'));
            if ($ext !== '.xml' && $ext !== '.xml.gz') {
                zen_redirect(zen_href_link(FILENAME_SITEMAPXML));
            }
            $file = zen_db_prepare_input($_POST['file']);
            switch ($action) {
                case 'view_file':
                    $fp = fopen(DIR_FS_CATALOG . $file, 'r');
                    if ($fp === false) {
                        $messageStack->add_session(sprintf(TEXT_MESSAGE_FILE_ERROR_OPENED, $file), 'error');
                        break;
                    }
                    header('Content-Length: ' . filesize(DIR_FS_CATALOG . $file));
                    header('Content-Type: text/plain; charset=' . CHARSET);
                    while (!feof($fp)) {
                        $contents = fread($fp, 8192);
                        echo $contents;
                    }
                    fclose($fp);
                    die();
                    break;

                case 'truncate_file':
                    $fp = fopen(DIR_FS_CATALOG . $file, 'w');
                    if ($fp === false) {
                        $messageStack->add_session(sprintf(TEXT_MESSAGE_FILE_ERROR_OPENED, $file), 'error');
                    } else {
                        fclose($fp);
                        $messageStack->add_session(sprintf(TEXT_MESSAGE_FILE_TRUNCATED, $file), 'success');
                    }
                    break;

                default:
                    if (unlink(DIR_FS_CATALOG . $file) !== false) {
                        $messageStack->add_session(sprintf(TEXT_MESSAGE_FILE_DELETED, $file), 'success');
                    } else {
                        $messageStack->add_session(sprintf(TEXT_MESSAGE_FILE_ERROR_DELETED, $file), 'error');
                    }
                    break;
            }
            zen_redirect(zen_href_link(FILENAME_SITEMAPXML));
            break;

        case 'select_plugins':
            $active_plugins = $_POST['plugin'] ?? '';
            $active_plugins = (is_array($active_plugins) ? implode(';', $active_plugins) : $active_plugins);
            $sql = "UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '" . zen_db_input($active_plugins) . "' WHERE configuration_key = 'SITEMAPXML_PLUGINS' LIMIT 1";
            $db->Execute($sql);
            zen_redirect(zen_href_link(FILENAME_SITEMAPXML));
            break;

        default:
            break;
    }
}
?>
<!DOCTYPE html>
<html <?= HTML_PARAMS ?>>
<head>
    <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
    <style>
.index, label.plugin_active, form#selectPlugins input.selected {
    font-weight: bold;
}
.zero {
    font-style: italic;
}
label {
    font-weight: normal;
}
#overviewTips {
    border: solid 1px black;
    padding: 1em;
}
    </style>
    <script>
function getFormFields(obj)
{
    var getParms = '';
    for (var i=0; i<obj.childNodes.length; i++) {
        if (obj.childNodes[i].name === 'securityToken') {
            continue;
        }
        if (obj.childNodes[i].tagName == 'INPUT') {
            if (obj.childNodes[i].type === 'text') {
                getParms += "&" + obj.childNodes[i].name + '=' + obj.childNodes[i].value;
            }
            if (obj.childNodes[i].type === 'hidden') {
                getParms += "&" + obj.childNodes[i].name + '=' + obj.childNodes[i].value;
            }
            if (obj.childNodes[i].type === 'checkbox') {
                if (obj.childNodes[i].checked) {
                    getParms += '&' + obj.childNodes[i].name + '=' + obj.childNodes[i].value;
                }
            }
            if (obj.childNodes[i].type === 'radio') {
                if (obj.childNodes[i].checked) {
                    getParms += '&' + obj.childNodes[i].name + '=' + obj.childNodes[i].value;
                }
            }
        }
        if (obj.childNodes[i].tagName == 'SELECT') {
            var sel = obj.childNodes[i];
            getParms += '&' + sel.name + '=' + sel.options[sel.selectedIndex].value;
        }
    }
    getParms = getParms.replace(/\s+/g, ' ');
    getParms = getParms.replace(/ /g, '+');
    return getParms;
}
    </script>
</head>
<body>
<?php
$start_parms = (SITEMAPXML_EXECUTION_TOKEN === '') ? '' : ('token=' . SITEMAPXML_EXECUTION_TOKEN);
$submit_link = zen_catalog_href_link(FILENAME_SITEMAPXML, $start_parms);
?>
    <?php require DIR_WS_INCLUDES . 'header.php'; ?>
    <div class="container-fluid">
        <h1><?= HEADING_TITLE ?> <small>v<?= SITEMAPXML_VERSION ?></small></h1>
        <div class="row">
            <h2><? TEXT_SITEMAPXML_INSTRUCTIONS_HEAD ?></h2>
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3><?= TEXT_SITEMAPXML_CHOOSE_PARAMETERS_REBUILD ?></h3>
                    </div>
                    <div class="panel-body">
                        <?= zen_draw_form('pingSE', FILENAME_SITEMAPXML, '', 'post', 'id="pingSE" target="_blank" onsubmit="javascript:window.open(\'' . $submit_link . '\'+getFormFields(this), \'sitemapPing\', \'resizable=1,statusbar=5,width=860,height=800,top=0,left=0,scrollbars=yes,toolbar=yes\');return false;"') ?>
                            <button type="submit"><?= IMAGE_SEND ?></button>
                        <?= '</form>' . PHP_EOL ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3><?= TEXT_SITEMAPXML_PLUGINS_LIST_SELECT ?></h3>
                    </div>
                    <div class="panel-body">
                        <?= zen_draw_form('selectPlugins', FILENAME_SITEMAPXML, '', 'post', 'id="selectPlugins"') ?>
                            <?= zen_draw_hidden_field('action', 'select_plugins') ?>
<?php
$plugins_files = glob(DIR_FS_CATALOG_MODULES . 'pages/sitemapxml/sitemapxml_*.php');
if (empty($plugins_files)) {
    $plugins_files = [];
}
$plugins_files_active = explode(';', SITEMAPXML_PLUGINS);
foreach ($plugins_files as $plugin_file) {
    $plugin_file = basename($plugin_file);
    $plugin_name = str_replace('.php', '', $plugin_file);
    $active = in_array($plugin_file, $plugins_files_active);

?>
                            <?= zen_draw_checkbox_field('plugin[]', $plugin_file, $active, '', 'id="plugin-' . $plugin_name . '"') ?>
                            <label for="<?= 'plugin-' . $plugin_name ?>" class="plugin<?= ($active === true ? '_active' : '') ?>"><?= $plugin_file ?></label><br>
<?php
}
?>
                            <button type="submit"><?= IMAGE_SAVE ?></button>
                        <?= '</form>' . PHP_EOL ?>
                    </div>
                </div>
            </div>
        </div>

        <h3><?= TEXT_SITEMAPXML_FILE_LIST ?></h3>
        <div>
            <button onclick="javascript: window.location.reload();"><?= TEXT_SITEMAPXML_RELOAD_WINDOW ?></button>
        </div>
        <br>
        <table class="table table-sm table-responsive table-hover">
            <thead>
                <tr class="dataTableHeadingRow">
                    <th class="dataTableHeadingContent"><?= TEXT_SITEMAPXML_FILE_LIST_TABLE_FNAME ?></th>
                    <th class="dataTableHeadingContent text-center"><?= TEXT_SITEMAPXML_FILE_LIST_TABLE_FSIZE ?></th>
                    <th class="dataTableHeadingContent text-center"><?= TEXT_SITEMAPXML_FILE_LIST_TABLE_FTIME ?></th>
                    <th class="dataTableHeadingContent text-center"><?= TEXT_SITEMAPXML_FILE_LIST_TABLE_FPERMS ?></th>
                    <th class="dataTableHeadingContent text-center"><?= TEXT_SITEMAPXML_FILE_LIST_TABLE_TYPE ?></th>
                    <th class="dataTableHeadingContent text-center"><?= TEXT_SITEMAPXML_FILE_LIST_TABLE_ITEMS ?></th>
                    <th class="dataTableHeadingContent text-center"><?= TEXT_SITEMAPXML_FILE_LIST_TABLE_COMMENTS ?></th>
                    <th class="dataTableHeadingContent text-right"><?= TEXT_SITEMAPXML_FILE_LIST_TABLE_ACTION ?></th>
                </tr>
            </thead>
            <tbody>
<?php
$indexFile = SITEMAPXML_SITEMAPINDEX . (SITEMAPXML_COMPRESS === 'true' ? '.xml.gz' : '.xml');
$sitemapFiles = glob(DIR_FS_CATALOG . 'sitemap' . '*' . '.xml');
if (empty($sitemapFiles)) {
    $sitemapFiles = [];
}
$sitemapFilesGZ = glob(DIR_FS_CATALOG . 'sitemap' . '*' . '.xml.gz');
if (empty($sitemapFilesGZ)) {
    $sitemapFilesGZ = [];
}
$sitemapFiles = array_merge($sitemapFiles, $sitemapFilesGZ);

if (SITEMAPXML_DIR_WS !== '') {
    $sitemapxml_dir_ws = SITEMAPXML_DIR_WS;
    $sitemapxml_dir_ws = trim($sitemapxml_dir_ws, '/');
    $sitemapxml_dir_ws .= '/';
    $files = glob(DIR_FS_CATALOG . $sitemapxml_dir_ws . 'sitemap' . '*' . '.xml');
    if (is_array($files)) {
        $sitemapFiles = array_merge($sitemapFiles, $files);
    }
    $files = glob(DIR_FS_CATALOG . $sitemapxml_dir_ws . 'sitemap' . '*' . '.xml.gz');
    if (is_array($files)) {
        $sitemapFiles = array_merge($sitemapFiles, $files);
    }
}
sort($sitemapFiles);

if (in_array(DIR_FS_CATALOG . $indexFile, $sitemapFiles)) {
    $sitemapFiles[] = DIR_FS_CATALOG . $indexFile;
}
$sitemapFiles = array_unique($sitemapFiles);
clearstatcache();
$l = strlen(DIR_FS_CATALOG);
foreach ($sitemapFiles as $file) {
    $f['name'] = substr($file, $l);
    $f['size'] = filesize($file);
    $f['time'] = date(PHP_DATE_TIME_FORMAT, filemtime($file));
    $f['perms'] = substr(sprintf('%o', fileperms($file)), -4);
    $class = '';
    $comments = '';
    $type = '';
    $items = '';
    if (!is_writable($file)) {
        $class .= ' alert';
        $comments .= ' ' . TEXT_SITEMAPXML_FILE_LIST_COMMENTS_READONLY;
    }
    if ($f['name'] == $indexFile) {
        $class .= ' index';
    }
    if ($f['size'] == 0) {
        $class .= ' zero';
        $comments .= ' ' . TEXT_SITEMAPXML_FILE_LIST_COMMENTS_IGNORED;
    }
    if ($f['size'] > 0) {
        $fp = fopen($file, 'r');
        if ($fp === false) {
            $items = '<span class="text-danger">Error!!!</span>';
        } else {
            $contents = '';
            while (!feof($fp)) {
                $contents .= fread($fp, 8192);
            }
            fclose($fp);
            if (strpos($contents, '</urlset>') !== false) {
                $type = TEXT_SITEMAPXML_FILE_LIST_TYPE_URLSET;
                $items = substr_count($contents, '</url>');
            } elseif (strpos($contents, '</sitemapindex>') !== false) {
                $type = TEXT_SITEMAPXML_FILE_LIST_TYPE_SITEMAPINDEX;
                $items = substr_count($contents, '</sitemap>');
            } else {
                $type = TEXT_SITEMAPXML_FILE_LIST_TYPE_UNDEFINED;
                $items = '';
            }
            unset($contents);
        }
    }
?>
                <tr class="dataTableRow text-center<?= $class ?>">
                    <td class="dataTableContent text-left">
                        <a href="<?= HTTP_CATALOG_SERVER . DIR_WS_CATALOG . $f['name'] ?>" target="_blank">
                            <?= $f['name'] ?>&nbsp;<?= zen_image(DIR_WS_IMAGES . 'icon_popup.gif', TEXT_SITEMAPXML_IMAGE_POPUP_ALT, '10', '10') ?>
                        </a>
                    </td>
                    <td class="dataTableContent<?= $class ?>"><?= $f['size'] ?></td>
                    <td class="dataTableContent<?= $class ?>"><?= $f['time'] ?></td>
                    <td class="dataTableContent<?= $class ?>"><?= $f['perms'] ?></td>
                    <td class="dataTableContent<?= $class ?>"><?= $type ?></td>
                    <td class="dataTableContent<?= $class ?>"><?= $items ?></td>
                    <td class="dataTableContent<?= $class ?>"><?= trim($comments) ?></td>
                    <td class="dataTableContent text-right<?= $class ?>">
<?php
    if ($f['size'] > 0) {
?>
                        <?= zen_draw_form('view_file', FILENAME_SITEMAPXML, '', 'post', 'target="_blank"') ?>
                            <?= zen_draw_hidden_field('action', 'view_file') ?>
                            <?= zen_draw_hidden_field('file', $f['name']) ?>
                            <button type="submit"><?= TEXT_ACTION_VIEW_FILE ?></button>
                        <?= '</form>' . PHP_EOL ?>

                        <?= zen_draw_form('truncate_file', FILENAME_SITEMAPXML, '', 'post', 'onsubmit="return confirm(\'' . sprintf(TEXT_ACTION_TRUNCATE_FILE_CONFIRM, $f['name']) . '\');"') ?>
                            <?= zen_draw_hidden_field('action', 'truncate_file') ?>
                            <?= zen_draw_hidden_field('file', $f['name']) ?>
                            <button type="submit"><?= TEXT_ACTION_TRUNCATE_FILE ?></button>
                        <?= '</form>' . PHP_EOL ?>
<?php
    }
?>
                        <?= zen_draw_form('delete_file', FILENAME_SITEMAPXML, '', 'post', 'onsubmit="return confirm(\'' . sprintf(TEXT_ACTION_DELETE_FILE_CONFIRM, $f['name']) . '\');"') ?>
                            <?= zen_draw_hidden_field('action', 'delete_file') ?>
                            <?= zen_draw_hidden_field('file', $f['name']) ?>
                            <button type="submit"><?= TEXT_ACTION_DELETE_FILE ?></button>
                        <?= '</form>' . PHP_EOL ?>
                    </td>
                </tr>
<?php
}
?>
            </tbody>
        </table>

        <h3><?= TEXT_SITEMAPXML_TIPS_HEAD ?></h3>
        <div id="overviewTips">
            <?= TEXT_SITEMAPXML_TIPS_TEXT ?>
        </div>
    </div>

    <div class="smallText center">Copyright &copy; 2004-<?= date('Y') . ' eCommerce-Service' ?></div>
    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
</body>
</html>
<?php require DIR_WS_INCLUDES . 'application_bottom.php'; ?>

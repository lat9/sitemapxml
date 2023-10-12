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

$sql = "SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'SITEMAPXML_VERSION' LIMIT 1";
$version = $db->Execute($sql);
if (!$version->EOF) {
    define('SITEMAPXML_VERSION_CURRENT', $version->fields['configuration_value']);
}

$action = $_POST['action'] ?? '';

if ($action !== '') {
    switch ($action) {
        case 'uninstall':
            require DIR_WS_MODULES . 'sitemapxml_install.php';
            require_once DIR_WS_CLASSES . 'ext_modules.php';
            $ext_modules = new ext_modules();
            $ext_modules->uninstall_configuration('SITEMAPXML_');
            $ext_modules->uninstall_admin_pages(['sitemapxml', 'sitemapxmlConfig']);
            zen_redirect(zen_href_link(FILENAME_SITEMAPXML));
            break;

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
<html <?php echo HTML_PARAMS; ?>>
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
    let getParms = '';
    for (let i=0; i<obj.childNodes.length; i++) {
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
            let sel = obj.childNodes[i];
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
            <h1><?php echo HEADING_TITLE . (defined('SITEMAPXML_VERSION_CURRENT') ? (' <small>v' . SITEMAPXML_VERSION_CURRENT . '</small>') : ''); ?></h1>
            <div class="row">
                <h2><?php echo TEXT_SITEMAPXML_INSTRUCTIONS_HEAD; ?></h2>
                <div class="col-md-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3><?php echo TEXT_SITEMAPXML_CHOOSE_PARAMETERS; ?></h3>
                        </div>
                        <div class="panel-body">
                            <?php echo zen_draw_form('pingSE', FILENAME_SITEMAPXML, '', 'post', 'id="pingSE" target="_blank" onsubmit="window.open(\'' . $submit_link . '\'+getFormFields(this), \'sitemapPing\', \'resizable=1,statusbar=5,width=860,height=800,top=0,left=0,scrollbars=yes,toolbar=yes\');return false;"'); ?>
                                <?php echo zen_draw_checkbox_field('rebuild', 'yes', false, '', 'id="rebuild"'); ?>
                                <label for="rebuild"><?php echo TEXT_SITEMAPXML_CHOOSE_PARAMETERS_REBUILD; ?></label>
                                <br>
                                <?php echo zen_draw_checkbox_field('ping', 'yes', false, '', 'id="ping"'); ?>
                                <label for="ping"><?php echo TEXT_SITEMAPXML_CHOOSE_PARAMETERS_PING; ?></label>
                                <br>
                                <button type="submit"><?php echo IMAGE_SEND; ?></button>
                            <?php echo '</form>' . PHP_EOL; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3><?php echo TEXT_SITEMAPXML_PLUGINS_LIST_SELECT; ?></h3>
                        </div>
                        <div class="panel-body">
                            <?php echo zen_draw_form('selectPlugins', FILENAME_SITEMAPXML, '', 'post', 'id="selectPlugins"'); ?>
                                <?php echo zen_draw_hidden_field('action', 'select_plugins'); ?>
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
                                <?php echo zen_draw_checkbox_field('plugin[]', $plugin_file, $active, '', 'id="plugin-' . $plugin_name . '"'); ?>
                                <label for="<?php echo 'plugin-' . $plugin_name . ''; ?>" class="plugin<?php echo ($active === true ? '_active' : ''); ?>"><?php echo $plugin_file; ?></label><br>
<?php
}
?>
                                <button type="submit"><?php echo IMAGE_SAVE; ?></button>
                            <?php echo '</form>' . PHP_EOL; ?>
                        </div>
                    </div>
                </div>
            </div>

            <h3><?php echo TEXT_SITEMAPXML_FILE_LIST; ?></h3>
            <div>
                <button onclick="window.location.reload();"><?php echo TEXT_SITEMAPXML_RELOAD_WINDOW; ?></button>
            </div>
            <br>
            <table class="table table-sm table-responsive table-hover">
                <thead>
                    <tr class="dataTableHeadingRow">
                        <th class="dataTableHeadingContent"><?php echo TEXT_SITEMAPXML_FILE_LIST_TABLE_FNAME; ?></th>
                        <th class="dataTableHeadingContent text-center"><?php echo TEXT_SITEMAPXML_FILE_LIST_TABLE_FSIZE; ?></th>
                        <th class="dataTableHeadingContent text-center"><?php echo TEXT_SITEMAPXML_FILE_LIST_TABLE_FTIME; ?></th>
                        <th class="dataTableHeadingContent text-center"><?php echo TEXT_SITEMAPXML_FILE_LIST_TABLE_FPERMS; ?></th>
                        <th class="dataTableHeadingContent text-center"><?php echo TEXT_SITEMAPXML_FILE_LIST_TABLE_TYPE; ?></th>
                        <th class="dataTableHeadingContent text-center"><?php echo TEXT_SITEMAPXML_FILE_LIST_TABLE_ITEMS; ?></th>
                        <th class="dataTableHeadingContent text-center"><?php echo TEXT_SITEMAPXML_FILE_LIST_TABLE_COMMENTS; ?></th>
                        <th class="dataTableHeadingContent text-right"><?php echo TEXT_SITEMAPXML_FILE_LIST_TABLE_ACTION; ?></th>
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
                    <tr class="dataTableRow text-center<?php echo $class; ?>">
                        <td class="dataTableContent text-left">
                            <a href="<?php echo HTTP_CATALOG_SERVER . DIR_WS_CATALOG . $f['name']; ?>" target="_blank">
                                <?php echo $f['name']; ?>&nbsp;<?php echo zen_image(DIR_WS_IMAGES . 'icon_popup.gif', TEXT_SITEMAPXML_IMAGE_POPUP_ALT, '10', '10'); ?>
                            </a>
                        </td>
                        <td class="dataTableContent<?php echo $class; ?>"><?php echo $f['size']; ?></td>
                        <td class="dataTableContent<?php echo $class; ?>"><?php echo $f['time']; ?></td>
                        <td class="dataTableContent<?php echo $class; ?>"><?php echo $f['perms']; ?></td>
                        <td class="dataTableContent<?php echo $class; ?>"><?php echo $type; ?></td>
                        <td class="dataTableContent<?php echo $class; ?>"><?php echo $items; ?></td>
                        <td class="dataTableContent<?php echo $class; ?>"><?php echo trim($comments); ?></td>
                        <td class="dataTableContent text-right<?php echo $class; ?>">
<?php
    if ($f['size'] > 0) {
?>
                            <?php echo zen_draw_form('view_file', FILENAME_SITEMAPXML, '', 'post', 'target="_blank"'); ?>
                                <?php echo zen_draw_hidden_field('action', 'view_file'); ?>
                                <?php echo zen_draw_hidden_field('file', $f['name']); ?>
                                <button type="submit"><?php echo TEXT_ACTION_VIEW_FILE; ?></button>
                            <?php echo '</form>' . PHP_EOL; ?>

                            <?php echo zen_draw_form('truncate_file', FILENAME_SITEMAPXML, '', 'post', 'onsubmit="return confirm(\'' . sprintf(TEXT_ACTION_TRUNCATE_FILE_CONFIRM, $f['name']) . '\');"');?>
                                <?php echo zen_draw_hidden_field('action', 'truncate_file'); ?>
                                <?php echo zen_draw_hidden_field('file', $f['name']); ?>
                                <button type="submit"><?php echo TEXT_ACTION_TRUNCATE_FILE; ?></button>
                            <?php echo '</form>' . PHP_EOL; ?>
<?php
    }
?>
                            <?php echo zen_draw_form('delete_file', FILENAME_SITEMAPXML, '', 'post', 'onsubmit="return confirm(\'' . sprintf(TEXT_ACTION_DELETE_FILE_CONFIRM, $f['name']) . '\');"'); ?>
                                <?php echo zen_draw_hidden_field('action', 'delete_file'); ?>
                                <?php echo zen_draw_hidden_field('file', $f['name']); ?>
                                <button type="submit"><?php echo TEXT_ACTION_DELETE_FILE; ?></button>
                            <?php echo '</form>' . PHP_EOL;
?>
                        </td>
                    </tr>
<?php
}
?>
                </tbody>
            </table>

            <h3><?php echo TEXT_SITEMAPXML_TIPS_HEAD; ?></h3>
            <div id="overviewTips">
                <?php echo TEXT_SITEMAPXML_TIPS_TEXT; ?>
            </div>

            <div class="row">
                <?php echo zen_draw_form('uninstall', FILENAME_SITEMAPXML, '', 'post'); ?>
                    <?php echo zen_draw_hidden_field('action', 'uninstall'); ?>
                    <br><button type="submit"><?php echo TEXT_UNINSTALL; ?></button>
                <?php echo '</form>' . PHP_EOL; ?>
            </div>
        </div>

        <div class="smallText center">Copyright &copy; 2004-<?php echo date('Y') . ' eCommerce-Service'; ?></div>
        <!-- footer //-->
        <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
        <!-- footer_eof //-->
    </body>
</html>
<?php require DIR_WS_INCLUDES . 'application_bottom.php'; ?>

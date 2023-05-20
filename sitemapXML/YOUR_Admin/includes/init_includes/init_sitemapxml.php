<?php
/**
 * @package functions
 * @copyright Copyright 2016 iSO Network - https://isonetwork.net.au
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: 2.6.1 17.04.2017 13:44:49 AndrewBerezin $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

// -----
// Wait until an admin is logged in before seeing if any initialization steps need to be performed.
// That ensures that "someone" will see the plugin's installation/update messages!
//
if (!isset($_SESSION['admin_id'])) {
    return;
}

$module_constant = 'SITEMAPXML_VERSION';
$module_installer_directory = DIR_FS_ADMIN . 'includes/installers/sitemapxml';
$module_name = "SitemapXML";
$zencart_com_plugin_id = 367;

//Just change the stuff above... Nothing down here should need to change

unset($configuration_group_id);
if (defined($module_constant)) {
    $current_version = constant($module_constant);
    $sql = "SELECT configuration_group_id FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = :configurationKey: LIMIT 1";
    $sql = $db->bindVars($sql, ':configurationKey:', $module_constant, 'string');
    $config = $db->Execute($sql);
    $configuration_group_id = $config->fields['configuration_group_id'];
    $sql = "SELECT DISTINCT configuration_group_id FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE ':configurationKey:%' AND configuration_group_id != :configurationGroupID:";
    $sql = $db->bindVars($sql, ':configurationKey:', $module_name, 'noquotestring');
    $sql = $db->bindVars($sql, ':configurationGroupID:', $configuration_group_id, 'integer');
    $check = $db->Execute($sql);
    foreach ($check as $next_group) {
        $sql = "UPDATE " . TABLE_CONFIGURATION . " SET configuration_group_id = :configurationGroupIDnew: WHERE configuration_group_id = :configurationGroupIDold: LIMIT 1";
        $sql = $db->bindVars($sql, ':configurationGroupIDnew:', $configuration_group_id, 'integer');
        $sql = $db->bindVars($sql, ':configurationGroupIDold:', $next_group['configuration_group_id'], 'integer');
        $db->Execute($sql);
        $sql = "DELETE FROM " . TABLE_CONFIGURATION_GROUP . " WHERE configuration_group_id = :configurationGroupIDold:";
        $sql = $db->bindVars($sql, ':configurationGroupIDold:', $next_group['configuration_group_id'], 'integer');
        $db->Execute($sql);
    }
} else {
    $current_version = '0.0.0';
    $sql = "SELECT DISTINCT configuration_group_id FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE ':configurationKey:%'";
    $sql = $db->bindVars($sql, ':configurationKey:', $module_name, 'noquotestring');
    $check = $db->Execute($sql);
    if ($check->EOF) {
        $sql =
            "INSERT INTO " . TABLE_CONFIGURATION_GROUP . " (configuration_group_title, configuration_group_description, sort_order, visible) VALUES (':configurationGroupTitle:', 'Set :configurationGroupTitle: Options', 1, 1)";
        $sql = $db->bindVars($sql, ':configurationGroupTitle:', $module_name, 'noquotestring');
        $db->Execute($sql);
        $configuration_group_id = $db->Insert_ID();
        $sql = "UPDATE " . TABLE_CONFIGURATION_GROUP . " SET sort_order = :configurationGroupID: WHERE configuration_group_id = :configurationGroupID: LIMIT 1";
        $sql = $db->bindVars($sql, ':configurationGroupID:', $configuration_group_id, 'integer');
        $db->Execute($sql);
    } elseif ($check->RecordCount() == 1) {
        $configuration_group_id = $check->fields['configuration_group_id'];
    } else {
        foreach ($check as $next_group) {
            if (!isset($configuration_group_id)) {
                $configuration_group_id = $next_group['configuration_group_id'];
            } else {
                $sql = "UPDATE " . TABLE_CONFIGURATION . " SET configuration_group_id = :configurationGroupIDnew: WHERE configuration_group_id = :configurationGroupIDold: LIMIT 1";
                $sql = $db->bindVars($sql, ':configurationGroupIDnew:', $configuration_group_id, 'integer');
                $sql = $db->bindVars($sql, ':configurationGroupIDold:', $next_group['configuration_group_id'], 'integer');
                $db->Execute($sql);
                $sql = "DELETE FROM " . TABLE_CONFIGURATION_GROUP . " WHERE configuration_group_id = :configurationGroupIDold:";
                $sql = $db->bindVars($sql, ':configurationGroupIDold:', $next_group['configuration_group_id'], 'integer');
                $db->Execute($sql);
            }
        }
    }
    $sql =
        "INSERT IGNORE INTO " . TABLE_CONFIGURATION . "
            (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function)
         VALUES
            ('Version', :configurationKey:, '0.0.0', 'Indicates the currently installed version of SitemapXML.', :configurationGroupID:, 0, now(), now(), NULL, NULL)";
    $sql = $db->bindVars($sql, ':configurationKey:', $module_constant, 'string');
    $sql = $db->bindVars($sql, ':configurationGroupID:', $configuration_group_id, 'integer');
    $db->Execute($sql);
}

$installers = glob($module_installer_directory . '/*.php');
foreach ($installers as $i => $file) {
    $file = basename($file);
    $installers[$i] = substr($file, 0, strpos($file, '.php'));
}
natsort($installers);
$newest_version = end($installers);

if (version_compare($newest_version, $current_version) > 0) {
    require DIR_WS_MODULES . 'sitemapxml_install.php';
    require_once DIR_WS_CLASSES . 'ext_modules.php';
    foreach ($installers as $installer) {
        if (version_compare($newest_version, $installer) >= 0 && version_compare($current_version, $installer) < 0) {
            require $module_installer_directory . '/' . $installer . '.php';
            $current_version = str_replace('_', '.', $installer);
            $sql = "UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = :configurationValue:, last_modified = now() WHERE configuration_key = :configurationKey: LIMIT 1";
            $sql = $db->bindVars($sql, ':configurationValue:', $current_version, 'string');
            $sql = $db->bindVars($sql, ':configurationKey:', $module_constant, 'string');
            $db->Execute($sql);
            $messageStack->add('Installed ' . $module_name . ' v' . $current_version, 'success');
        }
    }

    // add tools menu for Sitemap XML
    $admin_page = 'sitemapxml';
    if (!zen_page_key_exists($admin_page) && (int)$configuration_group_id > 0) {
        zen_register_admin_page($admin_page, 'BOX_SITEMAPXML', 'FILENAME_SITEMAPXML', '', 'tools', 'Y');
        $messageStack->add('Successfully enabled Sitemap XML Tool Menu.', 'success');
    }
    $admin_page = 'sitemapxmlConfig';
    if (!zen_page_key_exists($admin_page) && (int)$configuration_group_id > 0) {
        zen_register_admin_page($admin_page, 'BOX_CONFIGURATION_SITEMAPXML', 'FILENAME_CONFIGURATION', 'gID=' . $configuration_group_id, 'configuration', 'Y');
        $messageStack->add('Successfully enabled Sitemap XML Configuration Menu.', 'success');
    }
}

// Version Checking
if ($zencart_com_plugin_id !== 0) {
    $new_version_details = plugin_version_check_for_updates($zencart_com_plugin_id, $current_version);
    if (!empty($_GET['gID']) && $_GET['gID'] == $configuration_group_id && $new_version_details !== false) {
        $messageStack->add(
            'Version ' . $new_version_details['latest_plugin_version'] . ' of ' . $new_version_details['title'] . ' is available at <a href="' . $new_version_details['link'] . '" target="_blank">[Details]</a>',
            'caution'
        );
    }
}
$sitemap_current_version = $current_version;

<?php
/**
 * ext_modules.php
 *
 * @package ext_modules
 * @copyright Copyright 2004-2017 Andrew Berezin eCommerce-Service.com
 * @copyright Copyright 2003-2017 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: ext_modules.php, v 1.6.1 19.02.2017 17:20:16 AndrewBerezin $
 * @version $Id: ext_modules.php, v 3.9.9 (SitemapXML) lat9 $
 */
class ext_modules
{
    protected
        $configuration_group_id = null,
        $prefix = null,
        $configUpdates = [];

    public function install_configuration_group($prefix, $language_key, $configuration_group_description = '', $page_key = '')
    {
        global $db;

        $this->prefix = $prefix;
        $prefix = str_replace('_', '\_', $this->prefix);
        $sql =
            "SELECT DISTINCT configuration_group_id
               FROM " . TABLE_CONFIGURATION . "
               WHERE configuration_key LIKE ':prefix:%'";
        $sql = $db->bindVars($sql, ':prefix:', $prefix, 'noquotestring');
        $check = $db->Execute($sql);
        if ($check->RecordCount() > 1) {
            foreach ($check as $next_group) {
                if (!isset($this->configuration_group_id)) {
                    $this->configuration_group_id = $next_group['configuration_group_id'];
                } else {
                    $sql = "UPDATE " . TABLE_CONFIGURATION . " SET configuration_group_id = :configurationGroupIDnew: WHERE configuration_group_id = :configurationGroupIDold: LIMIT 1";
                    $sql = $db->bindVars($sql, ':configurationGroupIDnew:', $this->configuration_group_id, 'integer');
                    $sql = $db->bindVars($sql, ':configurationGroupIDold:', $next_group['configuration_group_id'], 'integer');
                    $db->Execute($sql);
                    $sql = "DELETE FROM " . TABLE_CONFIGURATION_GROUP . " WHERE configuration_group_id = :configurationGroupIDold:";
                    $sql = $db->bindVars($sql, ':configurationGroupIDold:', $next_group['configuration_group_id'], 'integer');
                    $db->Execute($sql);
                }
            }
        } elseif ($check->RecordCount() == 1) {
            $this->configuration_group_id = $check->fields['configuration_group_id'];
        } else {
            $sql =
                "INSERT INTO " . TABLE_CONFIGURATION_GROUP . "
                    (configuration_group_title, configuration_group_description, sort_order, visible)
                 VALUES
                    (:language_key:, :configuration_group_description:, 1, 1)";
            $sql = $db->bindVars($sql, ':language_key:', constant($language_key), 'string');
            $sql = $db->bindVars($sql, ':configuration_group_description:', $configuration_group_description, 'string');
            $db->Execute($sql);
            $this->configuration_group_id = $db->insert_ID();
            $sql = "UPDATE " . TABLE_CONFIGURATION_GROUP . " SET sort_order = :configuration_group_id: WHERE configuration_group_id = :configuration_group_id: LIMIT 1";
            $sql = $db->bindVars($sql, ':configuration_group_id:', $this->configuration_group_id, 'integer');
            $db->Execute($sql);
        }

        if ($page_key !== '') {
            $admin_page = [
                'page_key' => $page_key,
                'language_key' => $language_key,
                'main_page' => 'FILENAME_CONFIGURATION',
                'page_params' => 'gID=' . $this->configuration_group_id,
                'menu_key' => 'configuration',
                'display_on_menu' => 'Y',
                'sort_order' => $this->configuration_group_id,
            ];
            $this->install_admin_pages($admin_page);
        }
        return $this->configuration_group_id;
    }

    public function install_configuration($configuration)
    {
        global $db;

        $sql = "SELECT * FROM " . TABLE_CONFIGURATION . " WHERE configuration_group_id = :configuration_group_id:";
        $sql = $db->bindVars($sql, ':configuration_group_id:', $this->configuration_group_id, 'integer');
        $configuration_key = $db->Execute($sql);
        $checkArray = [];
        $this->configUpdates = [
            'del' => [],
            'add' => [],
            'upd' => [],
        ];
        foreach ($configuration_key as $config) {
            if (!isset($configuration[$config['configuration_key']])) {
                $this->configUpdates['del'][] = $config['configuration_key'] . " - " . $config['configuration_title'];
                $sql = "DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_id = :configuration_id: LIMIT 1";
                $sql = $db->bindVars($sql, ':configuration_id:', $configuration_key->fields['configuration_id'], 'integer');
                $db->Execute($sql);
            } elseif (isset($checkArray[$config['configuration_key']])) {
                $sql = "DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_id  =:configuration_id: LIMIT 1";
                $sql = $db->bindVars($sql, ':configuration_id:', $configuration_key->fields['configuration_id'], 'integer');
                $db->Execute($sql);
            } else {
                $checkArray[$config['configuration_key']] = $config;
            }
        }

        foreach ($configuration as $configKey => $configSql) {
            list($configuration_title, $configuration_value, $configuration_description, $sort_order, $use_function, $set_function) = $configSql;
            $configuration_group_id = $this->configuration_group_id;
            if (defined($configKey . '_TITLE')) {
                $configuration_title = constant($configKey . '_TITLE');
            }
            if (defined($configKey . '_DESCRIPTION')) {
                $configuration_description = constant($configKey . '_DESCRIPTION');
            }
            if (!isset($checkArray[$configKey])) {
                $sql_array = [
                    'configuration_key' => $configKey,
                    'configuration_group_id' => $configuration_group_id,
                    'configuration_title' => $configuration_title,
                    'configuration_value' => $configuration_value,
                    'configuration_description' => $configuration_description,
                    'sort_order' => $sort_order,
                    'use_function' => $use_function,
                    'set_function' => $set_function,
                    'date_added' => 'now()',
                ];
                $this->configUpdates['add'][] = $sql_array['configuration_key'] . ' - ' . $sql_array['configuration_title'];
                define($sql_array['configuration_key'], $sql_array['configuration_value']);
                zen_db_perform(TABLE_CONFIGURATION, $sql_array);
            } else {
                $sql_array = [];
                if ($configuration_title !== $checkArray[$configKey]['configuration_title']) {
                    $sql_array['configuration_title'] = $configuration_title;
                }
                if ($configuration_value !== $checkArray[$configKey]['configuration_value'] && substr($configKey, -8) === '_VERSION') {
                    $sql_array['configuration_value'] = $configuration_value;
                }
                if ($configuration_description !== $checkArray[$configKey]['configuration_description']) {
                    $sql_array['configuration_description'] = $configuration_description;
                }
                if ($sort_order !== $checkArray[$configKey]['sort_order']) {
                    $sql_array['sort_order'] = $sort_order;
                }
                if ($use_function != $checkArray[$configKey]['use_function']) {
                    $sql_array['use_function'] = $use_function;
                }
                if ($set_function !== $checkArray[$configKey]['set_function']) {
                    $sql_array['set_function'] = $set_function;
                }
                if ($sql_array !== []) {
                    $sql_array['last_modified'] = 'now()';
                    $this->configUpdates['upd'][] = $configKey . ' - ' . $configuration_title;
                    zen_db_perform(TABLE_CONFIGURATION, $sql_array, 'update', "configuration_key = '" . zen_db_input($configKey) . "'");
                }
            }
        }
    }

    public function install_db_table($table, $install_sql)
    {
        global $db, $sniffer;

        if ($sniffer->table_exists($table) === false) {
            $db->Execute($install_sql);
            return;
        }

        $install_sql = str_replace('`', '', $install_sql);
        $install_sql = str_replace("CREATE TABLE IF NOT EXISTS " . $table . " (", "CREATE TABLE IF NOT EXISTS `" . $table . "_temp` (", $install_sql);
        $db->Execute($install_sql);

        $sql = "SHOW CREATE TABLE `" . $table . "`";
        $show_columns = $db->Execute($sql);
        $tableFieldsOld = explode("\n", $show_columns->fields['Create Table']);
        unset($tableFieldsOld[0]);
        unset($tableFieldsOld[count($tableFieldsOld)]);

        $sql = "SHOW CREATE TABLE `" . $table . "_temp`";
        $show_columns = $db->Execute($sql);
        $tableFieldsNew = explode("\n", $show_columns->fields['Create Table']);
        unset($tableFieldsNew[0]);
        unset($tableFieldsNew[count($tableFieldsNew)]);

        if ($tableFieldsOld !== $tableFieldsNew) {
            foreach ($tableFieldsOld as $i => $field) {
                $field = trim($field);
                if (strpos($field, '`') === 0) {
                    list($field_name, $field_parms) = $this->extractField($field);
                    $tableFieldsOld[$field_name] = $field_parms;
                }
                unset($tableFieldsOld[$i]);
            }

            foreach ($tableFieldsNew as $field) {
                $field = trim($field);
                if (strpos($field, '`') !== 0) {
                    continue;
                }
                list($field_name, $field_parms) = $this->extractField($field);
                if (!isset($tableFieldsOld[$field_name])) {
                    $this->addField($table, $field);
                } elseif ($tableFieldsOld[$field_name] !== $field_parms) {
                    $this->addField($table, $field);
                }
                unset($tableFieldsOld[$field_name]);
            }
 
            foreach ($tableFieldsOld as $field_name => $field_parms) {
                $sql = "SHOW FIELDS FROM " . $table . " LIKE '" . $field_name . "'";
                $describe = $db->Execute($sql);
                if ($describe->EOF) {
                    $sql = "ALTER TABLE `" . $table . "` DROP `" . zen_db_input($field_name) . "`;";
                    $db->Execute($sql);
                }
            }
        }
        $sql = "DROP TABLE IF EXISTS `" . $table . "_temp`";
        $db->Execute($sql);
    }

    // -----
    // Takes a field-definition string from a "SHOW CREATE TABLE" query, e.g.
    //      `coupon_type` char(1) NOT NULL DEFAULT 'F',
    // and splits the result into the field's name (`coupon_type`) and its
    // field-definition parameters.
    //
    protected function extractField($field_str)
    {
        $field_pieces = explode(' ', rtrim($field_str, "\r\n,"));
        $field = trim($field_pieces[0], '`');
        unset($field_pieces[0]);
        $field_parms = implode(' ', $field_pieces);
        return [
            $field,
            $field_parms
        ];
    }

    protected function addField($table, $field, $field_parms = null)
    {
        global $db;

        static $tableFields = [];

        if (!isset($tableFields[$table])) {
            $sql = "SHOW FIELDS FROM " . $table;
            $describe = $db->Execute($sql);
            foreach ($describe as $next_field) {
                $tableFields[$table][$next_field['Field']] = $field;
            }
        }

        $field = trim($field);
        if ($field_parms === null) {
            list($field, $field_parms) = $this->extractField($field);
        }
      //  echo '<pre>'.__LINE__.': : ';var_dump($field, $field_parms, isset($tableFields[$table][$field]));echo '</pre>';
        if (!isset($tableFields[$table][$field])) {

          $sql = "ALTER TABLE `" . $table . "` ADD `" . $field . "` " . $field_parms;
          $db->Execute($sql);
          $tableFields[$table][$field] = $field_parms;
        } else {
          $parms = $field_parms;
          $parms = strtolower($parms);
          $parms = preg_replace('@\s*\(\s*@', '(', $parms);
          $parms = preg_replace('@\s*\)@', ')', $parms);
      //    echo '<pre>'.__LINE__.': : ';var_dump($tableFields[$table][$field]);echo '</pre>';
      //    echo '<pre>'.__LINE__.': : ';var_dump($parms);echo '</pre>';
      //    echo '<pre>'.__LINE__.': : ';var_dump($tableFields[$table][$field]['Type'] . " " . "default '" . $tableFields[$table][$field]['Default'] . "'" . " " . ($tableFields[$table][$field]['Null']=="NO" ? 'not ' : '') . "null");echo '</pre>';
          if ($tableFields[$table][$field]['Type'] . " " . "default '" . $tableFields[$table][$field]['Default'] . "'" . " " . ($tableFields[$table][$field]['Null']=="NO" ? 'not ' : '') . "null" !=  $parms) {
            $sql = "ALTER TABLE `" . $table . "` CHANGE `" . $field . "` `" . $field . "` " . $field_parms;
    //        echo $sql . "</td></tr>\n";
      // ALTER TABLE `products` CHANGE `yml_country_of_origin` `yml_country_of_origin` VARCHAR(32) DEFAULT '' NOT NULL
            $db->Execute($sql);
          }
        }
    }

    public function uninstall_configuration($prefix)
    {
        global $db;

        $prefix = str_replace('_', '\_', $prefix);
        $sql =
            "SELECT DISTINCT configuration_group_id
               FROM " . TABLE_CONFIGURATION . "
              WHERE configuration_key LIKE ':prefix:%'";
        $sql = $db->bindVars($sql, ':prefix:', $prefix, 'noquotestring');
        $configuration_group_id = $db->Execute($sql);
        if ($configuration_group_id->RecordCount() > 1) {
            return false;
        }
        if (!$configuration_group_id->EOF) {
            $sql = "DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_group_id = :configuration_group_id:";
            $sql = $db->bindVars($sql, ':configuration_group_id:', $configuration_group_id->fields['configuration_group_id'], 'integer');
            $db->Execute($sql);
            $sql = "DELETE FROM " . TABLE_CONFIGURATION_GROUP . " WHERE configuration_group_id = :configuration_group_id: LIMIT 1";
            $sql = $db->bindVars($sql, ':configuration_group_id:', $configuration_group_id->fields['configuration_group_id'], 'integer');
            $db->Execute($sql);
        }
        return true;
    }

    public function install_admin_pages($page)
    {
        if (zen_page_key_exists($page['page_key']) === true) {
            return false;
        }
        zen_register_admin_page($page['page_key'], $page['language_key'], $page['main_page'], $page['page_params'], $page['menu_key'], $page['display_on_menu']);
        return true;
    }

    public function uninstall_admin_pages($page)
    {
        zen_deregister_admin_pages($page);
    }
}

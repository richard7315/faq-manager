<?php

use Zencart\PluginSupport\ScriptedInstaller as ScriptedInstallBase;

class ScriptedInstaller extends ScriptedInstallBase
{
    protected function executeInstall(): bool
    {
        zen_deregister_admin_pages(['toolsZxFAQManager']);
        zen_register_admin_page('toolsZxFAQManager', 'BOX_TOOLS_FAQ_MANAGER', 'FILENAME_FAQ_MANAGER', '', 'tools', 'Y', 20);

        zen_define_default('TABLE_FAQ_CATEGORIES', DB_PREFIX . 'faq_categories');
        zen_define_default('TABLE_FAQ_CATEGORIES_DESCRIPTION', DB_PREFIX . 'faq_categories_description');
        zen_define_default('TABLE_FAQ_ITEMS', DB_PREFIX . 'faq_items');
        zen_define_default('TABLE_FAQ_ITEM_DESCRIPTIONS', DB_PREFIX . 'faq_item_descriptions');

        $sql = "CREATE TABLE IF NOT EXISTS ".TABLE_FAQ_ITEMS." (
              faq_id int(11) NOT NULL AUTO_INCREMENT,
              sort_order int(3) DEFAULT '0',
              status tinyint(1) DEFAULT '1',
              faq_category_id int(11) DEFAULT '0',
              date_added datetime DEFAULT NULL,
              last_modified datetime DEFAULT NULL,
              PRIMARY KEY (faq_id),
              KEY idx_status (status)
            ) ENGINE=MyISAM;";
        $this->executeInstallerSql($sql);

        $sql = "CREATE TABLE IF NOT EXISTS ".TABLE_FAQ_ITEM_DESCRIPTIONS." (
              faq_id int(11) NOT NULL DEFAULT '0',
              language_id int(11) NOT NULL DEFAULT '1',
              faq_title varchar(255) NOT NULL DEFAULT '',
              faq_content mediumtext NOT NULL,
              PRIMARY KEY (faq_id, language_id)
            ) ENGINE=MyISAM;";
        $this->executeInstallerSql($sql);

        $sql = "CREATE TABLE IF NOT EXISTS ".TABLE_FAQ_CATEGORIES." (
              faq_categories_id int(11) NOT NULL AUTO_INCREMENT,
              sort_order int(3) DEFAULT '0',
              date_added datetime DEFAULT NULL,
              last_modified datetime DEFAULT NULL,
              PRIMARY KEY (faq_categories_id)
            ) ENGINE=MyISAM;";
        $this->executeInstallerSql($sql);

        $sql = "CREATE TABLE IF NOT EXISTS ".TABLE_FAQ_CATEGORIES_DESCRIPTION." (
              faq_categories_id int(11) NOT NULL DEFAULT '0',
              language_id int(11) NOT NULL DEFAULT '1',
              faq_categories_name varchar(255) NOT NULL DEFAULT '',
              PRIMARY KEY (faq_categories_id, language_id)
            ) ENGINE=MyISAM;";
        $this->executeInstallerSql($sql);

        $sql = "INSERT INTO ".TABLE_FAQ_CATEGORIES." (sort_order, date_added) VALUES (10, now());";
        $this->executeInstallerSql($sql);
        $type_id = (int)$this->dbConn->insert_ID();

        $language_id = $_SESSION['languages_id'] ?? 1;
        $sql = "INSERT INTO ".TABLE_FAQ_CATEGORIES_DESCRIPTION." (faq_categories_id, language_id, faq_categories_name)
VALUES ($type_id, $language_id, 'General Questions');";

        $this->executeInstallerSql($sql);

        return true;
    }

    protected function executeUninstall(): bool
    {
        zen_deregister_admin_pages(['toolsZxFAQManager']);

        return true;
    }
}

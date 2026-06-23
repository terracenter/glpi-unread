<?php
/**
 * GLPI Plugin Unread - Installation and Uninstallation Hooks
 * Database table creation and destruction
 *
 * @license GPLv3+
 * @author Freddy Taborda & Team
 */

function plugin_terracenter_unread_tracker_install()
{
    global $DB;

    // Create table using Migration class for GLPI compatibility
    $migration = new Migration(PLUGIN_TERRACENTER_UNREAD_TRACKER_VERSION);

    if (!$DB->tableExists('glpi_plugin_terracenter_unread_tracker_read')) {
        $sql = "
        CREATE TABLE `glpi_plugin_terracenter_unread_tracker_read` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `tickets_id` INT NOT NULL,
            `users_id` INT NOT NULL,
            `date_read` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_ticket_user` (`tickets_id`, `users_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";

        $DB->queryOrDie($sql, "Failed to create glpi_plugin_terracenter_unread_tracker_read table");
    }

    return true;
}

function plugin_terracenter_unread_tracker_uninstall()
{
    global $DB;

    // Drop the table if it exists
    if ($DB->tableExists('glpi_plugin_terracenter_unread_tracker_read')) {
        $sql = "DROP TABLE IF EXISTS `glpi_plugin_terracenter_unread_tracker_read`;";
        $DB->queryOrDie($sql, "Failed to drop glpi_plugin_terracenter_unread_tracker_read table");
    }

    return true;
}

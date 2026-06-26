<?php
/**
 * GLPI Plugin Unread - Installation and Uninstallation Hooks
 * Database table creation and destruction
 *
 * @license GPLv3+
 * @author Freddy Taborda & Team
 */

use GlpiPlugin\Unreadtracker\Tracking;

function plugin_unreadtracker_install()
{
    global $DB;

    if (!$DB->tableExists('glpi_plugin_unreadtracker_read')) {
        $sql = "
        CREATE TABLE `glpi_plugin_unreadtracker_read` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `tickets_id` INT NOT NULL,
            `users_id` INT NOT NULL,
            `date_read` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `uniq_ticket_user` (`tickets_id`, `users_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        $DB->queryOrDie($sql, "Failed to create glpi_plugin_unreadtracker_read table");
    }

    return true;
}

function plugin_unreadtracker_uninstall()
{
    global $DB;

    if ($DB->tableExists('glpi_plugin_unreadtracker_read')) {
        $DB->queryOrDie(
            "DROP TABLE IF EXISTS `glpi_plugin_unreadtracker_read`",
            "Failed to drop glpi_plugin_unreadtracker_read table"
        );
    }

    return true;
}

function plugin_unreadtracker_item_update($item)
{
    global $DB;

    if (!($item instanceof Ticket)) {
        return;
    }

    $tickets_id = (int) $item->getID();
    if ($tickets_id <= 0) {
        return;
    }

    $DB->delete(Tracking::getTable(), ['tickets_id' => $tickets_id]);
}

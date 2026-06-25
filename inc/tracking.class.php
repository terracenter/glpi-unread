<?php
/**
 * GLPI Plugin Unread - Tracking Class
 * Handles read/unread logic and database interaction
 *
 * @license GPLv3+
 * @author Freddy Taborda & Team
 */

class PluginUnreadtrackerTracking extends CommonDBTM
{
    public const RIGHTNAME = 'plugin_unreadtracker_tracking';

    public static function getTypeName($nb = 0)
    {
        return _n('Unread Tracker', 'Unread Trackers', $nb, 'unreadtracker');
    }

    public static function markAsRead($tickets_id, $users_id)
    {
        global $DB;

        if (!$tickets_id || !$users_id) {
            return false;
        }

        $sql = "
        INSERT INTO `glpi_plugin_unreadtracker_read` (`tickets_id`, `users_id`, `date_read`)
        VALUES ($tickets_id, $users_id, NOW())
        ON DUPLICATE KEY UPDATE `date_read` = NOW();
        ";

        return $DB->queryOrDie($sql, "Failed to mark ticket as read");
    }

    public static function isUnread($tickets_id, $users_id)
    {
        global $DB;

        if (!$tickets_id || !$users_id) {
            return false;
        }

        // A ticket is unread if:
        // 1. No record exists for (tickets_id, users_id) pair
        // 2. OR ticket's date_mod > the recorded date_read
        $sql = "
        SELECT 1
        FROM `glpi_tickets` t
        LEFT JOIN `glpi_plugin_unreadtracker_read` ur
            ON ur.`tickets_id` = t.`id`
            AND ur.`users_id` = $users_id
        WHERE t.`id` = $tickets_id
            AND (ur.`id` IS NULL OR t.`date_mod` > ur.`date_read`)
        LIMIT 1;
        ";

        $result = $DB->query($sql);
        return $DB->numrows($result) > 0;
    }

    public static function getUnreadCountForUser($users_id)
    {
        global $DB;

        if (!$users_id) {
            return 0;
        }

        // Count tickets assigned to user that are unread
        $sql = "
        SELECT COUNT(DISTINCT t.`id`) as unread_count
        FROM `glpi_tickets` t
        INNER JOIN `glpi_tickets_users` tu
            ON tu.`tickets_id` = t.`id`
            AND tu.`users_id` = $users_id
            AND tu.`type` = 2
        LEFT JOIN `glpi_plugin_unreadtracker_read` ur
            ON ur.`tickets_id` = t.`id`
            AND ur.`users_id` = $users_id
        WHERE (ur.`id` IS NULL OR t.`date_mod` > ur.`date_read`)
        ;
        ";

        $result = $DB->query($sql);
        $row = $DB->fetchAssoc($result);
        return (int)($row['unread_count'] ?? 0);
    }

    public static function displayCentral()
    {
        echo '<div id="unread-badge-container" style="display:inline;"></div>';
    }
}

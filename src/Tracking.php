<?php

namespace GlpiPlugin\Unreadtracker;

use CommonDBTM;

class Tracking extends CommonDBTM
{
    public const RIGHTNAME = 'plugin_unreadtracker_tracking';

    public static function getTypeName($nb = 0)
    {
        return _n('Unread Tracker', 'Unread Trackers', $nb, 'unreadtracker');
    }

    public static function getTable($classname = null): string
    {
        return 'glpi_plugin_unreadtracker_read';
    }

    public static function markAsRead(int $tickets_id, int $users_id): bool
    {
        global $DB;

        if (!$tickets_id || !$users_id) {
            return false;
        }

        $now = date('Y-m-d H:i:s');

        $existing = $DB->request([
            'FROM'  => self::getTable(),
            'WHERE' => ['tickets_id' => $tickets_id, 'users_id' => $users_id],
            'LIMIT' => 1,
        ]);

        if (count($existing) > 0) {
            return (bool) $DB->update(
                self::getTable(),
                ['date_read' => $now],
                ['tickets_id' => $tickets_id, 'users_id' => $users_id]
            );
        }

        return (bool) $DB->insert(
            self::getTable(),
            ['tickets_id' => $tickets_id, 'users_id' => $users_id, 'date_read' => $now]
        );
    }

    public static function isUnread(int $tickets_id, int $users_id): bool
    {
        global $DB;

        if (!$tickets_id || !$users_id) {
            return false;
        }

        // JOIN + OR condition no expresable limpiamente en DBmysqlIterator; $users_id es int validado
        $table = self::getTable();
        $sql = "
            SELECT 1
            FROM `glpi_tickets` t
            LEFT JOIN `{$table}` ur
                ON ur.`tickets_id` = t.`id`
                AND ur.`users_id` = {$users_id}
            WHERE t.`id` = {$tickets_id}
                AND (ur.`id` IS NULL OR t.`date_mod` > ur.`date_read`)
            LIMIT 1
        ";

        $result = $DB->query($sql);
        return $DB->numrows($result) > 0;
    }

    public static function getUnreadCountForUser(int $users_id): int
    {
        global $DB;

        if (!$users_id) {
            return 0;
        }

        // COUNT + INNER JOIN + LEFT JOIN con OR: $users_id es int validado
        $table = self::getTable();
        $sql = "
            SELECT COUNT(DISTINCT t.`id`) AS `unread_count`
            FROM `glpi_tickets` t
            INNER JOIN `glpi_tickets_users` tu
                ON tu.`tickets_id` = t.`id`
                AND tu.`users_id` = {$users_id}
                AND tu.`type` = 2
            LEFT JOIN `{$table}` ur
                ON ur.`tickets_id` = t.`id`
                AND ur.`users_id` = {$users_id}
            WHERE (ur.`id` IS NULL OR t.`date_mod` > ur.`date_read`)
        ";

        $result = $DB->query($sql);
        $row = $DB->fetchAssoc($result);
        return (int) ($row['unread_count'] ?? 0);
    }

    public static function getUnreadIdsForUser(int $users_id): array
    {
        global $DB;

        if (!$users_id) {
            return [];
        }

        // INNER JOIN + LEFT JOIN con OR: $users_id es int validado
        $table = self::getTable();
        $sql = "
            SELECT DISTINCT t.`id`
            FROM `glpi_tickets` t
            INNER JOIN `glpi_tickets_users` tu
                ON tu.`tickets_id` = t.`id`
                AND tu.`users_id` = {$users_id}
                AND tu.`type` = 2
            LEFT JOIN `{$table}` ur
                ON ur.`tickets_id` = t.`id`
                AND ur.`users_id` = {$users_id}
            WHERE (ur.`id` IS NULL OR t.`date_mod` > ur.`date_read`)
        ";

        $result = $DB->query($sql);
        $ids = [];
        while ($row = $DB->fetchAssoc($result)) {
            $ids[] = (int) $row['id'];
        }
        return $ids;
    }

    public static function displayCentral(): void
    {
        echo '<div id="unread-badge-container" style="display:inline;"></div>';
    }
}

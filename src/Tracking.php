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

    public static function getUnreadStatsAndTickets(int $users_id): array
    {
        global $DB;

        if (!$users_id) {
            return [
                'stats' => ['total' => 0, 'new' => 0, 'updated' => 0, 'overdue' => 0],
                'tickets' => []
            ];
        }

        $table = self::getTable();
        $now = date('Y-m-d H:i:s');

        $sql = "
            SELECT DISTINCT t.`id`, t.`name`, t.`priority`, t.`date_mod`, t.`status`, t.`solve_delay_limit`, ur.`date_read`
            FROM `glpi_tickets` t
            INNER JOIN `glpi_tickets_users` tu
                ON tu.`tickets_id` = t.`id`
                AND tu.`users_id` = {$users_id}
                AND tu.`type` = 2
            LEFT JOIN `{$table}` ur
                ON ur.`tickets_id` = t.`id`
                AND ur.`users_id` = {$users_id}
            WHERE t.`status` < 5 
                AND (ur.`id` IS NULL OR t.`date_mod` > ur.`date_read`)
            ORDER BY t.`priority` DESC, t.`date_mod` DESC
        ";

        $result = $DB->query($sql);
        $tickets = [];
        $stats = [
            'total'   => 0,
            'new'     => 0,
            'updated' => 0,
            'overdue' => 0
        ];

        while ($row = $DB->fetchAssoc($result)) {
            $status = (int) $row['status'];
            $priority = (int) $row['priority'];
            
            $is_new = ($status === 1);
            $is_overdue = (!empty($row['solve_delay_limit']) && $row['solve_delay_limit'] < $now);
            $is_updated = (!$is_new && !empty($row['date_read']) && $row['date_mod'] > $row['date_read']);

            // Si es un ticket asignado no leído que no califica de nuevo ni actualizado, se cuenta como actualizado por defecto (acción pendiente)
            if (!$is_new && !$is_updated) {
                $is_updated = true;
            }

            $ticket_data = [
                'id'                => (int) $row['id'],
                'name'              => $row['name'],
                'priority'          => $priority,
                'date_mod'          => $row['date_mod'],
                'status'            => $status,
                'solve_delay_limit' => $row['solve_delay_limit'],
                'is_new'            => $is_new,
                'is_updated'        => $is_updated,
                'is_overdue'        => $is_overdue
            ];

            $tickets[] = $ticket_data;
            
            $stats['total']++;
            if ($is_new) $stats['new']++;
            if ($is_updated) $stats['updated']++;
            if ($is_overdue) $stats['overdue']++;
        }

        return [
            'stats'   => $stats,
            'tickets' => $tickets
        ];
    }

    public static function displayCentral(): void
    {
        echo '<div id="unread-badge-container" style="display:inline;"></div>';
    }
}

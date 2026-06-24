<?php
/**
 * Get list of unread ticket IDs for current user via AJAX
 * GET endpoint
 */

include('../../../inc/includes.php');
Session::checkLoginUser();
header('Content-Type: application/json');

global $DB;

$users_id = (int) $_SESSION['glpiID'];

$sql = "
SELECT DISTINCT t.`id`
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
$ids = [];

while ($row = $DB->fetchAssoc($result)) {
    $ids[] = (int) $row['id'];
}

echo json_encode(['ids' => $ids]);

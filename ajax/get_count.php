<?php
/**
 * Get unread count for current user via AJAX
 * GET endpoint
 */

include('../../../inc/includes.php');
Session::checkLoginUser();
header('Content-Type: application/json');

$users_id = (int) $_SESSION['glpiID'];
$count = PluginUnreadTracking::getUnreadCountForUser($users_id);

echo json_encode(['count' => $count]);

<?php
/**
 * Get unread count for current user via AJAX
 * GET endpoint
 */

include('../../../inc/includes.php');
Session::checkLoginUser();
header('Content-Type: application/json');

use GlpiPlugin\Unreadtracker\Tracking;

$users_id = (int) $_SESSION['glpiID'];
$count = Tracking::getUnreadCountForUser($users_id);

echo json_encode(['count' => $count]);

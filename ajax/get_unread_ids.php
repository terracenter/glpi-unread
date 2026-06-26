<?php
/**
 * Get list of unread ticket IDs for current user via AJAX
 * GET endpoint
 */

include('../../../inc/includes.php');
Session::checkLoginUser();
header('Content-Type: application/json');

use GlpiPlugin\Unreadtracker\Tracking;

$users_id = (int) $_SESSION['glpiID'];
$ids = Tracking::getUnreadIdsForUser($users_id);

echo json_encode(['ids' => $ids]);

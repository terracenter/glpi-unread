<?php
/**
 * Get unread tickets details and stats for current user via AJAX
 * GET endpoint
 */

include('../../../inc/includes.php');
Session::checkLoginUser();
header('Content-Type: application/json');

use GlpiPlugin\Unreadtracker\Tracking;

$users_id = (int) $_SESSION['glpiID'];
$result = Tracking::getUnreadStatsAndTickets($users_id);

echo json_encode($result);

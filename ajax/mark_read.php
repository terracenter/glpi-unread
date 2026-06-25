<?php
/**
 * Mark ticket as read via AJAX
 * POST endpoint
 */

include('../../../inc/includes.php');
Session::checkLoginUser();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!Session::checkToken()) {
    http_response_code(403);
    echo json_encode(['error' => 'invalid_token']);
    exit;
}

$tickets_id = (int) ($_POST['tickets_id'] ?? 0);
if ($tickets_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid_id']);
    exit;
}

$users_id = (int) $_SESSION['glpiID'];

if (PluginUnreadtrackerTracking::markAsRead($tickets_id, $users_id)) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to mark as read']);
}

<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(0); }
require_once __DIR__ . '/db.php';

$user = getAuthUser($conn);
if (!$user) { sendResponse(true, 'OK', ['reviewed_booking_ids' => []]); }

$stmt = $conn->prepare('SELECT booking_id FROM reviews WHERE user_id = ?');
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$res = $stmt->get_result();
$ids = [];
while ($row = $res->fetch_assoc()) $ids[] = intval($row['booking_id']);

sendResponse(true, 'OK', ['reviewed_booking_ids' => $ids]);
?>

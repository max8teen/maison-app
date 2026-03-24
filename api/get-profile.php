<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(0); }
require_once __DIR__ . '/db.php';

$user = getAuthUser($conn);
if (!$user) sendResponse(false, 'Unauthorized');

$stmt = $conn->prepare('SELECT id, full_name, email, phone, role, gender, dob, created_at FROM users WHERE id = ?');
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

sendResponse(true, 'OK', ['user' => $data]);
?>

<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(0); }
require_once __DIR__ . '/../../api/db.php';
$input = getJsonInput();
$user = getAuthUser($conn);
if (!$user || $user['role'] !== 'admin') sendResponse(false, 'Unauthorized');
$result = $conn->query("SELECT * FROM reservations ORDER BY reservation_date ASC, reservation_time ASC");
$reservations = [];
while ($row = $result->fetch_assoc()) $reservations[] = $row;
sendResponse(true, 'Loaded', ['reservations' => $reservations]);

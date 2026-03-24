<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);
require_once 'db.php';

$user = getAuthUser($conn);
if (!$user) sendResponse(false, 'Please login first');

$userId = $user['id'];
$result = $conn->query("SELECT * FROM reservations WHERE user_id=$userId ORDER BY reservation_date DESC, reservation_time DESC");
$bookings = [];
while ($row = $result->fetch_assoc()) $bookings[] = $row;
sendResponse(true, 'Bookings loaded', ['bookings' => $bookings]);

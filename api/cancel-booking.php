<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);
require_once 'db.php';

$user = getAuthUser($conn);
if (!$user) sendResponse(false, 'Please login first');

$input     = getJsonInput();  // uses cached input — safe
$bookingId = (int)($input['booking_id'] ?? 0);
if (!$bookingId) sendResponse(false, 'Invalid booking');

$userId = $user['id'];
$check  = $conn->query("SELECT id FROM reservations WHERE id=$bookingId AND user_id=$userId");
if ($check->num_rows === 0) sendResponse(false, 'Booking not found');

$conn->query("UPDATE reservations SET status='cancelled' WHERE id=$bookingId");
sendResponse(true, 'Booking cancelled');

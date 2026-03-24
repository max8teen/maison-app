<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(0); }
require_once __DIR__ . '/db.php';

$user = getAuthUser($conn);
if (!$user) sendResponse(false, 'Unauthorized');

$input          = getJsonInput();
$bookingId      = intval($input['booking_id']      ?? 0);
$rating         = intval($input['rating']          ?? 0);
$foodRating     = !empty($input['food_rating'])     ? intval($input['food_rating'])     : null;
$serviceRating  = !empty($input['service_rating'])  ? intval($input['service_rating'])  : null;
$ambienceRating = !empty($input['ambience_rating']) ? intval($input['ambience_rating']) : null;
$comment        = trim($input['comment'] ?? '');

if (!$bookingId)                        sendResponse(false, 'Invalid booking');
if ($rating < 1 || $rating > 5)         sendResponse(false, 'Invalid overall rating');
if (strlen($comment) > 300)             sendResponse(false, 'Comment must be under 300 characters');

// Validate category ratings if provided
foreach (['food'=>$foodRating,'service'=>$serviceRating,'ambience'=>$ambienceRating] as $k=>$v) {
    if ($v !== null && ($v < 1 || $v > 5)) sendResponse(false, 'Invalid '.$k.' rating');
}

// Booking must belong to this user and be completed
$stmt = $conn->prepare('SELECT id, status FROM reservations WHERE id = ? AND user_id = ?');
$stmt->bind_param('ii', $bookingId, $user['id']);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
if (!$booking)                           sendResponse(false, 'Booking not found');
if ($booking['status'] !== 'completed')  sendResponse(false, 'You can only review a completed dining experience');

// No duplicate
$stmt2 = $conn->prepare('SELECT id FROM reviews WHERE booking_id = ? AND user_id = ?');
$stmt2->bind_param('ii', $bookingId, $user['id']);
$stmt2->execute();
if ($stmt2->get_result()->fetch_assoc()) sendResponse(false, 'You have already reviewed this booking');

// Insert — comment is optional (empty string is fine)
$commentVal = $comment !== '' ? $comment : null;
$stmt3 = $conn->prepare("
    INSERT INTO reviews (user_id, booking_id, rating, food_rating, service_rating, ambience_rating, comment, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
");
$stmt3->bind_param('iiiiiss', $user['id'], $bookingId, $rating, $foodRating, $serviceRating, $ambienceRating, $commentVal);
if (!$stmt3->execute()) sendResponse(false, 'Failed to submit review');

sendResponse(true, 'Review submitted! It will appear after admin approval.');
?>

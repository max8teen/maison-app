<?php
// ─── create-booking.php ───────────────────────────────────────────────────────
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

require_once 'db.php';
require_once 'email-templates.php';

$user = getAuthUser($conn);
if (!$user) sendResponse(false, 'Please login first');

$input    = getJsonInput();
$name     = clean($conn, $input['guest_name']       ?? '');
$email    = clean($conn, $input['guest_email']      ?? '');
$phone    = clean($conn, $input['guest_phone']      ?? '');
$date     = clean($conn, $input['reservation_date'] ?? '');
$time     = clean($conn, $input['reservation_time'] ?? '');
$party    = (int)($input['party_size']              ?? 2);
$occasion = clean($conn, $input['occasion']         ?? 'none');
$requests = clean($conn, $input['special_requests'] ?? '');
$tableId  = (int)($input['table_id']               ?? 0);
$userId   = $user['id'];

if (!$name || !$email || !$date || !$time)
    sendResponse(false, 'Name, email, date and time are required');

// ── If table selected, verify still available (race condition protection) ─────
$tableNumber  = null;
$tableLocation = null;
if ($tableId > 0) {
    // Check no conflict
    $conflict = $conn->query("
        SELECT id FROM reservations
        WHERE table_id = $tableId
          AND reservation_date = '$date'
          AND status NOT IN ('cancelled','no_show')
          AND ABS(TIME_TO_SEC(TIMEDIFF(reservation_time, '$time'))) < 7200
        LIMIT 1
    ");
    if ($conflict && $conflict->num_rows > 0) {
        sendResponse(false, 'Sorry, that table was just booked. Please go back and select another.');
    }
    // Get table details for email
    $tRow = $conn->query("SELECT table_number, location FROM restaurant_tables WHERE id = $tableId LIMIT 1");
    if ($tRow && $tRow->num_rows > 0) {
        $t = $tRow->fetch_assoc();
        $tableNumber   = $t['table_number'];
        $tableLocation = $t['location'];
    }
}

$ref = generateBookingRef($conn);

// Build insert — table_id is NULL if not selected
$tableIdSql = $tableId > 0 ? $tableId : 'NULL';

$conn->query("INSERT INTO reservations
    (booking_ref,user_id,guest_name,guest_email,guest_phone,reservation_date,reservation_time,party_size,occasion,special_requests,status,table_id)
    VALUES ('$ref',$userId,'$name','$email','$phone','$date','$time',$party,'$occasion','$requests','confirmed',$tableIdSql)");

if ($conn->error) sendResponse(false, 'Booking failed: ' . $conn->error);

// Send confirmation email (with table info if selected)
sendBookingConfirmation($email, $name, $ref, $date, $time, $party, $occasion, $tableNumber, $tableLocation);

sendResponse(true, 'Booking confirmed', [
    'booking_ref'    => $ref,
    'table_number'   => $tableNumber,
    'table_location' => $tableLocation,
]);

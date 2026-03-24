<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(0); }
require_once __DIR__ . '/db.php';

$user = getAuthUser($conn);
if (!$user) sendResponse(false, 'Unauthorized');

$input    = getJsonInput();
$fullName = trim($input['full_name'] ?? '');
$phone    = trim($input['phone']     ?? '');
$gender   = trim($input['gender']    ?? '');
$dob      = trim($input['dob']       ?? '');

if (empty($fullName)) sendResponse(false, 'Full name is required');

// Build query dynamically
$fields = ['full_name = ?', 'phone = ?', 'gender = ?'];
$params = [$fullName, $phone, $gender];
$types  = 'sss';

if (!empty($dob)) {
    $fields[] = 'dob = ?';
    $params[]  = $dob;
    $types    .= 's';
} else {
    $fields[] = 'dob = NULL';
}

$params[] = $user['id'];
$types   .= 'i';

$sql  = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?';
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);

if (!$stmt->execute()) sendResponse(false, 'Failed to update profile');

// Return updated user
$stmt2 = $conn->prepare('SELECT id, full_name, email, phone, role, gender, dob FROM users WHERE id = ?');
$stmt2->bind_param('i', $user['id']);
$stmt2->execute();
$updated = $stmt2->get_result()->fetch_assoc();

sendResponse(true, 'Profile updated', ['user' => $updated]);
?>

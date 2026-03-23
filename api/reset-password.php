<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Invalid request');
}

$input    = getJsonInput();
$email    = clean($conn, $input['email'] ?? '');
$password = $input['password'] ?? '';

if (empty($email) || empty($password)) {
    sendResponse(false, 'Email and password are required');
}

if (strlen($password) < 6) {
    sendResponse(false, 'Password must be at least 6 characters');
}

$result = $conn->query("SELECT id FROM users WHERE email = '$email' LIMIT 1");

if ($result->num_rows === 0) {
    sendResponse(false, 'Account not found');
}

$hashed = password_hash($password, PASSWORD_DEFAULT);
$conn->query("UPDATE users SET password = '$hashed' WHERE email = '$email'");

sendResponse(true, 'Password reset successfully');

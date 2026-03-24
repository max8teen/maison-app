<?php
// ─── send-otp.php ─────────────────────────────────────────────────────────────
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db.php';
require_once 'email-templates.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') sendResponse(false, 'Invalid request method');

$input   = getJsonInput();
$email   = clean($conn, $input['email']   ?? '');
$purpose = clean($conn, $input['purpose'] ?? 'register');

if (empty($email))                            sendResponse(false, 'Email is required');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) sendResponse(false, 'Invalid email address');

$result = $conn->query("SELECT id, full_name FROM users WHERE email = '$email' LIMIT 1");
$exists = $result->num_rows > 0;
$row    = $exists ? $result->fetch_assoc() : null;

if ($purpose === 'register'       && $exists)  sendResponse(false, 'An account with this email already exists');
if ($purpose === 'reset_password' && !$exists) sendResponse(false, 'No account found with this email');

$otp     = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
$expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));

$conn->query("DELETE FROM otp_tokens WHERE email = '$email' AND purpose = '$purpose'");

if (!$conn->query("INSERT INTO otp_tokens (email, code, purpose, expires_at) VALUES ('$email', '$otp', '$purpose', '$expires')"))
    sendResponse(false, 'Failed to generate OTP. Please try again.');

// Get name — from DB if existing user, from request if new registration
$name = $row ? $row['full_name'] : ($input['full_name'] ?? 'Guest');

if ($purpose === 'register') {
    sendWelcomeOTP($email, $name, $otp);
} else if ($purpose === 'reset_password') {
    sendPasswordResetOTP($email, $name, $otp);
}

sendResponse(true, 'OTP sent to your email');

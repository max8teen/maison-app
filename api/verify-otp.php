<?php
// ─── verify-otp.php ───────────────────────────────────────────────────────────
// Called by: register.html (after user enters OTP)
// What it does:
//   1. Checks if OTP is correct
//   2. Checks if OTP is not expired (within 10 minutes)
//   3. Creates new user account in database
//   4. Returns user data + token (same as login)

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Invalid request method');
}

// Get data from JavaScript
// JavaScript sends: { email, otp, full_name, phone, password, purpose }
$input    = getJsonInput();
$email    = clean($conn, $input['email'] ?? '');
$otp      = clean($conn, $input['otp'] ?? '');
$purpose  = clean($conn, $input['purpose'] ?? 'register');

if (empty($email) || empty($otp)) {
    sendResponse(false, 'Email and OTP are required');
}

// Check OTP in database
// Must match email, code, purpose, not used, and not expired
$now = date('Y-m-d H:i:s');
$sql = "SELECT * FROM otp_tokens 
        WHERE email = '$email' 
        AND code = '$otp' 
        AND purpose = '$purpose'
        AND used = 0 
        AND expires_at > '$now'
        LIMIT 1";

$result = $conn->query($sql);

if ($result->num_rows === 0) {
    sendResponse(false, 'Invalid or expired OTP. Please try again.');
}

$otpRow = $result->fetch_assoc();

// Mark OTP as used so it can't be reused
$conn->query("UPDATE otp_tokens SET used = 1 WHERE id = {$otpRow['id']}");

// ── If registering: create new user account ───────────────────────────────────
if ($purpose === 'register') {
    $fullName = clean($conn, $input['full_name'] ?? '');
    $phone    = clean($conn, $input['phone'] ?? '');
    $password = $input['password'] ?? '';

    if (empty($fullName) || empty($password)) {
        sendResponse(false, 'Name and password are required');
    }

    // Hash the password before saving (NEVER save plain text password)
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user into database
    $sql = "INSERT INTO users (full_name, email, phone, password, role) 
            VALUES ('$fullName', '$email', '$phone', '$hashedPassword', 'customer')";

    if (!$conn->query($sql)) {
        sendResponse(false, 'Failed to create account. Email may already exist.');
    }

    // Get the new user's ID
    $newUserId = $conn->insert_id;
    $token     = 'tok-' . $newUserId;

    sendResponse(true, 'Account created successfully', [
        'token' => $token,
        'user'  => [
            'id'        => $newUserId,
            'full_name' => $fullName,
            'email'     => $email,
            'phone'     => $phone,
            'role'      => 'customer'
        ]
    ]);
}

// ── If resetting password: just confirm OTP is valid ─────────────────────────
if ($purpose === 'reset_password') {
    sendResponse(true, 'OTP verified successfully', [
        'email' => $email
    ]);
}

sendResponse(false, 'Invalid purpose');

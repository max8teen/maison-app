<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db.php';

// Read from GET, POST or JSON — whichever has data
if (!empty($_GET['email'])) {
    $full_name = clean($conn, $_GET['full_name'] ?? '');
    $email     = clean($conn, $_GET['email'] ?? '');
    $phone     = clean($conn, $_GET['phone'] ?? '');
    $password  = $_GET['password'] ?? '';
} elseif (!empty($_POST['email'])) {
    $full_name = clean($conn, $_POST['full_name'] ?? '');
    $email     = clean($conn, $_POST['email'] ?? '');
    $phone     = clean($conn, $_POST['phone'] ?? '');
    $password  = $_POST['password'] ?? '';
} else {
    $input     = getJsonInput();
    $full_name = clean($conn, $input['full_name'] ?? '');
    $email     = clean($conn, $input['email'] ?? '');
    $phone     = clean($conn, $input['phone'] ?? '');
    $password  = $input['password'] ?? '';
}

if (empty($full_name) || empty($email) || empty($password)) {
    sendResponse(false, 'Name, email and password are required');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendResponse(false, 'Invalid email address');
}

if (strlen($password) < 6) {
    sendResponse(false, 'Password must be at least 6 characters');
}

$check = $conn->query("SELECT id FROM users WHERE email = '$email' LIMIT 1");
if ($check->num_rows > 0) {
    sendResponse(false, 'Email already registered');
}

$hashed = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO users (full_name, email, phone, password, role) 
        VALUES ('$full_name', '$email', '$phone', '$hashed', 'customer')";

if ($conn->query($sql)) {
    $userId = $conn->insert_id;
    $token  = 'tok-' . $userId;
    sendResponse(true, 'Account created successfully', [
        'token' => $token,
        'user'  => [
            'id'        => $userId,
            'full_name' => $full_name,
            'email'     => $email,
            'phone'     => $phone,
            'role'      => 'customer',
        ]
    ]);
} else {
    sendResponse(false, 'Registration failed: ' . $conn->error);
}

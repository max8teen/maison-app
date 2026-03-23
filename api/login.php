<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendResponse(false, 'Invalid request method');
}

// Read from GET, POST or JSON body — whichever has data
if (!empty($_GET['email'])) {
    $email    = clean($conn, $_GET['email'] ?? '');
    $password = $_GET['password'] ?? '';
} elseif (!empty($_POST['email'])) {
    $email    = clean($conn, $_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
} else {
    $input    = getJsonInput();
    $email    = clean($conn, $input['email'] ?? '');
    $password = $input['password'] ?? '';
}

if (empty($email) || empty($password)) {
    sendResponse(false, 'Email and password are required');
}

$sql    = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    sendResponse(false, 'Invalid email or password');
}

$user = $result->fetch_assoc();

if (!password_verify($password, $user['password'])) {
    sendResponse(false, 'Invalid email or password');
}

$token = 'tok-' . $user['id'];

sendResponse(true, 'Login successful', [
    'token' => $token,
    'user'  => [
        'id'        => $user['id'],
        'full_name' => $user['full_name'],
        'email'     => $user['email'],
        'phone'     => $user['phone'],
        'role'      => $user['role'],
    ]
]);
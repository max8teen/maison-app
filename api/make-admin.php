<?php
// ONE-TIME USE: Run this once then DELETE IT
// Visit: https://maisonn.free.nf/api/make-admin.php?email=YOUR_EMAIL&key=maison2026
header('Content-Type: text/plain');

$key = $_GET['key'] ?? '';
if ($key !== 'maison2026') {
    die('Wrong key.');
}

$email = $_GET['email'] ?? '';
if (!$email) {
    die('Provide ?email=your@email.com&key=maison2026');
}

require_once 'db.php';
$email = $conn->real_escape_string($email);
$result = $conn->query("UPDATE users SET role='admin' WHERE email='$email'");
if ($conn->affected_rows > 0) {
    echo "SUCCESS: $email is now admin. DELETE this file now!";
} else {
    echo "No user found with email: $email  (or already admin)";
}

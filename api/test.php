<?php
// Upload this to htdocs/maison/api/test.php
// Then visit: yourdomain/maison/api/test.php
// It will show exactly what's wrong

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>PHP Test</h2>";
echo "PHP Version: " . phpversion() . "<br>";

// Test DB connection
define('DB_HOST', 'sql210.infinityfree.com');
define('DB_USER', 'if0_41341897');
define('DB_PASS', 'UD007mohit');
define('DB_NAME', 'if0_41341897_riyasat');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    echo "<b style='color:red'>DB FAILED: " . $conn->connect_error . "</b>";
} else {
    echo "<b style='color:green'>DB CONNECTED OK</b><br>";
    $r = $conn->query("SELECT COUNT(*) as total FROM users");
    $row = $r->fetch_assoc();
    echo "Users in database: " . $row['total'];
}

// Test getallheaders
echo "<br><br>getallheaders() exists: " . (function_exists('getallheaders') ? 'YES' : 'NO');
echo "<br>HTTP_AUTHORIZATION: " . ($_SERVER['HTTP_AUTHORIZATION'] ?? 'not set');

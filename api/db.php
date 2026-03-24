<?php
ob_start();

define('DB_HOST', 'bmtfvrfss5beoprqtpcd-mysql.services.clever-cloud.com');
define('DB_USER', 'uvgvjilp6w2dyarq');
define('DB_PASS', 'xxPHy7DFg26wCDhma0kx');
define('DB_NAME', 'bmtfvrfss5beoprqtpcd');
define('DB_PORT', 3306);

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
if ($conn->connect_error) {
    ob_clean();
    die(json_encode(['success'=>false,'message'=>'DB error: '.$conn->connect_error]));
}
$conn->set_charset('utf8mb4');

$GLOBALS['_RAW_INPUT'] = null;
$GLOBALS['_JSON_INPUT'] = null;

function getRawInput() {
    if ($GLOBALS['_RAW_INPUT'] === null) {
        $GLOBALS['_RAW_INPUT'] = file_get_contents('php://input');
    }
    return $GLOBALS['_RAW_INPUT'];
}

function getJsonInput() {
    if ($GLOBALS['_JSON_INPUT'] === null) {
        $raw = getRawInput();
        $json = json_decode($raw, true);
        if ($json && is_array($json)) {
            $GLOBALS['_JSON_INPUT'] = $json;
        } elseif (!empty($_POST)) {
            $GLOBALS['_JSON_INPUT'] = $_POST;
        } else {
            $GLOBALS['_JSON_INPUT'] = [];
        }
    }
    return $GLOBALS['_JSON_INPUT'];
}

function clean($conn, $data) {
    return $conn->real_escape_string(trim($data ?? ''));
}

function sendResponse($success, $message, $data = []) {
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode(['success'=>$success,'message'=>$message,'data'=>$data]);
    exit();
}

function generateBookingRef($conn) {
    $r   = $conn->query("SELECT COUNT(*) as c FROM reservations");
    $row = $r->fetch_assoc();
    return 'MR-' . str_pad($row['c'] + 1, 4, '0', STR_PAD_LEFT);
}

function getAuthUser($conn) {
    $token = '';

    if (!empty($_GET['t'])) {
        $token = trim($_GET['t']);
    }

    if (empty($token)) {
        $body  = getJsonInput();
        $token = trim($body['_token'] ?? '');
    }

    if (empty($token)) {
        if (function_exists('getallheaders')) {
            $h     = getallheaders();
            $token = trim(str_replace('Bearer ', '', $h['Authorization'] ?? $h['authorization'] ?? ''));
        }
        if (empty($token)) $token = trim(str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION'] ?? ''));
        if (empty($token)) $token = trim(str_replace('Bearer ', '', $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? ''));
    }

    if (empty($token)) return null;

    $parts  = explode('-', $token);
    $userId = (int)end($parts);
    if ($userId <= 0) return null;

    $r = $conn->query("SELECT id, full_name, email, phone, role FROM users WHERE id=$userId LIMIT 1");
    if (!$r || $r->num_rows === 0) return null;
    return $r->fetch_assoc();
}

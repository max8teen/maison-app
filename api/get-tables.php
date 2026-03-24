<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

require_once 'db.php';

$party = (int)($_GET['party'] ?? 1);
$date  = clean($conn, $_GET['date']  ?? '');
$time  = clean($conn, $_GET['time']  ?? '');

// No date/time — return all tables by capacity only
if (!$date || !$time) {
    $result = $conn->query("
        SELECT * FROM restaurant_tables
        WHERE capacity >= $party
          AND status != 'maintenance'
        ORDER BY capacity ASC
    ");
    $tables = [];
    while ($row = $result->fetch_assoc()) $tables[] = $row;
    sendResponse(true, 'Tables loaded', ['tables' => $tables]);
}

// With date+time — check 2hr blocking
$sql = "
    SELECT t.id, t.table_number, t.capacity, t.location, t.status,
        CASE WHEN r.id IS NOT NULL THEN 'booked' ELSE 'available' END AS real_status
    FROM restaurant_tables t
    LEFT JOIN reservations r
        ON  r.table_id = t.id
        AND r.reservation_date = '$date'
        AND r.status NOT IN ('cancelled','no_show')
        AND ABS(TIME_TO_SEC(TIMEDIFF(r.reservation_time, '$time'))) < 7200
    WHERE t.capacity >= $party
      AND t.status != 'maintenance'
    ORDER BY t.capacity ASC
";

$result = $conn->query($sql);
if (!$result) sendResponse(false, 'Query error: ' . $conn->error);

$tables = [];
while ($row = $result->fetch_assoc()) {
    $tables[] = [
        'id'           => (int)$row['id'],
        'table_number' => $row['table_number'],
        'capacity'     => (int)$row['capacity'],
        'location'     => $row['location'],
        'status'       => $row['real_status'],
    ];
}

sendResponse(true, 'Tables loaded', ['tables' => $tables]);

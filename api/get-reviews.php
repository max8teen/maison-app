<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(0); }
require_once __DIR__ . '/db.php';

// ── Stats: ALL reviews ────────────────────────────────────────
$statsResult = $conn->query("SELECT rating, food_rating, service_rating, ambience_rating FROM reviews");
$allRatings  = [];
$foodSum = $svcSum = $ambSum = 0;
$foodCnt = $svcCnt = $ambCnt = 0;
$breakdown = [5=>0,4=>0,3=>0,2=>0,1=>0];

while ($row = $statsResult->fetch_assoc()) {
    $r = intval($row['rating']);
    $allRatings[] = $r;
    $breakdown[$r]++;
    if (!empty($row['food_rating']))     { $foodSum += $row['food_rating'];    $foodCnt++; }
    if (!empty($row['service_rating']))  { $svcSum  += $row['service_rating']; $svcCnt++;  }
    if (!empty($row['ambience_rating'])) { $ambSum  += $row['ambience_rating'];$ambCnt++;  }
}

$totalAll = count($allRatings);
$avg      = $totalAll > 0 ? round(array_sum($allRatings) / $totalAll, 1) : 0;

$breakdown_pct = [];
foreach ($breakdown as $star => $count) {
    $breakdown_pct[$star] = $totalAll > 0 ? round(($count / $totalAll) * 100) : 0;
}

$catAvg = [
    'food'     => $foodCnt > 0 ? round($foodSum / $foodCnt, 1) : null,
    'service'  => $svcCnt  > 0 ? round($svcSum  / $svcCnt,  1) : null,
    'ambience' => $ambCnt  > 0 ? round($ambSum  / $ambCnt,  1) : null,
];

// ── Approved reviews for cards ────────────────────────────────
$sql = "
  SELECT r.id, r.rating, r.food_rating, r.service_rating, r.ambience_rating,
         r.comment, r.created_at,
         u.full_name, res.reservation_date, res.occasion
  FROM reviews r
  JOIN users u        ON u.id   = r.user_id
  JOIN reservations res ON res.id = r.booking_id
  WHERE r.status = 'approved'
  ORDER BY r.created_at DESC
";
$result  = $conn->query($sql);
$reviews = [];
while ($row = $result->fetch_assoc()) $reviews[] = $row;

// ── Current user's own pending reviews ───────────────────────
$myPending = [];
$user = getAuthUser($conn);
if ($user) {
    $stmt = $conn->prepare("
      SELECT r.id, r.rating, r.food_rating, r.service_rating, r.ambience_rating,
             r.comment, r.created_at,
             u.full_name, res.reservation_date, res.occasion
      FROM reviews r
      JOIN users u        ON u.id   = r.user_id
      JOIN reservations res ON res.id = r.booking_id
      WHERE r.user_id = ? AND r.status = 'pending'
      ORDER BY r.created_at DESC
    ");
    $stmt->bind_param('i', $user['id']);
    $stmt->execute();
    $res2 = $stmt->get_result();
    while ($row = $res2->fetch_assoc()) $myPending[] = $row;
}

sendResponse(true, 'OK', [
    'reviews'        => $reviews,
    'my_pending'     => $myPending,
    'total_all'      => $totalAll,
    'total_approved' => count($reviews),
    'average'        => $avg,
    'breakdown'      => $breakdown,
    'breakdown_pct'  => $breakdown_pct,
    'cat_avg'        => $catAvg,
]);
?>

<?php
// TEMPORARY DEBUG FILE - DELETE AFTER FIXING
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

$raw  = file_get_contents('php://input');
$body = json_decode($raw, true) ?: [];

// Check all possible token locations
$results = [
    'php_version'       => phpversion(),
    'request_method'    => $_SERVER['REQUEST_METHOD'],
    'raw_body_length'   => strlen($raw),
    'raw_body_preview'  => substr($raw, 0, 200),
    'body_parsed'       => $body,
    'body_has_token'    => isset($body['_token']) ? $body['_token'] : 'NOT FOUND',
    'getallheaders_exists' => function_exists('getallheaders'),
    'headers'           => function_exists('getallheaders') ? getallheaders() : 'unavailable',
    'SERVER_HTTP_AUTH'  => $_SERVER['HTTP_AUTHORIZATION'] ?? 'not set',
    'SERVER_REDIRECT_AUTH' => $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? 'not set',
];

echo json_encode($results, JSON_PRETTY_PRINT);

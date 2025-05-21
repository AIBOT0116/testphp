<?php
// CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: *");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

// Get input parameters
$base = $_GET['base'] ?? '';
$path = $_GET['path'] ?? '';

if (empty($base) || empty($path)) {
    http_response_code(400);
    echo "Missing 'base' or 'path' parameter.";
    exit();
}

// Ensure base URL is well-formed (fix any missing slashes)
if (!filter_var($base, FILTER_VALIDATE_URL)) {
    $base = "https://" . $base; // Ensure that "https://" is added if missing
}

// Build full target URL
$targetURL = rtrim($base, '/') . '/' . ltrim($path, '/');

// Set headers for fetching the resource
$context = stream_context_create([
    'http' => [
        'header' => "User-Agent: Mozilla/5.0\r\n",
        'follow_location' => 1,
        'timeout' => 5
    ]
]);

// Fetch the resource
$res = @file_get_contents($targetURL, false, $context);

if ($res === false) {
    http_response_code(502);
    echo "Failed to fetch resource.";
    exit();
}

// Detect content type
$ext = pathinfo(parse_url($path, PHP_URL_PATH), PATHINFO_EXTENSION);
switch ($ext) {
    case 'mpd':
        header("Content-Type: application/dash+xml");
        break;
    case 'm3u8':
        header("Content-Type: application/vnd.apple.mpegurl");
        break;
    default:
        header("Content-Type: application/octet-stream");
}

echo $res;
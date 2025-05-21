<?php
// CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: *");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

$get = isset($_GET['get']) ? ltrim($_GET['get'], '/') : '';
$domain = isset($_GET['domain']) ? trim($_GET['domain']) : '';

// Ensure trailing slash is not doubled
$domain = rtrim($domain, '/') . '/';

if (empty($get) || empty($domain)) {
    http_response_code(400);
    echo "Missing domain or path.";
    exit();
}

// Optional domain validation (allow full URLs now)
if (!filter_var($domain . $get, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    echo "Invalid domain or path.";
    exit();
}

$targetUrl = $domain . $get;

$context = stream_context_create([
    'http' => [
        'header' => "User-Agent: Mozilla/5.0\r\n",
        'follow_location' => 1,
        'timeout' => 5
    ]
]);

$response = @file_get_contents($targetUrl, false, $context);

if ($response === false) {
    http_response_code(502);
    echo "Failed to fetch resource.";
} else {
    // Try to forward content type
    header("Content-Type: text/plain");
    echo $response;
}
?>

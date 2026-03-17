<?php
header('Content-Type: application/json; charset=utf-8');

$version = isset($_GET['version']) ? $_GET['version'] : '';

// Allow only version patterns like 0.0.92
if (!preg_match('/^\d+(?:\.\d+)*$/', $version)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid version'], JSON_PRETTY_PRINT);
    exit;
}

$binFile = "xptouch.web.{$version}.bin";
$binPath = __DIR__ . DIRECTORY_SEPARATOR . $binFile;

if (!is_file($binPath)) {
    http_response_code(404);
    echo json_encode(['error' => 'BIN file not found'], JSON_PRETTY_PRINT);
    exit;
}

$manifest = [
    'name' => 'xptouch',
    'version' => $version,
    'builds' => [
        [
            'chipFamily' => 'ESP32-S3',
            'parts' => [
                [
                    'path' => $binFile,
                    'offset' => 0,
                ],
            ],
        ],
    ],
];

echo json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);


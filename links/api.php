<?php
declare(strict_types=1);
require __DIR__ . '/store.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed. Send a POST request.',
    ]);
    exit;
}

$contentType = strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? ''));
$payload = $_POST;

if (str_contains($contentType, 'application/json')) {
    $decoded = json_decode((string) file_get_contents('php://input'), true);
    if (!is_array($decoded)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid JSON request body.',
        ]);
        exit;
    }
    $payload = $decoded;
}

$target = strtolower(trim((string) ($payload['target'] ?? 'main')));
if (!in_array($target, ['main', 'v2', 'v3'], true)) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'error' => 'Target must be main, v2, or v3.',
    ]);
    exit;
}

try {
    $code = createTrackedLink($target, (string) ($payload['note'] ?? ''));
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'code' => $code,
        'url' => linkUrl($code),
        'target' => $target,
        'note' => trim((string) ($payload['note'] ?? '')),
    ], JSON_UNESCAPED_SLASHES);
} catch (Throwable $error) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Unable to generate a tracked link.',
    ]);
}

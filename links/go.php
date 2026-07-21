<?php
declare(strict_types=1);
require __DIR__ . '/store.php';

$code = strtoupper(trim((string) ($_GET['c'] ?? '')));
$target = withLinkStore(function (array &$links) use ($code): ?string {
    if (!preg_match('/^[A-Z2-9]{5}$/', $code) || !isset($links[$code])) {
        return null;
    }

    $links[$code]['clicks'] = (int) ($links[$code]['clicks'] ?? 0) + 1;
    $links[$code]['click_log'] ??= [];
    $links[$code]['click_log'][] = [
        'clicked_at' => gmdate('c'),
        'ip_address' => (string) ($_SERVER['REMOTE_ADDR'] ?? 'Unknown'),
    ];
    $target = (string) ($links[$code]['target'] ?? 'main');
    return in_array($target, ['main', 'v2', 'v3'], true) ? $target : 'main';
}, true);

if ($target === null) {
    http_response_code(404);
    echo '<!doctype html><title>Link not found</title><h1>Link not found</h1><p>This tracking code does not exist.</p>';
    exit;
}

header('Location: ' . profileUrl($target === 'main' ? '' : $target), true, 302);
exit;

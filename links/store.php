<?php
declare(strict_types=1);

const LINK_DATA_FILE = __DIR__ . '/data/links.json';

function withLinkStore(callable $callback, bool $write = false)
{
    $directory = dirname(LINK_DATA_FILE);
    if (!is_dir($directory)) {
        mkdir($directory, 0775, true);
    }

    $handle = fopen(LINK_DATA_FILE, 'c+');
    if ($handle === false) {
        throw new RuntimeException('Unable to open the link data store.');
    }

    try {
        if (!flock($handle, $write ? LOCK_EX : LOCK_SH)) {
            throw new RuntimeException('Unable to lock the link data store.');
        }

        rewind($handle);
        $contents = stream_get_contents($handle);
        $links = $contents ? json_decode($contents, true) : [];
        if (!is_array($links)) {
            $links = [];
        }

        $result = $callback($links);

        if ($write) {
            rewind($handle);
            ftruncate($handle, 0);
            fwrite($handle, json_encode($links, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            fflush($handle);
        }

        flock($handle, LOCK_UN);
        return $result;
    } finally {
        fclose($handle);
    }
}

function generateCode(array $links): string
{
    $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $maximum = strlen($characters) - 1;

    do {
        $code = '';
        for ($index = 0; $index < 5; $index++) {
            $code .= $characters[random_int(0, $maximum)];
        }
    } while (isset($links[$code]));

    return $code;
}

function linkUrl(string $code): string
{
    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $scheme = $secure ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $path = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/links/index.php')), '/');
    return sprintf('%s://%s%s/go.php?c=%s', $scheme, $host, $path, rawurlencode($code));
}

function profileUrl(string $version = ''): string
{
    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $scheme = $secure ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/links/index.php');
    $path = rtrim(dirname(dirname($script)), '/');
    $versionPath = in_array($version, ['v2', 'v3'], true) ? $version . '/' : '';
    return sprintf('%s://%s%s/%s', $scheme, $host, $path, $versionPath);
}

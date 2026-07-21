<?php
declare(strict_types=1);
require __DIR__ . '/store.php';
date_default_timezone_set('Asia/Kuala_Lumpur');

$createdCode = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? 'create');

    if ($action === 'remove') {
        $removeCode = strtoupper(trim((string) ($_POST['code'] ?? '')));
        withLinkStore(function (array &$links) use ($removeCode): void {
            if (preg_match('/^[A-Z2-9]{5}$/', $removeCode)) {
                unset($links[$removeCode]);
            }
        }, true);
    } else {
        $target = (string) ($_POST['target'] ?? 'main');
        $target = in_array($target, ['main', 'v2', 'v3'], true) ? $target : 'main';
        $note = trim((string) ($_POST['note'] ?? ''));
        $note = function_exists('mb_substr') ? mb_substr($note, 0, 200) : substr($note, 0, 200);

        $createdCode = withLinkStore(function (array &$links) use ($target, $note): string {
            $code = generateCode($links);
            $links[$code] = [
                'target' => $target,
                'note' => $note,
                'clicks' => 0,
                'created_at' => gmdate('c'),
            ];
            return $code;
        }, true);
    }
}

$links = withLinkStore(function (array $links): array {
    uasort($links, fn(array $a, array $b): int => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
    return $links;
});
$totalClicks = array_sum(array_map(fn(array $link): int => (int) ($link['clicks'] ?? 0), $links));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Link Tracker | Farhan Kamarzaman</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <div class="bg-grid"></div>
    <div class="glow"></div>
    <div class="page-shell link-page-shell">
        <header class="page-header">
            <a class="brand-link" href="../">Farhan Kamarzaman</a>
            <a class="btn btn-outline" href="../">Back to profile</a>
        </header>

        <main class="page-main">
            <section class="page-panel link-hero">
                <p class="status-badge"><span class="dot"></span>Link tracker</p>
                <h1>Generate a unique link</h1>
                <p class="page-description">Create a unique share link for your portfolio. Every visit is counted before the visitor is sent to your homepage.</p>

                <form class="link-form" method="post">
                    <input type="hidden" name="action" value="create">
                    <div class="link-form-fields">
                        <div class="form-field">
                            <label for="target">Page to share</label>
                            <select id="target" name="target">
                                <option value="main">Main website</option>
                                <option value="v2">Website V2</option>
                                <option value="v3">Website V3</option>
                            </select>
                        </div>
                        <div class="form-field form-field-note">
                            <label for="note">Note</label>
                            <input id="note" name="note" type="text" maxlength="200" placeholder="Example: Shared with ABC Company">
                        </div>
                    </div>
                    <button class="btn btn-primary generate-button" type="submit">Generate new share link</button>
                </form>

                <?php if ($createdCode): ?>
                    <div class="created-link" role="status">
                        <div><span>New tracked link</span><strong id="created-url"><?= htmlspecialchars(linkUrl($createdCode), ENT_QUOTES, 'UTF-8') ?></strong></div>
                        <button class="btn btn-outline" type="button" data-copy="#created-url">Copy</button>
                    </div>
                <?php endif; ?>
            </section>

            <section class="stats-strip link-stats" aria-label="Link statistics">
                <div class="stat-chip"><span class="stat-value"><?= count($links) ?></span><span class="stat-label">Links generated</span></div>
                <div class="stat-chip"><span class="stat-value"><?= $totalClicks ?></span><span class="stat-label">Total clicks</span></div>
            </section>

            <section class="page-panel">
                <h2 class="section-heading">Generated links</h2>
                <?php if (!$links): ?>
                    <p class="page-description">No links generated yet.</p>
                <?php else: ?>
                    <div class="link-table-wrap">
                        <table class="link-table">
                            <thead><tr><th>Share link</th><th>Page</th><th>Note</th><th>Clicks</th><th>Last click</th><th>Created</th><th>Actions</th></tr></thead>
                            <tbody>
                            <?php foreach ($links as $code => $link): ?>
                                <tr>
                                    <td><a id="link-<?= htmlspecialchars((string) $code, ENT_QUOTES, 'UTF-8') ?>" href="<?= htmlspecialchars(linkUrl((string) $code), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener"><?= htmlspecialchars(linkUrl((string) $code), ENT_QUOTES, 'UTF-8') ?></a></td>
                                    <td><span class="target-badge"><?= htmlspecialchars(strtoupper((string) ($link['target'] ?? 'main')), ENT_QUOTES, 'UTF-8') ?></span></td>
                                    <td class="note-cell"><?php if ((string) ($link['note'] ?? '') !== ''): ?><?= htmlspecialchars((string) $link['note'], ENT_QUOTES, 'UTF-8') ?><?php else: ?><span class="muted">—</span><?php endif; ?></td>
                                    <td><strong><?= (int) ($link['clicks'] ?? 0) ?></strong></td>
                                    <?php $clickLog = is_array($link['click_log'] ?? null) ? $link['click_log'] : []; $lastClick = $clickLog ? end($clickLog) : null; ?>
                                    <td><?php if ($lastClick): ?><?= htmlspecialchars(date('d M Y, H:i:s', strtotime((string) $lastClick['clicked_at'])), ENT_QUOTES, 'UTF-8') ?><small class="ip-address"><?= htmlspecialchars((string) ($lastClick['ip_address'] ?? 'Unknown'), ENT_QUOTES, 'UTF-8') ?></small><?php else: ?><span class="muted">Never</span><?php endif; ?></td>
                                    <td><?= htmlspecialchars(date('d M Y, H:i', strtotime((string) ($link['created_at'] ?? 'now'))), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="link-actions">
                                        <button class="btn btn-outline" type="button" data-copy="#link-<?= htmlspecialchars((string) $code, ENT_QUOTES, 'UTF-8') ?>">Copy</button>
                                        <form method="post" data-remove-form>
                                            <input type="hidden" name="action" value="remove">
                                            <input type="hidden" name="code" value="<?= htmlspecialchars((string) $code, ENT_QUOTES, 'UTF-8') ?>">
                                            <button class="btn btn-danger" type="submit">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php if ($clickLog): ?>
                                    <tr class="click-history-row">
                                        <td colspan="7">
                                            <details class="click-history">
                                                <summary>View <?= count($clickLog) ?> click record<?= count($clickLog) === 1 ? '' : 's' ?></summary>
                                                <div class="click-history-list">
                                                    <?php foreach (array_reverse($clickLog) as $click): ?>
                                                        <div class="click-record">
                                                            <span><?= htmlspecialchars(date('d M Y, H:i:s', strtotime((string) ($click['clicked_at'] ?? 'now'))), ENT_QUOTES, 'UTF-8') ?></span>
                                                            <code><?= htmlspecialchars((string) ($click['ip_address'] ?? 'Unknown'), ENT_QUOTES, 'UTF-8') ?></code>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </details>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>
    <script src="../background.js"></script>
    <script src="link.js"></script>
</body>
</html>

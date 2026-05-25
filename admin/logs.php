<?php
/**
 * Sweets Website
 * =============================================================
 * File: admin/logs.php
 * Description: Enterprise Log Viewer with Backwards Tail Reading,
 *              Security Sanitizer, and Pagination.
 * =============================================================
 */

require_once 'includes/header.php';
require_once 'includes/auth.php';
require_once 'includes/sidebar.php';

// Dynamic log file detection
$logDir = ROOT_PATH . '/logs';
$logFiles = glob($logDir . '/*.log');
$availableChannels = [];
if (is_array($logFiles)) {
    foreach ($logFiles as $file) {
        $base = basename($file, '.log');
        $availableChannels[$base] = $file;
    }
}

// Ensure php-errors and js-errors are always in the list even if files are missing
if (!isset($availableChannels['php-errors'])) {
    $availableChannels['php-errors'] = $logDir . '/php-errors.log';
}
if (!isset($availableChannels['js-errors'])) {
    $availableChannels['js-errors'] = $logDir . '/js-errors.log';
}

$channel = $_GET['channel'] ?? 'php-errors';
if (!preg_match('/^[a-zA-Z0-9_-]+$/', $channel)) {
    $channel = 'php-errors';
}

$logFile = $availableChannels[$channel] ?? ($logDir . '/' . $channel . '.log');
$action = $_GET['action'] ?? '';

// Handle actions
if ($action === 'clear' && file_exists($logFile)) {
    file_put_contents($logFile, '');
    header('Location: logs.php?channel=' . urlencode($channel) . '&cleared=1');
    exit();
}

/**
 * Memory-safe backwards tail reading function using chunks.
 */
function getTailLines(string $filename, int $offset = 0, int $limit = 50): array {
    if (!file_exists($filename) || !is_readable($filename)) {
        return ['lines' => [], 'total' => 0];
    }

    $fileSize = filesize($filename);
    if ($fileSize === 0) {
        return ['lines' => [], 'total' => 0];
    }

    $handle = fopen($filename, 'rb');
    if (!$handle) {
        return ['lines' => [], 'total' => 0];
    }

    // Fast total line counting using block reads
    $totalLines = 0;
    fseek($handle, 0);
    while (!feof($handle)) {
        $chunk = fread($handle, 1024 * 64);
        if ($chunk !== false) {
            $totalLines += substr_count($chunk, "\n");
        }
    }

    if ($totalLines === 0) {
        fclose($handle);
        return ['lines' => [], 'total' => 0];
    }

    $lines = [];
    $linesNeeded = $limit;
    $linesToSkip = $offset;

    $bufferSize = 8192;
    $pos = $fileSize;
    $leftover = '';

    while ($pos > 0 && count($lines) < $linesNeeded) {
        $readSize = min($pos, $bufferSize);
        $pos -= $readSize;
        fseek($handle, $pos, SEEK_SET);
        $chunk = fread($handle, $readSize);
        
        if ($chunk === false) {
            break;
        }

        $chunk .= $leftover;
        $chunkLines = explode("\n", $chunk);
        
        // The first element might be partial, save it as leftover
        if ($pos > 0) {
            $leftover = array_shift($chunkLines);
        } else {
            $leftover = '';
        }

        // Process lines in reverse order (newest first)
        for ($i = count($chunkLines) - 1; $i >= 0; $i--) {
            $line = trim($chunkLines[$i], "\r");
            if ($line === '') {
                continue;
            }

            if ($linesToSkip > 0) {
                $linesToSkip--;
                continue;
            }

            $lines[] = $line;
            if (count($lines) >= $linesNeeded) {
                break;
            }
        }
    }

    fclose($handle);
    return [
        'lines' => $lines,
        'total' => $totalLines
    ];
}

/**
 * Sanitizes sensitive information (passwords, card numbers, tokens, session IDs) from log text.
 */
function sanitizeLogOutput(string $text): string {
    // 1. Redact credit card numbers (13 to 19 digits)
    $text = preg_replace('/\b(?:\d[ -]*?){13,19}\b/', '[CARD_REDACTED]', $text);
    
    // 2. Redact API tokens / Secrets / Session IDs / Passwords in key-value format (query params, SQL, JSON)
    $patterns = [
        '/(password|pass|passwd|secret|token|cvv|cvc|card_number|card|pin|session_id|sessid|phpsessid)\s*([=:]|=>)\s*([\'"])(.*?)\3/i' => '$1$2$3[REDACTED]$3',
        '/(password|pass|passwd|secret|token|cvv|cvc|card_number|card|pin|session_id|sessid|phpsessid)\s*([=:]|=>)\s*([a-zA-Z0-9_-]{8,})/i' => '$1$2[REDACTED]',
        '/([\'"])(password|pass|passwd|secret|token|cvv|cvc|card_number|card|pin|session_id|sessid|phpsessid)\1\s*:\s*([\'"])(.*?)\3/i' => '$1$2$1: $3[REDACTED]$3',
    ];

    foreach ($patterns as $pattern => $replacement) {
        $text = preg_replace($pattern, $replacement, $text);
    }
    
    // 3. Redact JWT tokens
    $text = preg_replace('/eyJ[a-zA-Z0-9_-]+\.eyJ[a-zA-Z0-9_-]+\.[a-zA-Z0-9_-]+/i', '[JWT_REDACTED]', $text);

    return $text;
}

// Pagination setup
$page = (int)($_GET['page'] ?? 1);
if ($page < 1) {
    $page = 1;
}
$limit = 50;
$offset = ($page - 1) * $limit;

$tailResult = getTailLines($logFile, $offset, $limit);
$logsData = $tailResult['lines'];
$totalLogs = $tailResult['total'];
$totalPages = (int)ceil($totalLogs / $limit);
if ($totalPages < 1) {
    $totalPages = 1;
}

$logs = [];
foreach ($logsData as $line) {
    $decoded = json_decode($line, true);
    if ($decoded) {
        $logs[] = $decoded;
    }
}

$friendlyNames = [
    'php-errors'     => 'Server Errors (PHP)',
    'js-errors'      => 'Website Errors (JS)',
    'payment'        => 'Payment Logs',
    'orders'         => 'Order Logs',
    'inventory'      => 'Inventory Logs',
    'auth'           => 'Authentication Logs',
    'security'       => 'Security Logs',
    'api'            => 'API Logs',
    'admin-actions'  => 'Admin Actions',
    'app'            => 'Application Logs',
];

$channelsList = [];
foreach ($availableChannels as $base => $filePath) {
    $name = $friendlyNames[$base] ?? (ucfirst(str_replace('-', ' ', $base)) . ' Logs');
    $channelsList[$base] = $name;
}
asort($channelsList);
?>

<style>
    .log-table {
        font-family: 'Monaco', 'Consolas', monospace;
        font-size: 0.85rem;
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #f1e6d6;
    }
    .log-table thead {
        background: #fdf6ee;
        border-bottom: 2px solid #f1e6d6;
    }
    .log-table th {
        padding: 12px 16px;
        font-weight: 700;
        color: #7a1f1f;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
    }
    .log-table td {
        padding: 10px 16px;
        border-bottom: 1px solid #fdf6ee;
        vertical-align: top;
    }
    .lvl-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 4px;
        font-weight: 700;
        font-size: 0.7rem;
        color: #fff;
        min-width: 70px;
        text-align: center;
    }
    .lvl-DEBUG    { background: #6c757d; }
    .lvl-INFO     { background: #0dcaf0; }
    .lvl-NOTICE   { background: #198754; }
    .lvl-WARNING  { background: #ffc107; color: #000; }
    .lvl-ERROR    { background: #fd7e14; }
    .lvl-CRITICAL { background: #dc3545; }
    .lvl-ALERT    { background: #d63384; }

    .log-msg {
        color: #333;
        word-break: break-word;
        max-width: 650px;
    }
    .log-context {
        color: #777;
        font-size: 0.75rem;
        background: #f9f9f9;
        padding: 4px 8px;
        border-radius: 4px;
        margin-top: 4px;
        display: block;
        max-height: 200px;
        overflow-y: auto;
        white-space: pre-wrap;
        word-break: break-all;
    }
    .log-ref {
        color: #7a1f1f;
        font-weight: 700;
        font-size: 0.75rem;
    }
    .log-time {
        white-space: nowrap;
        color: #999;
    }
    .channel-pill {
        background: #f1e6d6;
        color: #7a1f1f;
        padding: 2px 8px;
        border-radius: 999px;
        font-size: 0.7rem;
        font-weight: 700;
    }
</style>

<div class="main-content">
    <?php require_once 'includes/topbar.php'; ?>

    <div class="container-fluid px-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold text-dark">System Logs</h1>
                <p class="text-muted small mb-0">Showing system channel: <strong><?php echo htmlspecialchars($channelsList[$channel] ?? $channel); ?></strong>. Total entries found: <?php echo $totalLogs; ?>.</p>
            </div>
            <div class="d-flex gap-2">
                <form action="" method="GET" class="d-flex gap-2">
                    <select name="channel" class="form-select form-select-sm" onchange="this.form.submit()">
                        <?php foreach ($channelsList as $chVal => $chName): ?>
                            <option value="<?php echo $chVal; ?>" <?php echo $channel === $chVal ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($chName); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
                <a href="logs.php?action=clear&channel=<?php echo urlencode($channel); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Clear all logs in this channel?')">
                    <i class="bi bi-trash me-1"></i> Clear Logs
                </a>
            </div>
        </div>

        <?php if (isset($_GET['cleared'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Logs for current channel have been cleared successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="table-responsive log-table">
            <table class="table table-borderless mb-0">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Level</th>
                        <th>Channel</th>
                        <th>Message</th>
                        <th>Ref</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">No log entries found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td>
                                    <span class="log-time"><?php echo date('H:i:s', strtotime($log['time'] ?? 'now')); ?></span><br>
                                    <small class="text-muted"><?php echo date('M d', strtotime($log['time'] ?? 'now')); ?></small>
                                </td>
                                <td>
                                    <span class="lvl-badge lvl-<?php echo htmlspecialchars($log['level'] ?? 'INFO'); ?>"><?php echo htmlspecialchars($log['level'] ?? 'INFO'); ?></span>
                                </td>
                                <td>
                                    <span class="channel-pill"><?php echo htmlspecialchars($log['channel'] ?? 'app'); ?></span>
                                </td>
                                <td>
                                    <div class="log-msg fw-semibold"><?php echo htmlspecialchars(sanitizeLogOutput($log['message'] ?? '')); ?></div>
                                    <div class="d-flex flex-wrap gap-2 mt-1 small">
                                        <?php if (!empty($log['request_uri'])): ?>
                                            <span class="text-muted"><i class="bi bi-link-45deg"></i> <?php echo htmlspecialchars(sanitizeLogOutput($log['request_uri'])); ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($log['ip'])): ?>
                                            <span class="text-muted"><i class="bi bi-laptop"></i> <?php echo htmlspecialchars($log['ip']); ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($log['user_id'])): ?>
                                            <span class="text-muted"><i class="bi bi-person"></i> ID: <?php echo htmlspecialchars((string)$log['user_id']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($log['context'])): ?>
                                        <?php 
                                            $contextJson = json_encode($log['context'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                                            $sanitizedContext = sanitizeLogOutput($contextJson);
                                        ?>
                                        <code class="log-context"><?php echo htmlspecialchars($sanitizedContext); ?></code>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="log-ref"><?php echo htmlspecialchars($log['corr_id'] ?? '-'); ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <nav class="d-flex justify-content-between align-items-center mt-4">
                <span class="text-muted small">
                    Showing <?php echo $offset + 1; ?> to <?php echo min($totalLogs, $offset + count($logs)); ?> of <?php echo $totalLogs; ?> entries
                </span>
                <ul class="pagination pagination-sm mb-0">
                    <!-- Previous Page -->
                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="logs.php?channel=<?php echo urlencode($channel); ?>&page=<?php echo $page - 1; ?>">Previous</a>
                    </li>
                    
                    <?php
                    // Show a window of page numbers around the current page
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    
                    if ($startPage > 1) {
                        echo '<li class="page-item"><a class="page-link" href="logs.php?channel=' . urlencode($channel) . '&page=1">1</a></li>';
                        if ($startPage > 2) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                    }
                    
                    for ($i = $startPage; $i <= $endPage; $i++) {
                        echo '<li class="page-item ' . ($page === $i ? 'active' : '') . '">';
                        echo '<a class="page-link" href="logs.php?channel=' . urlencode($channel) . '&page=' . $i . '">' . $i . '</a>';
                        echo '</li>';
                    }
                    
                    if ($endPage < $totalPages) {
                        if ($endPage < $totalPages - 1) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                        echo '<li class="page-item"><a class="page-link" href="logs.php?channel=' . urlencode($channel) . '&page=' . $totalPages . '">' . $totalPages . '</a></li>';
                    }
                    ?>
                    
                    <!-- Next Page -->
                    <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="logs.php?channel=<?php echo urlencode($channel); ?>&page=<?php echo $page + 1; ?>">Next</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<?php
/**
 * Lightweight activity/audit log.
 * Stage: 7 - Real-world features (admin oversight)
 *
 * Writes one line per event to logs/app.log. Kept file-based rather than a
 * DB table + admin UI page to stay proportional to this project's size -
 * open the file directly (or `tail -f logs/app.log`) to review activity.
 */
function log_action(?int $userId, string $action, string $details = ''): void
{
    $line = sprintf(
        "[%s] user=%s action=%s %s\n",
        date('Y-m-d H:i:s'),
        $userId ?? 'guest',
        $action,
        $details
    );

    $logFile = __DIR__ . '/../logs/app.log';
    @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
}

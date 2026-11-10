<?php
/** Run manually through PHP CLI from application root. No migrations, DDL, login or data export. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root = getcwd();
if (!is_file($root . '/bootstrap/app.php') || !is_file($root . '/vendor/autoload.php')) {
    fwrite(STDERR, "STOP: application root required\n"); exit(2);
}
try {
    require $root . '/vendor/autoload.php';
    $app = require $root . '/bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    $connection = Illuminate\Support\Facades\DB::connection();
    if ($connection->getDriverName() !== 'mysql') throw new RuntimeException('MySQL required');
    $pdo = $connection->getPdo();
    // Every SQL operation below is a SELECT in a read-only transaction.
    $pdo->exec('SET TRANSACTION READ ONLY');
    $pdo->beginTransaction();
    $db = $connection->getDatabaseName();
    $targets = ['anggota', 'transaksi', 'device_tokens', 'mobile_savings_requests', 'migrations', 'rilis_apk'];
    $placeholders = implode(',', array_fill(0, count($targets), '?'));
    $query = function ($sql, array $parameters = []) use ($pdo) {
        $statement = $pdo->prepare($sql); $statement->execute($parameters); return $statement->fetchAll(PDO::FETCH_ASSOC);
    };
    $result = ['php' => PHP_VERSION, 'laravel' => app()->version(), 'mysql' => $query('SELECT VERSION() AS version'),
        'disk_free_bytes' => @disk_free_space($root),
        'tables' => $query("SELECT TABLE_NAME, ENGINE, TABLE_ROWS, DATA_LENGTH, INDEX_LENGTH, TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME IN ($placeholders)", array_merge([$db], $targets)),
        'columns' => $query("SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME IN ($placeholders)", array_merge([$db], $targets)),
        'indexes' => $query("SELECT TABLE_NAME, INDEX_NAME, NON_UNIQUE, SEQ_IN_INDEX, COLUMN_NAME, SUB_PART FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME IN ($placeholders)", array_merge([$db], $targets)),
        // Names/counts only, never return trigger bodies, user credentials or notification tokens.
        'triggers' => $query("SELECT TRIGGER_NAME, EVENT_OBJECT_TABLE, ACTION_TIMING, EVENT_MANIPULATION FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA = ? AND EVENT_OBJECT_TABLE IN ($placeholders)", array_merge([$db], $targets)),
        'events' => $query('SELECT EVENT_NAME, STATUS FROM information_schema.EVENTS WHERE EVENT_SCHEMA = ?', [$db]),
        'routines' => $query('SELECT ROUTINE_NAME, ROUTINE_TYPE FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = ?', [$db]),
        'files' => [],
    ];
    $result['device_token_duplicate_groups'] = $query('SELECT COUNT(*) AS groups_count FROM (SELECT token FROM device_tokens GROUP BY token HAVING COUNT(*) > 1) AS duplicate_groups');
    $pdo->rollBack();
    foreach (['app/Http/Controllers/MobileController.php', 'app/Support/FcmSender.php', 'app/Support/MobileSessionGuard.php',
        'app/Support/MobileSavingsIdempotency.php', 'routes/api.php',
        'database/migrations/2026_11_10_000001_create_mobile_savings_requests_table.php', 'version.json', 'public/web.config'] as $relative) {
        $path = $root . '/' . $relative;
        $result['files'][] = ['path' => $relative, 'exists' => is_file($path),
            'bytes' => is_file($path) ? filesize($path) : null, 'sha256' => is_file($path) ? hash_file('sha256', $path) : null];
    }
    print json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
} catch (Throwable $error) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    // Do not echo exceptions: connection errors may contain credentials/DSNs.
    fwrite(STDERR, 'STOP: read-only preflight failed (' . get_class($error) . ")\n");
    exit(1);
}

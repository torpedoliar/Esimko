<?php
/**
 * CLI-only restore proof for the safety backup produced by production-backup.php.
 * Creates and drops ONLY a temporary database prefixed esimko_restore_check_.
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

$root = 'C:/IIS/Esimko';
$backupDir = isset($argv[1]) ? str_replace('\\', '/', rtrim($argv[1], '/\\')) : '';
if ($backupDir === '' || !is_file($backupDir . '/manifest.json')) {
    fwrite(STDERR, "STOP: backup manifest not found\n");
    exit(1);
}
if (strpos($backupDir, $root . '/backups/safety_') !== 0) {
    fwrite(STDERR, "STOP: backup path outside expected private root\n");
    exit(1);
}
$manifest = json_decode(file_get_contents($backupDir . '/manifest.json'), true);
if (!is_array($manifest)) {
    fwrite(STDERR, "STOP: manifest unreadable\n");
    exit(1);
}

require $root . '/vendor/autoload.php';
$app = require $root . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$config = config('database.connections.mysql');
$source = $config['database'];
$temp = 'esimko_restore_check_' . date('Ymd_His');
if ($temp === $source || strpos($temp, 'esimko_restore_check_') !== 0) {
    fwrite(STDERR, "STOP: unsafe temporary database name\n");
    exit(1);
}
if (!preg_match('/\Aesimko_restore_check_[0-9]{8}_[0-9]{6}\z/', $temp)) {
    fwrite(STDERR, "STOP: temporary database name guard failed\n");
    exit(1);
}

$credentialsFile = $backupDir . '/.restore-client.cnf';
file_put_contents($credentialsFile, "[client]\nuser=" . $config['username'] . "\n"
    . 'password="' . str_replace(['\\', '"'], ['\\\\', '\\"'], (string) $config['password']) . "\"\n"
    . 'host=' . $config['host'] . "\n");
exec('icacls "' . $credentialsFile . '" /inheritance:r /grant:r "*S-1-5-32-544:F" "*S-1-5-18:F" >NUL 2>&1');

$dump = $backupDir . '/esimko_restore.sql';
$errors = $backupDir . '/restore-dump.err';
$dumpTool = 'C:/tools/mysql/current/bin/mysqldump.exe';
$mysqlTool = 'C:/tools/mysql/current/bin/mysql.exe';
$result = [
    'created_at' => date('c'),
    'source' => $source,
    'temporary_database' => $temp,
    'steps' => [],
    'ok' => false,
];

$created = false;
try {
    $dumpCommand = '"' . $dumpTool . '" --defaults-extra-file="' . $credentialsFile . '"'
        . ' --single-transaction --quick --routines --triggers --events --hex-blob'
        . ' --set-gtid-purged=OFF --default-character-set=utf8mb4 "' . $source . '"'
        . ' > "' . $dump . '" 2> "' . $errors . '"';
    exec($dumpCommand, $dumpOut, $dumpExit);
    $dumpCompleted = false;
    if (is_file($dump)) {
        $handle = fopen($dump, 'rb');
        fseek($handle, max(0, filesize($dump) - 4096));
        $tail = fread($handle, 4096);
        fclose($handle);
        $dumpCompleted = strpos((string) $tail, '-- Dump completed') !== false;
    }
    $result['steps'][] = ['portable_dump', $dumpExit === 0 && $dumpCompleted, is_file($dump) ? filesize($dump) : null];
    $result['portable_dump_sha256'] = is_file($dump) ? hash_file('sha256', $dump) : null;
    if ($dumpExit !== 0 || !$dumpCompleted) {
        throw new RuntimeException('Portable dump failed');
    }

    \Illuminate\Support\Facades\DB::statement('CREATE DATABASE `' . $temp . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $created = true;
    $result['steps'][] = ['create_temp_database', true, null];

    $importCommand = '"' . $mysqlTool . '" --defaults-extra-file="' . $credentialsFile . '"'
        . ' --default-character-set=utf8mb4 "' . $temp . '" < "' . $dump . '"'
        . ' 2> "' . $backupDir . '/restore-import.err"';
    exec($importCommand, $importOut, $importExit);
    $result['steps'][] = ['import', $importExit === 0, $importExit];
    if ($importExit !== 0) {
        $lines = @file($backupDir . '/restore-import.err', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $result['import_error'] = $lines ? substr((string) $lines[0], 0, 200) : '';
        throw new RuntimeException('Import failed');
    }

    $tableCount = \Illuminate\Support\Facades\DB::selectOne(
        'SELECT COUNT(*) AS c FROM information_schema.tables WHERE table_schema = ?', [$temp]
    )->c;
    $sourceTableCount = \Illuminate\Support\Facades\DB::selectOne(
        'SELECT COUNT(*) AS c FROM information_schema.tables WHERE table_schema = ?', [$source]
    )->c;
    $result['table_count'] = ['source' => (int) $sourceTableCount, 'restored' => (int) $tableCount];
    $result['steps'][] = ['table_count', (int) $sourceTableCount === (int) $tableCount, null];

    $rows = [];
    foreach (['anggota', 'transaksi', 'angsuran', 'penjualan', 'device_tokens', 'rilis_apk', 'migrations'] as $table) {
        $rows[$table] = [
            'source' => (int) \Illuminate\Support\Facades\DB::table($table)->count(),
            'restored' => (int) \Illuminate\Support\Facades\DB::table($temp . '.' . $table)->count(),
        ];
    }
    $result['row_counts'] = $rows;

    $schemaMatches = [];
    $schemaDetails = [];
    foreach (['anggota', 'transaksi', 'device_tokens', 'migrations'] as $table) {
        $sourceSchema = \Illuminate\Support\Facades\DB::selectOne('SHOW CREATE TABLE `' . $source . '`.`' . $table . '`')->{'Create Table'};
        $restoredSchema = \Illuminate\Support\Facades\DB::selectOne('SHOW CREATE TABLE `' . $temp . '`.`' . $table . '`')->{'Create Table'};
        $normalize = function ($sql) {
            $sql = preg_replace('/ AUTO_INCREMENT=[0-9]+/', '', str_replace("\r\n", "\n", (string) $sql));
            // MySQL may print an explicit column charset that is identical to the
            // table default; this is cosmetic, not a schema difference.
            return str_replace(' CHARACTER SET utf8mb4', '', $sql);
        };
        $schemaMatches[$table] = $normalize($sourceSchema) === $normalize($restoredSchema);
        if (!$schemaMatches[$table]) {
            $schemaDetails[$table] = [
                'source' => $normalize($sourceSchema),
                'restored' => $normalize($restoredSchema),
            ];
        }
    }
    $result['schema_matches'] = $schemaMatches;
    $result['schema_details'] = $schemaDetails;
    $result['steps'][] = ['schema', !in_array(false, $schemaMatches, true), null];
    $result['ok'] = (int) $sourceTableCount === (int) $tableCount && !in_array(false, $schemaMatches, true);
} catch (Throwable $error) {
    $result['error'] = get_class($error);
} finally {
    if ($created) {
        try {
            \Illuminate\Support\Facades\DB::statement('DROP DATABASE `' . $temp . '`');
            $result['steps'][] = ['drop_temp_database', true, null];
        } catch (Throwable $dropError) {
            $result['steps'][] = ['drop_temp_database', false, get_class($dropError)];
            $result['ok'] = false;
        }
    }
    @unlink($credentialsFile);
    file_put_contents($backupDir . '/restore-check.json', json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
}
exit($result['ok'] ? 0 : 1);

<?php
/**
 * Run only from CLI on the production host. Creates a private backup of the
 * target files and a consistent database dump. Never prints credentials.
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

$root = 'C:/IIS/Esimko';
$stamp = date('Ymd_His');
$dir = $root . '/backups/safety_' . $stamp;
if (!is_dir($dir) && !mkdir($dir, 0700, true)) {
    fwrite(STDERR, "STOP: cannot create backup directory\n");
    exit(1);
}

$restrict = 'icacls "' . $dir . '" /inheritance:r /grant:r "*S-1-5-32-544:(OI)(CI)F" "*S-1-5-18:(OI)(CI)F"';
exec($restrict . ' >NUL 2>&1', $aclOut, $aclCode);

$files = [
    'app/Http/Controllers/MobileController.php',
    'app/Support/FcmSender.php',
    'routes/api.php',
    'version.json',
    'composer.lock',
];
$manifest = [
    'created_at' => date('c'),
    'host' => gethostname(),
    'backup_dir' => $dir,
    'acl_restricted' => $aclCode === 0,
    'files' => [],
    'new_files_expected_absent' => [],
];

foreach ($files as $relative) {
    $source = $root . '/' . $relative;
    if (!is_file($source)) {
        $manifest['files'][$relative] = null;
        continue;
    }
    $target = $dir . '/' . str_replace('/', '__', $relative);
    if (!copy($source, $target)) {
        fwrite(STDERR, "STOP: cannot copy " . $relative . "\n");
        exit(1);
    }
    $manifest['files'][$relative] = [
        'backup' => $target,
        'bytes' => filesize($target),
        'sha256' => hash_file('sha256', $target),
        'matches_source' => hash_file('sha256', $source) === hash_file('sha256', $target),
    ];
}

foreach ([
    'app/Support/MobileSessionGuard.php',
    'app/Support/MobileSavingsIdempotency.php',
    'database/migrations/2026_11_10_000001_create_mobile_savings_requests_table.php',
] as $newFile) {
    $manifest['new_files_expected_absent'][$newFile] = !file_exists($root . '/' . $newFile);
}

require $root . '/vendor/autoload.php';
$app = require $root . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$config = config('database.connections.mysql');
$credentialsFile = $dir . '/.client.cnf';
$credentials = "[client]\nuser=" . $config['username'] . "\n"
    . 'password="' . str_replace(['\\', '"'], ['\\\\', '\\"'], (string) $config['password']) . "\"\n"
    . 'host=' . $config['host'] . "\n";
if (file_put_contents($credentialsFile, $credentials) === false) {
    fwrite(STDERR, "STOP: cannot write temporary client config\n");
    exit(1);
}
exec('icacls "' . $credentialsFile . '" /inheritance:r /grant:r "*S-1-5-32-544:F" "*S-1-5-18:F" >NUL 2>&1');

$dump = $dir . '/esimko.sql';
$errors = $dir . '/dump.err';
$dumpTool = 'C:/tools/mysql/current/bin/mysqldump.exe';
if (!is_file($dumpTool)) {
    unlink($credentialsFile);
    fwrite(STDERR, "STOP: mysqldump not found\n");
    exit(1);
}
$command = '"' . $dumpTool . '" --defaults-extra-file="' . $credentialsFile . '"'
    . ' --single-transaction --quick --routines --triggers --events --hex-blob'
    . ' --default-character-set=utf8mb4 "' . $config['database'] . '"'
    . ' > "' . $dump . '" 2> "' . $errors . '"';
exec($command, $output, $exitCode);
@unlink($credentialsFile);

$dumpOk = $exitCode === 0 && is_file($dump) && filesize($dump) > 100000;
$completed = false;
if (is_file($dump)) {
    $handle = fopen($dump, 'rb');
    if ($handle) {
        fseek($handle, max(0, filesize($dump) - 4096));
        $tail = fread($handle, 4096);
        fclose($handle);
        $completed = strpos((string) $tail, '-- Dump completed') !== false;
    }
}
$manifest['dump'] = [
    'file' => $dump,
    'exit_code' => $exitCode,
    'bytes' => is_file($dump) ? filesize($dump) : null,
    'sha256' => is_file($dump) ? hash_file('sha256', $dump) : null,
    'completed_marker' => $completed,
];
if (is_file($errors)) {
    // Only the first line, never credentials.
    $lines = file($errors, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $manifest['dump_error'] = $lines ? substr((string) $lines[0], 0, 200) : '';
}
$manifest['ok'] = $dumpOk && $completed && !in_array(false, $manifest['new_files_expected_absent'], true);
file_put_contents($dir . '/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
exit($manifest['ok'] ? 0 : 1);

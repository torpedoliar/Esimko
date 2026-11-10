<?php
// Standalone synthetic MySQL proof. Never run through a live application's configuration.
if (getenv('ESIMKO_ISOLATED_MYSQL_TEST') !== '1' || getenv('DB_DATABASE') !== 'esimko_synthetic_test'
    || strpos((string) getenv('DB_SOCKET'), '/tmp/esimko-mysql-test.') !== 0) {
    fwrite(STDERR, "STOP: disposable MySQL harness required\n");
    exit(2);
}
require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.default' => 'mysql', 'database.connections.mysql.unix_socket' => getenv('DB_SOCKET'),
    'database.connections.mysql.database' => 'esimko_synthetic_test', 'database.connections.mysql.username' => 'root',
    'database.connections.mysql.password' => '']);
Illuminate\Support\Facades\DB::purge('mysql');
$schema = Illuminate\Support\Facades\Schema::getConnection();
if (Illuminate\Support\Facades\DB::selectOne('SELECT DATABASE() AS name')->name !== 'esimko_synthetic_test') {
    throw new RuntimeException('Wrong database');
}
// Fresh database only. Fail rather than dropping anything.
foreach (['anggota', 'device_tokens', 'transaksi', 'mobile_savings_requests'] as $table) {
    if (Illuminate\Support\Facades\Schema::hasTable($table)) throw new RuntimeException('Expected empty synthetic DB');
}
Illuminate\Support\Facades\DB::unprepared('CREATE TABLE anggota (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, no_anggota VARCHAR(50), token VARCHAR(100), login_at DATETIME NULL) ENGINE=InnoDB');
Illuminate\Support\Facades\DB::unprepared('CREATE TABLE device_tokens (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, fid_anggota VARCHAR(50), token VARCHAR(600), platform VARCHAR(20), created_at DATETIME NULL, updated_at DATETIME NULL, INDEX member_idx(fid_anggota)) ENGINE=InnoDB');
Illuminate\Support\Facades\DB::table('anggota')->insert([
    ['id' => 1, 'no_anggota' => 'TEST A', 'token' => 'token-a', 'login_at' => date('Y-m-d H:i:s')],
    ['id' => 2, 'no_anggota' => 'TEST B', 'token' => 'token-b', 'login_at' => date('Y-m-d H:i:s')],
]);
function check($condition, $label) { if (!$condition) throw new RuntimeException($label); echo 'PASS: ', $label, "\n"; }
function requestFor($bearer, array $body = []) {
    $request = Illuminate\Http\Request::create('/synthetic-only', 'POST', $body);
    $request->headers->set('Authorization', 'Bearer ' . $bearer);
    return $request;
}
function parallel(array $tasks) {
    Illuminate\Support\Facades\DB::disconnect('mysql');
    $pids = [];
    foreach ($tasks as $task) {
        $pid = pcntl_fork();
        if ($pid === -1) throw new RuntimeException('fork failed');
        if ($pid === 0) {
            try { Illuminate\Support\Facades\DB::purge('mysql'); $task(); exit(0); }
            catch (Throwable $error) { fwrite(STDERR, get_class($error) . ': ' . $error->getMessage() . "\n"); exit(1); }
        }
        $pids[] = $pid;
    }
    foreach ($pids as $pid) { pcntl_waitpid($pid, $status); if (pcntl_wexitstatus($status) !== 0) throw new RuntimeException('child failed'); }
    Illuminate\Support\Facades\DB::purge('mysql');
}
parallel([
    function () { check((new App\Http\Controllers\MobileController)->register_device(requestFor('token-a', ['token' => 'synthetic-shared-device']))->getStatusCode() === 200, 'claim A'); },
    function () { check((new App\Http\Controllers\MobileController)->register_device(requestFor('token-b', ['token' => 'synthetic-shared-device']))->getStatusCode() === 200, 'claim B'); },
]);
check(Illuminate\Support\Facades\DB::table('device_tokens')->where('token', 'synthetic-shared-device')->count() === 1, 'parallel first claims leave one association');
// Lock-wait proof: bearer is valid before the lock but rotated before the late request obtains it.
$barrier = tempnam(sys_get_temp_dir(), 'esimko-barrier-');
unlink($barrier);
parallel([
    function () use ($barrier) {
        Illuminate\Support\Facades\DB::transaction(function () use ($barrier) {
            Illuminate\Support\Facades\DB::table('anggota')->where('id', 1)->lockForUpdate()->first();
            file_put_contents($barrier, 'locked');
            usleep(500000);
            Illuminate\Support\Facades\DB::table('anggota')->where('id', 1)->update(['token' => 'token-a-new']);
        });
    },
    function () use ($barrier) {
        $deadline = microtime(true) + 5;
        while (!is_file($barrier) && microtime(true) < $deadline) usleep(10000);
        check(is_file($barrier), 'rotation barrier reached');
        $response = (new App\Http\Controllers\MobileController)->logout(requestFor('token-a'));
        check($response->getStatusCode() === 401, 'late logout rejects rotated bearer after real lock wait');
    },
]);
check(Illuminate\Support\Facades\DB::table('anggota')->where('id', 1)->value('token') === 'token-a-new', 'replacement bearer survives stale logout');
@unlink($barrier);
echo "PASS: isolated MySQL device concurrency proof complete\n";
Illuminate\Support\Facades\DB::unprepared('CREATE TABLE transaksi (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, fid_anggota VARCHAR(50), fid_status INT, fid_jenis_transaksi INT DEFAULT 3, nominal DECIMAL(15,2), keterangan TEXT) ENGINE=InnoDB');
require __DIR__ . '/../../database/migrations/2026_11_10_000001_create_mobile_savings_requests_table.php';
(new CreateMobileSavingsRequestsTable)->up();
function savingsRequest($amount = '10000') {
    $request = requestFor('token-a-new', ['action' => 'add', 'nominal' => $amount, 'keterangan' => 'synthetic consent']);
    $request->headers->set('Idempotency-Key', '019477a0-7c00-7000-8000-000000000001');
    return $request;
}
function createSavings($request) {
    usleep(200000);
    $id = Illuminate\Support\Facades\DB::table('transaksi')->insertGetId([
        'fid_anggota' => $request->no_anggota, 'fid_status' => 1,
        'nominal' => $request->nominal, 'keterangan' => $request->keterangan,
    ]);
    return App\Support\ApiResponse::success(['id' => $id]);
}
parallel([
    function () { check(App\Support\MobileSavingsIdempotency::run(savingsRequest(), 'setoran', 'createSavings')->getStatusCode() === 200, 'parallel savings first response'); },
    function () { check(App\Support\MobileSavingsIdempotency::run(savingsRequest(), 'setoran', 'createSavings')->getStatusCode() === 200, 'parallel savings replay response'); },
]);
check(Illuminate\Support\Facades\DB::table('transaksi')->count() === 1, 'parallel identical keys create one transaction');
check(Illuminate\Support\Facades\DB::table('mobile_savings_requests')->count() === 1, 'one durable idempotency record');
check(App\Support\MobileSavingsIdempotency::run(savingsRequest('10001'), 'setoran', 'createSavings')->getStatusCode() === 409, 'changed payload conflicts');
Illuminate\Support\Facades\DB::table('transaksi')->update(['fid_status' => 5]);
$replay = App\Support\MobileSavingsIdempotency::run(savingsRequest(), 'setoran', function () { throw new RuntimeException('Replay created again'); });
check($replay->getStatusCode() === 200, 'retry after status change refers to existing transaction');
check(Illuminate\Support\Facades\DB::table('transaksi')->count() === 1, 'cancelled transaction is not recreated');
echo "PASS: isolated MySQL savings concurrency proof complete\n";
function requestWithKey($suffix, $action) {
    $request = savingsRequest();
    $request->merge(['action' => $action]);
    $request->headers->set('Idempotency-Key', '019477a0-7c00-7000-8000-' . str_pad((string) $suffix, 12, '0', STR_PAD_LEFT));
    return $request;
}
// First race order: abandonment commits while an original create waits for the same member lock.
$abandonBarrier = tempnam(sys_get_temp_dir(), 'esimko-abandon-');
unlink($abandonBarrier);
parallel([
    function () use ($abandonBarrier) {
        Illuminate\Support\Facades\DB::transaction(function () use ($abandonBarrier) {
            Illuminate\Support\Facades\DB::table('anggota')->where('id', 1)->lockForUpdate()->first();
            $response = App\Support\MobileSavingsIdempotency::reconcile(requestWithKey(2, 'abandon'), 'setoran');
            check($response->getData(true)['data']['state'] === 'abandoned', 'abandon commits tombstone before delayed creation');
            file_put_contents($abandonBarrier, 'locked');
            usleep(400000);
        });
    },
    function () use ($abandonBarrier) {
        $deadline = microtime(true) + 5;
        while (!is_file($abandonBarrier) && microtime(true) < $deadline) usleep(10000);
        check(is_file($abandonBarrier), 'abandon barrier reached');
        $response = App\Support\MobileSavingsIdempotency::run(requestWithKey(2, 'add'), 'setoran', 'createSavings');
        check($response->getStatusCode() === 409, 'delayed create sees tombstone after lock wait');
    },
]);
check(Illuminate\Support\Facades\DB::table('transaksi')->count() === 1, 'abandon race creates no new transaction');
@unlink($abandonBarrier);
// Second race order: creation wins, reconciliation must return its ID and never authorize discard.
$createBarrier = tempnam(sys_get_temp_dir(), 'esimko-create-');
unlink($createBarrier);
parallel([
    function () use ($createBarrier) {
        Illuminate\Support\Facades\DB::transaction(function () use ($createBarrier) {
            Illuminate\Support\Facades\DB::table('anggota')->where('id', 1)->lockForUpdate()->first();
            $response = App\Support\MobileSavingsIdempotency::run(requestWithKey(3, 'add'), 'setoran', 'createSavings');
            check($response->getStatusCode() === 200, 'creation wins before reconciliation');
            file_put_contents($createBarrier, 'locked');
            usleep(400000);
        });
    },
    function () use ($createBarrier) {
        $deadline = microtime(true) + 5;
        while (!is_file($createBarrier) && microtime(true) < $deadline) usleep(10000);
        check(is_file($createBarrier), 'creation barrier reached');
        $response = App\Support\MobileSavingsIdempotency::reconcile(requestWithKey(3, 'abandon'), 'setoran');
        $data = $response->getData(true)['data'];
        check($data['state'] === 'existing' && $data['id'] > 0, 'reconcile returns committed ID and does not abandon');
    },
]);
check(Illuminate\Support\Facades\DB::table('transaksi')->count() === 2, 'creation race leaves exactly one additional transaction');
@unlink($createBarrier);
echo "PASS: isolated MySQL reconciliation race proof complete\n";

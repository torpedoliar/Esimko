<?php

namespace Tests\Feature;

use App\Support\ApiResponse;
use App\Support\MobileSavingsIdempotency;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

/**
 * Synthetic SQLite memory DB only; no RefreshDatabase or configured DB migrations.
 * SQLite cannot prove row-lock concurrency. Before release, use a disposable
 * MySQL/InnoDB schema with two connections: queue identical keys behind the same
 * member lock, then assert one row/transaction; repeat with different payloads
 * (one 409), and rotate/expire bearer while a request waits (401, no writes).
 */
class MobileSavingsIdempotencyTest extends TestCase
{
    const KEY = '019477a0-7c00-7000-8000-000000000001';

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Carbon::setTestNow(Carbon::now()->startOfSecond());
        Schema::create('anggota', function (Blueprint $table) {
            $table->increments('id');
            $table->string('no_anggota');
            $table->string('token')->nullable();
            $table->dateTime('login_at')->nullable();
        });
        Schema::create('transaksi', function (Blueprint $table) {
            $table->increments('id');
            $table->string('fid_anggota');
            $table->integer('fid_status');
            $table->integer('fid_jenis_transaksi')->default(3);
            $table->decimal('nominal', 15, 2);
            $table->text('keterangan');
        });
        require_once database_path('migrations/2026_11_10_000001_create_mobile_savings_requests_table.php');
        (new \CreateMobileSavingsRequestsTable)->up();
        foreach ([1, 2] as $id) {
            DB::table('anggota')->insert([
                'id' => $id, 'no_anggota' => 'AK ' . $id,
                'token' => 'session-' . $id, 'login_at' => now()->toDateTimeString(),
            ]);
        }
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        DB::disconnect('sqlite');
        parent::tearDown();
    }

    private function request(array $input = [], $key = self::KEY, $token = 'session-1'): Request
    {
        $request = Request::create('/synthetic-savings', 'POST', array_merge([
            'action' => 'add', 'no_anggota' => 'FORGED', 'nominal' => '10.000', 'keterangan' => 'tabungan',
        ], $input));
        if ($key !== null) {
            $request->headers->set('Idempotency-Key', $key);
        }
        if ($token !== null) {
            $request->headers->set('Authorization', 'Bearer ' . $token);
        }
        return $request;
    }

    private function create(Request $request)
    {
        $id = DB::table('transaksi')->insertGetId([
            'fid_anggota' => $request->no_anggota, 'fid_status' => 1,
            'nominal' => $request->nominal, 'keterangan' => $request->keterangan,
        ]);
        return ApiResponse::success(['id' => $id, 'status' => 1], 'OK', null, 201);
    }

    private function runRequest(Request $request, $jenis = 'setoran')
    {
        return MobileSavingsIdempotency::run($request, $jenis, function (Request $normalized) {
            return $this->create($normalized);
        });
    }

    public function test_normalized_retry_returns_current_state_before_callback_or_balance_validation()
    {
        $first = $this->runRequest($this->request());
        $this->assertSame(201, $first->getStatusCode());
        DB::table('transaksi')->update(['fid_status' => 3]);
        $retry = MobileSavingsIdempotency::run($this->request(['nominal' => '00010000'], strtoupper(self::KEY)), 'setoran', function () {
            $this->fail('Replay must not revalidate saldo or create another transaction.');
        });
        $this->assertSame(200, $retry->getStatusCode());
        $this->assertSame([
            'id' => $first->getData(true)['data']['id'],
            'fid_status' => 3, 'fid_jenis_transaksi' => 3, 'jenis' => 'setoran',
        ], $retry->getData(true)['data']);
        $this->assertSame(1, DB::table('transaksi')->count());
        $this->assertSame(1, DB::table('mobile_savings_requests')->count());
        $record = DB::table('mobile_savings_requests')->first();
        $this->assertSame(self::KEY, $record->request_key);
        $this->assertSame('tabungan', $record->keterangan);
        $this->assertSame('AK 1', DB::table('transaksi')->value('fid_anggota'));
    }

    public function test_payload_changes_conflict_including_jenis_and_exact_description()
    {
        $this->runRequest($this->request());
        foreach ([['nominal' => '10001'], ['keterangan' => 'tabungan ']] as $input) {
            $this->assertSame(409, $this->runRequest($this->request($input))->getStatusCode());
        }
        $this->assertSame(409, $this->runRequest($this->request(), 'penarikan')->getStatusCode());
        $this->assertSame(1, DB::table('transaksi')->count());
    }

    public function test_key_is_scoped_to_member_not_untrusted_member_input()
    {
        $one = $this->runRequest($this->request());
        $two = $this->runRequest($this->request([], self::KEY, 'session-2'));
        $this->assertNotSame($one->getData(true)['data']['id'], $two->getData(true)['data']['id']);
        $this->assertSame(['AK 1', 'AK 2'], DB::table('transaksi')->orderBy('id')->pluck('fid_anggota')->all());
    }

    public function test_deleted_or_reassigned_reference_is_controlled_conflict_never_recreated()
    {
        $this->runRequest($this->request());
        DB::table('transaksi')->update(['fid_anggota' => 'AK 2']);
        $this->assertSame(409, $this->runRequest($this->request())->getStatusCode());
        DB::table('transaksi')->delete();
        $this->assertSame(409, $this->runRequest($this->request())->getStatusCode());
        $this->assertSame(0, DB::table('transaksi')->count());
        $this->assertSame(1, DB::table('mobile_savings_requests')->count());
    }

    public function test_failure_response_rolls_back_callback_writes_and_does_not_consume_key()
    {
        $failure = MobileSavingsIdempotency::run($this->request(), 'penarikan', function (Request $request) {
            $this->create($request);
            return ApiResponse::error('Saldo simpanan tidak mencukupi', 422);
        });
        $this->assertSame(422, $failure->getStatusCode());
        $this->assertSame(0, DB::table('transaksi')->count());
        $this->assertSame(0, DB::table('mobile_savings_requests')->count());
        $this->assertSame(201, $this->runRequest($this->request(), 'penarikan')->getStatusCode());
    }

    public function test_exception_rolls_back_all_writes()
    {
        try {
            MobileSavingsIdempotency::run($this->request(), 'setoran', function (Request $request) {
                $this->create($request);
                throw new RuntimeException('synthetic failure');
            });
            $this->fail('Expected callback exception.');
        } catch (RuntimeException $exception) {
            $this->assertSame('synthetic failure', $exception->getMessage());
        }
        $this->assertSame(0, DB::table('transaksi')->count());
        $this->assertSame(0, DB::table('mobile_savings_requests')->count());
    }

    public function test_success_without_owned_reference_is_rolled_back()
    {
        $response = MobileSavingsIdempotency::run($this->request(), 'setoran', function () {
            return ApiResponse::success(['id' => 999]);
        });
        $this->assertSame(409, $response->getStatusCode());
        $this->assertSame(0, DB::table('mobile_savings_requests')->count());
    }

    public function test_bearer_is_revalidated_before_creation_and_even_before_replay()
    {
        $this->runRequest($this->request());
        DB::table('anggota')->where('id', 1)->update(['token' => 'replacement']);
        $this->assertSame(401, $this->runRequest($this->request())->getStatusCode());
        DB::table('anggota')->where('id', 1)->update(['token' => 'session-1', 'login_at' => now()->subDays(31)->toDateTimeString()]);
        $this->assertSame(401, $this->runRequest($this->request())->getStatusCode());
        DB::table('anggota')->where('id', 1)->update(['login_at' => 'not-a-date']);
        $this->assertSame(401, $this->runRequest($this->request())->getStatusCode());
        foreach ([null, 'unknown'] as $token) {
            $this->assertSame(401, $this->runRequest($this->request([], self::KEY, $token))->getStatusCode());
        }
        $this->assertSame(1, DB::table('transaksi')->count());
    }

    public function test_invalid_nominal_and_key_are_rejected_without_writes()
    {
        foreach (['0', 0, '-1', '+1', '1.00', '1,000', '1e3', ' 100 ', '1.234.56', '1000000000000', '9007199254740993', '999999999999999999999999', 1.5, 1000.0, [], null] as $nominal) {
            $this->assertSame(422, $this->runRequest($this->request(['nominal' => $nominal]))->getStatusCode(), var_export($nominal, true));
        }
        foreach (['', 'not-uuid', self::KEY . ' ', '00000000-0000-0000-0000-000000000000', '019477a0-7c00-7000-0000-000000000001'] as $key) {
            $this->assertSame(422, $this->runRequest($this->request([], $key))->getStatusCode());
        }
        $this->assertSame(422, $this->runRequest($this->request(['keterangan' => []]))->getStatusCode());
        $this->assertSame(0, DB::table('transaksi')->count());
        $this->assertSame(0, DB::table('mobile_savings_requests')->count());
    }

    public function test_upper_bound_integer_and_grouped_amounts_are_accepted()
    {
        foreach ([1, '999999999999', '999.999.999.999'] as $index => $nominal) {
            $key = '019477a0-7c00-7000-8000-' . str_pad((string) ($index + 10), 12, '0', STR_PAD_LEFT);
            $this->assertSame(201, $this->runRequest($this->request(['nominal' => $nominal], $key))->getStatusCode());
        }
    }

    public function test_null_and_empty_description_share_a_stable_fingerprint()
    {
        $one = $this->runRequest($this->request(['keterangan' => null]));
        $two = $this->runRequest($this->request(['keterangan' => '']));
        $this->assertSame($one->getData(true)['data']['id'], $two->getData(true)['data']['id']);
        $this->assertSame(200, $two->getStatusCode());
        $this->assertSame(1, DB::table('transaksi')->count());
        $this->assertSame('', DB::table('mobile_savings_requests')->value('keterangan'));
    }

    public function test_description_length_limit_is_2400_unicode_characters()
    {
        $this->assertSame(422, $this->runRequest($this->request(['keterangan' => str_repeat('a', 2401)]))->getStatusCode());
        $this->assertSame(422, $this->runRequest($this->request(['keterangan' => str_repeat('é', 2401)]))->getStatusCode());
        $this->assertSame(0, DB::table('transaksi')->count());
        $this->assertSame(201, $this->runRequest($this->request(['keterangan' => str_repeat('é', 2400)]))->getStatusCode());
    }

    public function test_existing_cancelled_or_rejected_rows_return_current_state_without_recreation()
    {
        $first = $this->runRequest($this->request(), 'penarikan');
        foreach ([2, 4, 5] as $status) {
            DB::table('transaksi')->update(['fid_status' => $status, 'fid_jenis_transaksi' => 6]);
            $retry = MobileSavingsIdempotency::run($this->request(), 'penarikan', function () {
                $this->fail('An existing transaction must never be recreated regardless of status.');
            });
            $this->assertSame(200, $retry->getStatusCode());
            $this->assertSame([
                'id' => $first->getData(true)['data']['id'],
                'fid_status' => $status,
                'fid_jenis_transaksi' => 6, 'jenis' => 'penarikan',
            ], $retry->getData(true)['data']);
        }
        $this->assertSame(1, DB::table('transaksi')->count());
        $this->assertSame(1, DB::table('mobile_savings_requests')->count());
    }

    public function test_missing_header_passes_through_legacy_callback_without_idempotency_record()
    {
        $request = $this->request(['no_anggota' => 'legacy'], null, null);
        $response = MobileSavingsIdempotency::run($request, 'setoran', function (Request $original) use ($request) {
            $this->assertSame($request, $original);
            return ApiResponse::error('legacy response');
        });
        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame(0, DB::table('mobile_savings_requests')->count());
    }
}

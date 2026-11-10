<?php

namespace Tests\Feature;

use App\Support\ApiResponse;
use App\Support\MobileSavingsIdempotency;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Synthetic SQLite memory DB only. Sequential tests exercise BOTH serial orders;
 * they do not prove InnoDB locking. Race create and reconcile on two connections
 * to a disposable MySQL schema before release, including bearer rotation while
 * waiting on the member lock. Never run this suite against a configured DB.
 */
class MobileSavingsReconciliationTest extends TestCase
{
    const KEY = '019477a0-7c00-7000-8000-000000000001';
    const NEW_KEY = '019477a0-7c00-7000-8000-000000000002';

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
            $table->integer('fid_jenis_transaksi');
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

    private function request(array $input = [], $key = self::KEY, $token = 'session-1', $method = 'POST'): Request
    {
        $request = Request::create('/synthetic-savings/reconcile', $method, array_merge([
            'action' => 'abandon', 'no_anggota' => 'FORGED',
            'nominal' => '10.000', 'keterangan' => 'tabungan',
        ], $input));
        if ($key !== null) {
            $request->headers->set('Idempotency-Key', $key);
        }
        if ($token !== null) {
            $request->headers->set('Authorization', 'Bearer ' . $token);
        }
        return $request;
    }

    private function create(Request $request, $jenis = 'setoran')
    {
        $request->merge(['action' => 'add']);
        return MobileSavingsIdempotency::run($request, $jenis, function (Request $normalized) use ($jenis) {
            $id = DB::table('transaksi')->insertGetId([
                'fid_anggota' => $normalized->no_anggota, 'fid_status' => 1,
                'fid_jenis_transaksi' => $jenis === 'setoran' ? 3 : 6,
                'nominal' => $normalized->nominal, 'keterangan' => $normalized->keterangan,
            ]);
            return ApiResponse::success(['id' => $id], 'OK', null, 201);
        });
    }

    private function reconcile(Request $request, $jenis = 'setoran')
    {
        return MobileSavingsIdempotency::reconcile($request, $jenis);
    }

    public function test_absent_draft_becomes_durable_tombstone_and_blocks_delayed_original_create()
    {
        $abandoned = $this->reconcile($this->request());
        $this->assertSame(200, $abandoned->getStatusCode());
        $this->assertSame(['state' => 'abandoned', 'id' => null, 'jenis' => 'setoran'], $abandoned->getData(true)['data']);
        $record = DB::table('mobile_savings_requests')->first();
        $this->assertSame('abandoned', $record->state);
        $this->assertNull($record->transaction_id);
        $this->assertSame('10000.00', number_format((float) $record->nominal, 2, '.', ''));
        $this->assertSame(1, (int) $record->member_id);

        $delayed = $this->request(['action' => 'add']);
        $response = MobileSavingsIdempotency::run($delayed, 'setoran', function () {
            $this->fail('A delayed original create must not run after committed abandonment.');
        });
        $this->assertSame(409, $response->getStatusCode());
        $this->assertSame(0, DB::table('transaksi')->count());
        $this->assertSame(1, DB::table('mobile_savings_requests')->count());
        $this->assertSame(201, $this->create($this->request([], self::NEW_KEY))->getStatusCode());
        $this->assertSame(1, DB::table('transaksi')->count());
    }

    public function test_repeated_abandonment_matches_normalized_original_payload_and_is_idempotent()
    {
        $first = $this->reconcile($this->request(['keterangan' => null]), 'penarikan');
        $retry = $this->reconcile($this->request(['nominal' => '00010000', 'keterangan' => ''], strtoupper(self::KEY)), 'penarikan');
        $this->assertSame(200, $retry->getStatusCode());
        $this->assertSame($first->getData(true), $retry->getData(true));
        $this->assertSame(1, DB::table('mobile_savings_requests')->count());
        $this->assertSame(0, DB::table('transaksi')->count());
    }

    public function test_existing_transaction_always_wins_and_returns_id_current_state_without_cancelling()
    {
        $created = $this->create($this->request(), 'penarikan');
        $id = $created->getData(true)['data']['id'];
        foreach ([1, 2, 3, 4, 5] as $status) {
            DB::table('transaksi')->where('id', $id)->update(['fid_status' => $status]);
            $response = $this->reconcile($this->request(), 'penarikan');
            $this->assertSame(200, $response->getStatusCode());
            $this->assertSame([
                'id' => $id, 'fid_status' => $status, 'fid_jenis_transaksi' => 6,
                'jenis' => 'penarikan', 'state' => 'existing',
            ], $response->getData(true)['data']);
            $this->assertSame($status, (int) DB::table('transaksi')->value('fid_status'));
        }
        $this->assertSame('completed', DB::table('mobile_savings_requests')->value('state'));
        $this->assertSame($id, (int) DB::table('mobile_savings_requests')->value('transaction_id'));
        $this->assertSame(200, $this->create($this->request(), 'penarikan')->getStatusCode());
        $this->assertSame(1, DB::table('transaksi')->count());
    }

    public function test_definitive_validation_error_can_be_abandoned_without_revalidating_balance()
    {
        foreach ([400, 422] as $code) {
            $key = $code === 400 ? self::KEY : self::NEW_KEY;
            $failed = MobileSavingsIdempotency::run($this->request(['action' => 'add'], $key), 'penarikan', function (Request $normalized) use ($code) {
                DB::table('transaksi')->insert([
                    'fid_anggota' => $normalized->no_anggota, 'fid_status' => 1,
                    'fid_jenis_transaksi' => 6, 'nominal' => $normalized->nominal, 'keterangan' => $normalized->keterangan,
                ]);
                return ApiResponse::error('Definitive validation failure', $code);
            });
            $this->assertSame($code, $failed->getStatusCode());
            $this->assertSame(0, DB::table('transaksi')->count());
            $response = $this->reconcile($this->request([], $key), 'penarikan');
            $this->assertSame('abandoned', $response->getData(true)['data']['state']);
            $this->assertSame(409, $this->create($this->request([], $key), 'penarikan')->getStatusCode());
        }
        $this->assertSame(2, DB::table('mobile_savings_requests')->count());
        $this->assertSame(0, DB::table('transaksi')->count());
    }

    public function test_payload_mismatch_never_abandons_or_discards_existing_or_tombstoned_key()
    {
        $this->create($this->request());
        $this->reconcile($this->request([], self::NEW_KEY));
        foreach ([self::KEY, self::NEW_KEY] as $key) {
            foreach ([['nominal' => '10001'], ['keterangan' => 'tabungan ']] as $input) {
                $this->assertSame(409, $this->reconcile($this->request($input, $key))->getStatusCode());
            }
            $this->assertSame(409, $this->reconcile($this->request([], $key), 'penarikan')->getStatusCode());
        }
        $this->assertSame(['completed', 'abandoned'], DB::table('mobile_savings_requests')->orderBy('id')->pluck('state')->all());
        $this->assertSame(1, DB::table('transaksi')->count());
    }

    public function test_account_scope_and_forged_member_number_cannot_abandon_another_accounts_transaction()
    {
        $created = $this->create($this->request());
        $other = $this->reconcile($this->request(['no_anggota' => 'AK 1'], self::KEY, 'session-2'));
        $this->assertSame('abandoned', $other->getData(true)['data']['state']);
        $owner = $this->reconcile($this->request(['no_anggota' => 'AK 2']));
        $this->assertSame('existing', $owner->getData(true)['data']['state']);
        $this->assertSame($created->getData(true)['data']['id'], $owner->getData(true)['data']['id']);
        $this->assertSame([1, 2], array_map('intval', DB::table('mobile_savings_requests')->orderBy('id')->pluck('member_id')->all()));
        $this->assertSame(1, DB::table('transaksi')->count());
    }

    public function test_missing_foreign_or_corrupt_reference_fails_closed_instead_of_authorizing_discard()
    {
        $this->create($this->request());
        DB::table('transaksi')->update(['fid_anggota' => 'AK 2']);
        $this->assertSame(409, $this->reconcile($this->request())->getStatusCode());
        DB::table('transaksi')->delete();
        $this->assertSame(409, $this->reconcile($this->request())->getStatusCode());
        DB::table('mobile_savings_requests')->update(['transaction_id' => null]);
        $this->assertSame(409, $this->reconcile($this->request())->getStatusCode());
        DB::table('mobile_savings_requests')->update(['state' => 'unknown']);
        $this->assertSame(409, $this->reconcile($this->request())->getStatusCode());
        DB::table('mobile_savings_requests')->update(['state' => 'abandoned', 'transaction_id' => 999]);
        $this->assertSame(409, $this->reconcile($this->request())->getStatusCode());
        $this->assertSame(409, $this->create($this->request())->getStatusCode());
        $this->assertSame(0, DB::table('transaksi')->count());
        $this->assertSame(1, DB::table('mobile_savings_requests')->count());
    }

    public function test_invalid_rotated_and_expired_bearer_cannot_abandon_absent_existing_or_tombstoned_draft()
    {
        $this->create($this->request());
        $this->reconcile($this->request([], self::NEW_KEY));
        $absent = '019477a0-7c00-7000-8000-000000000003';
        foreach ([self::KEY, self::NEW_KEY, $absent] as $key) {
            foreach ([null, 'unknown'] as $token) {
                $this->assertSame(401, $this->reconcile($this->request([], $key, $token))->getStatusCode());
            }
        }
        DB::table('anggota')->where('id', 1)->update(['token' => 'replacement']);
        $this->assertSame(401, $this->reconcile($this->request())->getStatusCode());
        DB::table('anggota')->where('id', 1)->update(['token' => 'session-1', 'login_at' => now()->subDays(31)->toDateTimeString()]);
        foreach ([self::KEY, self::NEW_KEY, $absent] as $key) {
            $this->assertSame(401, $this->reconcile($this->request([], $key))->getStatusCode());
        }
        $this->assertSame(2, DB::table('mobile_savings_requests')->count());
        $this->assertSame(1, DB::table('transaksi')->count());
    }

    public function test_reconciliation_requires_explicit_post_action_uuid_and_valid_original_payload()
    {
        foreach ([null, '', 'bad-key'] as $key) {
            $this->assertSame(422, $this->reconcile($this->request([], $key))->getStatusCode());
        }
        foreach ([['action' => 'add'], ['action' => null], ['nominal' => '0'], ['nominal' => 10000.0], ['keterangan' => []], ['keterangan' => str_repeat('a', 2401)]] as $input) {
            $this->assertSame(422, $this->reconcile($this->request($input))->getStatusCode());
        }
        $this->assertSame(422, $this->reconcile($this->request(), 'invalid')->getStatusCode());
        foreach (['GET', 'DELETE', 'PUT'] as $method) {
            $this->assertSame(405, $this->reconcile($this->request([], self::KEY, 'session-1', $method))->getStatusCode());
        }
        $this->assertSame(0, DB::table('mobile_savings_requests')->count());
        $this->assertSame(0, DB::table('transaksi')->count());
    }

    public function test_storage_failure_never_returns_abandoned_success_or_leaves_a_tombstone()
    {
        DB::statement("CREATE TRIGGER fail_reconciliation BEFORE INSERT ON mobile_savings_requests BEGIN SELECT RAISE(ABORT, 'synthetic storage failure'); END");
        try {
            $this->reconcile($this->request());
            $this->fail('Storage failure must propagate, not authorize discarding the pending draft.');
        } catch (QueryException $exception) {
            $this->assertStringContainsString('synthetic storage failure', $exception->getMessage());
        }
        $this->assertSame(0, DB::table('mobile_savings_requests')->count());
        $this->assertSame(0, DB::table('transaksi')->count());
        $this->assertSame(0, DB::connection()->transactionLevel());
    }
}

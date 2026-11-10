<?php

namespace Tests\Feature;

use App\Anggota;
use App\DeviceToken;
use App\Http\Controllers\MobileController;
use App\Http\Middleware\MobileAuth;
use App\Support\MobileSessionGuard;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use LogicException;
use RuntimeException;
use Tests\TestCase;

/**
 * Synthetic in-memory schema only: these tests never migrate the configured DB.
 * SQLite proves state transitions/rollback, NOT SELECT FOR UPDATE concurrency.
 * MySQL/InnoDB follow-up (two independent connections and explicit barriers):
 *  - pause logout/register after middleware; rotate login; release the late
 *    request and assert 401, new bearer intact, no stale device association;
 *  - hold member lock during registration, queue logout, release registration,
 *    then assert logout clears bearer and every association for that member;
 *  - concurrently claim the same previously absent FCM token as two members;
 *    assert both successful calls serialize to exactly one final association;
 *  - run claim-vs-logout in both commit orders, including the mutex member;
 *    assert no stale reattachment and no unhandled deadlock after retries.
 * Use a disposable local database only; do not use production credentials.
 */
class MobileDeviceSessionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Carbon::setTestNow(Carbon::now());
        Schema::create('anggota', function (Blueprint $table) {
            $table->increments('id');
            $table->string('no_anggota');
            $table->string('nama_lengkap');
            $table->string('password');
            $table->integer('fid_status');
            $table->string('token')->nullable();
            $table->dateTime('login_at')->nullable();
            $table->string('avatar')->nullable();
        });
        Schema::create('user_akses', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('fid_anggota');
            $table->integer('fid_hak_akses');
        });
        Schema::create('device_tokens', function (Blueprint $table) {
            $table->increments('id');
            $table->string('fid_anggota', 50)->index();
            // Deliberately match production's NON-unique token index.
            $table->string('token', 600)->index();
            $table->string('platform', 20)->default('android');
            $table->timestamps();
        });
        foreach ([1 => 'AK 1001', 2 => 'AK 1002'] as $id => $number) {
            DB::table('anggota')->insert([
                'id' => $id, 'no_anggota' => $number, 'nama_lengkap' => 'Member ' . $id,
                'password' => Hash::make('secret'), 'fid_status' => 2,
                'token' => 'session-' . $id, 'login_at' => now()->toDateTimeString(),
            ]);
            DB::table('user_akses')->insert(['fid_anggota' => $id, 'fid_hak_akses' => 2]);
        }
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function request($bearer = 'session-1', array $input = []): Request
    {
        $request = Request::create('/synthetic-mobile', 'POST', $input);
        if ($bearer !== null) {
            $request->headers->set('Authorization', 'Bearer ' . $bearer);
        }
        return $request;
    }

    private function authenticate(Request $request): void
    {
        $passed = false;
        (new MobileAuth)->handle($request, function () use (&$passed) {
            $passed = true;
        });
        $this->assertTrue($passed, 'Request must pass middleware before the simulated session change.');
    }

    private function device($member = 'AK 1001', $token = 'fcm-one'): void
    {
        DeviceToken::create(['fid_anggota' => $member, 'token' => $token, 'platform' => 'android']);
    }

    private function login($password = 'secret')
    {
        return (new MobileController)->login($this->request(null, [
            'username' => 'AK 1001', 'password' => $password,
        ]));
    }

    public function test_locked_member_requires_an_active_transaction()
    {
        $this->expectException(LogicException::class);
        MobileSessionGuard::lockedMember($this->request());
    }

    public function test_registration_mutex_requires_an_active_transaction()
    {
        $this->expectException(LogicException::class);
        MobileSessionGuard::lockDeviceRegistration();
    }

    public function test_locked_member_ignores_forged_member_number()
    {
        $member = DB::transaction(function () {
            return MobileSessionGuard::lockedMember($this->request('session-1', ['no_anggota' => 'AK 1002']));
        });
        $this->assertInstanceOf(Anggota::class, $member);
        $this->assertSame('AK 1001', $member->no_anggota);
    }

    public function test_locked_member_rejects_missing_unknown_and_expired_bearers()
    {
        foreach ([null, 'unknown'] as $token) {
            $this->assertNull(DB::transaction(function () use ($token) {
                return MobileSessionGuard::lockedMember($this->request($token));
            }));
        }
        foreach ([now()->subDays(30)->subSecond()->toDateTimeString(), 'not-a-date'] as $date) {
            DB::table('anggota')->where('id', 1)->update(['login_at' => $date]);
            $this->assertNull(DB::transaction(function () {
                return MobileSessionGuard::lockedMember($this->request());
            }));
        }
    }

    public function test_exact_thirty_day_boundary_and_legacy_null_date_match_middleware()
    {
        foreach ([now()->subDays(30)->toDateTimeString(), null] as $date) {
            DB::table('anggota')->where('id', 1)->update(['login_at' => $date]);
            $this->assertInstanceOf(Anggota::class, DB::transaction(function () {
                return MobileSessionGuard::lockedMember($this->request());
            }));
        }
    }

    public function test_every_successful_login_rotates_without_silencing_other_devices()
    {
        $this->device();
        $this->device('AK 1002', 'other-device');
        $first = $this->login();
        $this->assertSame(200, $first->getStatusCode());
        $firstToken = $first->getData(true)['data']['token'];
        $this->assertNotSame('session-1', $firstToken);
        $this->assertSame($firstToken, Anggota::find(1)->token);
        // Multi-device push must survive a login on another device.
        $this->assertSame(1, DeviceToken::where('fid_anggota', 'AK 1001')->count());
        $this->assertSame(1, DeviceToken::where('fid_anggota', 'AK 1002')->count());
        $second = $this->login();
        $this->assertSame(200, $second->getStatusCode());
        $this->assertNotSame($firstToken, $second->getData(true)['data']['token']);
    }

    public function test_bad_password_does_not_rotate_or_detach_devices()
    {
        $this->device();
        $this->assertSame(401, $this->login('wrong')->getStatusCode());
        $this->assertSame('session-1', Anggota::find(1)->token);
        $this->assertSame(1, DeviceToken::count());
    }

    public function test_logout_uses_bearer_owner_and_detaches_only_its_associations()
    {
        $this->device();
        $this->device('AK 1001', 'second-device');
        $this->device('AK 1002', 'other-device');
        $response = (new MobileController)->logout($this->request('session-1', ['no_anggota' => 'AK 1002']));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertNull(Anggota::find(1)->token);
        $this->assertNull(Anggota::find(1)->login_at);
        $this->assertSame('session-2', Anggota::find(2)->token);
        $this->assertSame(['AK 1002'], DeviceToken::pluck('fid_anggota')->all());
    }

    public function test_late_logout_after_login_cannot_revoke_replacement_session()
    {
        $late = $this->request();
        $this->authenticate($late);
        $newToken = $this->login()->getData(true)['data']['token'];
        $this->device('AK 1001', 'new-session-device');
        $this->assertSame(401, (new MobileController)->logout($late)->getStatusCode());
        $this->assertSame($newToken, Anggota::find(1)->token);
        $this->assertSame(1, DeviceToken::count());
    }

    public function test_expiry_after_middleware_rejects_logout_and_registration()
    {
        $late = $this->request('session-1', ['token' => 'new-device']);
        $this->authenticate($late);
        $this->device();
        DB::table('anggota')->where('id', 1)->update(['login_at' => now()->subDays(31)->toDateTimeString()]);
        $controller = new MobileController;
        $this->assertSame(401, $controller->logout($late)->getStatusCode());
        $this->assertSame(401, $controller->register_device($late)->getStatusCode());
        $this->assertSame('session-1', Anggota::find(1)->token);
        $this->assertSame(['fcm-one'], DeviceToken::pluck('token')->all());
    }

    public function test_registration_reassigns_existing_duplicates_to_bearer_owner()
    {
        $this->device('AK 1002', 'shared-device');
        $this->device('AK 1002', 'shared-device');
        $this->device('AK 1002', 'unrelated-device');
        $request = $this->request('session-1', ['token' => ' shared-device ', 'no_anggota' => 'AK 1002']);
        $controller = new MobileController;
        $this->assertSame(200, $controller->register_device($request)->getStatusCode());
        $this->assertSame(200, $controller->register_device($request)->getStatusCode());
        $this->assertSame(1, DeviceToken::where('token', 'shared-device')->count());
        $this->assertSame('AK 1001', DeviceToken::where('token', 'shared-device')->value('fid_anggota'));
        $this->assertSame('AK 1002', DeviceToken::where('token', 'unrelated-device')->value('fid_anggota'));
    }

    public function test_late_registration_after_logout_or_rotation_cannot_reattach()
    {
        $late = $this->request('session-1', ['token' => 'late-device']);
        $this->authenticate($late);
        $controller = new MobileController;
        $this->assertSame(200, $controller->logout($this->request())->getStatusCode());
        $this->assertSame(401, $controller->register_device($late)->getStatusCode());
        $newToken = $this->login()->getData(true)['data']['token'];
        $this->assertSame(401, $controller->register_device($late)->getStatusCode());
        $this->assertSame($newToken, Anggota::find(1)->token);
        $this->assertSame(0, DeviceToken::count());
    }

    public function test_unknown_or_missing_bearer_cannot_logout_or_claim_device()
    {
        $this->device();
        foreach ([null, 'unknown'] as $token) {
            $request = $this->request($token, ['no_anggota' => 'AK 1001', 'token' => 'fcm-one']);
            $this->assertSame(401, (new MobileController)->logout($request)->getStatusCode());
            $this->assertSame(401, (new MobileController)->register_device($request)->getStatusCode());
        }
        $this->assertSame('session-1', Anggota::find(1)->token);
        $this->assertSame(1, DeviceToken::count());
    }

    public function test_registration_insert_failure_rolls_back_previous_association_deletion()
    {
        $this->device('AK 1002', 'shared-device');
        DeviceToken::creating(function () {
            throw new RuntimeException('Synthetic insert failure');
        });
        try {
            (new MobileController)->register_device($this->request('session-1', ['token' => 'shared-device']));
            $this->fail('Expected synthetic insert failure.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Synthetic insert failure', $exception->getMessage());
        } finally {
            DeviceToken::flushEventListeners();
        }
        $this->assertSame(1, DeviceToken::count());
        $this->assertSame('AK 1002', DeviceToken::where('token', 'shared-device')->value('fid_anggota'));
    }

    public function test_logout_save_failure_rolls_back_device_deletion()
    {
        $this->device();
        Anggota::saving(function () {
            throw new RuntimeException('Synthetic member save failure');
        });
        try {
            (new MobileController)->logout($this->request());
            $this->fail('Expected synthetic member save failure.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Synthetic member save failure', $exception->getMessage());
        } finally {
            Anggota::flushEventListeners();
        }
        $this->assertSame('session-1', Anggota::find(1)->token);
        $this->assertSame(1, DeviceToken::count());
    }
}

<?php
namespace Tests\Feature;

use App\Support\FcmSender;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SyntheticFcmSender extends FcmSender
{
    public static $messages = [];
    public static function fromFile($path) { return new static([]); }
    public function send($token, $title, $body, array $data = []) {
        self::$messages[] = [$token, $title, $body, $data];
        // A concurrent logout removes the remaining association before dispatch.
        DB::table('device_tokens')->where('token', 'removed-device')->delete();
        return true;
    }
}

class MobilePushPrivacyTest extends TestCase
{
    public function test_push_is_generic_deduplicated_and_rechecks_association()
    {
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('device_tokens', function (Blueprint $t) {
            $t->increments('id'); $t->string('fid_anggota'); $t->string('token');
        });
        foreach (['current-device', 'current-device', 'removed-device'] as $token) {
            DB::table('device_tokens')->insert(['fid_anggota' => 'TEST A', 'token' => $token]);
        }
        DB::table('device_tokens')->insert(['fid_anggota' => 'TEST B', 'token' => 'other-device']);
        SyntheticFcmSender::$messages = [];
        $sent = SyntheticFcmSender::pushToAnggota('TEST A', 'Private decision', 'Private rejection reason', ['id' => '123']);
        $this->assertSame(1, $sent);
        $this->assertCount(1, SyntheticFcmSender::$messages);
        $message = SyntheticFcmSender::$messages[0];
        $this->assertSame('current-device', $message[0]);
        $this->assertSame('Pembaruan eSIMKO', $message[1]);
        $this->assertStringNotContainsString('Private', $message[2]);
        $this->assertSame(['id' => '123'], $message[3]);
    }
}

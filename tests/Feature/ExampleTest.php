<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        // Landing reads the latest APK release; keep this smoke test independent of MySQL.
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('rilis_apk', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('version_code');
        });
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}

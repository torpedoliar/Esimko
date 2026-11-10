<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMobileSavingsRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('mobile_savings_requests', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->unsignedBigInteger('member_id');
            // Canonical lowercase UUID; compact unique index, even on utf8mb4.
            $table->char('request_key', 36);
            $table->string('jenis', 12);
            $table->decimal('nominal', 15, 2);
            $table->text('keterangan');
            $table->char('fingerprint', 64);
            // Intentionally no cascading FK: retain the ledger on deletion.
            // Abandonment is durable even when no transaction was ever created.
            $table->unsignedBigInteger('transaction_id')->nullable();
            $table->string('state', 12)->default('completed');
            $table->timestamps();
            $table->unique(['member_id', 'request_key'], 'mobile_savings_member_key_unique');
            $table->index('transaction_id', 'mobile_savings_transaction_index');
        });
    }

    public function down()
    {
        Schema::dropIfExists('mobile_savings_requests');
    }
}

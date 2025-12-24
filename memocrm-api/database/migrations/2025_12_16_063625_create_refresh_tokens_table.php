<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('refresh_tokens', function (Blueprint $table) {
            $table->increments('seq_cd');
            $table->unsignedInteger('user_cd');
            $table->string('token_hash', 64)->unique();
            $table->string('device_name', 100)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('expires_time');
            $table->timestamp('revoked_time')->nullable();
            $table->unsignedInteger('replaced_by_seq_cd')->nullable();
            $table->dateTime('create_time')->useCurrent();
            $table->dateTime('update_time')->nullable();

            $table->foreign('user_cd')->references('user_cd')->on('users')->cascadeOnDelete();

            $table->index(['user_cd', 'expires_time']);
            $table->index(['user_cd', 'revoked_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refresh_tokens');
    }
};

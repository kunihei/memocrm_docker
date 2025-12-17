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
            $table->integer('seq_cd')->primary();
            $table->integer('user_id');
            $table->string('token_hash', 64)->unique();
            $table->string('device_name')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('expires_time');
            $table->timestamp('revoked_time')->nullable();
            $table->integer('replaced_by_token_id')->nullable();
            $table->dateTime('create_time')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('update_time')->nullable();

            $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();

            $table->index(['user_id', 'expires_time']);
            $table->index(['user_id', 'revoked_time']);
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

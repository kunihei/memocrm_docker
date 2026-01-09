<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('co_memos', function (Blueprint $table) {
            $table->increments('memo_cd');
            $table->unsignedInteger('co_cd');
            $table->string('title', 100);
            $table->string('content', 2000);
            $table->boolean('del_flg')->default(false);
            $table->timestamp('create_time')->useCurrent();
            $table->timestamp('update_time')->nullable();

            $table->foreign('co_cd')->references('co_cd')->on('customers')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('co_memos');
    }
};

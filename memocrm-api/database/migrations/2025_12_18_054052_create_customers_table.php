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
        Schema::create('customers', function (Blueprint $table) {
            $table->increments('co_cd');
            $table->integer('user_cd');
            $table->string('co_name', 100);
            $table->string('co_address', 200);
            $table->string('co_tanto_name', 100);
            $table->string('co_tanto_tel', 15);
            $table->boolean('del_flg')->default(false);
            $table->timestamp('create_time')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('update_time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};

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
        Schema::create('tags', function (Blueprint $table) {
            $table->increments('tag_cd');
            $table->unsignedInteger('user_cd');
            $table->string('tag_name', 100);
            $table->boolean('del_flg')->default(false);
            $table->dateTime('create_time')->useCurrent();
            $table->dateTime('update_time')->nullable();

            $table->foreign('user_cd')->references('user_cd')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};

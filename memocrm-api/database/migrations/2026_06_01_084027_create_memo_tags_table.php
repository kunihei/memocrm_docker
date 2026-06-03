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
        Schema::create('memo_tags', function (Blueprint $table) {
            $table->unsignedInteger('co_cd');
            $table->unsignedInteger('memo_cd');
            $table->unsignedInteger('tag_cd');
            $table->dateTime('create_time')->useCurrent();

            $table->primary(['co_cd', 'memo_cd', 'tag_cd']);

            $table->foreign(['co_cd', 'memo_cd'])->references(['co_cd', 'memo_cd'])->on('co_memos')->cascadeOnDelete();
            $table->foreign('tag_cd')->references('tag_cd')->on('tags')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memo_tags');
    }
};

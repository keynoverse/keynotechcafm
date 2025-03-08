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
        Schema::create('asset_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            // Nested set columns
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->integer('_lft')->unsigned();
            $table->integer('_rgt')->unsigned();
            $table->timestamps();

            $table->foreign('parent_id')
                ->references('id')
                ->on('asset_categories')
                ->onDelete('cascade');
            $table->index(['_lft', '_rgt', 'parent_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_categories');
    }
};

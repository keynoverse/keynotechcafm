<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('asset_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->json('metadata')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->integer('_lft')->default(0);
            $table->integer('_rgt')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('parent_id')
                  ->references('id')
                  ->on('asset_categories')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('asset_categories');
    }
}; 
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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('asset_categories')->onDelete('restrict');
            $table->foreignId('space_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('model')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('serial_number')->nullable();
            $table->timestamp('purchase_date')->nullable();
            $table->decimal('purchase_cost', 12, 2)->nullable();
            $table->timestamp('warranty_expiry')->nullable();
            $table->integer('maintenance_frequency')->nullable();
            $table->string('maintenance_unit')->nullable();
            $table->timestamp('next_maintenance_date')->nullable();
            $table->string('status')->default('active');
            $table->string('condition')->default('good');
            $table->string('criticality')->default('low');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};

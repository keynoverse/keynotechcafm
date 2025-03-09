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
        Schema::table('users', function (Blueprint $table) {
            $table->string('employee_id')->nullable()->after('remember_token');
            $table->string('department')->nullable();
            $table->string('position')->nullable();
            $table->string('phone')->nullable();
            $table->string('status')->default('active');
            $table->json('metadata')->nullable();
            $table->string('role')->default('user');
            $table->timestamp('last_login')->nullable();
            $table->json('preferences')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'employee_id',
                'department',
                'position',
                'phone',
                'status',
                'metadata',
                'role',
                'last_login',
                'preferences',
            ]);
            $table->dropSoftDeletes();
        });
    }
};

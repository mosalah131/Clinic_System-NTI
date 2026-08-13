<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TABLE: users
 *
 * Every human being in the system lives in this one table. The "role" column
 * decides what they are allowed to do (admin / doctor / reception / patient).
 * Admin and Reception do not need their own profile tables - all the data they
 * need is already here.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->enum('role', ['admin', 'doctor', 'reception', 'patient']);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->rememberToken();
            $table->softDeletes();   // adds the "deleted_at" column
            $table->timestamps();    // adds "created_at" and "updated_at"
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};

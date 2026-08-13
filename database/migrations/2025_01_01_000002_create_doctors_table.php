<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TABLE: doctors
 *
 * The professional profile of a user whose role is "doctor".
 *   users  1 --- 1  doctors        (One-to-One, that is why user_id is unique)
 *   departments 1 --- many doctors (One-to-Many)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->string('specialization');
            $table->decimal('consultation_fee', 8, 2);
            $table->text('bio')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};

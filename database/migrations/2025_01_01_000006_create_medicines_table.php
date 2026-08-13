<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TABLE: medicines
 *
 * The clinic's medicine catalogue. A doctor SELECTS from this list instead of
 * typing a medicine name by hand.
 *
 * NOTE: name / description / soft delete come straight from the PDF schema.
 * category, price, quantity and status are extra columns added so the existing
 * "Medicines" screen (which shows those columns) works with real data.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->decimal('price', 8, 2)->default(0);
            $table->unsignedInteger('quantity')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
